<?php

require 'email.inc.php';

function onemail($recipient) {

  $srvname = $_SERVER['SERVER_NAME'];

  $srva = $_SERVER['SERVER_ADDR'];
  $srvp = $_SERVER['SERVER_PORT'];
  $gmt = gmstrftime("%b %d %Y %H:%M:%S")." GMT";
  $uri = htmlspecialchars($_SERVER ['REQUEST_URI']);

  $text = <<<EOF
The SMTP server is on $srvname ($srva:$srvp)

The MedCommons Team

Thank you for using MedCommons.
EOF;

  $html = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv='Content-Type' content="text/html; charset=iso-8859-1" />
    <title>MedCommons Test Message Sent From $srvname at $gmt</title>
  </head>
  <body>
    <p>
    <img src='cid:logo' />
    <br />
    <small>The SMTP server is on $srvname ($srva:$srvp)</small>
    </p>

    <p>The MedCommons Team</p>

    <p>Thank you for using MedCommons. </p>

  </body>
</html>
EOF;

  $stat = send_mc_email($recipient, "Test message from MedCommons $srvname",
			$text, $html,
			array("logo" => get_logo_as_attachment()));
     
  if ($stat)
    $stat = "ok";
  else
    $stat =  "send mail failure: $stat";

  return "SMTP test on $srvname ($srva:$srvp) returned with status $stat";
}

$recipient = $_REQUEST['to'];
if ($recipient == "")
  $recipient = "billdonner@gmail.com";

echo onemail($recipient);

?>
