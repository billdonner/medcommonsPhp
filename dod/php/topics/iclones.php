<?php
require_once 'tlib.inc.php';

//starts here
$topic=$_REQUEST['a'];
$p=testif_logged_in(); 
if ($p===false) {header ("Location: iclinfo.php"); exit;}
list($accid,$fn,$ln,$email,$idp,$cookie) =$p;
require_once "template.inc.php";
$tpl = new Template('../'.$GLOBALS['layout_tpl_php']);
list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in(); // does not return if not lo
$db = aconnect_db(); // connect to the right database
$tpl->set_title("MedCommons - Custom Topics Pages Derived from $topic");
$contents = "<div><h3>All Custom Topics Pages Derived from $topic</h3><ul>";
	$q = "select * from clonedpages where roottopic='$topic'";
	$counter=0;
	$result = mysql_query($q) or die("Cant $q".mysql_error());
	while ($r = mysql_fetch_object($result)) {
		$counter++;
		$topic = $r->name;
		$maccid = $r->accid;
		$pageid = $r->pageid;
	$contents.="<li>
	<a href='pg.php?topic=$topic&accid=$maccid'>$topic</a>&nbsp;
	<small>
	<a href='icledit.php?pageid=$pageid'>edit</a>&nbsp;
	<a href='iclpages.php?op=delete&pageid=$pageid'>delete</a>&nbsp;
	</small>
	</li>";	
		
	}

	if ($counter==0) 
		$contents.="</ul><p>There are no Custom Pages for this Topic. At this point, the ability to customize pages is limited to qualified Medical Residents. If you'd link to create a Custom Topics Page, select the 'edit' link on the topic page.</p></div>"; 
		else $contents .= "</ul></div>";
$tpl->set("relPath", "../"); // the code in home is up one level
$tpl->set("content", $contents);
$tpl->set_topicfile('topics.htm');

$contents =  $tpl->fetch();
echo $contents;
?>