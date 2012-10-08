<?
/**
 * Main page - based on template
 */
include("urls.inc.php");
require_once "template.inc.php";
require_once "../utils.inc.php";
$template="layout";
if(isset($_REQUEST['tpl'])) {
  $template = $_REQUEST['tpl'];
}
// We need to send the user to a different rls page
// if they are in a widget context to a full page context
$rlsPage = "../rlswidget.php";

if(!is_logged_in() || ($template!="widget")) {
  $tpl = new Template("../$template.tpl.php");
  $contentTpl = new Template("content.php");
  $contentTpl->set("rlsPage",$rlsPage);
  $contentTpl->set("loggedIn",is_logged_in());
  $contentTpl->set("showFooter",$template!="widget");
  $tpl->set("content", $contentTpl);
  echo $tpl->fetch();
}
else {
  include("$rlsPage");
}
?>
