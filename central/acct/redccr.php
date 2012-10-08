<?php
//redccr.php (find the RED ccr associated with a particular account, and jump to the MedCommons Viewer)
require_once "dbparams.inc.php";
function doccr ($accid,$status){
	// find an entry in the CCR log for this account and status
	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");

	$query = "SELECT * from ccrlog where (accid = '$accid') and (status='$status')";

	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;

	//	echo "numrows is $rowcount";
	$errcount=0; $blurb = "";
	$emit = "";
	if ($result=="") {$emit= "?no accounts?"; return $emit;}
	$l = mysql_fetch_array($result,MYSQL_ASSOC);
	$date = $l['date'];
	$samlidp = $l['samlidp'];
	$from = $l['src'];
	$to= $l['dest'];
	$subject = $l['subject'];
	$guid = $l['guid'];
	$status = $l['status'];
	if ($status=='RED') $rowclass = "class='emergencyccr'"; else $rowclass='';
	$freeride = "&p=99999";


	mysql_free_result($result);

	//errcount>0
	mysql_close();

$remoteaddr = $_SERVER["REMOTE_ADDR"];
$qs = $_SERVER["QUERY_STRING"];

	$homepageurl = $GLOBALS['Homepage_Url'];

	$homepagehtml= "<a href=$homepageurl>$homepageurl</a>";


	$message = <<<XXX

		
<HTML><HEAD><TITLE>Emergency CCR Access Notification</TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<img src=http://www.medcommons.net/images/smallwhitelogo.gif />
<p>
The Emergency CCR for account $accid registered to $fn $ln ($email) has been accessed from $remoteaddr $qs

<p>    
HIPAA Security and Privacy Notice: The Study referenced in this 
invitation contains Protected Health Information (PHI) covered under 
the HEALTH INSURANCE PORTABILITY AND ACCOUNTABILITY ACT OF 1996 (HIPAA).
The MedCommons user sending this invitation has set the security 
requirements for your access to this study and you may be required to 
register with MedCommons prior to viewing the PHI. Your access to this 
Study will be logged and this log will be available for review by the 
sender of this invitation and authorized security administrators. 
<p><small>For more information about MedCommons privacy and security policies, 
please visit $homepagehtml </small>
</BODY>
</HTML>
XXX;
	// the following would benefit from being moved to a separate routine as part of the parent class
	$time_start = microtime(true);// this is php5 only
	$email = "billdonner@gmail.com";
	$srv = $_SERVER['SERVER_NAME'];
	$head = "From: MedCommons@{$srv}\r\n" ."Reply-To: cmo.medcommons.net\r\n";
	$subjectline = "Emergency CCR Access for Account $accid";
	$stat = @mail($email, $subjectline,
	$message,$head."Content-Type: text/html; charset= iso-8859-1;\r\n"
	);
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	if($stat) $stat = "ok $srv  elapsed $time"; else die( "send mail failure from $srv elpased $time" );

	
	header("Location: https://gateway001.private.medcommons.net:8443/router/tracking.jsp?tracking=$guid$freeride");
	echo "Redirecting to    'https://gateway001.private.medcommons.net:8443/router/tracking.jsp?tracking=$guid$freeride'";
	exit;

}


/*** start of main program ***/


$accid=$_REQUEST['accid'];
$returnurl = $_REQUEST['returnurl'];
doccr($accid,"RED");
/* only returns if it fails*/
header ("Location: $returnurl");
echo "Redirecting to $returnurl";
exit;

?>