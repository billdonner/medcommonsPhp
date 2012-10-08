#!/bin/bash

MYSQLDB="mcx"

mysqladmin --defaults-extra-file=/root/.mcdb/mcx.ini create $MYSQLDB

FILES=`ls -1 ???_*.sql`

for i in mcextio.sql $FILES permissions.sql;
do
  bn=`basename $i .sql`
  if [ -O "${bn}.py" ]; then
    if python ${bn}.py ; then
      echo "${bn}.py executed successfully."
    else
      echo
      echo "${bn}.py failed - please investigate."
      echo
      exit 1;
    fi
  fi
  if mysql --defaults-extra-file=/root/.mcdb/mcx.ini $MYSQLDB < $i ; then
    echo "$i executed successfully."
  else
    echo
    echo "$i failed - please investigate."
    echo
    exit 1;
  fi
  if [ -O "${bn}.sh" ]; then
    if sh ${bn}.sh ; then
      echo "${bn}.sh executed successfully."
    else
      echo
      echo "${bn}.sh failed - please investigate."
      echo
      exit 1;
    fi
  fi
done
