<?php
require_once "dbparamsidentity.inc.php";
require_once "utils.inc.php";
require_once "../alib.inc.php";
require_once "JSON.php";

function error($msg) {
  $json = new Services_JSON();
  $result = new stdClass;
  $result->status = "failed";
  $result->message = $msg;
  echo $json->encode($result);
  exit;
}

function cleanreq($x) {
  if(isset($_REQUEST[$x])) {
    return mysql_escape_string(get_magic_quotes_gpc() ? stripslashes($_REQUEST[$x]) : $_REQUEST[$x]);
  }
  else 
    return "";
}

$accid = cleanreq("accid");
nocache();
header ("Content-type: text/javascript");
aconnect_db();

$storageId = cleanreq('accid');
$json = new Services_JSON();
$result = new stdClass;
$auth = cleanreq('auth');
$updateAccts = array();
$allaccts = array();

// Find accounts to update
foreach($_REQUEST as $accid => $rights ) {
  if($rights == "None")
    $rights = "";
  if(preg_match("/^[0-9]{16}$/",$accid)) { // If parameter matches account id format

    // Only process if we did not already process this account
    if(isset($allaccts[$accid]))
      continue;

    $allaccts[$accid]=true;

    $updateAccts[]="$accid=".$rights;

    // Expand to group members if there are any
    $members = q_group_members($accid);
    foreach($members as $m) {
      // if set explicitly in params, use explicit value, otherwise inherit from group
      $updateAccts[]="$m=".(isset($_REQUEST[$m])?$_REQUEST[$m]:$rights);
      $allaccts[$m]=true;
    }
  }
  else 
  if(preg_match("/^es_.*/",$accid)) { // If parameter matches external share format
    dbg("account: $accid");
    $updateAccts[]=urlencode($accid)."=".$rights;
  }
  else 
  if(preg_match("/^at_.*/",$accid)) { // If parameter matches application token
    dbg("updating rights for application token: $accid");
    $updateAccts[]=urlencode($accid)."=".$rights;
  }
}

$updateUrl = gpath('Commons_Url')."/ws/updateAccess.php?accid=".$storageId."&".join($updateAccts,"&");
$updateUrl .= "&auth=$auth";
dbg("Fetching url: $updateUrl");
try {
  $contents = get_url($updateUrl); 
  $updateResult = $json->decode($contents);
  dbg("Update result ".$contents);
  $result->status = "ok";
  if( !$updateResult || ($updateResult->status != "ok")) {
    $result->status = "failed";
  }
}
catch(Exception $e) {
  $result->status = "failed";
  $result->error = $e->getMessage();
}
echo $json->encode($result);
mysql_close();
?>
