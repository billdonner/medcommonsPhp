<?php
require_once 'tlib.inc.php';
require_once 'urls.inc.php';

// display a page, no login required
//starts here
require_once "template.inc.php";
$tpl = new Template('../'.$GLOBALS['layout_tpl_php']);
$db = aconnect_db(); // connect to the right database

$accid = $_REQUEST['accid'];
$topic = $_REQUEST['topic'];
$q = "select * from clonedpages where accid='$accid' and name='$topic'";
$result = mysql_query($q) or die("Cant $q".mysql_error());
$r = mysql_fetch_object($result);
if ($r===false) {header ("Location: iclinfo.php?err=cantfindtopicpage"); exit;}
$p = testif_logged_in();
/*if ($p!==false) $editlink="<a href=icledit.php?pageid=".$r->pageid." >edit</a>";
else
*/ $editlink='';
if ($p!==false)   $mytopicslink ='<a href=iclpages.php>mytopics</a>';
else

$mytopicslink='';
$share = ($r->shared==1);
if (!$share) {
	// if not sharing, make sure user is logged in and it matches

	if ($p===false) {header ("Location: iclinfo.php?err=exclusive"); exit;}
	list($accid,$fn,$ln,$email,$idp,$mc) = $p;

	if ($r->accid!=$accid) {header ("Location: iclinfo.php?err=exclusive"); exit;}
}
/*
if ($r->clone==1)
$clonelink = " <a href='icl.php?accid=$accid&topic=$topic' >clone</a>"; else
*/
$clonelink='';

$ilks = array();
if (strlen($r->ilinks)<10) $ilk = false; else $ilk = explode ('|',$r->ilinks);

$xlks = array();
if (strlen($r->xlinks)<10) $xlk = false; else $xlk = explode ('|',$r->xlinks);

$tlks = array();
if (strlen($r->phrlinks)<10) $tlk = false; else $tlk = explode ('|',$r->phrlinks);
$out = "<div><h3>Topic: $accid:$topic <small>$mytopicslink $editlink $clonelink</small></h3>";

$glks = array();
if
(strlen($r->thegroup)<10) $glk = false; else
{
	
	$glk = explode ('|',$r->thegroup);
	$out .= '<div id="mc-exampleGroup" ><h4>Medical Residents Who Are Editors of this Page</h4><ul>';
	foreach ($glk as $gk) {
		list ($email,$mcid,$screenname,$other) = explode ('!',$gk);
		if ($screenname=='')$screenname=$email;
		$out.="<li><a class='mclink resGroup  S'
			                   title='$mcid $other' href='mailto:".$email."'>$screenname</a></li>";
	}
	$out .='</ul></div>';

}
$out .= '<div align="left" id="mc-topics">';

$urls=array();
if (($ilk!==false)&& (count($ilk)>0))
{
	$out .= '<h4>related topics</h4><ul>';
	foreach ($ilk as $ik) {
		list ($label,$url) = explode ('!',$ik);
		// build list of unique urls

		$found=false; for ($i=0; $i<count($urls); $i++)
		{
			if ($urls[$i][0]==$url) {$found=true; break;}
		}
		if (!$found) {$urls[]=array($url,$label);}
	}
	// play them out
	$counter=0;


	foreach ($urls as $hurl)
	{
		$url = $hurl[0]; $label = $hurl[1];
		list($url,$images) =rewrite_url($url);// $src = find_src($url);if ($src!==false) $src = "title='via $src'";
		$tt = trim($url);
		if (substr($tt,0,17)=='0000000000000000:') $tt = substr($tt,17).'.html'; else
		{
			$acc = substr($tt,0,16);
			$topic = substr($tt,17);
			$tt = "pg.php?accid=$acc&topic=$topic";

		}

		$out.="<li><a class='mclink up S' href='".$tt."'>".$label."</a></li>";

	}
	$out.='</ul>';
}

if (($xlk!==false)&& (count($xlk)>0))
{
	$out .= '<h4>external references</h4><ul>';
	foreach ($xlk as $xk) {
		list ($label,$url) = explode ('!',$xk);
		list($url,$images) =rewrite_url($url);//$src = find_src($url);if ($src!==false) $src = "title='via $src'";
		$pos = strpos($url,'u=');
		if ($pos!==false)
		$url=substr($url,$pos+2);
		$url=urldecode($url);
		$out.="<li><a class='mclink ext S' target='_new' href='".$url."'>".$label."</a></li>";
	}
	$out.='</ul>';
}



if (($tlk!==false)&& (count($tlk)>0))
{
	$out .= '<h4>PHR Examples from Medical Residents</h4><ul>';
	foreach ($tlk as $tk) {
		list ($label,$url) = explode ('!',$tk);
		list($url,$images) =rewrite_url($url);//$src = find_src($url);if ($src!==false) $src = "title='via $src'";
		$url = $GLOBALS['Commons_Url']."trackemail.php?a=$url";
		$out.="<li><a class='mclink phr S' target='_new' href='".$url."'>".$label."</a></li>";
	}
	$out .='</ul>';
}
$out.='</div>';
$tpl->set("relPath", "../"); // the code in home is up one level
$tpl->set("content", $out);
$tpl->set_title("MedCommons - Custom Page $accid:$topic");
$tpl->set_description("MedCommons - View User Generated Custom Page");
$tpl->set_topicfile('topics.htm');

$contents =  $tpl->fetch();
echo $contents;
?>