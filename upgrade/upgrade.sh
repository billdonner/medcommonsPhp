#!/bin/bash
##################################################
#
# Appliance Setup / Update Script
#
##################################################
#
[ ! -d php ] && svn co http://svn.medcommons.net:6666/svn/services/trunk/php
cd php
svn update . || exit 1;
cp /var/www/php/urls.inc.php include/urls.inc.php
sudo python ./deploy.py verbose || exit 1;
cd ..
[ ! -d gw ] && mkdir gw && svn co http://svn.medcommons.net:6666/svn/router/demo/bin gw/bin
cd gw/
svn update bin || exit 1;
./bin/update_appliance_gw.sh || exit 1;
cd ..
[ ! -d identity ] && svn co http://svn.medcommons.net:6666/svn/services/trunk/java/identity
cd identity/
svn update . || exit 1
./update_appliance.sh || exit 1;
cd ..
[ ! -d console ] && svn co http://svn.medcommons.net:6666/svn/services/trunk/console
svn update console
sudo svn export --force console/ /var/www/console

[ ! -d schema ] && svn co http://svn.medcommons.net:6666/svn/services/trunk/schema
svn update schema
cd schema
./apply.sh mcidentity
./apply.sh mcx
curl -d "" http://localhost/cgi-bin/publish
