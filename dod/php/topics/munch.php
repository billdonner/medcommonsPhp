<?php
//
// handles op=scan  generates links and tags for batch of pages
//
require_once 'mlib.inc.php';
function my_array_search($nt,$skiplist)
{
	// the array search seems partially broken, so we will use this temporarily
	foreach ($skiplist as $sk)
	{
		if (false){
			echo "comparing $nt with $sk yields";
			echo ($nt==$sk)?" equal<br>":" unequal<br>";
		}
		if ($nt==$sk) return true;
	}
	return false;
}
function 	compute_tags ($pageid, $incoming,$outgoing,$mylabel,$inlink,$prefix)
{
	$mylink = prep_link($inlink,$prefix);
	$tags = array();
	$prelen = strlen($prefix);
	add_w2label($tags,$mylabel);
	if ($incoming!==false)
	{
		foreach ($incoming as $r)
		{
			$up = 	prep_link($r->parentlink,$prefix);
			$lab = label_from_url($r->parentlink);
			if ($lab!==false) { // make sure there is a label
				//$div .= "<li>up: <a href='$up'>".$lab."</a></li>\r\n";
				//$toplist[label_from_url($r->parentlink)]=array('mclink up S',$up);
				add_w2label($tags,$r->label);
			}
		}

	} // there are some links
	if ($outgoing!==false){
		foreach ($outgoing as $r)
		{
			if (substr($r->link, 0, $prelen) == $prefix ) //if we have a match  ***** must be improved
			{
				// new, check to make sure its not already been shown on the incoming list
				$found = false;
				if ($incoming!==false)
				foreach ($incoming as $ri)
				if  ($ri->parentlink == $r->link) $found=true;
				if (!$found)
				{
					add_w2label($tags,$r->label);
					{
						$up = 	prep_link($r->link,$prefix);
						//$div .= "<li><a href='$up'>".label_from_url($r->link)."</a></li >\r\n";
						//$toplist[label_from_url($r->link)]=array('mclink in L',$up);
						add_w2link($tags,$up);
					}
				}
			}
		}
		foreach ($outgoing as $r)
		{
			if (substr($r->link, 0, $prelen) != $prefix ) //if we have a match  ***** must be improved
			{
				add_w2label($tags,$r->label);
				{
					list($up,$throwaway) = 	rewrite_url($r->link); // this points outside which is what we want
					//$div .= "<li><a target='_new' href='$up'>$r->label</a></li >\r\n";
					//$extlist[$r->label]=array('mclink ext M',$up);
				}
			}
		}
		// add the incoming link as a special link
		//$div .= "<li>nih: <a target='_new' href='mxremote.php?theURL=$inlink'>$mylabel</a></li >\r\n";
		//$extlist[$mylabel]=array('mclink nih XL',"mxremote.php?theURL=$inlink");
	} // there are some links
	//ksort($extlist);	ksort($toplist);
	$tags = array_unique($tags);
	natsort($tags);
	return $tags;
}
function add_w2link(&$tags,$link)
{
	//strip the end bit and any front matter before a slash
	$pos =  strrpos($link,'.htm');
	if ($pos === false) return ; // do nothing if not an html filename
	$pos2 = strrpos($link,'/');
	if ($pos2===false) $xlink = substr($link,0,$pos);
	else $xlink = substr($link,$pos2+1,$pos-$pos2-1);
	$tags[]=$xlink;
}
function add_w2label(&$tags,$label)
{
	if (strpos($label,'?')!==false) return;
	if (strpos($label,'(')!==false) return;
	if (strpos($label,'-')!==false) return;
	if (strpos($label,')')!==false) return;
	if (strpos($label,':')!==false) return;
	if (strpos($label,'"')!==false) return;
	if (strpos($label,"'")!==false) return;
	//	$label = str_replace(array(' ','	',','),array('|','|','|'),$label);
	//$newtags=explode('|',$label);
	// split the phrase by any number of commas or space characters,
	// which include " ", \r, \t, \n and \f
	$newtags = preg_split("/[\s,]+/", $label);
	//need to sort out dupes and short tags
	$smashtag=''; $smashcount=0;
	foreach ($newtags as $nt) {
		if (strlen($nt)>2) {
			//check if its numeric
			if (!is_numeric($nt))
			// if its a decent size tag and we dont already have it
			if (array_search($nt,$tags)===false)
			// also check to make sure we dont have a variation which is just one char short
			if (array_search($nt.'s',$tags)===false)
			// or one char long
			if (array_search(substr($nt,0,strlen($nt)-1),$tags)===false)
			// check against the skip list
			if (my_array_search($nt,$GLOBALS['skiplist'])===false)
			{
				$tags[]=strtolower($nt);
				$smashtag.= strtolower($nt); // work on this later shud be: ucfirst($nt);
				$smashcount++;
			}
		}
	}
	// if the smashtag is more than one word long and its now already in then include it
	if ($smashcount>1)
	if (array_search($smashtag,$tags)===false)
	//if (array_search(strtolower($smashtag),$tags)===false)
	$tags[]=$smashtag;

}
function  find_add_to_tag_table($tag)
{
	$q = "select tagid,refcount from mcdirtags where tag='$tag'";
	$result = mysql_query($q) or die ("$q ".mysql_error());
	$r=mysql_fetch_object($result);
	mysql_free_result($result);
	if ($r)

	{ // bump the refcount
		$tagid = $r->tagid; $rfc= 1+$r->refcount;
		$update = "update mcdirtags set refcount = '$rfc' where tagid='$tagid'";
		mysql_query($update) or die ("$update ".mysql_error());
		return $tagid; // if already in the table
	}

	$insert = "insert into mcdirtags set tag='$tag', refcount='1'";
	mysql_query($insert) or die ("$insert".mysql_error());
	$tagid=mysql_insert_id(); // get the id
	return $tagid;

}
function xref_pagetag ($pageid,$tagid)
{
	$insert = "replace into mcdirpagetags set pageid='$pageid',tagid='$tagid'";
	$result = mysql_query($insert) or die ("$insert".mysql_error());
}

function update_tag_tables($pageid, $tags)
{
	foreach ($tags as $tag)
	{
		$tagid = find_add_to_tag_table($tag);
		$success = xref_pagetag ($pageid,$tagid);
	}
}
// starts here

$GLOBALS['skiplist'] = array(); // everything we want to skip over
if (isset($_REQUEST['noise']))
{
	$s = $_REQUEST['noise']; // skip file
	$skip = file($s); // entire file into array

	$skcount = count($skip);
	for ($i=0; $i<$skcount;$i++) {
		$GLOBALS['skiplist'][]=trim($skip[$i]);
	}
}
$db = $_REQUEST['db'];
$op = $_REQUEST['op'];
$prefix = $_REQUEST['pre'];
if (!isset($_REQUEST['out'])) $outprefix=false; else $outprefix = $_REQUEST['out'];
$t = $_REQUEST['template'];
$template = file_get_contents($t); // template file

echo"<html><head><title>Topics Link and Tag Generator</title></head><body><h4>Topics Link and Tag Generator</h4>";
if ($outprefix!==false)
echo "<small>urls with a prefix of <i>$prefix</i> will be rewritten to start with <i>$outprefix</i>";
mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");

// clean up any tags we already have if not in preview mode

echo "munch: cleaning up<br>";
$query = "delete from mcdirpagetags";
$result = mysql_query($query) or die ($query.' '.mysql_error());

//get list of pages to work on
$urls=array();
$query = "select pageid,url,ilinks,xlinks,tags from mcdirpages";
$result = mysql_query($query) or die ($query.' '.mysql_error());
while ($r = mysql_fetch_object($result)) $urls[]=$r; mysql_free_result($result);

//for each url
$iter=0;
foreach ($urls as $r)
{
	$timestart = microtime(true);

	$url = $r->url;

	$pageid = $r->pageid;
	$ilinks = stripslashes($r->ilinks);
	$xlinks = stripslashes($r->xlinks);
	$tags = $r->tags;

	// get list of incoming links
	$incoming = '';
	$query = "select * from mcdirlinks where link='$url' order by link";
	$result = mysql_query($query) or die ($query.' '.mysql_error());
	$lastlink = '';
	while ($r = mysql_fetch_object($result)) $incoming.=label_from_url($r->parentlink).
	"!".prep_link($r->parentlink,$prefix)."|";
	mysql_free_result($result);

	// get list of outgoing links
	$outgoing = '';
	$query = "select * from mcdirlinks where parentlink='$url' order by parentlink";
	$result = mysql_query($query) or die ($query.' '.mysql_error());
	$lastlink='';
	while ($r = mysql_fetch_object($result))
	{
		//echo $r->link." ".$prefix."<br>";

		if	(substr ($r->link,0,strlen($prefix))==$prefix)
		$incoming.=label_from_url($r->link).
		"!".substr($r->link,strlen($prefix)+1)."|"; else
		$outgoing.=label_from_url($r->link)."!".$r->link."|";
	}
	mysql_free_result($result);
	$timeend1 = microtime(true);
	$elapsed1= $timeend1-$timestart; //float

	$outgoing = substr($outgoing,0,strlen($outgoing)-1); // remove last pipe
	$incoming = substr($incoming,0,strlen($incoming)-1); // remove last pipe

	$tags='';//compute_tags ($pageid,$incoming,$outgoing,label_from_url($url),$url,$prefix);

	$incoming = addslashes($incoming);
	$outgoing = addslashes ($outgoing);
	$tags = addslashes ($tags);
	$update = "update mcdirpages set ilinks='$incoming', xlinks='$outgoing', tags='$tags' where
		pageid='$pageid'";
	mysql_query($update) or die("Cant $update".mysql_error());
	$timeend = microtime(true);
	$elapsed = $timeend-$timestart; //float
	$elapsed = round ($elapsed,3);
	echo "munch:  processed $url in $elapsed secs<br>";
	@ob_flush();
	flush();
	$iter++;
}
echo "munch: all done with $iter pages";
?>