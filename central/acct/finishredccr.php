<?php

// change the entry marked RED, then return back to caller
//
//&id = internal id of record to mark red
//&returnurl = where to return to

require "dbparams.inc.php";





$returnurl = $_REQUEST['returnurl'];
$id = $_REQUEST['id'];
$submit = $_REQUEST['submit'];

$accid = $_REQUEST['accid'];
$fn = $_REQUEST['fn'];
$ln = $_REQUEST['ln'];
$email = $_REQUEST['email'];
$from = $_REQUEST['from'];


if ($submit!='Cancel') {

	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");


	$query = "SELECT * FROM ccrlog WHERE (id = '$id')";


	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) die("bad id $id in query of ccrlog");

	$record = mysql_fetch_array($result,MYSQL_ASSOC);

	$testaccid = $record ['accid'];
	if ($testaccid != $accid) die ("bad accid $accid not equal $testaccid");
	mysql_free_result($result);

	$update1 = "UPDATE ccrlog SET status='WASRED' WHERE (accid = '$accid') AND (status ='RED')";
	$result = mysql_query ($update1) or die("can not update1 table ccrlog - ".mysql_error());

	$update2 = "UPDATE ccrlog SET status='RED' WHERE (id ='$id')";
	$result = mysql_query ($update2) or die("can not update2 table ccrlog - ".mysql_error());

	mysql_close();



	$homepageurl = $GLOBALS['Homepage_Url'];

	$homepagehtml= "<a href=$homepageurl>$homepageurl</a>";


	$message = <<<XXX

		
<HTML><HEAD><TITLE>Emergency CCR Reset Notification</TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<img src=http://www.medcommons.net/images/smallwhitelogo.gif />
<p>
The Emergency CCR for account $accid registered to $fn $ln ($email) has been reset to $id

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

	$srv = $_SERVER['SERVER_NAME'];
	$head = "From: MedCommons@{$srv}\r\n" ."Reply-To: cmo.medcommons.net\r\n";
	$subjectline = "Emergency CCR Reset for Account $accid";
	$stat = @mail($email, $subjectline,
	$message,$head."Content-Type: text/html; charset= iso-8859-1;\r\n"
	);
	$time_end = microtime(true);
	$time = $time_end - $time_start;
	if($stat) $stat = "ok $srv  elapsed $time"; else die( "send mail failure from $srv elpased $time" );

}


header ("Location: $returnurl");
echo "Mail $stat; Redirecting to $returnurl";

?>