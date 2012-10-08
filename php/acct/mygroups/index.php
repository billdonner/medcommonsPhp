<?
/**
 * Main page - based on template
 */
require_once "template.inc.php";
$tpl = new Template($GLOBALS['layout_tpl_php']);
$tpl->set("content", new Template("content.php"));
$tpl->set("relPath", "../");
echo $tpl->fetch();
?>

