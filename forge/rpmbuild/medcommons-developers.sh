#!/bin/sh

VERS=`python vers.py SPECS/medcommons-developers.spec` ||
    exit 1

mv SOURCES/medcommons-developers-*.tar.gz HISTORY || :
mv SOURCES/medcommons-developers-* HISTORY || :
mv RPMS/noarch/medcommons-developers-*.noarch.rpm HISTORY || :

cp -R ~/work/services/trunk/forge/developers SOURCES/medcommons-developers-$VERS &&
find SOURCES/medcommons-developers-$VERS -type d -name ".svn" -exec rm -rf {} \;

cp SPECS/medcommons-developers.spec HISTORY/medcommons-developers-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-developers-$VERS.tar.gz medcommons-developers-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-developers.spec &&
ls -l RPMS/noarch/medcommons-developers-$VERS-1.noarch.rpm
