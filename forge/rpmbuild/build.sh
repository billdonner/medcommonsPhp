echo "" &&
echo "Medcommons package/repo build of `date` on `hostname`" &&
echo "" &&

pushd ~/work &&

echo "" &&
echo ">>->> SVN Update" &&
echo "" &&
svn update router/components &&
svn update router/demo &&
svn update services &&

echo "" &&
echo ">>->> Router Build" &&
echo "" &&
cd ~/work/router/demo &&
ant real-clean &&
ant update-version &&
ant installer-tomcat &&

echo "" &&
echo ">>->> Router Components Build" &&
echo "" &&
cd ~/work/router/components/dicomclient/trunk &&
gant cleanall &&
gant &&

echo "" &&
echo ">>->> Identity Build" &&
echo "" &&
cd ~/work/services/trunk/java/identity &&
ant clean &&
ant &&
popd &&

echo "" &&
echo ">>->> Package Builds" &&
echo "" &&

#
# These unconditionally bump the version number in the SPECS/X.spec file and build the RPM. 
#  Uncomment config to build/distribute changes to /usr/bin/medcommons-commands
#  Uncommen developers to build/distribute changes to dev/administrator individuals/credentials
#  medcommons and medcommons-tomcat (possibly also jai and jsvc don't currently build on forge).
#
sh medcommons-DDL.sh &&
#sh medcommons-config.sh &&
sh medcommons-console.sh &&
sh medcommons-developers.sh &&
sh medcommons-gateway.sh &&
sh medcommons-identity.sh &&
#sh medcommons-jai.sh &&
#sh medcommons-jsvc.sh &&
sh medcommons-mc_backups.sh &&
sh medcommons-mc_locals.sh &&
sh medcommons-php.sh &&
sh medcommons-schema.sh &&
#sh medcommons-tomcat.sh &&
#sh medcommons.sh &&

echo "" &&
echo ">>->> Publish" &&
echo "" &&
sh publish.sh &&
echo "yum repository on appliance.medcommons.net successfully updated."
