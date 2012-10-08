#!/bin/bash
export PATH=$PATH:/cygdrive/c/apachefriends/xampp/mysql/bin
export PATH=$PATH:/cygdrive/c/xampp/mysql/bin
export PATH=$PATH:/usr/local/mysql/bin
export PATH=/Volumes/Macintosh\ HD/Applications/MAMP/Library/bin:$PATH

[ -z $MYSQLHOST ] && {
  MYSQLHOST="-h $MYSQLHOST";
}

[ -z $MYSQLUSER ] && {
  MYSQLUSER="root";
}
[ ! -z $MYSQLPASS ] && {
  MYSQLPASS="-p$MYSQLPASS";
}

[ -z $MYSQLDB ] && {
  MYSQLDB="mcx";
}

mysqladmin -u $MYSQLUSER $MYSQLPASS -f drop $MYSQLDB > /dev/null 2>&1
mysqladmin -u $MYSQLUSER $MYSQLPASS create $MYSQLDB

SQL_FILES=`ls *.sql | grep "^[0-9].*sql"`
for i in mcextio.sql $SQL_FILES;
do
  if mysql -u $MYSQLUSER $MYSQLHOST $MYSQLPASS $MYSQLDB < $i ;
  then
    echo
    echo "$i executed successfully."
    echo
  else
    echo
    echo "$i failed - please investigate."
    echo
    exit 1;
  fi
done
