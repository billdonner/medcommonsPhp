<?php 
// ajax server side call to
// change the entry to DELETED then return back to caller
// then repaints the whole tabs structure
//&id = internal id of record to mark red

require_once "dbparams.identity.inc.php";
require_once "ccrloglib.inc.php"; // the hard work is all in here

$op = $_REQUEST['op'];
$id = $_REQUEST['id'];
$accid = $_REQUEST['accid'];


$db=$GLOBALS['DB_Database'];

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");


$query = "SELECT * FROM ccrlog WHERE (id = '$id') and (accid = '$accid')";
$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
$rowcount = mysql_num_rows($result);
if ($rowcount == 0) die("bad id $id in query of ccrlog");
$record = mysql_fetch_array($result,MYSQL_ASSOC);

mysql_free_result($result);

	$timenow=time();												
	
	$ob= "UPDATE users SET  ccrlogupdatetime = '$timenow' where (mcid = '$accid')";
		$result = mysql_query ($ob) or die("can not update1 table users - ".mysql_error());


	$update1 = "UPDATE ccrlog SET status='DELETED' WHERE (accid = '$accid') AND (id='$id')";
	$result = mysql_query ($update1) or die("can not update1 table ccrlog to clear - ".mysql_error());


mysql_close();



$homepageurl = $GLOBALS['Homepage_Url'];

$homepagehtml= "<a href=$homepageurl>$homepageurl</a>";


$message = <<<XXX

		
<HTML><HEAD><TITLE>Emergency CCR Deletion Notification</TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<img src=http://www.medcommons.net/images/smallwhitelogo.gif />
<p>
A CCR from account $accid has been deleted.
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
$subjectline = "CCR Deleted from Account $accid";
$stat = @mail($email, $subjectline,
$message,$head."Content-Type: text/html; charset= iso-8859-1;\r\n"
);
$time_end = microtime(true);
$time = $time_end - $time_start;
if($stat) $stat = "ok $srv  elapsed $time"; else die( "send mail failure from $srv elpased $time" );

// the flow at this point is quite similar to myccrlogview, but the whole page does not have to be rendere

$idp = '';
// get passed in parameters
$idplogo = $_REQUEST['idplogo'];
if ($idplogo =='') $idplogo="images/MEDcommons_logo_246x50.gif";
$idpdomain = $_REQUEST['idpdomain'];
if ($idpdomain =='') $idpdomain="www.medcommons.net";
$miniview=true;
$accid=$_REQUEST['accid'];
$from=stripslashes($_REQUEST['from']);
// do a bunch of database reads to get rows from ccr log, sorted by idp
$count = readdb(true,$accid,$from,$content,$tab,$emailbuf,$fn,$ln,$email,$street1,$street2,
$city,$state,$postcode,$country,$mobile,$emergencyccr,$patientcard,$einfo,$trackerdb);
// put together tab0
$tab0content = tab0(true,$email,$mobile,$ln,$fn,$street1,$street2,$city,$state,$postcode);
// assemble all the tabs
$alltabs = assembletabs($miniview,$count,$content,$tab,$tab0content);
// echo back the whole div
$synch = time();
//<patientcard>$patientcard</patientcard><emergencyccr>$emergencyccr</emergencyccr><timesynch>$synch</timesynch><content>$alltabs</content><emergencyccr>$emergencyccr</emergencyccr>

echo "<ajblock><patientcard>$patientcard</patientcard><content>$alltabs</content><timesynch>$synch</timesynch><emergencyccr>$emergencyccr</emergencyccr><status>CCR has been deleted</status></ajblock>";

exit;

?>
