<?php 
<?PHP 
// handles responses from verisign payflo manager
require_once "setup.inc.php";

$type = $_POST['TYPE'];
$authcode = $_POST ['AUTHCODE'];
$avsdata = $_POST ['AVSDATA'];
$hostcode = $_POST ['HOSTCODE'];
$pnref= $_POST['PNREF'];
$respmsg = $_POST ['RESPMSG'];
$result = $_POST['RESULT'];
$csmatch = $_POST['CSCMATCH'];
$custid = $_POST['CUSTID'];
$amount = $_POST['AMOUNT'];
$pprice = $_POST['USER1'];
$user2 = $_POST['USER2']; // still available, as is $user9
$mcid = $_POST['USER3'];
$posttime = $_POST['USER4'];
$partner = $_POST['USER6'];
$product = $_POST['USER5'];
$xid = $_POST['USER7'];
$remoteaddr = $_POST['USER8'];
$self = $_SERVER['PHP_SELF']; //$_POST['USER9'];

$now=time();





// now write an entry in the mysql database

$insert="INSERT INTO ccstatus (time,type,authcode,avsdata,hostcode,pnref,respmsg,csmatch,custid,amount,
		pprice,user2,mcid,posttime,product,partner,xid,remoteaddr,self) VALUES(".
" '$now','$type','$authcode','$avsdata','$hostcode','$pnref','$respmsg','$csmatch','$custid','$amount',
	           '$pprice','$user2','$mcid','$posttime','$product','$partner','$xid','$remoteaddr','$self')";

dosql($insert);


islog('Failed',$callerip,"type $type avsdata $avsdata hostcode $hostcode pnref $pnref respmsg $resmsg amount $amount");
echo "200 OK\r\n"; // indicate we have received the data


?>



?>