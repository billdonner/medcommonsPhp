#!/bin/bash

MYSQLDB="mcx"

ls -1 ???_*.sql > /tmp/medcommons-schema.root.sql.post
FILES=`diff /tmp/medcommons-schema.root.sql.pre /tmp/medcommons-schema.root.sql.post | sed -n -e 's|> \([0-9][0-9][0-9]_.*\.sql\)|\1|p'`

for i in $FILES;
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

rm /tmp/medcommons-schema.root.sql.*
