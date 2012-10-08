<?php

// purchase.php
//
//	sku - the product
//	id - the medcommons account id
//	cc - the credit card selector /nikname
require_once "dbparamspay.inc.php";
require_once "../acct/appsrvlib.inc.php";

require_once "payviacc.inc.php";


function choosecreditcard ($accid,$price,$ret)
{	
	$pp = prettyprice($price);
echo frontmatter("Choose Credit Card");
$select = "SELECT * FROM ccdata WHERE (accid ='$accid')";
$result = mysql_query($select) or error_redirect("sql err ".mysql_error());
if (0==mysql_num_rows($result))
{ echo "No credit cards associated with this account"; }
else {
	if ($price!='') {
		echo "<h4>Please choose a credit card to charge $pp</h4><table>";
	} else
	{
		echo "
		<h4>Manage Your Cards</h4><table >";	}

		while ($l=mysql_fetch_assoc($result))
		{
			$nikname= $l['nikname'];
			$cardnum = $l['cardnum'];
			$pcardnum = "**** **** **** ".substr($cardnum,12,4);
			if ($price!='') $which= 'payviaccfin.php'; else $which='';
			if ($price!='') $op = 'select'; else $op = 'edit';
			if ($price!='') $del = ''; else $del = "<a href='delcc.php?price=$price&accid=$accid&cc=$nikname'>delete</a>";
			echo "<tr><td>$nikname</td>
			<td><a href='$which?price=$price&cc=$nikname&ret=$ret'>$op</a></td>
			<td>$pcardnum</td>
			<td>$del</td>
			</tr>";
		}
		echo "</table>";
}
$name=''; $address=''; $city=''; $state=''; $zip=''; $cardnum=''; $expdate=''; $nikname='';
// put a form right here to absorb info"
if ($price!='') $x='<small><a href=payviacc.php>add or edit cards</a> - use all 999s for a free ride</small> '; else
$x = <<<XXX
		<h4>Add a new credit card to MedCommons Account $accid</h4>
<form method="POST" name = "addcc" id = "addcc" action="addcc.php">
<input type="hidden" name="mcid" value="$accid">
<input type="hidden" name="ret" value="$ret">
<p><small>Please give this card a nikname:<input type="text" name="NIKNAME" value="$nikname"></small>
<br><small>Please enter all fields exactly as they appear on your credit card:</small>
<table border="0">
<tr><td>Name:</td><td><input type="text" name="NAME" value="$name"></td></tr>
<tr><td>Address:</td><td><input type="text" name="ADDRESS" value="$address"></td></tr>
<tr><td>City:</td><td><input type="text" name="CITY" value="$city"></td></tr>
<tr><td>State:</td><td><input type="text" name="STATE" value="$state"></td></tr>
<tr><td>Zip:</td><td><input type="text" name="ZIP" value="$zip"></td></tr>
<tr><td>Cardnum:</td><td><input type="text" name="CARDNUM" value="$cardnum"></td></tr>
<tr><td>Expdate:</td><td><input type="text" name="EXPDATE" value="$expdate"></td></tr>
</table>
<input type='submit' value='add CC'>
</p>
</form>
XXX;
echo $x."</body></html>";
exit;
}
//main starts here
$price= $_REQUEST['price'];
$ret = $_REQUEST['ret'];
$check = $_REQUEST['check'];
if ($ret=='') $ret = $GLOBALS['Payments_Url']."paydone.php?arg=alldone";

list($accid,$fn,$ln,$email,$idp,$cookie) = pconfirm_logged_in (); // does not return if not lo
$db = pconnect_db(); // connect to the right database
// if there is no sku, then just manage the credit cards and exit
if ($price=='') { choosecreditcard($accid,$price,$ret); exit; }

// ask the user which credit card, or give him an opportunity to add one
$status=choosecreditcard($accid,$price,$ret);
/*
$status = purchase(
$sku, // product name
$accid,// account id
$cc, // credit card selector
$ret); //where to continue
*/
// if we get here it is due to a problem with the purchase

echo "Failure return from purchase in payviacc.php status = $status";
exit;
?>