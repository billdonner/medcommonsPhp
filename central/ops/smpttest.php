<?php

function onemail( $recipient)		 
				 { 
$srvname = $_SERVER['SERVER_NAME'];

$srva = $_SERVER['SERVER_ADDR'];
$srvp = $_SERVER['SERVER_PORT'];
$gmt = gmstrftime("%b %d %Y %H:%M:%S")." GMT";
$uri = htmlspecialchars($_SERVER ['REQUEST_URI']);
$message = <<<XXX
<HTML><HEAD><TITLE>MedCommons Test Message Sent From $srvname at $gmt </TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<p>
<small>The  SMTP server is on $srvname ($srva:$srvp)</small><br>

<p>The MedCommons Team</p>

<p>Thank you for using MedCommons. </p>

</BODY>
</HTML>
XXX;
			 
$stat = @mail($recipient,
	    "test message from MedCommons $srvname", 
		$message,
     "From: MedCommons@{$_SERVER['SERVER_NAME']}\r\n" .
     "Reply-To: cmo.medcommons.net\r\n" .
     "bcc: cmo@medcommons.net\r\n".
     "Content-Type: text/html; charset= iso-8859-1;\r\n"
     );
     
if($stat) $stat=  "ok"; else $stat =  "send mail failure: $stat";
return "Smtp Test on $srvname ($srva:$srvp) returned with status $stat";
}				 

$recipient = $_REQUEST['to'];
if ($recipient=="") $recipient = "billdonner@gmail.com";
echo onemail($recipient);

?>