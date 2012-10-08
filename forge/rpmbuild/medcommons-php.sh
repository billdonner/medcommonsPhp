#!/bin/sh

VERS=`python vers.py SPECS/medcommons-php.spec` ||
    exit 1

DST=SOURCES/medcommons-php-$VERS

mv SOURCES/medcommons-php-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-php-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-php-* || :

mkdir -p $DST &&
pushd $DST &&
cp -a ~/work/services/trunk/php . &&
cp ~/work/services/trunk/config/linux/common/etc/php.d/medcommons.ini . &&
cp ~/work/services/trunk/php/rewrite.conf . &&
popd &&
cp SPECS/medcommons-php.spec HISTORY/medcommons-php-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-php-$VERS.tar.gz medcommons-php-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-php.spec &&
ls -l RPMS/noarch/medcommons-php-$VERS-1.noarch.rpm

