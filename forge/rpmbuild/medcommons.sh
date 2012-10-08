#!/bin/sh

VERS=`python vers.py SPECS/medcommons.spec` ||
    exit 1

DST=SOURCES/medcommons-$VERS

mv SOURCES/medcommons-0*.tar.gz HISTORY || :
mv RPMS/noarch/medcommons-0*.noarch.rpm HISTORY || :
rm -rf SOURCES/medcommons-0* || :

mkdir -p $DST/etc &&
ROUT=`svn info ~/work/router | grep Revision | awk '{print $2'}`
SERV=`svn info ~/work/services | grep Revision | awk '{print $2'}`
echo "MedCommons Appliance $VERS ($ROUT/$SERV)" > $DST/etc/medcommons-release &&
cp SPECS/medcommons.spec HISTORY/medcommons-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-$VERS.tar.gz medcommons-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons.spec &&
ls -l RPMS/noarch/medcommons-$VERS-1.noarch.rpm
