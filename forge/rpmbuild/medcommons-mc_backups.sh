#!/bin/sh

VERS=`python vers.py SPECS/medcommons-mc_backups.spec` ||
    exit 1

DST=SOURCES/medcommons-mc_backups-$VERS

mv SOURCES/medcommons-mc_backups-*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-mc_backups-*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-mc_backups-* || :

mkdir -p $DST/opt/mc_backups $DST/etc/init.d &&
cp ~/work/services/trunk/daemons/mc_backups/*.py $DST/opt/mc_backups &&
cp ~/work/services/trunk/daemons/mc_backups/*.rc $DST/opt/mc_backups &&
cp ~/work/services/trunk/daemons/mc_backups/mc_backups $DST/etc/init.d &&
cp SPECS/medcommons-mc_backups.spec HISTORY/medcommons-mc_backups-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-mc_backups-$VERS.tar.gz medcommons-mc_backups-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-mc_backups.spec &&
ls -l RPMS/noarch/medcommons-mc_backups-$VERS-1.noarch.rpm
