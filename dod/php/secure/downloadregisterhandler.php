<?php

require_once "dbparamsmcextio.inc.php";

require 'email.inc.php';

function newmember ($email,$product)
{
	mysql_connect($GLOBALS['DB_Connection'],
			$GLOBALS['DB_User'],
			$GLOBALS['DB_Password']
			) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");

	$remoteaddr = $_SERVER['REMOTE_ADDR']; 

	// now write an entry in the mysql database

	$insert="INSERT INTO downloaders (email,remoteaddr,time)".
				"VALUES('$email','$remoteaddr',NOW()
				)";
	mysql_query($insert) or die("can not insert into table downloaders - ".mysql_error());
	
$homepageurl = $GLOBALS['Homepage_Url'];

		$homepagehtml= "<a href=$homepageurl>$homepageurl</a>";

		$a = $email;
		$b = $product;

		$html = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <meta http-equiv='Content-Type' content="text/html; charset=ISO-8859-1" />
  <title>Download Notification</title>
  <style type='text/css'><!--
body {
  background-color: white;
  color: black;
}
// --></style>
 </head>
 <body>
  <p>
  <img src='cid:logo' />
  <br />
  A MedCommons download of $b was requested by $a - $remoteaddr.
  </p>
 </body>
</html>
EOF;

		$text = <<<EOF
MedCommons

A MedCommons download of $b has occurred to $a - $remoteaddr.
EOF;

		$time_start = microtime(true);// this is php5 only

		$srv = $_SERVER['SERVER_NAME'];

		$subjectline = "Internal Download Notification of $b to $a - $remoteaddr";

		send_mc_email("billdonner@medcommons.net", $subjectline,
			      $text, $html,
			      array('logo' => get_logo_as_attachment()));

		$time_end = microtime(true);
		$time = $time_end - $time_start;
	
mysql_close();
	
}

// begin here
$GLOBALS['buf']='';
$email=$_POST['email'];
$product =$_POST['product'];
$returnurl = $_POST['returnurl'];
newmember ($email,$product);
header("Location: $returnurl");

?>