<?php 
// if info is set, then we are going to add a line
if (!isset($_POST['info'])) $info=''; else $info=$_POST['info'];
if (!isset($_POST['tags'])) $tags=''; else $tags=$_POST['tags'];
if (!isset($_POST['appliance'])) $appliance=''; else $appliance=$_POST['appliance'];
if (!isset($_GET['search'])) $search=false; else
{
	$search = true;
}
if (!isset($_GET['max_items'])) $maxi=10; else
{
	$maxi = $_GET['max_items'];
}
require_once "dbparams.inc.php";  // appliances table is in mcx

$db=$GLOBALS['DB_Database'];
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

if ($info!='')
{
	//	should clean this up into a proper REST call
	$stmt = "SELECT * FROM appliances where name='$appliance'";
	$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());
	if ($row = mysql_fetch_array($result)) {
		// ok, write to the log,
		$stmt =  $sql = "INSERT INTO `appliances_log` (`appliance`, `tag`, `timestamp`, `loginfo`) VALUES ('$appliance', '$tags', NOW(), '$info')";
		$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());
		header("Content-type: text/html",false,200);
		echo "<applog_status>OK</applog_status>";
		exit;
	}
	else {
		header("Content-type: text/html",false,400);
		echo "<applog_status>Bad Appliance</applog_status>";
		exit;
	}
}
else {
	// all other cases

	$top=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="author" content="MedCommons, Inc." />
    <meta name="keywords" content="Appliance,CCR,PHR,HealthRecords" />
    <meta name="description" content="MedCommons Global Services" />
   <meta name="robots" content="noindex,nofollow">
       <meta http-equiv='Refresh' content='30' />
    <title>MedCommons Global Appliances Log</title>
    <link rel="shortcut icon" href="images/favicon.gif" type="image/gif" />
    <style type="text/css" media="all">
@import "/style.css";
	</style>
    <script type="text/javascript" src="/base.js"></script>
 </head>
<body onload='www_init()'>
    <div id='wrapper'>
        <div id='header'>
        <img id='pbmclogo' alt='Powered by MedCommons' height='50px'
					src='/images/PBMC_138x30.png' />
        <h2 id='title'>
			    MedCommons Global Appliances Log</h2>
        </div>
<div id='content'>
XXX;
	if ($search==true) {
		$stmt = "SELECT * FROM appliances_log order by timestamp desc limit $maxi";
		$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());
		echo $top."<table><tbody>\n";
		while ($row = mysql_fetch_array($result)) {

			echo "\t<tr>\n";
			echo "\t  <td>".$row ['timestamp']."</td>\n";
			echo "\t  <td>" . $row['appliance'] . "</td>\n";
			echo "\t  <td>".$row ['tag']."</td>\n";
			echo "\t  <td>".$row ['loginfo']."</td>\n";
			echo "\t</tr>\n";
		}

		echo "</table>";


	}

	else {
		// this case with no arg supplied, put up a little test form
		$form = <<<XXX
	<h3>POST Entry to Appliances Log</h3>
	<p>Should only be here if we have a valid medcommons operations cert</p>
	<p>To Search the log add ?search and optional &max_items=NN to URI</p>
	<p>This form sends a POST to Add an Entry to the Appliances Log, your program should do the same</p>
		<form method="POST" action=applog.php>
		<label for="ap">Appliance:</label> 
			<INPUT TYPE="TEXT" id="ap" NAME="appliance" /><br />
		<label for="ta">Tags:</label> 
			<INPUT TYPE="TEXT" id="ta" NAME="tags" /><br />
		<label for="in">Log Info:</label> 
			<INPUT TYPE="TEXT" id="in" size='80' NAME="info" /><br />
			<input type="SUBMIT" value="SEND POST REQUEST"/>
		</form>
XXX;
		echo $top.$form;
	}
	echo"</content></wrapper></body></html>";
}
?>