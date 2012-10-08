<? 
require_once "utils.inc.php";
require_once "mc.inc.php";
require_once "JSON.php";
require_once "urls.inc.php";

/**
 * returns information about all users that have access to given account
 *
 * @throws Exception for all failure modes
 */
function get_sharing_info($accid) {

  if(!is_valid_mcid($accid,true)) 
    throw new Exception("Bad format for parameter accid:  $accid");

  dbg("get_sharing_info($accid)");
  $consents = array();

  dbg("commons url = ".$GLOBALS['Commons_Url']);

  // Get accounts that can access the given one
  $url = rtrim($GLOBALS['Commons_Url'],"/")."/ws/queryAccess.php?accid=$accid";

  // Get accounts that can access the given one
  $url = rtrim($GLOBALS['Commons_Url'],"/")."/ws/queryAccess.php?accid=$accid";
  dbg("querying sharing rights: $url");
  $queryAccessResult = file_get_contents($url);
  if(!$queryAccessResult) {
    throw new Exception("Unable to access sharing accounts for accid=$accid");
  }

  // Decode JSON
  $json = new Services_JSON();
  $result = $json->decode($queryAccessResult);
  if($result->status!="ok") {
    throw new Exception("Failed to retrieve sharing info for account $accid: ".$result->message);
  }

  // Get info for each row
  $shareAccounts = array();
  $shareAccess = array();
  $externalShares = array();
  foreach($result->result as $r) {
    if($r->es_id) { // External Id (OpenID)
      $externalShares[]=$r;
      //dbg("found external share {$r->es_id}");
    }
    else {
      $shareAccounts[]="'".$r->account_id."'";
      $shareAccess[strval($r->account_id)] = $r->rights;
    }
  }

  // No accounts sharing at all?
  if((count($shareAccounts)==0) && (count($externalShares)==0)) {
    $consents = new stdClass; // Hack - we want the result to encode as object when returned as json
    return $consents; // Return empty array
  }

  // Create a practice / group into which all individuals will be put
  if(!isset($consents["Individuals"])) {
    $p = new stdClass;
    $p->accounts = array();
    $p->practiceName = "Individuals";
    $p->groupAcctId = 0;
    $p->access = "";
    $consents["Individuals"] = $p;
  }

  if(count($shareAccounts)>0) {
    $shareAccountsIn = join($shareAccounts,",");

    // We don't know if the share account ids are groups or not
    // First we query them as groups
    $result = mysql_query("select gi.accid as group_acct_id, gi.groupinstanceid, gi.name, p.practiceid, p.practicename, u.*
                           from groupinstances gi
                              left join practice p on  p.providergroupid = gi.groupinstanceid
                              left join groupmembers m on m.groupinstanceid = gi.groupinstanceid 
                              left join users u on u.mcid = m.memberaccid
                           where gi.accid in ( $shareAccountsIn )");
                           
    if(!$result)
      throw new Exception("Failed to query accounts for groups ( $shareAccountsIn )");

    // Track all the accounts
    $all_accounts = array();

    // Build the result
    while($a = mysql_fetch_object($result)) {
      $entryId = $a->group_acct_id;
      if(!isset($consents[$entryId])) {
        $p = new stdClass;
        $p->accounts = array();
        $p->practiceName = isset($a->practicename) ? $a->practicename : $a->name;
        $p->groupAcctId = $a->group_acct_id;
        $p->access = $shareAccess[ $a->group_acct_id ];
        $consents[$entryId] = $p;
      }
      else
        $p = $consents[$entryId];

      // default to practice level access. will get overwritten below
      $a->access = $shareAccess[ $a->group_acct_id ]; 

      // add to accounts belonging to this practice
      if($a->mcid)
        $p->accounts[]=$a;

      $all_accounts[strval($a->mcid)] = $a;
    }

    // Now query for individual accounts
    $result = mysql_query("select * from users 
                           where acctype <> 'GROUP'
                           and mcid in ( $shareAccountsIn )");
    
    if(!$result) {
      throw new Exception("Failed to query accounts for users ( $shareAccountsIn )");
    }

    while($u = mysql_fetch_object($result)) {
      $u->access = $shareAccess[ strval($u->mcid) ];
      $u->es_id = null;
      if(isset($all_accounts[strval($u->mcid)])) { // User in one of the practices already handled?
        // Since they were already handled, just set the permissions accordingly
        $all_accounts[strval($u->mcid)]->access = $shareAccess[ strval($u->mcid) ];
      }
      else { // not in a practice.  Add to dummy group of "duals"
        $p = $consents["Individuals"];
        $p->accounts[]= $u;
      }
    }
  }

  $openid_groups = array();

  // Finally, add external shares
  dbg("found ".count($externalShares)." external shares");
  foreach($externalShares as $r) {
    // dbg("adding user for external share {$r->es_id} with type {$r->es_identity_type}");

    $u = new stdClass;
    $u->access = $r->rights;
    $u->email = ""; // TODO: cross associate with linked users
    $u->first_name = $r->es_first_name;
    $u->last_name = $r->es_last_name;
    $u->es_id = $r->es_id; 
    $u->es_identity_type = $r->es_identity_type;
    $u->es_create_date_time = strtotime($r->es_create_date_time);
    if($r->es_identity_type == "Application") {
      $app_info = explode("/",$r->account_id);
      $app_name = trim($app_info[0]);
      if(!isset($consents[$app_name])) {
        $consents[$app_name] = new stdClass;
        $app = $consents[$app_name];
        $app->accounts = array();
        $app->practiceName = $app_name;
        $app->groupAcctId = 0;
        $app->access = $r->rights;
        $app->es_identity_type = $r->es_identity_type;
        $app->application_token = $r->application_token;
      }
      else
        $app = $consents[$app_name];

      if(count($app_info)>1) {
        $u->mcid = $app_info[1];
        $app->accounts[]=$u;
      }
    }
    else 
    if($r->es_identity_type == "PIN") {
      if(!isset($consents['PIN'])) {
        $consents['PIN'] = new stdClass;
        $pins = $consents['PIN'];
        $pins->accounts = array();
        $pins->practiceName = "Tracking Number / PIN Access";
        $pins->groupAcctId = 0;
        $pins->access = $r->rights;
        $pins->es_identity_type = $r->es_identity_type;
        $pins->application_token = $r->application_token;
      }
      $u->mcid = $r->account_id;
      $consents['PIN']->accounts[]=$u;
    }
    else
    if(is_url($r->account_id) && (strpos($r->account_id,"*")!==false)) {  
      $parsed_url = parse_url($r->account_id);
      $grp_name = $parsed_url['host'];
      $consents[$grp_name] = new stdClass;
      $grp = $consents[$grp_name];
      $grp->accounts = array();
      $grp->practiceName = $grp_name;
      $grp->groupAcctId = 0;
      $grp->access = $r->rights;
      $grp->es_identity_type = $r->es_identity_type;
      $grp->es_id = $r->es_id;
      $openid_groups[$r->account_id] = $grp;
    }
    else {
      // Should not be possible for account id to be null
      // but inconsistent database can cause it (rights entry referring
      // to non-existant external share).
      if($r->account_id !== null) {
        $u->mcid = $r->account_id;
        $p = $consents["Individuals"];
        $p->accounts[]= $u;
      }
    }
  }

  // Sort individuals into openid groups
  $individuals = array();
  foreach($consents["Individuals"]->accounts as $ind) {
    $matched = false;
    if(is_url($ind->mcid)) { // the account is an openid
      // If matches openid format, remove it from the Individuals and add it to the matching group
      $parsed_url = parse_url($ind->mcid);
      foreach($openid_groups as $pattern => $grp ) {
        dbg("testing match of {$ind->mcid} to $pattern");
        if(match_openid_url_pattern($parsed_url, $pattern)) {
          $grp->accounts[] = $ind;
          dbg("group {$ind->mcid} matches pattern $pattern");
          $matched = true; // set flag so it will not be added to individuals array below
          break;
        }
      }
    }

    if(!$matched) // Not matched by openid group, add it as individual
      $individuals[]=$ind;
  }
  $consents["Individuals"]->accounts = $individuals;

  // If no individuals, remove that group
  if(count($consents["Individuals"]->accounts) == 0) {
    unset($consents["Individuals"]);
  }

  return $consents;
}
?>
