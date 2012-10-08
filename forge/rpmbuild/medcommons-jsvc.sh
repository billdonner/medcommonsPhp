#!/bin/sh

VERS=6.0.14

mv SRPMS/medcommons-jsvc-*.src.rpm HISTORY || :
mv RPMS/medcommons-jsvc-*.noarch.rpm HISTORY || :

sed -i.bak -e "s|^Release:.*$|Release:\t${VERS}|" SPECS/medcommons-jsvc.spec &&
cp SPECS/medcommons-jsvc.spec HISTORY/medcommons-jsvc-$VERS.spec &&

rpmbuild -bs SPECS/medcommons-jsvc.spec &&
mock --no-clean SRPMS/medcommons-jsvc-$VERS-1.src.rpm &&
ls -l RPMS/i386/medcommons-jsvc-$VERS-1.i386.rpm
