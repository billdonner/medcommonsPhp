#!/bin/sh

VERS=`python vers.py SPECS/medcommons-DDL.spec` ||
    exit 1

DST=SOURCES/medcommons-DDL-$VERS

mv SOURCES/medcommons-DDL-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-DDL-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-DDL-* || :

mkdir -p $DST/opt/gateway/webapps/DDL $DST/etc/httpd/conf.d &&
pushd $DST/opt/gateway/webapps/DDL &&
jar xf ~/work/router/components/dicomclient/trunk/build/jnlp/DDL.war &&
popd &&
cp ~/work/services/trunk/config/linux/common/etc/httpd/conf.d/ddl_ajp.conf $DST/etc/httpd/conf.d &&	
cp SPECS/medcommons-DDL.spec HISTORY/medcommons-DDL-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-DDL-$VERS.tar.gz medcommons-DDL-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-DDL.spec &&
ls -l RPMS/noarch/medcommons-DDL-$VERS-1.noarch.rpm

