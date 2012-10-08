<?php
require_once "alib.inc.php";
require_once "layout.inc.php";

$text = <<<XXX
<h4>We are working hard to make sure you can integrate MedCommons into your own applications,
web sites, wikis, blogs, and just about anywhere else.</h4>
<p class='p1'>Here is what is currently available:
<ul>
<li><a target='_new' href='Wx.html'>MedCommons Widgets</a> for incorporation into your site, your Google Homepage, and more</li>
<li><a target='_new' href='http://www.myhealthespace.com'>MedCommons Sample IDP</a> for federated identity management can be incorporated into your enterprise</li>
<li><a target='_new' href='/toys/Vxreadme.html' >Adapters</a> for hooking up blood pressure monitors, etc</li>
</ul>
XXX;

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
$db = aconnect_db(); // connect to the right database


echo std("Generate Code","Generate Code Swatches for $accid",false,
false, stdlayout ( $text));

?>