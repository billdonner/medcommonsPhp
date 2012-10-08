<?php
//redccr.php (find the RED ccr associated with a particular account, and jump to the MedCommons Viewer)
require_once "dbparamsidentity.inc.php";
function doccr ($accid,$status,$redirurl){
  //error_log("accid=$accid status=$status redirurl=$redirurl");
	// find an entry in the CCR log for this account and status
	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	
	$query = "SELECT * from users where (mcid = '$accid')";

	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_ASSOC);
	$email = $a['email'];
	$fn = $a['first_name'];
	$ln = $a['last_name'];

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
	$idp = $l['idp'];
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
<img src='http://www.medcommons.net/images/smallwhitelogo.gif' />
<p>
The Emergency CCR for your account $accid registered to $fn $ln ($email) has been accessed from $remoteaddr 
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

		$head = "From: MedCommons@{$srv}\r\n" ."Reply-To: cmo.medcommons.net\r\n".
		"bcc: cmo@medcommons.net\r\n";
	$subjectline = "Emergency CCR Access for Your MedCommons Account $accid";
  if($GLOBALS['Disable_Account_Emails']!='true') {
    $stat = @mail($email, $subjectline, $message,$head."Content-Type: text/html; charset= iso-8859-1;\r\n");
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    if($stat) $stat = "ok $srv  elapsed $time"; else echo( "<small><b>mail delivery failed from $srv, mail follows:</b></small><br>
$head<br>
$subjectline<br>
$message<br>" );
  }
/* redirect to the right place, but in a new window*/
//$url=$redirurl."?guid=$guid";
$url=$redirurl."?accid=$accid";
$hurl=$GLOBALS['Homepage_Url']."/?f=erinprogress.htm";
$x=<<<XXX
<html><head><title>Redirecting to MedCommons Repository Gateway from redccr via $url</title>
<meta http-equiv="REFRESH" content="0;url='$hurl'"></HEAD>
  <SCRIPT language="JavaScript">
    function redir() {
      window.open('$url','ccrdisplay','toolbar=1,location=1,directories=1,status=1,menubar=1,scrollbars=1,resizable=1');
      //document.location.href='$url';
    }
  </SCRIPT>
</HEAD>
<body onload="redir();">
  <p>
  Please wait while we retrieve the Emergency CCR for $accid...
  <br>You may need to enable pop-ups to see this CCR
  </p>
</body>
</html>
XXX;
echo $x;
exit;

}


/*** start of main program ***/


$accid=$_REQUEST['accid'];
$returnurl = $_REQUEST['returnurl'];
$redirurl = $_REQUEST['redirurl'];
doccr($accid,"RED",$redirurl);
/* only returns if it fails*/
header ("Location: $returnurl&lookupfailure");
echo "Redirecting to $returnurl lookupfailure";
exit;

?>
