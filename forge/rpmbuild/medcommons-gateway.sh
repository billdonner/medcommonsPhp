#!/bin/sh

VERS=`python vers.py SPECS/medcommons-gateway.spec` ||
    exit 1

DST=SOURCES/medcommons-gateway-$VERS

mv SOURCES/medcommons-gateway-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-gateway-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-gateway-* || :

mkdir -p $DST/opt/gateway/webapps/gateway $DST/opt/gateway/work $DST/opt/gateway/data/Repository $DST/opt/gateway/native/jai $DST/etc/init.d $DST/etc/httpd/conf.d $DST/opt/apache-tomcat/common/classes/ &&
cp -a ~/work/router/demo/build/installer/tomcat/* $DST/opt/gateway &&
cp -a ~/work/router/demo/build/installer/tomcat/common/classes/* $DST/opt/apache-tomcat/common/classes/ &&
pushd $DST/opt/gateway/webapps/gateway &&
mv ../gateway.war . &&
jar xf gateway.war &&
popd &&
pushd $DST/opt/gateway/native/jai &&
cp -a ~/work/router/demo/lib/jai/* .
popd &&
cp SOURCES/gateway $DST/etc/init.d &&
cp SOURCES/LocalBootParameters.properties SOURCES/server.xml $DST/opt/gateway/conf &&
cp ~/work/services/trunk/config/linux/common/etc/httpd/conf.d/gateway_ajp.conf $DST/etc/httpd/conf.d &&
cp ~/work/services/trunk/config/linux/common/etc/httpd/conf.d/router_ajp.conf $DST/etc/httpd/conf.d &&
cp SPECS/medcommons-gateway.spec HISTORY/medcommons-gateway-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-gateway-$VERS.tar.gz medcommons-gateway-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-gateway.spec &&
ls -l RPMS/noarch/medcommons-gateway-$VERS-1.noarch.rpm
