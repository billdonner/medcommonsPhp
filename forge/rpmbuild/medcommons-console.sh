#!/bin/sh

VERS=`python vers.py SPECS/medcommons-console.spec` ||
    exit 1

DST=SOURCES/medcommons-console-$VERS

mv SOURCES/medcommons-console-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-console-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-console-* || :

mkdir -p $DST/var/www/cgi-bin $DST/var/www/mc_templates $DST/var/www/php $DST/opt/gateway/conf $DST/etc/httpd/conf.d &&
cp -a ~/work/services/trunk/console $DST/var/www &&
cp ~/work/services/trunk/console/console.conf $DST/etc/httpd/conf.d &&
cp ~/work/services/trunk/console/publish $DST/var/www/cgi-bin &&
touch $DST/var/www/php/local_settings.php &&
cp SPECS/medcommons-console.spec HISTORY/medcommons-console-$VERS.spec &&

pushd SOURCES &&
find medcommons-console-$VERS -name .svn -exec rm -rf {} \; || true &&
tar czf medcommons-console-$VERS.tar.gz medcommons-console-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-console.spec &&
ls -l RPMS/noarch/medcommons-console-$VERS-1.noarch.rpm
