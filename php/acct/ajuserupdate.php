<?php
require_once "dbparamsidentity.inc.php";
require_once "ccrloglib.inc.php";

//respond to ajax updates on the users info
$accid= $_GET['accid'];
$namesx = $_GET['names'];
$names = explode(',',$namesx);
$valuesx = $_GET['values'];
$values = explode(',',$valuesx);
$lim = min(count($names),count($values))-1;
$ob = '';
$ab ='';
for ($i=0; $i<=$lim; $i++) {
	$acc = 0;
	switch($names[$i])
	{// translate into either primary or secondary db, and straighten out field names
	case 'fn' : $n="first_name"; $acc=1; break;
	case 'ln' : $n="last_name"; $acc=1;break;
	case 'email': $n="email";$acc=1;break;
	case 'telephone': $n="mobile";$acc=1;break;
	
	case 'street1': $n="address1"; break;
	case 'street2': $n="address2"; break;
	case 'city': $n="city"; break;
	case 'state': $n="state"; break;
	case 'postcode': $n="postcode"; break;
	case 'country': $n="country"; break;
	
	default: $acc=2; break; // don't forget it might be some other tag that will throw us off
	
	}
	if ($acc==1){
		$ob .= $n."='".$values[$i]."',";
	
	} else if ($acc==0){
		$ab .= $n."='".$values[$i]."',";
	
	}
}
$timenow = time(); // always update the account record to indicate the last time 
$ob .= "updatetime = '$timenow',"; 
//
// open database and get account info
$db=$GLOBALS['DB_Database'];

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

if ($ob!='')
{
	$ob= "UPDATE users SET ".substr($ob,0,strlen($ob)-1);

	$query = "$ob where (mcid = '$accid')";

	$result = mysql_query ($query);
	if ($result==false)
	{
		echo "<p>can not update table users - ".mysql_error()."</p>"; exit;
	}
	$rowcount = mysql_affected_rows();
	if ($rowcount == 0) {echo "<p>can not update table users - rowcount==0</p>"; exit;}
	else $ret = "Successfully updated account $accid";
}
if ($ab!='')
{
	$ab= "UPDATE addresses SET ".substr($ab,0,strlen($ab)-1);

	$query = "$ab where (mcid = '$accid')";

	$result = mysql_query ($query);
	if ($result==false)
	{
		echo "<p>can not update table addresses - ".mysql_error()."</p>"; exit;
	}
	$rowcount = mysql_affected_rows();
	if ($rowcount == 0) { $ret = 'No changes were made to addresses table';}
	else $ret = "Successfully updated account $accid";
	
}

// now re-read the account record to re-establish the patientcard

	$query = "SELECT * from users where (mcid = '$accid')";

	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) { echo "cant find account"; return false;}
	$a = mysql_fetch_array($result,MYSQL_ASSOC);
	$email = $a['email'];
	$fn = $a['first_name'];
	$ln = $a['last_name'];
	$mobile = $a['mobile'];
	$patientcard = patientCard($fn,$ln,$email,$accid);


$synch = time();

echo "<?xml version='1.0' encoding='UTF-8'?><ajreturnblocks><patientcard>$patientcard</patientcard><status>$ret</status><timesynch>$synch</timesynch></ajreturnblocks>";

?>