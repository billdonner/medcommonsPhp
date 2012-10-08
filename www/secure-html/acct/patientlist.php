<?php  
require_once "dbparamsidentity.inc.php";
function prettyguid($guid)
{	$size = strlen($guid);
	return ( substr($guid,0,4)."..".substr($guid,$size-4,4));
}
function lookup ($username, $idp){

	// given the external name and provider id, find the mcid
	$query = "SELECT * from identity_providers where (name = '$idp')";

	$result = mysql_query ($query) or die("can not query table external_users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_ASSOC);
	$providerid = $a['id'];	
	
	// given the external name and provider id, find the mcid
	$query = "SELECT * from external_users where (username = '$username') and (provider_id ='$providerid')";

	$result = mysql_query ($query) or die("can not query table external_users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_ASSOC);
	$accid = $a['mcid'];
	$idpclause = "and (ccrlog.idp ='$idp') ";
	if ($idp=='')$idpclause="";
	
	$query = "SELECT COUNT(*) from ccrlog where (accid = '$accid') and (status <> 'DELETED') $idpclause;";

	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) return false;
	$a = mysql_fetch_array($result,MYSQL_NUM);
	$counter = $a[0];
	
	mysql_free_result($result);

	return $counter;
}
//
//
// start here
//
//

/*
if($GLOBALS['NO_CCRLOG_LOGIN_CHECK']!=true) {
	$mc = $_COOKIE['mc'];
	if ($mc =='')	
		{ echo "You Must Logged On to MedCommons to view the Patient List"; exit;}
}
*/
$userlist=$_REQUEST['ul'];
$idp=stripslashes($_REQUEST['idp']);
$callback = $_REQUEST['cb'];


	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	
	mysql_select_db($db) or die ("can not connect to database $db");

$usernames = explode(':',$userlist);
$count = count($usernames);
$emit="<ul>";
for ($i=0;$i<$count;$i++) {
	
	$counters[$i]=lookup($usernames[$i],$idp);
 	$emit.= "<li><a target='_parent' href='$callback?idp=$idp&u=$usernames[$i]'>$usernames[$i]($counters[$i])</a></li>";

}
$emit .="</ul>";
                       
                                                  
$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons patientList"/>
        <meta name="robots" content="all"/>
        <title>MedCommons Patient List</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css"; </style>
    </head>

<body>
        <div id="container">
                 <div id="supportingText" title="MedCommons Patient List">
                <div id="patientCCRLog">
                    <h3>
                        <span>Patient List</span>
                    </h3>
                    <p class="p1">
                        <table>
                            $emit
                        </table>
                    </p>
                </div>
  
                  
 			 <div id="footer"> 
                              <p class = "p2"><img src = "http://www.medcommons.net/images/tinywhitelogo.gif"></p>
            </div>
            <!-- Add a background image to each and use width and height to control sizing, place with absolute positioning -->
             </div>             
    </body>
XXX;


echo $x;
?>
