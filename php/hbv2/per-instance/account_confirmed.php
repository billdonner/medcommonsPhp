<?
/**
 * Callback handler that updates an account's status to confirmed
 * when an appliance has successfully processed the user's confirmation
 * request.
 */
require_once "appinclude.php";

if(!isset($_REQUEST["fbid"]))
  throw new Exception("Required parameter fbid missing");

$fbid = $_REQUEST["fbid"];

// Must be right format
if(preg_match("/^[0-9]{2,16}$/",$fbid)===0)
  throw new Exception("invalid fbid");

$result = mysql_query("update fbtab set storage_account_claimed = 1 where fbid = ".$fbid);
if(!$result)
  throw new Exception("Failed to update fbtab with new storage_account_claimed flag: ".mysql_error());

mysql_close();
?>
OK
