<?php
require_once "glib.inc.php";

function r($r)
{ if (isset($_REQUEST[$r]))
return $_REQUEST[$r];
else return false;
}
list($accid,$fn,$ln,$email,$idp,$coookie) = confirm_logged_in (); // does not return if not logged on
$db = connect_db(); // connect to the right database

$id = r('id');
$gid = r('gid');
$email = r('email');
$op = r('op');
$cl = r('cl');
//echo "toggletodir id $id gid $gid op $op email $email <br>";
if ($op!='NEW')
{
	$q="select * from todir where groupid ='$gid' and id='$id'";
	$result= mysql_query($q) or die ("can not select $q".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount!=0)
	{
		if ($op=='DELETE') {
			$q="delete from todir where groupid='$gid' and id='$id'";
			$result = mysql_query($q) or die ("Can not delete todir $q ".mysql_error());
		}
		else
		{
			// already in the todir, just update
			$xtra = '';
			$sharedgroup = r('sharedgroup');
			if ($sharedgroup!==false)
			{
				$sharedgroup = ($sharedgroup=='SOLO')?'SHARED':'SOLO';
				$xtra.= "sharedgroup='$sharedgroup'";
			}
			$pin = r('pin');
			if ($pin!==false) {
				$pin = ($pin=='NOPIN')?'PIN':'NOPIN';
				if ($xtra!='') $xtra.= ',';
				$xtra.="pinstate='$pin'";
			}

			if ($cl!='') {

				if ($xtra!='') $xtra.= ',';
				$xtra.="contactlist='$cl'";
			}
			$q= "update todir set ".$xtra." where id='$id' and groupid='$gid'";
			//		echo $q;
			$result = mysql_query($q) or die ("Can not update todir $q ".mysql_error());
		}
	}
}
else {
	//  op = new rowcount = 0 means new entry in todir
	$q= "insert into todir set  groupid='$gid', alias='$email', accid = '$accid', pinstate='NOPIN', sharedgroup='SHARED',
				contactlist = '$cl'";
	//	echo $q;
	$result = mysql_query($q) or die ("Can not insert into $q ".mysql_error());
}
//exit;
//finally goto the user's start page
//require_once "../acct/goStart.php";
header("Location: ../acct/goStart.php")
?>