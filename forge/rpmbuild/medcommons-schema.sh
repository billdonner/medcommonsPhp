#!/bin/sh

VERS=`python vers.py SPECS/medcommons-schema.spec` ||
    exit 1

DST=SOURCES/medcommons-schema-$VERS

mv SOURCES/medcommons-schema-*.tar.gz HISTORY || :
mv SOURCES/medcommons-schema-* HISTORY || :
mv RPMS/noarch/medcommons-schema-*.noarch.rpm HISTORY || :

mkdir -p $DST/root/schema &&
find ~/work/services/trunk/schema -type f -regex ".*\.\(sql\|sh\|py\)" -exec cp {} $DST/root/schema \; &&
cp SPECS/medcommons-schema.spec HISTORY/medcommons-schema-$VERS.spec &&

pushd SOURCES &&
tar czf medcommons-schema-$VERS.tar.gz medcommons-schema-$VERS &&
popd &&
rpmbuild -bb SPECS/medcommons-schema.spec &&
ls -l RPMS/noarch/medcommons-schema-$VERS-1.noarch.rpm

