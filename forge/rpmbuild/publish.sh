rm -rf ../medcommons/i386/* || :
for fn in `find RPMS -type f -name "*.rpm" | tr '\n' ' '`; do
  cp -a $fn ../medcommons/i386
done &&
createrepo ../medcommons/i386 &&
pushd ~/work/services/trunk/forge/medcommons/i386 &&
find . -type f -newer .lastpublish | sed -e 's|\./\(.*\)|retry s3.py put appliance.medcommons.net/0.3.12/dist/i386/\1 \1|' | sh &&
find . -type f -newer .lastpublish | sed -e 's|\./\(.*\)|retry s3.py share appliance.medcommons.net/0.3.12/dist/i386/\1 owner:FULL_CONTROL all:READ|' | sh &&
touch .lastpublish &&
popd
