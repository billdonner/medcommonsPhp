ARCH=i386
if [ -z $1 ]; then
    echo "usage: sh spin.sh <version>"
    exit 1
fi
VERS=$1
pushd spin &&
split -d -b 50m MedCommons-$VERS-$ARCH-vmwarevm.7z MedCommons-$VERS-$ARCH-vmwarevm.7z. &&
python ../page.py $VERS &&
ls -1 *vmwarevm.7z* *.html | sed -e "s|\(.*\)|retry s3.py put appliance.medcommons.net/${VERS}/\1 \1|" | sh &&
ls -1 *vmwarevm.7z* *.html | sed -e "s|\(.*\)|retry s3.py share appliance.medcommons.net/${VERS}/\1 owner:FULL_CONTROL all:READ|" | sh &&
retry s3.py put appliance.medcommons.net index.html
retry s3.py share appliance.medcommons.net/index.html owner:FULL_CONTROL all:READ
popd &&
echo "spun complete!"
