ARCH=i386
if [ -z $1 ]; then
    echo "usage: sh push.sh <version>"
    exit 1
fi
VERS=$1
pushd updates/7/$ARCH
find . -type f | sed -e "s|\./\(.*\)|retry s3.py put appliance.medcommons.net/${VERS}/updates/${ARCH}/\1 ./\1|" | sh
find . -type f | sed -e "s|\./\(.*\)|retry s3.py share appliance.medcommons.net/${VERS}/updates/${ARCH}/\1 owner:FULL_CONTROL all:READ|" | sh
popd