<?php
/**
 * Redirects to RSS feed for a given guid
 */
require "dbparams.inc.php";
require_once "session.inc.php";
require_once "securelib.inc.php";

function xexec ($s, $p)
{
	$result = mysql_query($s) or die("Can not query in xexec $p ".mysql_error());
	if ($result=="") {exit;}
	return $result;
}
function r($r)
{
	if (isset($_REQUEST[$r])) return $_REQUEST[$r]; else return '';
}

dbconnect();
$guid = r('guid');
$accid = r('accid');
$node = find_node($guid);
if($node) {
  $auth = get_auth();
  $gw = $node->hostname;
  $url = "$gw/rss?a=".urlencode($accid)."&auth=$auth";
  $strongUrl = $url; //strong_url($url);
  header("Location: $strongUrl");
  exit;
}

// If we got here then we failed
header('Content-Type: text/xml');
echo "<?xml version='1.0' encoding='UTF-8'?>
  <rss version='2.0'>
    <channel>
    <title>MedCommons RSS Feed for Account $accid Unavailable - Please Login</title>
    <link>http://www.medcommons.net</link>
    <description>Unable to locate RSS Feed for this user.  If you are not logged in, please log in to access this feed.</description>
    <category>healthURL, health URL</category>
    <generator>Medcommons Gateway</generator>
    <webMaster>cmo@medcommons.net</webMaster>
    <language>en-us</language>
    <copyright>Copyright 2007 MedCommons, Inc</copyright>
    <image>
      <title>MedCommons Home Page</title>
      <url>http://www.medcommons.net/images/mclogo.gif</url>
      <link>http://www.medcommons.net/index.html</link>
    </image>
    </channel>
    </rss>";
?>
