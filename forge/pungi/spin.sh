ARCH=i386
if [ -z $1 ]; then
    echo "usage: sh spin.sh <version>"
    exit 1
fi
VERS=$1
rm -rf MedCommons spin
mkdir spin &&
sed -i -e "s|^version = [0-9.]*|version = ${VERS}|" pungi.conf &&
pungi -c pungi.conf --all-stages &&
pushd spin &&
mv ../MedCommons/$VERS/Appliance/$ARCH/iso/*.iso . &&
split -d -b 50m MedCommons-$VERS-$ARCH-DVD.iso MedCommons-$VERS-$ARCH-DVD.iso. &&
python ../page.py $VERS &&
ls -1 | sed -e "s|\(.*\)|retry s3.py put appliance.medcommons.net/${VERS}/\1 \1|" | sh &&
ls -1 | sed -e "s|\(.*\)|retry s3.py share appliance.medcommons.net/${VERS}/\1 owner:FULL_CONTROL all:READ|" | sh &&
popd &&
echo "spin complete!"

