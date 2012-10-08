<?php


// this should runin the background, or  set  refresh=N to run as a webpage

$traceflag = false;

function trace($x){ global $traceflag; if ($traceflag) echo $x.'
';}
function isnumeric($e) {
	// true for cellphone email addresses only
	$pos = strpos($e,'@');
	if ($pos===false) return false;
	for ($i=0; $i<$pos; $i++)
	{ $c= substr($e,$i,1);
	if ('0'>$c) return false;
	if  ('9'<$c) return false;
	}
	return true;
}

function 	logpoll ($server,$delta,$results)
{


		$xml = @simplexml_load_string($results);
		if (isset($xml->Tests))
		{
			$tests = $xml->Tests;
			$now = time();
			$fcount = 0;
			$stmt = "INSERT into appl_perflog set server='$server', delta='$delta',time='$now' ";
			foreach ($tests->Test as $test)
			{
				if ($test->Status =='OK')
				{   $fcount++;
				$name = $test->Name; // this doesnt change properly, not a problem when only one per page
				$time = $test->TimeMsec;
				$name = 't'.str_replace(' ','',$name);
				$stmt .= ", $name=$time ";
				}
				else trace ("Server $server Bad Status $test->Status Name $test->Name");		
			}
			if ($fcount==0) trace ("Server $server No Good Tests"); else
			trace ($stmt);
			$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());

		}
		else trace ("Failed to parse response from server $server");

}

/// starts here
if (!isset($_REQUEST['refresh'])) $refresh=0; else
{
	$refresh=$_REQUEST['refresh'];
}
$commandmode = ($refresh!=0);


$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_User']= "medcommons";
$GLOBALS['DB_Password'] ='';

if (isset($_REQUEST['db'])) $GLOBALS['DB_Database'] =$_REQUEST['db'] ; //get db from command line
else $GLOBALS['DB_Database']='alertinfo';
$db=$GLOBALS['DB_Database'];
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

$cyclenum = 0;;

while (true) // if running from command line this will repeat
{
	$emailcount = $phonemailcount = 0;
	$starttime = time();
	$t = gmdate('M d Y H:i:s');
	if (!$commandmode) $top = "MedCommons Operations Appliance Performance Alerter running at $t --- "; else
	{

		$host = $_SERVER['HTTP_HOST']; $self = $_SERVER['PHP_SELF']; // these are not available when ryb on command line???

		$top = <<<XXX
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="MedCommons, Inc." />
		<meta name="robots" content="noindex,nofollow">
		<meta http-equiv='Refresh' content='$refresh' />
		<title>Appliance Performance Notifier - Checks All Servers Every $refresh Seconds from $host $self</title>
		</head>
		<body >
		<h2 id='title'> MedCommons Appliance Performance Cycle Running on $host at $t </h2>
<div id='content'>
XXX;
	}
	echo $top;
	$first = true;
	$slist = array();
	$stmt = "SELECT distinct server from appl_notifiers"; // get list of servers
	$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());
	while ($row = mysql_fetch_array($result)) $slist[] =$row['server'];
	mysql_free_result($result);

	foreach ($slist as $server)
	{
		$t = 'gmt '.gmdate('M d Y H:i:s');
		trace("<h4>$t >> Polling http://$server/router/SelfTest.action?fmt=xml </h4>");
		$greenball = "http://$server/router/SelfTest.action?fmt=xml";
		// measure time
		$t1 = microtime(true);
		$results = @file_get_contents($greenball);
		$t2 = microtime(true);
		$delta = round($t2-$t1,3);
		$count=strlen($results);
		if ($count>100)
		{	logpoll ($server,$delta,$results);
		trace( "<h4>$t >>Returned successfully</h4>");
		}
		else
		{
			//logpoll ($server,-1);
			trace("<h4>$t >>Did Not Return - out figuring notifications</h4>");
			$nlist = array(); $now = time();
			$stmt = "SELECT * from appl_notifiers where server='$server' "; // get list of servers
			$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());

			while ($row = mysql_fetch_array($result))
			{
				$endpoint = $row['endpoint'];
				$isnumeric =isnumeric($endpoint);
				if (($row['lasttime'] + $row['mintime']) <$now)
				{
					trace("<h5>$t >>Notifying Endpoint ".$endpoint."</h5>");
					if (!$isnumeric) {
						$t2 = 'gmt '.gmdate('M d Y H:i:s',$now+$row['mintime']);
						$msg = <<<XXX

						<b>Server Failure on $server</b>\n
						<p>At $t the periodic probe of <a href=$greenball >$greenball</a> failed.</p>\n
						<p>Some useful links: </p>\n
						<ul>\n
						<li><a href=https://$server/console >Appliance Console for $server</a>\n
						<small>may not work if server is kaput</small></li>\n
						<li><a href=https://globals.medcommons.net/iga/status >MedCommons Production Systems</a></li>\n
						<li><a href=https://ci.myhealthespace.com/iga/status >MedCommons Test Systems</a></li>\n
						</ul>\n
						<p>You will not be notified about any other problems with $server until $t2 at the earliest.</p>\n
<p><small>This mail produced by MedCommons on Demand Global Alerter.</small></p>\n
XXX;
						trace($msg); // normally goes in email
						$wrapped = <<<XXX
						<html>
						<head>
						<title>MedCommons Appliance Performance Failure on $server at $t</title>
						</head>
						<body>
						$msg
</body>
</html>
XXX;
						// To send HTML mail, the Content-type header must be set Bcc: billdonner@gmail.com
						$headers  = <<<XXX
						MIME-Version: 1.0
						User-Agent: MedCommons Mailer 1.0
						Content-type: text/html; charset=iso-8859-1
						To: $endpoint
From: ops@medcommons.net <ops@medcommons.net>
Reply-To:ops@medcommons.net
XXX;
						$status = mail($endpoint,"MedCommons Appliance Performance Failure on $server at $t",$wrapped,$headers);
						$emailcount++;

					}
					else
					{ // numeric email for a cellphone
						$headers  = <<<XXX
MIME-Version: 1.0
User-Agent: MedCommons Mailer 1.0
From: ops@medcommons.net 
XXX;
						$msg = <<<XXX
see https://globals.medcommons.net/iga/status and https://ci.myhealthespace.com/iga/status
XXX;
						$status = mail($endpoint,"$server Appliance Peformance Failure",$msg,$headers);
						$phonemailcount++;
					}
					if (!$status ) trace ("<h5>Mail was not accepted for delivery</h5><p>$headers</p>");
					$noticecount = $row['noticecount']+1;
					$stmt = "Update appl_notifiers set lasttime = '$now', noticecount ='$noticecount'
					where server='$server'  and endpoint ='$endpoint'"; // get list of servers
					mysql_query($stmt) or die("Can not $stmt ".mysql_error());
				} else
				{
					trace("<h5>$t >>Skipping Endpoint ".$row['endpoint']."</h5>");
				}
			}
			mysql_free_result($result);
		}
	}
	$t = 'gmt '.gmdate('M d Y H:i:s'); $cyclenum++;
	if (!$commandmode) echo " cycle $cyclenum sent $emailcount emails and $phonemailcount msgs at $t\r\n";
	else  {
		echo "</body></html>"; exit;  // once only for html page
	}
	$timenow = time();
	if ($timenow<($starttime+600)) sleep(600- ($timenow-$starttime));
} // of outer while loop
?>