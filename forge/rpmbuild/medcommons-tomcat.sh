#!/bin/sh

VERS=6.0.14
SRC=~/Work
DST=SOURCES/medcommons-tomcat-$VERS

mv SOURCES/medcommons-tomcat-*.tar.gz HISTORY || :
mv SRPMS/medcommons-tomcat-*.src.rpm HISTORY || :
mv RPMS/medcommons-tomcat-*.noarch.rpm HISTORY || :

mkdir -p $DST/etc/init.d $DST/opt $DST/var/apache-tomcat/logs $DST/var/apache-tomcat/temp $DST/var/apache-tomcat/webapps $DST/var/apache-tomcat/work &&
cp ~/work/services/trunk/config/linux/common/etc/init.d/tomcat $DST/etc/init.d &&
pushd SOURCES &&
tar xzf apache-tomcat-$VERS.tar.gz &&
popd &&
mv SOURCES/apache-tomcat-$VERS $DST/opt &&
cp -R $DST/opt/apache-tomcat-$VERS/conf $DST/var/apache-tomcat &&
cp SPECS/medcommons-tomcat.spec HISTORY/medcommons-tomcat-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-tomcat-$VERS.tar.gz medcommons-tomcat-$VERS &&
popd &&
rpmbuild -ba SPECS/medcommons-tomcat.spec &&
ls -l RPMS/medcommons-tomcat-$VERS-1.noarch.rpm
