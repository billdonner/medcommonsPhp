#!/bin/bash
[ -z $MYSQLUSER ] && {
  MYSQLUSER="root";
}

[ ! -z $MYSQLPASS ] && {
  MYSQLPASS="-p$MYSQLPASS";
}

[ -z $MYSQLDB ] && {
  MYSQLDB="mcx";
}

function ms() {
  echo "$1" | mysql -u $MYSQLUSER $MYSQLPASS $MYSQLDB;
}

echo
echo "Welcome to the 999999's script."
echo
echo "Available guids:"
echo
ms "select creation_time, guid from document";

echo
while [ -z "$REPLY" ];
do
  read -p "Enter the guid you would like set for nines: "
done
guid="$REPLY";

echo
echo "Removing old nines ..."
ms "delete from ccrlog where accid=9999999999999999";
ms "delete from users where mcid=9999999999999999";
echo
echo "Inserting new nines ..."
echo
# example guid 8e843a42e0838ba28e0cde8bcd32edf1dee79a8d
ms "insert into users values (9999999999999999, 'onemctest@gmail.com', '2cee3c63210829f3e9d3768dbe4c4d12ad784b31',0,NULL, 'Joe', 'User', NULL, NULL, 0,0);";
ms "insert into ccrlog (id, accid, idp, guid) values (NULL, 9999999999999999, 'POPS', '$guid');";
ms "update ccrlog set status='RED' where accid = '9999999999999999';";

echo
echo "Done."
echo
