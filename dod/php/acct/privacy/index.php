<?
/**
 * Main page - based on template
 */
require_once "template.inc.php";
$tpl = new Template("../".$GLOBALS['layout_tpl_php']);
$tpl->set("content", file_get_contents("content.html"));
$tpl->set("relPath", "../");
echo $tpl->fetch();
?>

