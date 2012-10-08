<?
/**
 * A page that figures out the correct practice id and then forwards to the
 * user to it as a widget.
 *
 * NOTE: there is also as rlswidget.tpl.php - that is used to render the widget the first
 * time.   
 */
require_once "dbparamsidentity.inc.php";
require_once "alib.inc.php";

// Get login information
list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not logged on
$db = aconnect_db(); // connect to the right database
$practiceIds = q_member_practice_ids($accid);
$_REQUEST['pid']=$practiceIds[0];
$_REQUEST['widget']=true;

# Forward to real page
include "rls.php";
?>
