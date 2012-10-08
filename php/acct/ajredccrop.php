<?php 
// ajax server side call to
// change the entry marked RED, then return back to caller
// then repaints the whole tabs structure
//&id = internal id of record to mark red

require_once "dbparamsidentity.inc.php";
require_once "ccrloglib.inc.php"; // the hard work is all in here
require 'email.inc.php';

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

if ($op==1) // case where we are setting, clear existing
{
	$update1 = "UPDATE ccrlog SET status='WASRED' WHERE (accid = '$accid') AND (status ='RED')";
	$result = mysql_query ($update1) or die("can not update1 table ccrlog - ".mysql_error());

	$update2 = "UPDATE ccrlog SET status='RED' WHERE (id ='$id')";
	$result = mysql_query ($update2) or die("can not update2 table ccrlog - ".mysql_error());

}
else // case where we are clearing, just do it
{
	$update1 = "UPDATE ccrlog SET status='WASRED' WHERE (accid = '$accid') AND (status ='RED') AND (id='$id')";
	$result = mysql_query ($update1) or die("can not update1 table ccrlog to clear - ".mysql_error());
}

mysql_close();



$homepageurl = $GLOBALS['Homepage_Url'];

$t = new Template();
$t->set('accid', $accid);
$t->set('homepageurl', $homepageurl);
$text = $t->fetch(email_template_dir() . "resetText.tpl.php");
$html = $t->fetch(email_template_dir() . "resetHTML.tpl.php");

$time_start = microtime(true);// this is php5 only

$srv = $_SERVER['SERVER_NAME'];
$subjectline = "Emergency CCR Changed for Account $accid";

$stat = send_mc_email($email, $subjectline,
		      $text, $html,
		      array('logo' => get_logo_as_attachment()));

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

echo "<ajblock><patientcard>$patientcard</patientcard><content>$alltabs</content><timesynch>$synch</timesynch><emergencyccr>$emergencyccr</emergencyccr><status>Emergency CCR has been reset</status></ajblock>";

exit;

?>
