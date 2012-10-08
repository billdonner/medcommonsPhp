<?php

require_once "dbparamsmcextio.inc.php";

function emit($s)
{
	$GLOBALS['buf'].=$s;
};

function by($s)
{   $count = strlen ($s);
echo "bytecount ".strlen($s)." ";
for ($i=0; $i<$count; $i++)
echo $i." ".substr($s,$i,1)."*";
}



function newmember ($email)
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
$b = "Firefox Plug-in 0.9.5";

		$message = <<<XXX

		
<HTML><HEAD><TITLE>Download Notification</TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<img src=http://www.medcommons.net/images/smallwhitelogo.gif />
<p>
A MedCommons download of $b has occurred to $a - $remoteaddr
XXX;
		$time_start = microtime(true);// this is php5 only

		$srv = $_SERVER['SERVER_NAME'];
        $to = "From: MedCommons@{$srv}\r\n" ."Reply-To: cmo.medcommons.net\r\n";
		$subjectline = "Internal Download Notification of $b to $a - $remoteaddr";
	$stat = @mail("cmo@medcommons.net", $subjectline,
		$message,$to."Content-Type: text/html; charset= iso-8859-1;\r\n"
		);
		$time_end = microtime(true);
		$time = $time_end - $time_start;
	
 emit("Thank you $email. We will contact you when a new version of CCR Send is available");
mysql_close();
	
}


$email=$_POST['email'];
$returnurl = $_POST['returnurl'];
newmember ($email);
header("Location: $returnurl");
echo "Redirecting $email from downloadregisterhandler to $returnurl";
?>