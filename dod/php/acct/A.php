<?php

require_once 'alib.inc.php';

function ccrlog_row($r) 
{
	$guid = $r->guid;
	$tracking = $r->tracking;
	$to = $r->dest; // found by phplint
	$subject = $r->subject;
	$id = $r->id;

	$out= "
          <doc>
            	<accid>$r->accid</accid>
                <tndate>$r->date</tndate>
                <trackingnumber>$tracking</trackingnumber>
                <to>$to</to>
                <subject>$subject</subject>
                <guid>$guid</guid>
                <status>$r->status</status>
          </doc>  ";

	
	return $out;
}

function A ($format, $gid,$limit,$filter)
{
// builds either a full system log ($gid==0) or a group level log

// the algorith for inclusion
 

//build menu to present from arg
// get settings for how to behave
$db = aconnect_db(); // connect to the right database
if ($gid =='') $q="select *, DATE_FORMAT(date, '%c/%d/%Y %H:%i') as prettydate from ccrlog"; else 
$q = "SELECT *,DATE_FORMAT(date, '%c/%d/%Y %H:%i') as prettydate from ccrlog,groupmembers where memberaccid=accid and groupinstanceid = '$gid' $filter";
$q.= " order by date DESC LIMIT $limit";

$result = mysql_query($q) or die ("can not query $q ".mysql_error());


//echo "A: format $format \r\n";
if ($format=='xml') {
$out= "<account_details>";
	while (
		$l = mysql_fetch_object($result))
		$out.=ccrlog_row($l);
	
$out.= "</account_details>";
header ("Content-type: text/xml");
echo $out;
} else if ($format=='pipe')
{

	while (
		$l = mysql_fetch_object($result))
		if (isset($out)) $out.='|'.$l->guid; else $out=$l->guid;
	
header ("Content-type: text/plain");
echo $out;
}else 
die ("unknown format type");

}

if (!isset($_REQUEST['format'])){
	$format='pipe'; 
}
else $format = $_REQUEST['format'];

if (isset($_REQUEST['mckey'])) list($sha1,$accid,$email) = explode('|',base64_decode($_REQUEST['mckey'])); else 
 list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in();
 

A($format,0,20,'');
?>