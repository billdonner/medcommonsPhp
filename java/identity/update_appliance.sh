#!/bin/bash
#############################################################
# Deploy Identity War file to Identity Server on Appliance
# 
# By default it is copied from ci.myhealthespace.com, but if
# you set DIST_SOURCE it will copy from that location.  The 
# special value "local" indicates to deploy from local build.
#
#############################################################

# Location where war files will be sync'ed from 
: ${DIST_SOURCE:="ci@ci.myhealthespace.com:/home/ci/build/identity/dist"}

###############################################################
#
# Output an error message and exit
#
###############################################################
function err() {
  echo
  printf "$1\n"
  echo
  exit 1;
}

###############################################################
#
# Output a message
#
###############################################################
function msg() {
  echo
  echo "$1"
  echo
}

msg "Deploying Identity Server from Source: $DIST_SOURCE"
if [ "local" == "$DIST_SOURCE" ];
then
  msg "Deploying from local build ..."
else
  mkdir -p build/stage
  cd build/stage
  rsync -v --progress "$DIST_SOURCE/*.war" . || er "Unable to copy war files"
  cd ../..
fi
msg "Stopping Identity Server ..."
sudo /etc/init.d/tomcat stop || err "Unable to stop Identity Server"
sudo /bin/rm -rf /var/apache-tomcat/webapps/identity/ || err "Unable to remove old identity server files"
cd build/stage
msg "Deploying"
sudo /bin/cp identity.war /var/apache-tomcat/webapps || err "Unable to deploy identity server war file"
cd ../..
msg "Starting Identity Server ..."
sudo /etc/init.d/tomcat start || err "Unable to restart Identity Server"
