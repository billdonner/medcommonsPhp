 <?php
 
 //REST web service = returns basic demographic info for a given mcid
 

// bill - July 25, 2008 

require 'settings.php';
require 'mc.inc.php';
function query_demographics ($mcid ) {
	$sql = <<<EOF
SELECT email,email_verified,mobile,mobile_verified,acctype,first_name,last_name,photoURL
FROM   users
WHERE mcid = '$mcid'
EOF;

	$result = mysql_query($sql) or die ("Cant $sql ". mysql_error());
	return $result;
}
$GLOBALS['DB_Database']='mcx';
$GLOBALS['DB_User'] = 'medcommons';
$GLOBALS['DB_Connection']='mysql.internal';
$GLOBALS['DB_Password']='';

$q = $_REQUEST['q'];

$blurb = $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?q=$q 'return demographics re medcommons account $q?' ";


$db=$GLOBALS['DB_Database'];
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

$mcid = clean_mcid($q);
$result = query_demographics($mcid);
$r = mysql_fetch_object($result);
if (!$r) $err = "Unknown mcid $mcid"; 
else {
	$doc = "<appliance><request>$blurb</request><response><status>1</status>
	<info>
	<acctype>$r->acctype</acctype>
	<first_name>$r->first_name</first_name>
	<last_name>$r->last_name</last_name>
	<photoURL>$r->photoURL</photoURL>
	<email>$r->email</email>
	<email_verified>$r->email_verified</email_verified>
	<mobile>$r->mobile</mobile>
	<mobile_verfied>$r->mobile_verified</mobile_verfied>
	</info></response></appliance>";
	header("Content-type: text/xml");
	echo $doc;	exit;
}
$doc = "<appliance><request>$blurb</request><response><status>0</status><error>$err</error></response></appliance>";
header("Content-type: text/xml");
echo $doc;
exit;
?>


