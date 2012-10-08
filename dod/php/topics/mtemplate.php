<?php
/**
 * generate the template needed to drive mgen.php thru /interests, producing static html files
 */
require_once "template.inc.php";
require_once "urls.inc.php";
$accurl = $GLOBALS['Accounts_Url'];

$outfile = $_REQUEST['out']; // where to write file to
$tpl = new Template('../'.$GLOBALS['layout_tpl_php']);
$tpl->set_keywords('**{{keywords go here}}**, personal health record, ccr');
$tpl->set_phtml('**{{phtml}}**');
$tpl->set_description('MedCommons - **{{title goes here}}**');
$tpl->set_title('MedCommons - **{{title goes here}}**');
$tpl->set_topicfile('');
$tpl->set_searchdef('**{{title goes here}}**');
$tpl->set("relPath", "../"); // the code in home is up one level

$tem = <<<XXX
<iframe name="homeframe" id="homeframeel" 
src='$accurl/home.php' width='98%' allowtransparency='true' 
 frameborder='0' scrolling='no' height='370px'>Your browser doesn't support iframes.</iframe>
**{{topics div goes here}}**
XXX;

$tpl->set("content", $tem);

file_put_contents($outfile,$tpl->fetch());
echo "<a href='$outfile'>$outfile</a> was written";
?>
