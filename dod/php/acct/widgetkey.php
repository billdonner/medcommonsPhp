<?php
require_once "alib.inc.php";

// produce a key for the logged on user
$result = testif_logged_in (); // returns false if not logged on
if ($result!==false){
list($accid,$fn,$ln,$email,$idp,$cookie) = $result;
		$key = base64_encode(sha1($accid.$email).'|'.$accid.'|'.$email);
		list ($sha1,$accid,$email)=explode('|',base64_decode($key)); //if starting automagically
echo "Your MedCommons Widget Key for $accid email $email is <p><small><xmp>$key</xmp></small></p>";

echo "This key provides direct access to your account, use it wisely<br>";
}
else
{
	echo "You must be logged on to MedCommons to see your key<br>";
}
?>