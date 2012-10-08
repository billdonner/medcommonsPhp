#!/bin/bash
##############################################################
#
# MedCommons Schema Deployment Script
#
# Copyright MedCommons Inc. 2006
#
# $Id: apply.sh 5281 2008-04-30 06:45:24Z ssadedin $
#
# Author:  ssadedin@medcommons.net, terry@medcommons.net
#
##############################################################
USAGE="
Usage: apply.sh [ -s ] [-r <start rev>:<end rev>] <database>

  -s  :   write output to std out instead of executing
  -q  :   don't ask questions, just do it
";


function err() {
  if $SOUT;
  then
    echo "/* ERROR: $* */";
  else
    echo "ERROR: $*";
  fi
}

function out() {
  $SOUT || {
    echo "$*";
  }
}

REV=""
REVEND=""
SOUT=false
QUIET=false

i=0

# Chomp the arguments
while getopts "sqr:" option; 
do
  case $option in
    s ) SOUT="true"; let 'i++';;
    q ) QUIET="true"; let 'i++';;
    r ) REVEND=${OPTARG#*:};
        REV=${OPTARG%:*}; let 'i+=2';;
    * ) echo "$USAGE"; exit 1;;
  esac
done

while [ $i -gt 0 ];
do
  shift;
  let 'i--';
done 

if [ -z "$1" ] || [ ! -z "$2" ];
then
  echo "1: $1  2: $2"
  echo "$USAGE";
  exit 1;
fi

DB=$1

out
out "Probing for mysql user ..."
out

if mysql -u root mcx --execute "" > /dev/null 2> probe.txt;
then
  MYSQL_USER=root;
  MYSQL_DB=mcx;
  MYSQL_HOST="";
elif [ ! -z "$MYSQL_PASSWORD" ] &&  mysql -u root -p$MYSQL_PASSWORD mcx --execute "" > /dev/null 2>> probe.txt;
then
  MYSQL_USER=root
  MYSQL_DB=mcx
  MYSQL_HOST="";
  MYSQL_PASSWORD="-p$MYSQL_PASSWORD"
elif [ ! -z "$MYSQL_PASSWORD" ] &&  mysql -u admin -p$MYSQL_PASSWORD mcx --execute "" > /dev/null 2>> probe.txt;
then
  MYSQL_USER=admin
  MYSQL_DB=mcx
  MYSQL_HOST="";
  MYSQL_PASSWORD="-p$MYSQL_PASSWORD"
elif mysql -u medcommons -h mysql.internal mcx --execute "" > /dev/null 2>> probe.txt;
then
  MYSQL_USER=medcommons
  MYSQL_DB=mcx
  MYSQL_HOST="-h mysql.internal";
else
  err
  err "I couldn't figure out a database connection parameter set that worked... sorry."
  err
  err "Here are the errors received: "
  err
  cat probe.txt
  rm probe.txt
  exit 1;
fi

rm probe.txt

out
out "Found database $MYSQL_DB with user $MYSQL_USER"
out

# Check if specified db exists, otherwise assume always MCX
if mysql -u $MYSQL_USER $MYSQL_HOST $MYSQL_PASSWORD $DB --execute "" 2>/dev/null;
then
  MYSQL_DB=$DB;
else
  MYSQL_DB="mcx";
fi

if [ -z "$REV" ];
then
  REV=`mysql -u $MYSQL_USER $MYSQL_HOST $MYSQL_PASSWORD mcx --execute 'select value + 1 from mcproperties where property="'${DB}'_revision"' --skip-column-names --batch`
fi

if [ -z "$REV" ]
then
  REV="1311"
fi

if [ -z "$REVEND" ];
then
  REVEND=`svn info 2>/dev/null | grep 'Revision:' | awk '{print $2}'`
fi

if [ "$REV" -lt 1311 ];
then
  err "WARNING: using this script with revisions earlier than 1311 may have unreliable results."  
  err
fi

out;
out "Fetching shema modifications for svn range $REV to $REVEND ...";
out; 

FILES=`svn log -v -r $REV:$REVEND 2>/dev/null | grep "^ *A " | awk '{print $2}' | grep  -o "[0-9]\{3,4\}.*$DB.\(sql\|py\)" | sort -n | uniq`

for i in $FILES;
do
  if [ -e $i ];
  then
    TMPFILES="$TMPFILES
$i"
  fi
done

FILES="$TMPFILES"

if [ -z "$FILES" ]
then
  exit 0
fi

DONE_ALL="y"
$SOUT && DONE_ALL="n"

out 
out "The following files will be executed in database $MYSQL_DB:"
out "$FILES"
out
for i in $FILES;
do
  $SOUT || $QUIET || read -p "Apply $i ? (y/n)"
  if $SOUT || $QUIET || [ "y" == "$REPLY" ];
  then
    # If it's a python script then just run it
    if [ ${i#*.} == "py" ];
    then
      python $i;
    else
      # Regular SQL - execute or echo as required
      out
      if $SOUT;
      then
        cat $i
        echo
      else
        mysql -u $MYSQL_USER $MYSQL_HOST $MYSQL_PASSWORD $MYSQL_DB < $i;
      fi
    fi
  else
    DONE_ALL="n"
  fi
  echo
done

# Update the revision number in the database

if [ "y" == "$DONE_ALL" ]
then
  if [ "$REVEND" == "HEAD" ];
  then
    REVEND=`svn info . | grep Revision | awk '{print $2'}`
  fi

  $QUIET || read -p "Update stored schema revision to $REVEND (y/n)? "
  if $QUIET || [ "$REPLY" == "y" ];
  then
    mysql -u $MYSQL_USER $MYSQL_HOST $MYSQL_PASSWORD mcx --execute "UPDATE mcproperties SET value = $REVEND WHERE property='${DB}_revision'"
  fi
fi
