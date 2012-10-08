<?
 /**
  * JSON web service to initializes an account for use with HealthBook.  
  * Specifically, this means we create a HealthBook group and add consents for it.
  *
  * @param accid - the account to initialize for HealthBook access.
  * @param fbid - the facebook user that is being initialized
  */
  require_once "JSON.php";
  require_once 'settings.php';
  require_once "utils.inc.php";
  require_once "mc.inc.php";
  require_once "alib.inc.php";

  nocache();

  $json = new Services_JSON();
  $res = new stdClass;
  $res->status="ok";
  try {
    $accid = req('accid');
    $fbid = req('fbid');
    $appcode = req('APPCODE');

    if(($accid == null) || !is_valid_mcid($accid,true))
      throw new Exception("parameter accid is missing or incorrect format");

    if(($appcode == null) || ($appcode == ""))
      throw new Exception("parameter APPCODE is missing or incorrect format");

    // TODO: Enable this once all healthbook instances are up to date
    // if(($fbid == null) || (preg_match("/[0-9]*/",$fbid)!==1))
    // throw new Exception("parameter fbid is missing or incorrect format");

    dbg("initializing user $accid for access to facebook as fbid=$fbid");

    // Check that the appliance has Facebook enabled as an IDP
    $idps = pdo_query("select * from identity_providers where name = 'Facebook' and source_id = ?", $appcode);
    if($idps === false)
      throw new Exception("Unable to query for identity_providers"); 

    if(count($idps) === 0)
      throw new Exception("Facebook is not enabled as an Identity Provider for this appliance"); 

    // Remember the facebook idp id
    $fbIdpId = $idps[0]->id;

    // Does the user already have a group for HealthBook?
    $hbGroups = pdo_query("select gi.* from groupinstances gi, groupadmins a
                            where a.adminaccid = ? and a.groupinstanceid = gi.groupinstanceid
                            and gi.name = 'My HealthBook Care Team'", $accid);

    // Maybe this user is reconnecting - check!
    if(count($hbGroups)==0) { // group does not exist yet - this user is uninitialized
      // Allocate the mcid
      global $URL, $NS;
      $client = new SoapClient(null, array('location' => $URL, 'uri' => $NS));
      $res->groupAcctId = $client->next_mcid();

      // Add the group
      $groupInstanceId = pdo_execute("insert into groupinstances 
                                        (groupinstanceid, name, groupLogo, adminUrl, memberUrl, accid)
                                        values (NULL, 'My HealthBook Care Team', '','','',?)", array($res->groupAcctId));

      // Add the entry in user table for the group
      pdo_execute("INSERT INTO users (mcid,acctype) VALUES (?,'GROUP')", array($res->groupAcctId));

      // Add user as admin of group - note: this is the only way the user is linked to the group at all!
      pdo_execute("INSERT INTO groupadmins (groupinstanceid,adminaccid,comment) VALUES (?,?,'')", 
        array($groupInstanceId,$accid));

      // Add consent for group to access user's account
      $consentResult = 
        file_get_contents(gpath('Commons_Url')."/ws/grantAccountAccess.php?accessTo=".$accid."&accessBy=".$res->groupAcctId."&rights=RW");
    }
    else { // Existing group - just return it
      $res->groupAcctId = $hbGroups[0]->accid;
    }

    // Enable Facebook as IDP for this user
    // TODO:  Remove if when all appliances are up to date 
    if($fbid !== null) {
      pdo_execute("REPLACE INTO external_users (mcid, provider_id, username) values (?,?,?)",
                  array($accid, $fbIdpId, $fbid));
    }

    $res->gw = allocate_gateway($accid);
  }
  catch(Exception $e) {
    $res->status = "failed";
    $res->error = $e->getMessage();
  }
  echo $json->encode($res);
?>
