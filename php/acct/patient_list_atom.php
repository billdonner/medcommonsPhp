<?
/**
 * Returns the patient list for a group as an Atom feed.
 */

require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "mc.inc.php";
  
$auth = req('auth');
if(!$auth) {
    $info = get_account_info();
    $auth = $info->auth;
}
    
if(!$auth) 
    throw new Exception("Missing required parameter 'auth'");

$token = pdo_first_row("select * from authentication_token where at_token = ? and at_priority = 'G'", array($auth));
if(!$token)
    throw new Exception("Unknown auth token or not authorized for group.");

$groupAccountId = $token->at_account_id;

$group = 
  pdo_first_row("select * from groupinstances where accid = ?", array($groupAccountId));

if(!$group) 
  throw new Exception("Unknown group");

$_REQUEST['pid'] = $group->parentid;
$_REQUEST['limit'] = 20;
$_GET['fmt'] = "atom";

$no_login_necessary = true;

include("rls.php");
?>
