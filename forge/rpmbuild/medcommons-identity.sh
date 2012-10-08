#!/bin/sh

VERS=`python vers.py SPECS/medcommons-identity.spec` ||
    exit 1

DST=SOURCES/medcommons-identity-$VERS

mv SOURCES/medcommons-identity-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-identity-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-identity-* || :

mkdir -p $DST/var/apache-tomcat/webapps/identity $DST/var/apache-tomcat/conf $DST/etc/httpd/conf.d &&
pushd $DST/var/apache-tomcat/webapps/identity &&
jar xf ~/work/services/trunk/java/identity/dist/identity.war &&
popd &&
cp ~/work/services/trunk/config/linux/common/etc/httpd/conf.d/identity_ajp.conf $DST/etc/httpd/conf.d &&
cp SPECS/medcommons-identity.spec HISTORY/medcommons-identity-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-identity-$VERS.tar.gz medcommons-identity-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-identity.spec &&
ls -l RPMS/noarch/medcommons-identity-$VERS-1.noarch.rpm

