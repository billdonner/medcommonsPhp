#!/bin/bash
###########################################################
#
# Subversion Trigger Script
#
# This script is invoked for every single checkin on svn in
# the /service repo.  It attempts to build / update all the 
# essential system components.
#
# It also performs a small build procedure that minifies
# several javascript files and checks them in so that they
# are updated in the real distribution.
#
# Note that some of the commands below use sudo, so if you want
# it to work unsupervised you need to have a whole bunch of
# default sudo rules with NOPASSWD attribute set.
#
# $Id: update_central.sh 7300 2009-05-20 07:30:08Z ssadedin $
#
############################################################

##
# Upgrade the schema with any changes added in svn
##
function update_schema() {
  # Update schema
  echo
  echo "Updating schema ..."
  echo
  pushd /home/ci/build/schema
  svn update .
  ./apply.sh -q mcx
  ./apply.sh -q mcidentity
  popd
}

##
# Updates the console code and if any changes found
# deployes it to /var/www/console and then runs the
# console unit tests.  Test failures are reported by email.
##
function update_console() {
  # Update console templates, media
  cd /home/ci/build/console/
  svn update . > consolesvn.log && {
    if ! grep -q  "^U \|^A " consolesvn.log;
    then
      echo "No console updates."
    else
      svn info . | grep Revision | sed 's/[^0-9]//g' > media/revision.txt
      svn commit -m "Auto update of console revision tag" media/revision.txt
      cd ..
      sudo svn --force export console /var/www/console/

      # restart httpd
      sudo /etc/init.d/httpd restart

      sleep 1 

      # publish latest changes
      curl -d "" http://localhost/cgi-bin/publish

      # Run console tests
      cd /var/www/console
      python manage.py test > /tmp/console-tests.txt 2>&1 || { 
        echo
        echo "One or more console tests failed"
        echo
        [ "nobody" != "$SPAM_LIST" ] && {
          cat /tmp/console-tests.txt | mail -s "Console Test Failure - Please Investigate" $SPAM_LIST
        }
      }
    fi
  }
}

##
# Echoes the svn revision for each silo (/acct, /secure, /www)
# out to a file revision.txt in each location.  These are 
# checked in later on if any changes are made
#
# Needs changes available in svnchanges.log
##
function update_revision_tags() {
  # Update the revision tag files
  if grep -q "acct/" svnchanges.log;
  then
    echo "Updating /acct revision"
    pushd /home/ci/build/central/acct
    svn info . | grep Revision | sed 's/[^0-9]//g' > revision.txt
    COMMIT=true;
    popd
  fi

  if grep -q "web/" svnchanges.log;
  then
    echo "Updating /web revision"
    pushd /home/ci/build/central/web
    svn info . | grep Revision | sed 's/[^0-9]//g' > revision.txt
    COMMIT=true;
    popd
  fi

  if grep -q "secure/" svnchanges.log;
  then
    echo "Updating /secure revision"
    pushd /home/ci/build/central/secure
    svn info . | grep Revision | sed 's/[^0-9]//g' > revision.txt
    COMMIT=true;
    popd
  fi
}

##
# Uses deploy.py to copy changes up to normal places
# but adds some tweaks specific to CI
##
function update_php() {
  # deploy
  cd /home/ci/build/central/

  echo "Updating PHP  ..."
  # Save our urls.inc.php from being flattened
  cp /var/www/php/urls.inc.php /tmp
  svn update . 
  sudo /usr/bin/python /home/ci/build/central/deploy.py verbose
  cp /tmp/urls.inc.php /var/www/php/urls.inc.php

  # Copy modpay stuff up - deploy.py will not do that
  cp -uv modpay/* /var/www/html/modpay

  # Fix broken default urls.inc.php
  sed -i.bak 's/?>/$GLOBALS["use_combined_files"]=true;\n$GLOBALS["Default_Repository"]="https:\/\/ci.myhealthespace.com\/router";\n?>/' /var/www/php/urls.inc.php

  update_site

  update_facebook

}

function update_facebook() {
  echo
  echo "Updating facebook code ..."
  pushd /var/www/html/facebook/000
  svn update . 
  popd
}

function update_timc() {
  echo
  echo "Updating TIMC app"
  pushd /home/ci/build/TIMC
  ./ci.sh
  popd
}

##
# Deploy PHP code for main central site
##
function update_site() {
  echo "Updating site code ..."
  pushd ~/build/central
  sudo cp -R site /var/www/html/
  popd
}

##
# Deploy PHP code for globals
##
function update_globals() {
  echo "Updating site code ..."
  pushd ~/build/central/global
  sudo /bin/cp -R login /var/www/html/
  popd
}

##
# Send emails and kick off Badboy Tests
##
function notify_and_test() {
  changers=`cat svnchanges.log | grep "^r[0-9]\{3,4\}.*line" | awk '{ print $3 "@medcommons.net"}' | sort -u | awk 'BEGIN{X="";} {X=X $1 ","} END { print X;}'`
  changers="$changers,ssadedin@medcommons.net"

  echo "changers are : $changers" >> svnchanges.log

  encoded_changers=`printf "$changers" |  groovy -e 'println URLEncoder.encode(System.in.text)'`

  echo "encoded changers are : $encoded_changers";

  curl 'http://mcpurple05.homeip.net:8030/bbq/addScript?planId=11894784&notifyEmail='"$encoded_changers" > bbq.html
}


##
# MAIN UPDATE ROUTINE
#
# Note: Do everything in subshell so as to allow us to catch errors
##
(

  # Set default email notification list here
  : ${SPAM_LIST:="ssadedin@badboy.com.au"} 

  cd /home/ci/build/central

  svn log -v -r BASE:HEAD | awk 'BEGIN { linecount=0; }  /----/ { linecount=0; }  { linecount++; if(linecount >= 2) { print $0; } }' > svnchanges.log

  TS=`date`

  echo
  echo "=================================================="
  echo "Running update at $TS ....." 

  svn update . > svn.log && {
      echo "==="
      cat svn.log
      echo "==="
      # No updates?
      if ! grep -q  "^U \|^A " svn.log;
      then
              echo "No PHP code updates."
      else
          # If the account javascript / css files were updated then run the script to make the consolidated files
          if [ ! -e /home/ci/build/central/update_central.running ] ;
          then
              echo > /home/ci/build/central/update_central.running

              COMMIT=false;

              # If any javascript or css was change, rebuild the compiled files
              if grep -q "acct/.*\..*s$" svnchanges.log 
              then
                  pushd /home/ci/build/central/acct
                  ./mk_all.sh
                  COMMIT=true;
                  popd
              else 
                  echo "No changes to acct js or css"
              fi

              update_revision_tags

              # Commit all changes that we made
              if $COMMIT;
              then
                echo "Committing ..."
                svn commit -m "Auto update of revision tag, acct_all.css, acct_all.js and settings-min.js" ./acct/acct_all.css ./acct/acct_all.js ./acct/settings-min.js ./acct/revision.txt ./secure/revision.txt 
              fi
                                      
              # Notify about changes
              mail -s "PHP Checkin Notification" sdoyle@medcommons.net ssadedin@medcommons.net < svnchanges.log

              update_php

              notify_and_test

              # Remove flag signalling that we are running
              echo "Removing running flag";
              rm /home/ci/build/central/update_central.running
          fi
      fi
  }

  # Update schema
  update_schema

  # Build identity server, if necessary
  cd /home/ci/build/identity
  ./ci.sh -a > ci.log
   
  update_timc

  update_globals
  # Deploy latest code to console, run publish
  update_console

) >> /home/ci/update_central.log 2>&1

