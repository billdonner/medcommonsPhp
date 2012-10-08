<?php
require_once 'tlib.inc.php';
require_once 'urls.inc.php';
		function topic_url($s)
		{
			// if it actual ends with .html transform into 0000000 etc notation
			$pos = strpos($s,'.html');
			if ($pos === false) return $s; else
			return '0000000000000000:'.substr($s,0,$pos);
		}
function do_emails ($emails,$identity)
{
	$pageid = $_REQUEST['pageid'];
	$pagename = $_REQUEST['pagename'];
	$authoraccid = $_REQUEST['authoraccid'];

	//	echo "Send email to $emails about $authoraccid:$pagename";


	$srv = $_SERVER['SERVER_NAME'];
	$upagename = urlencode($pagename);
	$gg = $srv."/interests/pg.php?accid=$authoraccid&topic=$upagename";

	$extraheaders =
	"From: Refererals@{$srv}\n" .
	"Reply-To: cmo.medcommons.net\n".
	"bcc: billdonner@medcommons.net\n".
	"User-Agent: MedCommons Mailer 1.0\n".
	"MIME-Version: 1.0\n".
	"Content-Type: text/plain\n";
	// send an email
	$mailstat = mail($emails,
	"MedCommons -  $authoraccid:$pagename updated by $identity",
	"$identity wants you to know there is a new custom page that may interest you. The page
my be viewed here: $gg ",
	$extraheaders);

	if (!$mailstat) echo "Could not send mail;";
	//
	// after successful update, just go to the newly renamed page
	//
	$goto = "pg.php?accid=$authoraccid&topic=$upagename";
	header ("Location: $goto");
	echo "Redirecting to $goto";
	exit;
}
function do_renamepage ($newname)
{
	$pageid = $_REQUEST['pageid'];
	$pagename = $_REQUEST['pagename'];
	$authoraccid = $_REQUEST['authoraccid'];
	$update = "update clonedpages set name ='$newname'
	              where pageid='$pageid'";
	mysql_query($update) or die("cant update $update".mysql_error());
	//
	// after successful update, just go to the newly renamed page
	//
	$goto = "pg.php?accid=$authoraccid&topic=$newname";
	header ("Location: $goto");
	echo "Redirecting to $goto";
	exit;
}
function do_changeprops ($pageid, $shared,$clone,$robots)
{
	$pageid = $_REQUEST['pageid'];
	$pagename = $_REQUEST['pagename'];
	$authoraccid = $_REQUEST['authoraccid'];
	$update = "update clonedpages set shared='$shared', clone='$clone', robots='$robots'
	              where pageid='$pageid'";
	mysql_query($update) or die('cant update $update'.mysql_error());
	//
	// after successful update, just go to the page
	//
	$goto = "pg.php?accid=$authoraccid&topic=$pagename";
	header ("Location: $goto");
	echo "Redirecting to $goto";
	exit;
}

function do_preview(){
	$pagename = $_REQUEST['pagename'];
	$authoraccid = $_REQUEST['authoraccid'];
	$out = "<div><h3>Unsaved Preview of $authoraccid:$pagename&nbsp;<small><a href=iclpages.php>mytopics</a></small>";
	$counter = 0;
	$out .= '<h4>topics</h4><ul>';
	while (true)
	{
		if (!isset($_REQUEST["ii$counter"])) break; else
		{
		$tt = trim($_REQUEST["ii$counter"]);
		if (substr($tt,0,17)=='0000000000000000:') $tt = substr($tt,17).'.html'; else
		{
			$acc = substr($tt,0,16);
			$topic = substr($tt,17);
			$tt = "pg.php?accid=$acc&topic=$topic";
				
		}
		if ($tt!='')
		$out.="<li><a href='".$tt."'>".$_REQUEST["iii$counter"]."</a></li>";
		}
		$counter++;
	}
	$out.='</ul>';
	$counter = 0;
	$out .= '<h4>xrefs</h4><ul>';
	while (true)
	{
		if (!isset($_REQUEST["xx$counter"])) break; else
		if (trim($_REQUEST["xx$counter"])!='')
		$out.="<li><a target='_new' href='".$_REQUEST["xx$counter"]."'>".$_REQUEST["xxx$counter"]."</a></li>";
		$counter++;
	}
	$out.='</ul>';
	$counter = 0;
	$tem = $GLOBALS['Commons_Url']."trackemail.php?a=";
	$out .= '<h4>phrs</h4><ul>';
	while (true)
	{
		if (!isset($_REQUEST["pp$counter"])) break; else
		if (trim($_REQUEST["pp$counter"])!='')
		$out.="<li><a target='_new' href='".$tem.$_REQUEST["pp$counter"]."'>".$_REQUEST["ppp$counter"]."</a></li>";
		$counter++;
	}

	$out .= '</ul></div>';
	return $out;
}
function do_save($saveall)
{
	//	echo "saveall=$saveall ";

	$pageid = $_REQUEST['pageid'];
	$pagename = $_REQUEST['pagename'];
	$authoraccid = $_REQUEST['authoraccid'];

	$counter = 0;
	$ilinks = '';
	while (true)
	{
		if (!isset($_REQUEST["ii$counter"])) break; else
		if (trim($_REQUEST["ii$counter"])!='')
		if ($saveall || !isset($_REQUEST["i$counter"]))
		{
			//	echo "ilinks $counter";
			$ilinks.=$_REQUEST["iii$counter"]."!".$_REQUEST["ii$counter"]."|";
		}
		$counter++;
	}
	$ilinks = substr($ilinks,0,strlen($ilinks)-1);
	$counter = 0;
	$xlinks = '';
	while (true)
	{
		if (!isset($_REQUEST["xx$counter"])) break; else
		if (trim($_REQUEST["xx$counter"])!='')
		if ($saveall || !isset($_REQUEST["x$counter"]))
		{
			//echo "xlinks $counter";


			$xlinks.=$_REQUEST["xxx$counter"]."!".$_REQUEST["xx$counter"]."|";
		}
		$counter++;
	}
	$xlinks = substr($xlinks,0,strlen($xlinks)-1);
	$counter = 0;
	$plinks='';
	while (true)
	{
		if (!isset($_REQUEST["pp$counter"])) break; else
		if (trim($_REQUEST["pp$counter"])!='')
		if ($saveall || !isset($_REQUEST["p$counter"]))
		{
			//echo "plinks $counter";


			$plinks.=$_REQUEST["ppp$counter"]."!".$_REQUEST["pp$counter"]."|";
		}
		$counter++;
	}
	$plinks = substr($plinks,0,strlen($plinks)-1);
	$update = "update clonedpages set ilinks='$ilinks', xlinks='$xlinks', phrlinks='$plinks'
	              where pageid='$pageid'";
	mysql_query($update) or die('cant update $update'.mysql_error());
	//
	// after successful update, just go to the page
	//
	$goto = "pg.php?accid=$authoraccid&topic=$pagename";
	header ("Location: $goto");
	echo "Redirecting to $goto";
	exit;
}

//starts here
$p=testif_logged_in(); 
if ($p===false) {header ("Location: iclinfo.php"); exit;}
list($accid,$fn,$ln,$email,$idp,$cookie) =$p;

require_once "template.inc.php";
$tpl = new Template('../'.$GLOBALS['layout_tpl_php']);
 // 
$db = aconnect_db(); // connect to the right database
if (isset($_REQUEST['emails']))
{
	$identity = "$accid ($email) $fn $ln ";
	do_emails($_REQUEST['emailist'],$identity);
}
else
if (isset($_REQUEST['renamepage']))
{

	do_renamepage($_REQUEST['newpagename']);
}
else
if (isset($_REQUEST['changeprops']))
{
	$shared = isset($_REQUEST['shared'])?1:0;
	$clone = isset($_REQUEST['clone'])?1:0;
	$robots = isset($_REQUEST['robots'])?1:0;
	do_changeprops($_REQUEST['pageid'],$shared,$clone,$robots);
}
else

if (isset($_REQUEST['submit']))
{
	$topic = $_REQUEST['pagename']; // this is the topic
	// check button push, process post variables
	if ($_REQUEST['submit']=='Preview') $contents = do_preview(); else
	if ($_REQUEST['submit']=='Save Page') $contents = do_save(true); // does not return
	if ($_REQUEST['submit']=='Delete Selected') $contents = do_save(false); // does not return
}
else {

	//no form action here
	if (isset($_REQUEST['addtopics'])) $addtopics = $_REQUEST['addtopics']; else $addtopics = 0;
	if (isset($_REQUEST['addextrefs'])) $addextrefs = $_REQUEST['addextrefs']; else $addextrefs = 0;
	if (isset($_REQUEST['addphrefs'])) $addphrefs = $_REQUEST['addphrefs']; else $addphrefs = 0;
	if ($addtopics>3) $addtopics=3;
	if ($addextrefs>3) $addextrefs=3;
	if ($addphrefs>3) $addphrefs = 3;
	$pageid = $_REQUEST['pageid'];

	$q = "select * from clonedpages where pageid='$pageid'";
	$result = mysql_query($q) or die("Cant $q".mysql_error());
	$r = mysql_fetch_object($result);
	if ($r===false) die ('There is no topic page with that id');
	$topic =$r->name;
	$ilks = array();

	if (strlen($r->ilinks)<10) $ilk = false; else $ilk = explode ('|',$r->ilinks);
	$xlks = array();
	if (strlen($r->xlinks)<10) $xlk = false; else $xlk = explode ('|',$r->xlinks);

	$tlks = array();
	if (strlen($r->phrlinks)<10) $tlk = false; else $tlk = explode ('|',$r->phrlinks);


	$urls=array();
	if ((($ilk!==false) &&(count($ilk)>0))|| ($addtopics>0))
	{
		$topicsdiv = '<!-- Start MC TOPICS --><div align="left" id="mc-topics">
			<h4>related topics</h4>
			<small>builtin or user generated in form accid:pagename</small>
			<table>';
		if ($ilk!==false)
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
			$url = topic_url($url);
			$topicsdiv.="<tr><td><input type=checkbox name=i$counter /></td><td><span class='mclink up S'><input type=text size=40 name=ii$counter value='$url' /></span></td><td><input type=text size=32 name=iii$counter value='$label' /></td></tr>\r\n";
			$counter++;
		}
		for ($i=0; $i<$addtopics; $i++)
		{
			$topicsdiv.="<tr><td><input type=checkbox name=i$counter /></td><td><span class='mclink up S'><input type=text size=40 name=ii$counter /></span></td><td><input type=text size=32 name=iii$counter /></td></tr>\r\n";
			$counter++;
		}
		$topicsdiv.="</table></div><!-- End MC TOPICS -->";
	}
	else $topicsdiv = '';



	$counter=0;
	if ((($xlk!==false)&& (count($xlk)>0))|| ($addextrefs>0))
	{
		$xrefdiv = '<!-- Start MC XREFS --><div align="left" class="mc-xrefs">
		<h4>external references</h4>
		<small>full blown references to external websites, include http: or https:</small>
		<table>';
		if ($xlk!==false)
		foreach ($xlk as $xk) {
			list ($label,$url) = explode ('!',$xk);
			list($url,$images) =rewrite_url($url);//$src = find_src($url);if ($src!==false) $src = "title='via $src'";
			$pos = strpos($url,'u=');
			if ($pos!==false) $url=substr($url,$pos+2);
			$url=urldecode($url);
			$xrefdiv.="<tr><td><input type=checkbox name=x$counter /></td><td><span class='mclink ext M'><input type=text size=40 name=xx$counter value='$url' /></span></td><td><input type=text size=32 name=xxx$counter value='$label'/></td></tr>\r\n";

			$counter++;
		}

		for ($i=0; $i<$addextrefs; $i++)
		{
			$xrefdiv.="<tr><td><input type=checkbox name=x$counter /></td><td><span class='mclink up S'><input type=text size=40 name=xx$counter /></span></td><td><input type=text size=32 name=xxx$counter /></td></tr>\r\n";
			$counter++;
		}
		$xrefdiv.="</table></div><!-- End MC XREFS -->";

	}
	else $xrefdiv = '';

	$counter=0;
	if ((($tlk!==false)&& (count($tlk)>0))||($addphrefs>0))
	{

		$phrefdiv = '<!-- Start MC phrefS --><div align="left" class="mc-phrefs">
		<h4>personal health records</h4>
		<small>simple 12 digit tracking numbers for now</small>
		<table>';

		if ($tlk!==false) foreach ($tlk as $tk) {
			list ($label,$url) = explode ('!',$tk);
			list($url,$images) =rewrite_url($url);//$src = find_src($url);if ($src!==false) $src = "title='via $src'";
			$url=urldecode($url);
			$phrefdiv.="<tr><td><input type=checkbox name=p$counter /></td><td><span class='mclink ext M'><input type=text size=40 name=pp$counter value='$url' /></span></td><td><input type=text size=32 name=ppp$counter value='$label'/></td></tr>\r\n";
			$counter++;
		}

		for ($i=0; $i<$addphrefs; $i++)
		{
			$phrefdiv.="<tr><td><input type=checkbox name=p$counter /></td><td><span class='mclink up S'><input type=text size=40 name=pp$counter /></span></td><td><input type=text size=32 name=ppp$counter /></td></tr>\r\n";
			$counter++;
		}
		$phrefdiv.="</table></div><!-- End MC phrefS -->";
	}
	else $phrefdiv = '';

	if ($r->shared==1) $shared='checked'; else $shared='';
	if ($r->clone==1) $clone='checked'; else $clone='';
	if ($r->robots==1) $robots='checked'; else $robots='';
	$sharedbox = "<input  type=checkbox $shared name='shared' />";
	$clonebox = "<input  type=checkbox $clone name='clone' />";
	$robotsbox = "<input  type=checkbox $robots name='robots' />";
	$contents = "<div><h3>Editing Topic Page ".$r->accid.':'.$topic.'&nbsp;<small><a href=iclpages.php>mytopics</a></small></h3>';
	$authoraccid = $r->accid; $name = $r->name;
	$contents.= "<form action=icledit.php method=post>\r\n";
	$contents .= "<input type=hidden value='$pageid' name=pageid />\r\n";
	$contents .= "<input type=hidden value='$authoraccid' name=authoraccid />\r\n";
	$contents .= "<input type=hidden value='$topic' name=pagename />\r\n";

	$contents.= $topicsdiv;
	$contents.= $xrefdiv;
	$contents.= $phrefdiv;
	$contents.="<p><small>changes not committed until save or delete</p>
	        <input type=submit name=submit value='Preview'>&nbsp;
			<input type=submit name=submit value='Save Page'>&nbsp;
            <input type=submit name=submit value='Delete Selected'>
            </form>\r\n";
	$contents .= "<p><form action=icledit.php method=post>\r\n
<input type=hidden value='$pageid' name=pageid />
<small>or add <input type=text value=0 name=addtopics size=1 />&nbsp;related topics,
<input type=text value=0 name=addextrefs size=1 />&nbsp;external refs, and
<input type=text value=0 name=addphrefs size=1 />&nbsp;phr refs
<input type=submit name=addrefs value='Go' /></small>
</form>
</p>
<p><form action=icledit.php method=post>\r\n
<input type=hidden value='$pageid' name=pageid />
<input type=hidden value='$authoraccid' name=authoraccid />
<input type=hidden value='$topic' name=pagename />
<small>or <span>$sharedbox  share &nbsp;&nbsp; $clonebox allow clones&nbsp;&nbsp; $robotsbox allow robots</span>
<input type=submit name=changeprops value='Go' /></small>
</form>
</p>
</p>
<p><form action=icledit.php method=post>\r\n
<input type=hidden value='$pageid' name=pageid />
<input type=hidden value='$authoraccid' name=authoraccid />
<input type=hidden value='$topic' name=pagename />
<small>or rename page to <input type=text size=32 value='$topic' name=newpagename />&nbsp;
<input type=submit name=renamepage value='Go' /></small>
</form>
</p>
<p><form action=icledit.php method=post>\r\n
<input type=hidden value='$pageid' name=pageid />
<input type=hidden value='$authoraccid' name=authoraccid />
<input type=hidden value='$topic' name=pagename />
<small>or notify others (separate emails with ,) <input type=text size=32 value='' name=emailist />&nbsp;
<input type=submit name=emails value='Go' /></small>
</form>
</p>
</div>";

} // end of the not-submitted case

/**
 * Commons page - based on template
 */


$tpl->set("relPath", "../"); // the code in home is up one level
$tpl->set("content", $contents);
$tpl->set_title("MedCommons - Editing Topic Page $accid:$topic");
$tpl->set_description("MedCommons - Edit User Generated Topic Page");
$tpl->set_topicfile('topics.htm');

$contents =  $tpl->fetch();
echo $contents;
?>