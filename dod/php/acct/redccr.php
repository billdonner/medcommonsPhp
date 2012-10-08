<?php

require 'email.inc.php';
require 'template.inc.php';
require 'settings.php';

// redccr.php (find the RED ccr associated with a particular account, and jump to the MedCommons Viewer)
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

	$t = new Template();
	$t->set('homepageurl', $homepageurl);
	$t->set('accid', $accid);
	$t->set('fn', $fn);
	$t->set('ln', $ln);
	$t->set('email', $email);
	$t->set('remoteaddr', $remoteaddr);

	$text = $t->fetch(email_template_dir() . "emergencyText.tpl.php");
	$html = $t->fetch(email_template_dir() . "emergencyHTML.tpl.php");

	$time_start = microtime(true);// this is php5 only

	$srv = $_SERVER['SERVER_NAME'];

	$subjectline = "Emergency CCR Access for Your $acApplianceName Account $accid";
  if(isset($GLOBALS['Disable_Account_Emails'])&&($GLOBALS['Disable_Account_Emails']!='true')) {

    $stat = send_mc_email($email, $subjectline,
			  $text, $html,
			  array('logo' => get_logo_as_attachment()));

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    if($stat) $stat = "ok $srv  elapsed $time"; else echo( "<small><b>mail delivery failed from $srv, mail follows:</b></small><br>
$head<br>
$subjectline<br>
$message<br>" );
  }
/* redirect to the right place, but NOT (wld 17 nov 06 in a new window*/
//$url=$redirurl."?guid=$guid";
$url=$redirurl."?accid=$accid";
/*
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
*/
// this just puts the ccr into the same window, for emergency access, no popup bullshit
$x=<<<XXX
<html><head><title>Redirecting to MedCommons for Emergency Room PHR Access via $url</title>
<meta http-equiv="REFRESH" content="0;url='$url'"></HEAD>
</HEAD>
<body>
  <p>
  Please wait while we retrieve the Emergency PHR for $accid...
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
header ("Location: $returnurl?&lookupfailure");
echo "Redirecting to $returnurl lookupfailure";
exit;

?>
