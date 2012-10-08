#!/bin/sh

VERS=`python vers.py SPECS/medcommons-mc_locals.spec` ||
    exit 1

DST=SOURCES/medcommons-mc_locals-$VERS

mv SOURCES/medcommons-mc_locals-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-mc_locals-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-mc_locals-* || :

mkdir -p $DST/usr/sbin $DST/usr/etc $DST/etc/init.d &&
cp ~/work/services/trunk/daemons/mc_locals/mc_locals.py $DST/usr/sbin &&
cp ~/work/services/trunk/daemons/mc_locals/init.d/mc_locals $DST/etc/init.d &&
cp ~/work/services/trunk/daemons/mc_locals/mc_locals.rc $DST/usr/etc &&
cp SPECS/medcommons-mc_locals.spec HISTORY/medcommons-mc_locals-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-mc_locals-$VERS.tar.gz medcommons-mc_locals-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-mc_locals.spec &&
ls -l RPMS/noarch/medcommons-mc_locals-$VERS-1.noarch.rpm
