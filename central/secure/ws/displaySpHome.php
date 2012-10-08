<?php
require_once "../dbparams.inc.php";
$trackinghandler = $GLOBALS['uri_trackinghandler'];
$terr = urlencode("bad tracking number");
$returnurl = $GLOBALS['uri_returnurl']."?trackingerr=$terr";
$trackingerr =$_REQUEST['trackingerr'];
if ($trackingerr=="") $trackingerr = "MedCommons Tracking No: ";
$hostname = "sp.".$GLOBALS['DB_Database'].".".$_SERVER['SERVER_NAME'];


$x=<<<XXX
<html><head><title>MedCommons Service Provider -- Do You Really Want to be here?</title>

<script language="javascript">
function init() {  
}
</script>
</head>

<body onload="init();">
<table><tr><td><img src=../images/MEDcommons_logo_246x50.gif></td>
<td><h2>MedCommons Policy Provider on $hostname</h2>
<small>You can't do much here.
<br> There are no accounts.
<br>You can do POPS</small></td>
<td><form name="trackingForm" method="post" action=$trackinghandler target="_top">
    <input type="hidden" name="returnurl" value=$returnurl>
    <small>$trackingerr </small>
<input style="width:180px; border:1px solid #000; background-color:#FFFFFF; margin: 30px 0px 0px 10px;" type="text" name="trackingNumber" class="AcctNumBox">
    <input  type="submit" value="track" >
  </form>
</td></tr>
</table>
<ul>Some places you might rather be:
<li><a href="../idp/mc/home.php" >MedCommons IdP - familiar look and feel</a></li>
<li ><a href="http://virtual03.medcommons.net/idp/dr101/home.php">Dr101 IdP</a></li>
<li ><a href="http://virtual03.medcommons.net/idp/bfh/home.php">BFH IdP</a></li>
</ul>

New -  <a href="https://secure.private.medcommons.net/xpi_warning.htm" target="_top">download MedCommons CCR Client</a>


<div id="footer">This is a MedCommons Test Site &copy; 2005 MedCommons all rights reserved.&nbsp;&nbsp;&nbsp;page last modified:&nbsp;<script type="text/javascript"> document.write(document.lastModified); document.logonForm.trackingNumber.focus();</script></div>
</body>
</html>
XXX;
echo $x;
?>
