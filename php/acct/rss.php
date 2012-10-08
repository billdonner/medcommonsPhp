<?php
//generate RSS feed
// if logged in, it will default to the current account's feed
//
require_once "alib.inc.php";

function erroret()
{
	echo "
<rss version='2.0'>
<channel>
<title>MedCommons RSS Feed - login required</title>
<link>http://www.medcommons.net</link>
<description>MedCommons Personal healthURL Feed</description>
<category>healthURL, health URL</category>
<generator>medcommons development team</generator>
<webMaster>cmo@medcommons.net</webMaster>
<language>en-us</language>
<copyright>Copyright 2007 MedCommons, Inc</copyright>
<image>
<title>MedCommons > Home Page</title>
<url>http://www.medcommons.net/images/mclogo.gif</url>
<link>http://www.medcommons.net/index.html</link>
</image>
<item>
<title>Please login to your appliance</title>
<link>../index.html</link>
<description>You must be logged in to your medcommons appliance to enjoy all of its benefits</description>
<author>MedCommons, Inc.</author>
</item>
</channel>
</rss>
";
}
// start here, we will make a feed no matter what

header("Content-type: text/xml \r\n");
echo '<?xml version="1.0" encoding="UTF-8"?>';

$v = testif_logged_in();
if ($v===false)
{
	if (!isset($_REQUEST['id'])) {erroret(); exit;}
	$accid = $_REQUEST['id'];
}
else {
	if (isset($_REQUEST['id']))
	$accid = $_REQUEST['id']; else
	list ($accid,$fn,$ln,$email,$idp,$mc) = $v;
}



$db = aconnect_db(); // connect to the right database


echo "
<rss version='2.0'>
<channel>
<title>MedCommons RSS Feed for healthURL $accid</title>
<link>http://www.medcommons.net</link>
<description>MedCommons Personal healthURL Feed</description>
<category>healthURL, health URL</category>
<generator>medcommons development team</generator>
<webMaster>cmo@medcommons.net</webMaster>
<language>en-us</language>
<copyright>Copyright 2007 MedCommons, Inc</copyright>
<image>
<title>MedCommons > Home Page</title>
<url>http://www.medcommons.net/images/mclogo.gif</url>
<link>http://www.medcommons.net/index.html</link>
</image>
";
$query = "SELECT * from ccrlog where accid = '$accid' ORDER BY date DESC LIMIT 20";// where accid = '$accid'";
$result = mysql_query($query) or die ("can not $query ".mysql_error());
while ($r = mysql_fetch_object($result))
{
	echo "<item>
<title>Tracking $r->tracking guid $r->guid </title>
<link>https://www.medcommons.net/secure/?healthURL=$r->tracking</link>
<description>$r->date $r->subject</description>
<author>$r->src</author>
	<pubDate>$r->date</pubDate>
</item>
";
}
/*
<category>$r->status</category>
<guid isPermaLink='false'>https://www.medcommons.net/secure/?healthGUID=$r->guid</guid>

*/
echo '
</channel>
</rss>
';
?>