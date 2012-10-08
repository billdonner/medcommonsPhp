<?php
require_once 'tlib.inc.php';

//starts here
$p=testif_logged_in();
if ($p===false) {header ("Location: iclinfo.php"); exit;}
list($accid,$fn,$ln,$email,$idp,$cookie) =$p;
require_once "template.inc.php";
$tpl = new Template('../'.$GLOBALS['layout_tpl_php']);
list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in(); // does not return if not lo
$db = aconnect_db(); // connect to the right database
$resaccid = '0000000000001111'; // hack for the residents
$tpl->set_title('MedCommons - My Custom Topics Pages');
if (isset($_REQUEST['op']))
{
	if ('delete'==$_REQUEST['op'])
	{
		$pageid = $_REQUEST['pageid'];
		$q = "delete from clonedpages where pageid='$pageid'and accid='$resaccid' "; // make sure
		mysql_query($q) or die("Cant $q ".mysql_error());
		// fall into remaing code
	}
}
$contents = "<div><h3>My Custom Topics Pages </h3><ul>";
$q = "select * from clonedpages where accid='$resaccid'";
$counter=0;
$result = mysql_query($q) or die("Cant $q".mysql_error());
while ($r = mysql_fetch_object($result)) {

	$topic = $r->name;
	$pageid = $r->pageid;
	//hack to see if we are in thegroup
	if (strpos($r->thegroup,"!$accid!"))
	{


		$contents.="<li>
	<a href='pg.php?topic=$topic&accid=$resaccid'>$topic</a>&nbsp;
	<small>
	<a href='icledit.php?pageid=$pageid'>edit</a>&nbsp;
	<a href='iclpages.php?op=delete&pageid=$pageid'>delete</a>&nbsp;
	</small>
	</li>";
		$counter++;
	}
}
if ($counter==0)
$contents.="</ul><p>You have no Custom Pages. Customization of pages is limited to qualified Medical Residents</p><p>
		If you'd link to create a Custom Topics Page, select the 'edit' link on the topic page.</p></div>"; 
else $contents .= "</ul></div>";
$tpl->set("relPath", "../"); // the code in home is up one level
$tpl->set("content", $contents);
$tpl->set_topicfile('topics.htm');

$contents =  $tpl->fetch();
echo $contents;
?>