#!/bin/bash
. shared/lib/common/dependencies/cilib*.sh || {
  ant init
  . shared/lib/common/dependencies/cilib*.sh || { echo "Internal error: cannot load cilib.sh"; exit 1; }
}

TS=`date`
msg "Performing Integration Build of Identity Server at $TS"

(
  svn update . | tee svn.log || err "Unable to update identity server code."

  if svn_log_has_no_changes svn.log;
  then
    msg "No changes - no build required.";
    exit 0;
  fi

  msg "Building ..."

  ant clean || err "Unable to clean identity server"
  ant || err "Unable to build identity server"

  APPLIANCE_MODE=false
  [ "$1" == "-a" ] && {
    APPLIANCE_MODE=true
  }

  if $APPLIANCE_MODE; 
  then
    export DIST_SOURCE="local"
    ./update_appliance.sh
  else
    cp dist/identity.war ~/test_install_dir/tomcat/webapps
    cd ../..
    if [ -e IdentityConfig.properties ];
    then
      cp IdentityConfig.properties ~/test_install_dir/tomcat/conf
    fi
  fi
) | tee build.log 2>&1 || { 
  msg "Build failed ... sending spam."
  exit 1;
}

# If arrived here, everything went OK
msg "Build succeeded."
