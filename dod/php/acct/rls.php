<?php
/**
 * RLS / Worklist / Patient List Query Logic
 *
 * This page is the main driver that performs queries to display the patient list
 * (aka worklist).  It servers both AJAX dynamic updates and also the initial rendering
 * that displays the whole page.  Content is rendered using the template rlstable.tpl.php.
 *
 * Originally written by bdonner, updated and maintained by ssadedin@medcommons.net.
 */
require_once "dbparamsidentity.inc.php";
require_once "template.inc.php";
require_once "utils.inc.php";
require_once "alib.inc.php";
nocache();


/**
 * Get clean value from request
 */
function strip($x) {
  return isset($_REQUEST[$x]) ?  (get_magic_quotes_gpc() ? stripslashes($_REQUEST[$x]) : $_REQUEST[$x]) : "";
}

function cleanreq ($x) { if (isset($_REQUEST[$x])) return $_REQUEST[$x]; else return false;}

/**
 * Fail with given error message
 */
function fail($msg) {
  echo "<p>Error:  $msg</p>";
  error_log("Query failed:  ".mysql_error());
  exit;
}



// these are the basic query parameter arguments that are passed around
//
//
$pfn = cleanreq('PatientFamilyName');
$pgn = cleanreq('PatientGivenName');
$pid= cleanreq('PatientIdentifier');
$pis= cleanreq('PatientIdentifierSource');
$psx = cleanreq('PatientSex');
$pag = cleanreq('PatientAge');
$spid  = cleanreq('SenderProviderId');
$rpid  = cleanreq('ReceiverProviderId');
$dob  = cleanreq('DOB');
$cc = cleanreq('ConfirmationCode');
// these are not query parameters, but are passed around
$rs = cleanreq('RegistrySecret');
$guid = cleanreq('Guid');
$purp = cleanreq('Purpose');
$cxpserv = cleanreq('CXPServerURL');
$cxpvendor = cleanreq('CXPServerVendor');
$viewerurl = cleanreq('ViewerURL');
$comment = cleanreq('Comment');
// these params control the formatting of output
$int = cleanreq('int'); // if non-zero, ajax'd dynamic updates
$st = cleanreq('st');
$ti = cleanreq('ti');
$limit = cleanreq('limit');
$logo = cleanreq('logo');
$page = cleanreq('page');

$showHidden = false;

if(req('showHidden','false')=='true')
  $showHidden = true;

if($page == '')
  $page = 1;

// this multiplexes the group - wld 072506
//$gid = cleanreq('gid'); // retired in favor of pgid

// this is a hacked version of glib.inc.php because I cant figure out the nesting structure of dbparams, etc.

$GLOBALS['RLS_Name'] = "MedCommons Builtin Registry";
$GLOBALS['RLS_Version'] = "0.2";
$GLOBALS['RLS_DB']="practiceccrevents";

list($accid,$fn,$ln,$email,$idp,$cookie,$auth) = aconfirm_logged_in (); // does not return if not logged on
$db = aconnect_db(); // connect to the right database

// let this entire file be 'required_once' by setting the $__practicegroupid
if (isset($__practicegroupid)) $practicegroupid = $__practicegroupid; else
// otherwise lets try this
$practicegroupid = $_REQUEST['pid']; //specifies the practicegroup

$select = "SELECT p.providergroupid,p.practicename,gi.worklist_limit
           from practice p, groupinstances gi
           where p.practiceid=$practicegroupid
             and gi.groupinstanceid = p.providergroupid";

$res = mysql_query($select);
$result = mysql_fetch_array($res);
$providersid = $result[0];  // this is what we care about

if($limit === false) {
  if($result[2] != null) {
    $limit = $result[2];
  }
}

if(($limit == false) || ($limit == null)) {
  $limit = 6;
}

$practicename = htmlspecialchars($result[1]);

aconfirm_member_access($accid,$providersid); // does not return if this user is not a group member
$gid = $practicegroupid;

$int = 10; // now integrating the ajax stuff, all calls should come right back here

$lasttime = cleanreq('lt'); // wld if missing, then its the first call, otherwise it is an ajax refresh

// build WHERE clause for select statement based on the arguments
$where = ""; $wc = 1;

// Search criteria
$searchPatientName = mysql_real_escape_string(strip('searchPatientName'));
if($searchPatientName!="") {
  $showHidden = true;
  $wc++;
  $names = explode(",",$searchPatientName);
  if(count($names) == 1) 
    $where .= " AND (e.PatientFamilyName like '%$searchPatientName%' OR e.PatientGivenName like '%$searchPatientName%')";
  else
    $where .= " AND (e.PatientFamilyName like '%".trim($names[1])."%' AND e.PatientGivenName like '%".trim($names[0])."%')";
}

$searchLastUpdate = req('searchLastUpdate',"");
if(($searchLastUpdate!="") && ($searchLastUpdate!="all")) {
  $showHidden = true;
  $day = 3600*24;
  $wc++;
  if($searchLastUpdate=="week") {
    $where .= " AND (e.CreationDateTime > ".(time()-$day*7).")";
  }
  else
  if($searchLastUpdate=="month") {
    $where .= " AND (e.CreationDateTime > ".(time()-$day*30).")";
  }
  else 
  if($searchLastUpdate=="year") {
    $where .= " AND (e.CreationDateTime > ".(time()-$day*365).")";
  }
}

$searchPurpose = req('searchPurpose',"");
if($searchPurpose!="") {
  $showHidden = true;
  $wc++;
  $where .= " AND (e.Purpose like '%$searchPurpose%')";
}
$searchStatus = req('searchStatus',"");
if($searchStatus!="") {
  $showHidden = true;
  $wc++;
  $where .= " AND (e.Status like '%$searchStatus%')";
}

$viewStatusClause = " AND e.ViewStatus = 'Visible' ";
if($showHidden) {
  $viewStatusClause = " AND e.ViewStatus in ('Visible','Hidden')";
  // error_log("## show hidden");
}
// else 
//  error_log("## no hidden");

if ($wc!=0) $whereclause = $where; else $whereclause='';


$isajax = ($int!=0);
$mb = $GLOBALS['RLS_Name'];
$start = ($page-1) * $limit;

// error_log($whereclause);

/*
 * Get count of all rows (visible and non-visible)
 */
$allCountSql="SELECT count(*) FROM practiceccrevents e WHERE e.practiceid = '$gid' $whereclause AND e.ViewStatus in ('Visible','Hidden') ";
$result = mysql_query($allCountSql) or die("can not select from  table practiceccrevents - $countSql".mysql_error());
$row = mysql_fetch_array($result);
$allCount = $row[0];

/*
 * Get count of Visible rows only
 */
$countSql=$allCountSql . $viewStatusClause;
$result = mysql_query($countSql) or die("can not select from  table practiceccrevents - $countSql".mysql_error());
$countRow = mysql_fetch_array($result);
$count = $countRow[0];

/*
 * Main query - retrieve actual data
 */
$select = "SELECT e.*, wia.wi_id as wi_available_id, wid.wi_id as wi_downloaded_id, c.couponum, c.status as couponstatus
FROM practice p, practiceccrevents e
LEFT JOIN workflow_item wia ON e.PatientIdentifier = wia.wi_target_account_id AND wia.wi_type = 'Download Status' AND wia.wi_active_status = 'Active' and wia.wi_status = 'Available'
LEFT JOIN workflow_item wid ON e.PatientIdentifier = wid.wi_target_account_id AND wid.wi_type = 'Download Status' AND wid.wi_active_status = 'Active' and wid.wi_status = 'Downloaded'
LEFT JOIN modcoupons c on c.mcid = e.PatientIdentifier
WHERE e.practiceid = '$gid' AND e.practiceid = p.practiceid 
AND ((p.accid = wia.wi_source_account_id) OR (wia.wi_source_account_id is NULL))
AND ((p.accid = wid.wi_source_account_id) OR (wid.wi_source_account_id is NULL))
$whereclause $viewStatusClause
GROUP BY e.PatientGivenName, e.PatientFamilyName, e.Guid
ORDER BY e.CreationDateTime DESC LIMIT $start,$limit";

//error_log($select);

$result = mysql_query($select) or die("can not select from  table practiceccrevents - $select".mysql_error());
$rowCount = mysql_numrows($result);
$pages = ceil($count/$limit);

$pageLinks = "";
if($pages > 1) {
$pageLinks = "Page ";
  for($p=0; $p<$pages; $p++) {
    $pn = $p + 1;
    if($pn == $page)
    $pageLinks.="$pn&nbsp;";
    else
    $pageLinks.="<a href='javascript:page($pn);'>$pn</a>&nbsp;";
  }
}

$displayedCount = $count < $limit ? $count : $limit;

// Query status values
$results = pdo_query("select value from mcproperties where property = 'acAccountStatus'");
if($results === false) {
  error_page("Unable to render your Worklist.  Please contact Support for help.",
    "Unable to query acAccountStatus property");
}

$statusValues = count($results)>0 ? $results[0]->value : "";

$patientIds = array();

$rows = array();
  while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
  $rows[]=$l;
  if($l["PatientIdentifier"])
    $patientIds[]=$l["PatientIdentifier"];
}

// Get dicom statuses
if(count($patientIds)>0) {
  $ds =
    pdo_query("select * from dicom_status where ds_account_id in (".implode(",",$patientIds).") ".
              "order by ds_create_date_time desc", array());
}
else
  $ds = array();

dbg("got ".count($ds). " dicom status values for ".count($patientIds)." patients");

$dicomStatus = array();
foreach($ds as $s) {
  if(!isset($dicomStatus[$s->ds_account_id]))
    $dicomStatus[$s->ds_account_id] = $s;
}

$tpl = new Template(resolveUp('rlstable.tpl.php'));
$tpl->set("accid",$accid);
$tpl->set("auth",$auth);
$tpl->set("lasttime",$lasttime);
$tpl->esc("mb",$mb);
$tpl->set("rows",$rows);
$tpl->set("rowCount",$rowCount);
$tpl->set("visibleCount",$count);
$tpl->set("displayedCount",$displayedCount);
$tpl->set("allCount",$allCount);
$tpl->set("pageLinks",$pageLinks);
$tpl->set("statusValues",$statusValues);
$tpl->set("showHidden",$showHidden);
$tpl->set("dicomStatus",$dicomStatus);
$content = $tpl->fetch();


$synch = time();

$newUrl = new_ccr_url($accid);

error_log("$newUrl");

if(isset($_REQUEST['widget'])) {
  $tpl = new Template(resolveUp('rlswidget.tpl.php'));
  $tpl->set("content", $content);
  $tpl->set("auth",$auth);
  $tpl->set("pid", $practicegroupid);
  $tpl->set("limit", $limit);
  $tpl->set("practicename", $practicename);
  $tpl->set("newUrl", $newUrl);
  $tpl->set("statusValues",$statusValues);
  $tpl->set("accid",$accid);
  $tpl->set("allCount",$allCount);
  $tpl->set("visibleCount",$count);
  $tpl->set("displayedCount",$displayedCount);
  $tpl->set("rowCount",$rowCount);
  $tpl->set("searchPatientName",$searchPatientName);
  $tpl->set("searchLastUpdate",$searchLastUpdate);
  $tpl->set("searchPurpose",$searchPurpose);
  $tpl->set("searchStatus",$searchStatus);
  $tpl->set("showHidden",$showHidden);
  $tpl->set("dicomStatus",$dicomStatus);
  echo $tpl->fetch();
}
else {
 echo "<table id='rlsUpdate'><tbody>$content</tbody></table>";
}

?>

