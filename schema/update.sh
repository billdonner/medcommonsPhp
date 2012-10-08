#!/bin/bash
FILES=`svn update | awk '{print $2}' | grep "^[0-9].*sql"`
echo 
echo "The following files will be executed:"
echo
echo "$FILES"
echo
for i in $FILES;
do
	mysql -u root mcx < $i;
done
