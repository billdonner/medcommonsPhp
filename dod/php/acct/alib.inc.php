<?php 
// library for the /acct service
require_once "dbparamsidentity.inc.php";
require_once "settings.php";
require_once "urls.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";

/**
 * Returns account information about the current user, derived
 * from the database. 
 * @TODO this should be done using the encrypted portion of the cookie
 * rather than the non-encrypted portion
 */
function get_validated_account_info() {
  try {
    // Get info from cookie
    if(!is_logged_in())
      return false;

    $info = get_account_info();
    if(!$info)
      return false;

    $u = pdo_query("select u.email, u.first_name, u.last_name, u.acctype, u.enable_vouchers, u.active_group_accid, p.*
      from users u
      left join groupinstances gi on gi.accid = u.active_group_accid
      left join practice p on p.providergroupid = gi.groupinstanceid
      where mcid = ?", $info->accid);

    if($u === false)
      error_page("Unable to load information about account ".$info->accid);

    if(count($u) === 0)
      return false;
    $u = $u[0];

    $info->email = $u->email;
    $info->fn = $u->first_name;
    $info->ln = $u->last_name;
    $info->acctype = $u->acctype;
    $info->enable_vouchers = $u->enable_vouchers;

    if($u->practiceid) {
      $p = new stdClass;
      $p->practiceid = $u->practiceid;
      $p->practicename = $u->practicename;
      $p->providergroupid = $u->providergroupid;
      $p->accid = $u->active_group_accid;
      $info->practice = $p;
    }
    else
      $info->practice = false;

    return $info;
  }
  catch(Exception $e) {
    error_log("Failed to query user information for user ".$info->accid.": ".$e->getMessage());
    return false;
  }
}

/**
 * Returns Base URL of a gateway that may be used for creation of new content.
 *
 * @param accid - account id that will be creating the content.
 * @return - base url to gateway for creation of content, or false if error occurred.
 */
function allocate_gateway($accid) {
  // TODO: implement multiple gateway support
  return $GLOBALS['Default_Repository'];
}

/**
 * Return a url to a gateway for creation of a new CCR
 *
 * @param accid - account id, if any, for which the CCR is being created.
 * @return - complete url for creation of new ccr, or false if error occurred.
 */
function new_ccr_url($accid,$auth="",$action="new") {
  $gwUrl = allocate_gateway($accid);
  if($gwUrl !== false) {
    return $gwUrl."/tracking.jsp?tracking=$action&accid=$accid&auth=$auth";
  }
  else 
    throw new Exception("Unable to locate gateway for new account");
}

/**
 * create_group
 * 
 * @param		$user user to create group for, as loaded by 
 *			get_validated_account_info()
 * @param $groupName	name of group to create
 * @throws Exception	for database errors
 */
function create_group($user, $groupName) {

  dbg("Creating group for user ".$user->accid);

  global $URL, $NS;
  $client = new SoapClient(null, array('location' => $URL, 'uri' => $NS));
  $groupAccId = $client->next_mcid();

  dbg("New group accid = ".$groupAccId);

  // Add group user
  pdo_execute("insert into users (mcid,acctype) values (?,?)", array($groupAccId, 'GROUP')); 

  $groupId = pdo_execute("insert into groupinstances (groupinstanceid,name,groupLogo,adminUrl,memberUrl,accid) 
                          values (NULL,?,'','','',?)",array($groupName, $groupAccId));

  dbg("New group groupId = $groupId");

  // Add practice associated with group
  $practiceId = pdo_execute("insert into practice (practiceid,practicename,providergroupid,practiceRlsUrl,practiceLogoUrl,accid) 
               values (NULL,?,?,?,?,?)",array($groupName, $groupId, '','',$groupAccId));

  dbg("New group practiceId = $practiceId");

  $practiceRlsUrl = gpath('Accounts_Url').'/ws/R.php?pid='.$practiceId;

  dbg("Updating practice RLS url to $practiceRlsUrl");

  pdo_execute('update practice set practiceRlsUrl = ? where practiceid = ?',array($practiceRlsUrl,$practiceId));

  pdo_execute('update groupinstances set parentid = ? where groupinstanceid = ?',array($practiceId,$groupId));

  // Add user to the group
  pdo_execute("insert into groupmembers (groupinstanceid,memberaccid) values (?,?)",array($groupId,$user->accid));

  // Add user as admin of group
  pdo_execute("insert into groupadmins (groupinstanceid,adminaccid) values (?,?)",array($groupId,$user->accid));

  // Make newly created group default active group for user
  pdo_execute("update users set active_group_accid = ? where mcid = ?",array($groupAccId, $user->accid));

  return array($groupAccId, $practiceRlsUrl);
}

/**
 * Returns all practices of which the given account is a member as PHP Objects
 * Returns false if no practices found.
 *
 * @param accid - the account id to query
 * @param practiceId - optional practice id to filter on
 */
function q_member_practices($accid, $practiceId = null)
{
  $sql = "SELECT q.*,i.accid, ga.adminaccid from practice q, groupmembers p, users u, groupinstances i
            left join groupadmins ga on ga.groupinstanceid = i.groupinstanceid
            where p.memberaccid=? 
            and  q.providergroupid=i.groupinstanceid  
            and i.parentid>0 
            and  p.groupinstanceid= i.groupinstanceid 
            and p.memberaccid=u.mcid";
  if($practiceId !== null) {
    $sql .= " and q.practiceid = ?";
  }

  $practices =  pdo_query($sql,$accid,$practiceId);

  if($practices === false) {
    return false;
  }

  if(count($practices) > 0) {
    return $practices;
  }
  else
    return false;
}

// return array of ids of practices to which this member belongs
function q_member_practice_ids($accid) {
  $query = "select p.practiceid from practice p, groupinstances g, groupmembers m
              where p.providergroupid = g.groupinstanceid
              and m.groupinstanceid = g.groupinstanceid
              and m.memberaccid = '$accid'
              order by p.practiceid";
	$result = mysql_query ($query) or die("can not query $query - ".mysql_error());
	return mysql_fetch_array($result);
}

// counted queries

// administrator of  practices
function count_admin_practices($accid)
{
	$query = "SELECT COUNT(*) from practice q, groupadmins p, groupinstances i , users u
	where p.adminaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
	i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
	p.adminaccid=u.mcid 
              ";
	$result = mysql_query ($query) or die("can not query $query - ".mysql_error());
	$count = mysql_fetch_array($result);
	mysql_free_result($result);
	return $count[0];
	return $count[0];
}


function count_member_practices($accid)
{
	// member of practices

	$query = "SELECT COUNT(*)  from practice q, groupmembers p, groupinstances i , users u
	where p.memberaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
	i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
	p.memberaccid=u.mcid
              ";
	$result = mysql_query ($query) or die("can not query $query - ".mysql_error());
	$count = mysql_fetch_array($result);
	mysql_free_result($result);
	return $count[0];
}
// administrator of groups
function count_admin_groups($accid)
{
	$query = "SELECT COUNT(*)  from groupadmins p, groupinstances i , users u
	where p.adminaccid='$accid' and  i.parentid=0 and  p.groupinstanceid= i.groupinstanceid and p.adminaccid=u.mcid
 	order by p.groupinstanceid,p.adminaccid ";
	$result = mysql_query ($query) or die("can not query $query - ".mysql_error());
	$count = mysql_fetch_array($result);
	mysql_free_result($result);
	return $count[0];
}
// member of groups

function count_member_groups($accid)
{
	$query = "SELECT COUNT(*)  from groupmembers p, groupinstances i , users u
	where p.memberaccid='$accid' and i.parentid=0 and p.groupinstanceid= i.groupinstanceid and p.memberaccid=u.mcid 
	order by p.groupinstanceid,p.memberaccid ";
	$result = mysql_query ($query) or die("can not query $query - ".mysql_error());
	$count = mysql_fetch_array($result);
	mysql_free_result($result);
	return $count[0];
}

// returns array of rows of document_type that are ccr merges for $accid
function q_ccr_merges($accid,$maxrows) {
  $query = "select * from document_type
              where dt_type = 'CURRENTCCR'
              and dt_account_id = '$accid'
              order by dt_create_date_time desc
              limit $maxrows";
	$result = mysql_query ($query) or die("can not query $query - ".mysql_error());
  $merges = array();
  while($row = mysql_fetch_array($result)) {
    $merges[]=$row;
  }
	return $merges;
}

function getECCRGuid($accid)
{
	$status = 'RED';
	$query = "SELECT guid from ccrlog where (accid = '$accid') and (status='RED')";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$row = mysql_fetch_row($result);
	$guid = $row[0];
	mysql_free_result($result);
	if ($guid===false) return false;
	return $guid;
}

function tryECCR ($accid)
{
  $guid = getECCRGuid($accid);
	if ($guid===false) return false;
	return $GLOBALS['Commons_Url']."gwredirguid.php?guid=".$guid;
}
function tryCCR ($accid) {

	// this is a gruesome query to find the newest CCR and then to go there if we have one
  // ssadedin: not too sure what this query is doing .... see tryCCCR() for current ccr
	$query = "SELECT guid
              from ccrlog 
              left join document_type d on dt_account_id = accid  and ((dt_tracking_number = tracking) or (dt_guid = guid))
              where (accid = '$accid') and (status <> 'DELETED') LIMIT 1;";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$obj = mysql_fetch_object($result);
	mysql_free_result($result);
	if ($obj===false) return false;
	return $GLOBALS['Commons_Url']."gwredirguid.php?guid=".$obj->guid;
}
/**
 * Returns the guid of the current ccr of this user or false if there is none
 */
function getCurrentCCRGuid($accid) {
  $query = "select dt_guid from document_type where dt_type='CURRENTCCR' and dt_account_id='$accid' order by dt_create_date_time desc, dt_id desc limit 1";
  $result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
  $obj = mysql_fetch_object($result);
  mysql_free_result($result);
  if ($obj===false) 
    return false;
   else
     return $obj->dt_guid;
}

/**
 * return a url for the current ccr of this user, or return false if there is none.
 */
function tryCCCR($accid) {
  $guid = getCurrentCCRGuid($accid);
  if($guid === false)
     return false;
     
  return $GLOBALS['Commons_Url']."gwredirguid.php?guid=".$guid;
}

function tryRls ($accid) {

	// see if we are a member of any practice before going to providerPage where the RLS will be brought up

	$query = "SELECT * from practice q, groupmembers p, groupinstances i , users u
			where p.memberaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
			i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
			p.memberaccid=u.mcid order by q.practicename LIMIT 1";
	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	mysql_free_result($result);
	//		echo "in tryRLS rowcount is $rowcount";
	if ($rowcount==0) return false;

	return $GLOBALS['Accounts_Url']."providerPage.php";
}

function tryGroups($accid) {

	$gpage = $GLOBALS['Accounts_Url']."GxGroup.php?op=members";
	if (count_member_groups($accid)>0) return $gpage.'&f=1'; else
	if (count_admin_groups($accid)>0) return $gpage.'&f=2'; else
	if (count_member_practices($accid)>0) return $gpage.'&f=3'; else
	if (count_admin_practices ($accid)>0) return $gpage.'&f=4'; else
	return false;

}

function tryPracticeAdmin ($accid) {

	// see if we are an administrator of any practices before going to the practiceadmin page
	$query = "SELECT * from practice q, groupadmins p, groupinstances i , users u
		where p.adminaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
		i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
		p.adminaccid=u.mcid order by q.practicename ";
	$result = mysql_query ($query) or die("can not query table groupadmins - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	mysql_free_result($result);
	//		echo "in tryPracticeAdmin rowcount is $rowcount";
	if ($rowcount==0) return false;
	return $GLOBALS['Accounts_Url']."flatPageAdmin.php";

}
function tryFullPage () {return $GLOBALS['Accounts_Url']."flatPageFull.php";}
function tryNoStart () {return $GLOBALS['Accounts_Url']."noStart.php";}

function put_switches($accid,$switches)
{
	$q="update users set startparams='$switches' where (mcid ='$accid')";
	mysql_query($q) or die("can't update users".mysql_error());

}

function get_switches($accid)
{
	$q="select startparams,validparams from users where (mcid ='$accid')";
	$result=mysql_query($q) or die("can't access users".mysql_error());
	$fow = mysql_fetch_array($result);
	return $fow;
}

function aconnect_db()
{
	$db=$GLOBALS['DB_Database'];
	mysql_pconnect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	return $db;
}

// Global pdo object
$pdo = null;

function pdo_connect() {
  global $pdo,$IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS;
  if($pdo === null) {
    $pdo = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);
    $pdo->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  if(!$pdo)
    throw new Exception("Failed to connect to database");

  return $pdo; 
}

function pdo_begin_tx() {
  global $pdo;
  try {
    $pdo = pdo_connect();
    $pdo->beginTransaction();
  }
  catch(PDOException $ex) {
    error_log("begin_tx failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    throw new Exception("Database statement failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
  }
}

function pdo_commit() {
  global $pdo;
  try {
    $pdo = pdo_connect();
    $pdo->commit();
  }
  catch(PDOException $ex) {
    error_log("commit failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    throw new Exception("Database commit failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
  }
}

function pdo_rollback() {
  global $pdo;
  try {
    $pdo = pdo_connect();
    $pdo->rollback();
  }
  catch(PDOException $ex) {
    error_log("rollback failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    throw new Exception("Database rollback failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
  }
}

 /**
  * Executes the given insert / update, throwing an exception if there is
  * any kind of failure.
  *
  * @param sql - sql string containing question marks (?) for bind parameters
  * @param params - optional array of parameters, one for each ? in the sql
  *
  * @throws Exception - for all database failures
  * @return - the id of inserted row (if any)
  */
function pdo_execute($sql, $params = array()) {
  global $pdo;
  try {
    $pdo = pdo_connect();

    dbg("SQL: $sql");

    $s = $pdo->prepare($sql);
    if(!$s) {
      throw new Exception("Failed to prepare sql [$sql]");
    }
    
    if(!$s->execute($params)) {
      throw new Exception("Failed to execute sql [$sql] with params (".var_dump($params).")");
    }
    return $pdo->lastInsertId();
  }
  catch(PDOException $ex) {
    error_log("query $sql failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    throw new Exception("Database statement failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo()."[sql=$sql]");
  }
}

/**
 * Executes SQL with given bind parameters, loads
 * all results and then returns the first result if there
 * is one.  If no rows returned returns null.
 */
function pdo_first_row($sql, $params) {
  $result = pdo_query($sql,$params);
  if(count($result)<1)
    return null;
  else
    return $result[0];
}

/**
 * Executes the given sql, binding the given parameters if passed.
 * Returns an array of PHP Objects containing the data returned.
 *
 * returns false upon failure. (NOTE: Does NOT throw ANY exceptions upon failure)
 */
function pdo_query($sql, $p1=null,$p2=null,$p3=null,$p4=null) {
  global $pdo;

  if(!is_array($p1)) {
    $parameters = array($p1);
    if($p2 != null)
      $parameters[]=$p2;
    if($p3 != null)
      $parameters[]=$p3;
    if($p4 != null)
      $parameters[]=$p4;
  }
  else
    $parameters = $p1;

  dbg("SQL: $sql");
  try {
    $pdo = pdo_connect();

    $s = $pdo->prepare($sql);
    if(!$s) {
     error_log("query $sql failed with Error Info: ".$pdo->errorInfo());
     return false;
    }

    $index = 1;
    foreach($parameters as $p) {
      // NOTE: do NOT bind $p, it's bound by reference
      // you will lose several hours of your life figuring out
      // why it doesn't work
      $s->bindParam($index,$parameters[$index-1]);
      $index++;
    }

    $results = array();
    if($s && $s->execute()) {
      while($r = $s->fetch(PDO::FETCH_OBJ)) {
        $results[]=$r;
      }
    }
    else {
     error_log("query $sql failed with Error Info: ".$pdo->errorInfo());
    }
    return $results;
  }
  catch(PDOException $ex) {
    error_log("query $sql failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    return false;
  }
  catch(Exception $ex) {
    error_log("query $sql failed: ".$ex->getMessage());
    return false;
  }
}

function adoquery($q)
{
	// execute query and return only first fow of interest
	$result=mysql_query($q) or die ("Cant execute query $q ".mysql_error());
	$r = mysql_fetch_assoc ($result);
	$rowcount = mysql_num_rows($result);
	//		echo "Rowcount in doquery $q is $rowcount <br>";
	return $r; // return whole associate array, might be null
}

function testif_logged_in()
{
	if (!isset($_COOKIE['mc'])) //wld 10 sep 06 strict type checking
	return false;
	$mc = $_COOKIE['mc'];

	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
	if ($mc!='')
	{
		$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
		$props = explode(',',$mc);
		for ($i=0; $i<count($props); $i++) {
			list($prop,$val)= explode('=',$props[$i]);
			switch($prop)
			{
				case 'mcid': $accid=$val; break;
				case 'fn': $fn = $val; break;
				case 'ln': $ln = $val; break;
				case 'email'; $email = $val; break;
				case 'from'; $idp = stripslashes($val); break;
				case 'auth'; $auth = $val; break;
			}
		}
	}
	return array($accid,$fn,$ln,$email,$idp,$mc,$auth);
}

function aconfirm_member_access($accid,$gid){
	// does not return if this user is not a group member
	$q = "Select * from groupmembers where '$accid'=memberaccid and '$gid'=groupinstanceid";
	$rec = adoquery($q);
	if ($rec!==false) return $rec;
	group_error_template($accid);
};

/**
 * Hide the given patient in the worklist / patient list
 *
 * @return json encoded status
 */
function hide_patient($practiceId,$patientId) {
  $json = new Services_JSON();
  // Update patient
  error_log("hiding patient $patientId for practice $practiceId");
  try {
    pdo_execute("update practiceccrevents set ViewStatus='Hidden' where practiceid=? and PatientIdentifier=? and ViewStatus = 'Visible'",array($practiceId,$patientId));
  }
  catch(Exception $e) {
    error_log("Failed to hide patient: ".$e->getMessage());
    return $json->encode(array('status'=>'failed', 'error'=>'Unable to update ccr events'));
    exit;
  }
  return $json->encode(array('status'=>'ok'));
}

/**
 * Unhide a given specified patient in the worklist / patient list
 *
 * @return json encoded status
 */
function unhide_patient($practiceId,$patientId) {
  $json = new Services_JSON();
  $info = get_account_info();
  $practices = q_member_practices($info->accid);
  $practice = $practices[0];

  error_log("Hiding patient $patientId for practice ".$practiceId);

  // Update patient
  try {
    // Find most recent row
    $result = pdo_query("select * from practiceccrevents where ViewStatus = 'Hidden' and PatientIdentifier=? and practiceid=? order by CreationDateTime desc limit 1", $patientId, $practiceId);

    if(($result === false) || (count($result)==0))
      throw new Exception("Unable to locate hidden patient $patientId");

    // Unhide this record
    pdo_execute("update practiceccrevents set ViewStatus='Visible' where practiceid=? and PatientIdentifier=? and ViewStatus = 'Hidden' and ConfirmationCode=?",array($practiceId,$patientId,$result[0]->ConfirmationCode));
  }
  catch(Exception $e) {
    error_log("Failed to restore patient: ".$e->getMessage());
    return $json->encode(array('status'=>'failed', 'error'=>'Unable to update ccr events'));
    exit;
  }
  return $json->encode(array('status'=>'ok'));
}

function aconfirm_logged_in($fail_if_not=false)
{
	// $fail_if_not is optional string that forces complete death if not logged on

	if (isset($GLOBALS['__mckey']))
	{
		list ($sha1,$accid,$email)=explode('|',base64_decode($GLOBALS['__mckey'])); //if starting automagically
		return array($accid,'','',$email,'','');
	}
	else

	if (!isset($_COOKIE['mc']))
	{
 		if ($fail_if_not) die($fail_if_not); 
		//header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
		//echo "Redirecting to MedCommons Web Site";
		$home = $GLOBALS['Homepage_Url'];
		$irl = $GLOBALS['Identity_Base_Url'];
		$trl = $GLOBALS['Commons_Url'].'trackinghandler.php';
		$errurl = $GLOBALS['Accounts_Url'].'goStart.php';
		if (isset($GLOBALS['Script_Domain'])) //svn 824 with enhanccement
		$domain = $GLOBALS['Script_Domain']; else $domain=false;
		$setDomain = "";
		if($domain && ($domain!= "")) {
			$setDomain = "document.domain = '$domain';";
		}
		$html=<<<XXX
		

<?xml version='1.0' encoding='US-ASCII' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=US-ASCII" />
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Home Page"/>
        <meta name="robots" content="all"/>

        <title>MedCommons - Interoperable and Private Personal Health Records</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        <style type="text/css" media="all"><!--

td {
    vertical-align: top;
    padding: 10px;
    border: 1px;
    border-style: solid;
    border-color: #fff #fff #ccc #ccc;
}

td p, td a {
    font-size: x-small;
    padding-bottom: 0px;
    margin-bottom: 0px;
}

#forgotten {
    padding-top: 0px;
    margin-top: 0px;
}

.label {
    font-size: x-small;
}

.error {
	background-color: #c00;
	color: #fff;
}
 
h4 {
    background-color: #ccc;
}

// --></style>
    </head>
     <body onload="$setDomain;" >
        <div id="container">
            <div id="intro">
			<a href="$home" ><img src='images/mclogotiny.png' alt="MedCommons"></a>
            </div>
            <div id="supportingText">
	        <h3>
                    <span>Sign In</span>
                </h3>
		<div id='login'>
		  <table><tr><td>

		    <form method='post' action='$irl/login'>
		        <h4>Existing Account</h4>
		  <!--<a class='label' href='$irl/register'>Create a New Account</a>-->
      <p style="font-size: 9px;">Please Note: New Registrations are currently disabled and will resume after a short tesing period currently in-progress.</p>
			<p>Your MCID or E-Mail Address:</p>
			<input name='mcid' size='19' value='' />

			<p>Your Password:</p>

			<input name='password' type='password' />
			<p id='forgotten'>
			    <a href='$irl/forgotten'>Forgotten Password?</a>
			</p>
			<input type='hidden' name='userId' value='' />
			<input type='hidden' name='sourceId' value='' />
			<input type='submit' value='Sign On>>' />
		    </form>

		    </td></tr></table>
		</div>

		<div id='viaTN'>
		  <table><tr><td>

		    <form method='post' action='$trl'>
		        <h4>Find CCR By Tracking #</h4>
			<p>Enter 12 Digit Tracking #</p>
			<input name='trackingNumber' size='19' value='' />
			<input type=hidden name='returnurl' value='$errurl' />
			<input type='submit' value='Lookup>>' />
		    </form>

		    </td></tr></table>
		</div>

            </div>
        </div>
        <div id="footer">
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp; <a
                href="http://creativecommons.org/licenses/by-nc-sa/1.0/" title="View details of the
                license of this site, courtesy of Creative Commons.">cc</a> &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>

            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
            <p class="p1">&#169; MedCommons 2006</p>
        </div>
    </body>
</html>
XXX;
		echo $html;
		exit;
	}

	// here if we have a cookie
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $cl=""; $auth="";

	$mc = $_COOKIE['mc'];
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$mc);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;
			case 'fn': $fn = $val; break;
			case 'ln': $ln = $val; break;
			case 'email'; $email = $val; break;
			case 'from'; $idp = stripslashes($val); break;
			case 'auth'; $auth = $val; break;
		}
	}


	return array($accid,$fn,$ln,$email,$idp,$cl,$auth);
}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function amyUserInfo($accid)
{
	$q="select photoUrl,picslayout,stylesheetUrl,affiliationgroupid from users
	where mcid='$accid'";
	$result = mysql_query($q) or die("cant select from users ".mysql_error());
	$obj = mysql_fetch_object($result);

	return $obj;
}
function amyAffiliationInfo($gid)
{
	$q="select affiliatename, affiliatelogo from
	            affiliates where  
	              affiliateid='$gid'";
	$result = mysql_query($q) or die("cant select from affiliates ".mysql_error());
	$row = mysql_fetch_object($result);
	return $row;
}

class aInfoClass
{
	var $header;
	var $logo;
	var $valid;
	var $groupname;
	var $leftphotourl;
	var $rightphotourl;
	var $stylesheeturl;
	var $picslayout;
	var $personaimg;
}
/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function make_acct_form_components ($accid)
{//photoUrl,picslayout,stylesheetUrl,affiliationgroupid
	$info = new aInfoClass;
	$info->header = '';

	$u = amyUserInfo($accid);
	if ($u->photoUrl=='') $photourl = "<div><small>set photo via My Prefs</small></div>"; else
	$photourl = "<img width='100px' src='".$u->photoUrl."' alt='".$u->photoUrl."' />";
	if ($u->affiliationgroupid!='-1')
	{
		$a = amyAffiliationInfo($u->affiliationgroupid); // all the dirty work is here
		if(isset($info->logo)) { // ssadedin:  e_strict
			$info->logo = "<img src='".$a->affiliatelogo."' alt='".$a->affiliatename."' />";
		}
		$info->groupname = isset($a->affiliatename) ? $a->affiliatename : '';
		$info->stylesheeturl = isset($u->stylesheetUrl) ? $u->stylesheetUrl : '';
	}
	else {
		$info->logo='';$info->stylesheeturl = '';
	}

	$info->header .=	"<div id='myacct_form_header'>".$info->logo."</div>";
	$info->value = true;
	$info->rightphotourl = '';
	$info->leftphotourl = '';
	if (substr($u->picslayout,0,1)=='S') $info->leftphotourl = $photourl;
	if (substr($u->picslayout,1,1)=='S') $info->rightphotourl = $photourl;
	return $info;
}



/**
 * Enter description here...
 *
 * @param unknown_type $info
 * @param unknown_type $accid
 * @param unknown_type $email
 * @param unknown_type $id
 * @param unknown_type $desc
 * @param unknown_type $title
 * @param unknown_type $startpage
 * @param unknown_type $me
 * @return unknown
 */
function make_acct_page_top ($info, $accid,$email, $id,$desc,$title,$startpage,$me='')
{

	if ($info->leftphotourl!='') $leftphotoblock="<td align=left>$info->leftphotourl</td>"; else $leftphotoblock='';
	if ($info->rightphotourl!='') $rightphotoblock="<td align=right>$info->rightphotourl</td>"; else $rightphotoblock='';

	//	if ($startpage=='') $sp='';
	//	else  $sp="<a href=../acct/setStart.php?p=$startpage?id=$id>mark</a>&nbsp;";
	$iden =   $info->personaimg."<a href='../acct/goStart.php'>$accid</a>";
	$identityUrl = $GLOBALS['Identity_Base_Url'];
	$x=<<<XXX
           <table width="100%"><tr>
              $leftphotoblock
				<td align=right><table><tr><td><b>
                $title</b></td></tr></table>
                <table><tr><td>$iden $email</td></tr>
				<tr><td align=right><small> 
               <a href='${identityUrl}logout'>logout</a>
				</small></td></tr>
				</table>
              </td>
          <td align=right>$info->header</td>$rightphotoblock</tr></table>
XXX;
	return $x;
}
function make_acct_page_bottom ($info)
{  $host = $_SERVER['HTTP_HOST'];
$acct = $GLOBALS['Accounts_Url'];
$html=<<<XXX
	     <div id="footer">
          <div class="noprint">
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp;  <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>
            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
          </div>
            <div class="p1">$host <a href='$acct/myPrefs.php'>&#169;</a> MedCommons 2006</div>
        </div>
XXX;

return $html;
}

function acct_error($info,$errorstring)
{
	$html = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
      <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Accounts Error"/>
        <meta name="robots" content="all"/>
        <title>MedCommons $errorstring</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "accstyle.css"; </style>
    </head>
                <table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="$errorstring" /></a>
                </td><td>Accts Maintenance Error<small> 
                &nbsp;
						&nbsp;
					</small></td></tr>
					</table>
$info->header

$errorstring
<form action=goStart.php method=post>
<input type=hidden name=id value='$info->id'>
<input type=submit value='Ok'>
</form>
</body>
</html>
XXX;
	echo $html;
	exit;
}

function group_error_template($accid) {
  $tpl = new Template(resolveUp("widget.tpl.php"));
  $tpl->set("content",new Template(resolveUp("group_error.tpl.php")));
  $tpl->set("accid",$accid);
  echo $tpl->fetch();
  exit;
}



function get_user_interests() {
  if(!is_logged_in()) {
    return false;
  }

  $info = get_account_info();

  aconnect_db();

  $result = mysql_query("select interests from users where mcid = ".$info->accid);
  if($result !== false) {
    $allA = mysql_fetch_array($result);
    if($allA) {
      $all = $allA[0];
      // Got the interests, split them out
      return explode("|",$all);
    }
  }

  // no interests
  return array();
}

/**
 * Check that the given authentication token is correct format
 * (implemented) and is a valid auth token in the system (TODO).
 */
function validate_auth($auth) {
  if(preg_match("/^(token:){0,1}[a-z0-9]{40}$/",$auth)!==1)
    throw new Exception("Invalid authentication token");
}

/**
 * Returns the permissions that the given auth token has to 
 * the specified account.  Makes web service call to 
 * /secure service to do this, so this is not a cheap 
 * operation.
 *
 * @throws Exception - if /secure call fails
 */

function getPermissions($auth,$toAccount) {
  $contents  = get_url(gpath('Commons_Url')."/ws/getPermissions.php?toAccount=$toAccount&auth=$auth");
  if(preg_match(",<rights>(.*)</rights>,",$contents,$rights)===0)
    throw new Exception("getPermissions call failed for account $toAccount and $auth with output ".$contents);
  return count($rights>0) ? $rights[1] : "";
}

function backtrace()
{
   $bt = debug_backtrace();
  
   echo("<br /><br />Backtrace (most recent call last):<br /><br />\n");   
   for($i = 0; $i <= count($bt) - 1; $i++)
   {
       if(!isset($bt[$i]["file"]))
           echo("[PHP core called function]<br />");
       else
           echo("File: ".$bt[$i]["file"]."<br />");
      
       if(isset($bt[$i]["line"]))
           echo("&nbsp;&nbsp;&nbsp;&nbsp;line ".$bt[$i]["line"]."<br />");
       echo("&nbsp;&nbsp;&nbsp;&nbsp;function called: ".$bt[$i]["function"]);
      
       if($bt[$i]["args"])
       {
           echo("<br />&nbsp;&nbsp;&nbsp;&nbsp;args: ");
           for($j = 0; $j <= count($bt[$i]["args"]) - 1; $j++)
           {
               if(is_array($bt[$i]["args"][$j]))
               {
                   print_r($bt[$i]["args"][$j]);
               }
               else
                   echo($bt[$i]["args"][$j]);   
                          
               if($j != count($bt[$i]["args"]) - 1)
                   echo(", ");
           }
       }
       echo("<br /><br />");
   }
}
?>
