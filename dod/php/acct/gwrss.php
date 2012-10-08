<?
  require_once "urls.inc.php";
  require_once "dbparamsidentity.inc.php";
  require_once "utils.inc.php";
  require_once "alib.inc.php";
  nocache();
  aconnect_db();
  $accid = req('a');

  // Find gateway where Current CCR of user resides
  $cccrGuid = getCurrentCCRGuid($accid);
  if($cccrGuid) {
    header("Location: ".detrail($GLOBALS['Commons_Url'])."/rssredir.php?guid=$cccrGuid&accid=$accid&dest=".urlencode("rss?a=$accid"));
  }
  else {
    echo "<p>Error: invalid or unknown account or account not enabled for RSS access.</p>";
  }
?>
