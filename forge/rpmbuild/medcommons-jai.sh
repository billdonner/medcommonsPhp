#!/bin/sh

VERS=1.1.3

mv RPMS/i586/medcommons-jai-*.i586.rpm HISTORY || :

pushd SOURCES &&
tar czf medcommons-jai-$VERS.tar.gz medcommons-jai-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-jai.spec &&
ls -l RPMS/i586/medcommons-jai-$VERS-1.i586.rpm
