<?
/**
 * Renders input form to allow existing group member to invite another user to join
 * the group.
 */
require_once "dbparamsidentity.inc.php";
require_once 'urls.inc.php';
require_once "../alib.inc.php";
require_once "utils.inc.php";
require_once "template.inc.php";

nocache();

$info = get_account_info();

$practiceId = req("pid");
$removeAccid = req("accid");

function error($msg = null) {
  $tpl = template("error.tpl.php");
  if($msg)
    $tpl->set("msg",$msg);
  echo $tpl->fetch();
  exit;
}

// Only an admin for the practice can remove a member
$practices = q_member_practices($info->accid,$practiceId);
if(!$practices) 
  error("Unable to query practices");

$p = $practices[0];

if(!$p) { // Should always find it, if not this is internal error
  error_log("Attempt to remove member from practice $practiceId to which logged in user ".$info->accid." does not belong");
  echo template("error.tpl.php")->fetch();
  exit;
}

// Must be admin
if(($info->accid != $removeAccid) && ($p->adminaccid == null)) {
  error_log("Attempt to remove member from practice $practiceId to which logged in user ".$info->accid." does not belong");
  error("You must be an administrator of a practice to remove a member.");
}

// Everything looks ok, proceed
try {
  $db = pdo_connect();
  $s = $db->prepare("delete from groupmembers where groupinstanceid = ? and memberaccid = ?");
  if(!$s)
      throw new Exception("Failed to execute statement");
  $s->bindParam(1,$p->providergroupid);
  $s->bindParam(2,$removeAccid);
  if(!$s->execute())
    throw new Exception("Failed to prepare statement");

  // All good, render the output page
  if($removeAccid == $info->accid)
    header("Location: inviteGroupMember.php?selfremoved");
  else
    header("Location: inviteGroupMember.php?removed");
}
catch(Exception $ex) {
    error_log("Unable to delete from groupmembers table: ".$ex->getMessage()." at line ".$ex->getLine());
    error();
}
