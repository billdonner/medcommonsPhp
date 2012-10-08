<?
/**
 * Main page - based on template
 */
require_once "urls.inc.php";
require_once "template.inc.php";
require_once "../utils.inc.php";
//header("Location: ".$GLOBALS['Accounts_Url']."/cover.php");
$tpl = new Template("../".$GLOBALS['layout_tpl_php']);
//if(is_logged_in()) {
  $tpl->set("content", new Template("cover.php"));
//}
//else {
//  $tpl->set("content", file_get_contents("content.html"));
//}
echo $tpl->fetch();
?>

