<?php
require_once 'tlib.inc.php';

//starts here

require_once "template.inc.php";
$tpl = new Template('../'.$GLOBALS['layout_tpl_php']);
$tpl->set_title('MedCommons - My Custom Topics Error');
if (isset($_REQUEST['err']))
{
	$error = $_REQUEST['err'];
	switch ($error)
	{
		case 'regdisabled': $errstr ="Direct registration is currently disabled. You must have a sponsored account to utilize MedCommons"; break;
		case 'alreadyaneditor': $errstr = "You are already an editor of this topic page"; break;
		case 'noedudomain': $errstr = "Your email address is not in the .edu domain"; break;
		case 'cantfindtopicpage': $errstr = "Cant find the page"; break;
		case 'exclusive': $errstr = "This page is for the exclusive use of another user"; break;
		default:  $errstr ="unknown error $error";break;
	}
 
} else $errstr = "You must be logged on to MedCommons to perform this function";

$contents = "<div><h3>$errstr</h3><p>Please remedy and try again</p></div>";
$tpl->set("relPath", "../"); // the code in home is up one level
$tpl->set("content", $contents);
$tpl->set_topicfile('topics.htm');

$contents =  $tpl->fetch();
echo $contents;
?>