#!/bin/bash
#
# DDL update script
#
OLDSVNREV=`svnversion -n  /home/ci/build/components/dicomclient/trunk | grep -o '^[0-9]*'`
(
  (
  source /home/ci/.bash_profile
  source /home/ci/build/components/ci/cilib.sh

  cd /home/ci/build/components/dicomclient/trunk

# Wait for previous build to finish ....
  while [ -e running ];
  do
    sleep 10;
  done
    
    
  echo "running" > running
  svn update > svn.log
  if svn_log_has_no_changes svn.log;
  then
    echo "No changes in dicomclient"
  else 
    # First, check if components are to be built
    pushd /home/ci/build/components/
    ./ci.sh || err "Unable to build components"
    popd

    cd /home/ci/build/components/dicomclient/trunk

    msg "Building ...."

    msg "### on failure will revert to revision $OLDSVNREV ###"

    date
    gant cleanall
    gant # strange obscure groovy compile errors seem to need double compile?
    gant clean && gant  || err "Unable to build DDL"
    # gant test-components || err "Failed unit tests"
    
    msg "Copying to gateway webapps ..."
    sudo /bin/rm -rf /opt/gateway/webapps/DDL /opt/gateway/webapps/DDL.war || err "Unable to remove old DDL webapp"
    sudo /bin/mkdir /opt/gateway/webapps/DDL || err "Unable to make new DDL dir"
    cd /opt/gateway/webapps/DDL || err "Unable to change dir to DDL webapp dir"
    sudo /usr/bin/jar xf /home/ci/build/components/dicomclient/trunk/build/jnlp/DDL.war || err "Unable to unzip DDL war file"
    msg "Finished"
    date
    echo "=========================================="
    echo "" | mail -s "DDL Build Succeeded" ssadedin@medcommons.net 

    nohup ssh badboy@mcpurple05.homeip.net '/c/mc/ddl/run_ddl_test.sh' > /home/ci/ddl_test.log 2>&1 &
  fi

  ) 2>&1 || {
    # revert the ddl directory to the old svn revision so that it will retry the
    # build upon next checkin
    svn update -r $OLDSVNREV /home/ci/build/components/dicomclient/trunk 
    cat update_rt.log |  mail -s "DDL Build failed" ssadedin@medcommons.net 
    echo "DDL Build Failed" | groovy /home/ci/smack.groovy 'ssadedin@gmail.com'
  }
) | tee update_rt.log
rm /home/ci/build/components/dicomclient/trunk/running
