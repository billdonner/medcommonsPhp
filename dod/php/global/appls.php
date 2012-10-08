<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="author" content="MedCommons, Inc." />
    <meta name="keywords" content="Appliance,CCR,PHR,HealthRecords" />
    <meta name="description" content="MedCommons Global Services" />
   <meta name="robots" content="noindex,nofollow">
       <meta http-equiv='Refresh' content='30' />
    <title>MedCommons Global Appliances Console</title>
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
			    MedCommons Global Appliances Console</h2>
        </div>
<div id='content'>
<?php 
//<img src="serverbadge.php?t=mc&url=http://mcid.internal:1080/status&name=Locals"/>
// get list of appliances if any
if (!isset($_REQUEST['a'])) $applcount=0; else
{
	$appls = explode('|',$_REQUEST['a']);
	$applcount = count($appls);
}
require_once "dbparams.inc.php";  // appliances table is in mcx

$db=$GLOBALS['DB_Database'];
mysql_pconnect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");
$first = true;

$stmt = "SELECT name, url, email FROM appliances";
$result = mysql_query($stmt) or die("Can not $stmt ".mysql_error());
echo "<table><tbody>\n";
while ($row = mysql_fetch_array($result)) {
	if ($applcount==0) $include = true; else
	if (in_array($row['name'],$appls)) $include = true;
	else $include=false;

	if ($include){
			$url = $row['url']; //show servers
		echo "\t<tr>\n";
		echo "\t  <td><b>" . $row['name'] . "</b></td>\n";
		echo "\t  <td><a href='" . $row['url'] . "'> "  . " homepage</a></td>\n";
		echo "\t  <td><a href='" . $row['url'] . "/console/'>"  . " console</a></td>\n";
		echo "\t  <td><a href='mailto:" . $row['email'] . "'>" . " contact</a></td>\n";

		echo "\t</tr>\n";
		
	$console_version=@file_get_contents("$url/console/media/revision.txt");
    $account_version = @file_get_contents("$url/acct/revision.txt");
    $secure_version=@file_get_contents("$url/secure/revision.txt");
    $gateway_version=@file_get_contents("$url/router/revision.jsp");
    
    		echo "\t<tr>\n";
		echo "\t  <td>versions - console:$console_version</td>\n";
		echo "\t  <td>/acct:$account_version</td>\n";
		echo "\t  <td>/secure:$secure_version</td>\n";
		echo "\t  <td>/gateway:$gateway_version</td>\n";

		echo "\t</tr>\n";
	
		$more = <<<XXX
      <tr>
      <td>&nbsp;</td>
      <td>
<img src="serverbadge.php?t=db&url=$url/centralstatus.php&name=DB" alt='DB' />
      </td>

      <td>
<img src="serverbadge.php?t=ap&url=$url/appsrvstatus.php&name=AP" alt='AP' />
      </td>
      <td>
<img src="serverbadge.php?t=gw&url=$url/router/status.do?fmt=xml&name=GW" alt="GW" />
      </td>
    </tr>
XXX;

		echo $more;

	}
}

echo "</table><br/></br></content></wrapper></body></html>";
?>