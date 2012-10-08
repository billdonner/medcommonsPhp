<?php
require_once 'tlib.inc.php';
function make_index ($outpre,$url)
{
	if ($outpre!== false) // output only if user specified a place to put it
	{
		$contents= file_get_contents($outpre.$url);
		$mylink = 'index.html';
		$fn = $outpre.$mylink;
		if (file_exists($fn))
		{ // if we get to the page twice, dont bother re-writing it
			unlink ($fn);
			echo "Replacing file $fn<br>";
		}
		else
		echo "Writing file $fn <br>";
		file_put_contents($outpre.$mylink,$contents);
	}
	if ($outpre!== false) // output only if user specified a place to put it
	{
		$mylink = 'topics.htm'; // alright, do it again
		$contents= file_get_contents($outpre.$url);
		$pos1 = strpos($contents,'<!-- Start MC TOPICS'); // dont match whole thing because of version numbers
		$pos2 = strpos ($contents, '<!-- End MC TOPICS -->');
		if (($pos1===false) or ($pos2===false)) die("Internal inconsistency cant find topics s:$pos1 e:$pos2");
		$contents = substr($contents,$pos1,$pos2-$pos1+strlen('<!-- End MC TOPICS -->'));
		$contents = str_replace("<span class='mclink up S'><a  href='",
		"<span class='mclink up S'> <a  href='/interests/",$contents
		);
		$fn = $outpre.$mylink;
		if (file_exists($fn))
		{ // if we get to the page twice, dont bother re-writing it
			unlink ($fn);
			echo "Replacing file $fn<br>";
		}
		else
		echo "Writing file $fn <br>";
		file_put_contents($outpre.$mylink,$contents);
	}

}
function 	build_page ($masterpage, $pageid, $ilinks,$xlinks,$tlinks,$keywords, $mylabel,$inlink,$prefix,$outpre,$template)
{
	//echo "buildpage mylabel $mylabel inlink $inlink prefix $prefix outpre $outpre<br>";
	//$div = '';
	//$div .= "<div class ='InterestGroups' align=left style='min-width: 420px;'><h4>$mylabel <small><a href='index.html'>group home</a></small></h4>";
	$ilks = array();
	$ilk = explode ('|',$ilinks); 
	sort($ilk); // just try this litle hack
	$xlks = array();
	$xlk = explode ('|',$xlinks);
	//	echo "ilinks is $ilinks ";
	$tlks = array();
	$tlk = explode ('|',$tlinks);
	//http://beta.medcommons.org/topics/mxremote.php?u=http%3A%2F%2Fkidshealth%2Eorg%2Fteen%2Fyour%5Fmind%2Fproblems%2Fpet%5Fdeath%2Ehtml
	//http://beta.medcommons.org/topics/mxremote.php?u=http%3A%2F%2Fkidshealth%2Eorg%2Fkid%2Ffeeling%2Fthought%2Fpet%5Fdeath%2Ehtml
	// ok, play out the links
	$cat =// $GLOBALS['Homepage_Url'].
	              "../improve.php?op=topic&a=".urlencode($mylabel);
	$clone = "../edittopic.php?&a=".urlencode($mylabel);
	if ($GLOBALS['resident_editors'])
	{
		$topiclink = "<i><a href=iclpages.php>mytopics</a></i>&nbsp;";
		$contactus = "<i><a href=$clone>(become an editor)</a></i>";
		$topiclink2 = "<i><a href='/interests/iclpages.php' >mytopics</a></i>&nbsp;";
	}
	else
	{
		$contactus ='';$topiclink = ''; $topiclink2='';
	}
	if ($masterpage) $banner = "<hr width='100%' /><h4>All Topics <small>$topiclink2</small></h4>";
	else $banner = "<hr width='100%' /><h4>Topic: $mylabel&nbsp;$topiclink</h4>";
	$topicsdiv = "<!-- Start MC TOPICS v0.12-->
	            <script type='text/javascript'>
	            setSuggestions('$cat');
	            </script>";
	$topicsdiv .='<div align="left" id="mc-topics">'.$banner;
	$phrcount=0;
	$urls=array();
	// if any customized pages from the residents, put their links out right here
	$tagged = addslashes($mylabel); // handle quotes
	$qqquery = "select * from clonedpages where roottopic='$tagged' limit 1";
	echo $qqquery;
	$result = mysql_query($qqquery) or die ($qqquery.' '.mysql_error());
	$r=mysql_fetch_object($result);
	if ($r!==false)
	{
		// echo "******** found ".$r->roottopic."  ".$r->phrlinks;
		$glks = array();
		if
		(strlen($r->thegroup)<10) $glk = false; else
		{
			$glk = explode ('|',$r->thegroup);
			$topicsdiv .= '<div id="mc-exampleGroup" ><p>Editors: ';
			foreach ($glk as $gk) {
				list ($email,$mcid,$screenname,$other) = explode ('!',$gk);
				if ($screenname=='')$screenname=$email;
				$topicsdiv.="<span class='mclink resGroup  S'><a
			                   title='$mcid $other' href='mailto:".$email."'>$screenname</a>&nbsp;</span>";
			}

			$topicsdiv .='</p></div>';

		}
		$tlks = array();
		if
		(strlen($r->phrlinks)<10) $tlk = false; else
		{
			$tlk = explode ('|',$r->phrlinks);
			$topicsdiv .= '<div id="mc-examplePHRs" ><p>Example Cases: ';
			foreach ($tlk as $tk) {
				list ($label,$url) = explode ('!',$tk);
				list($url,$images) =rewrite_url($url);//$src = find_src($url);if ($src!==false) $src = "title='via $src'";
				$url = $GLOBALS['Commons_Url']."trackemail.php?a=$url";
				$phrcount++;
				$topicsdiv.="<span class='mclink phr S'><a target='_new' href='".$url."'>".$label."</a></span>&nbsp;";
			}
			$topicsdiv .='</p></div>';

		}
	}


	if (count($ilk)>0)
	foreach ($ilk as $ik) {
		list ($label,$url) = explode ('!',$ik);
		if (strpos($url,'healthtopics.')===false)
		// build list of unique urls
		{
		$found=false; for ($i=0; $i<count($urls); $i++)
		{
			if ($urls[$i][0]==$url) {$found=true; break;}
		}
		if (!$found) {$urls[]=array($url,$label);}
		}
	}
	// play them out
	foreach ($urls as $hurl)
	{
		$url = $hurl[0]; $label = fixupm($hurl[1]);
		list($url,$images) =rewrite_url($url); $src = find_src($url);if ($src!==false) $src = "title='via $src'";
		$topicsdiv.="<span class='mclink up S'><a $src href='$url'>$label</a></span>&nbsp;\r\n";

	}
	$topicsdiv.="</div><!-- End MC TOPICS -->";

	//	echo "$xlk count xlk ".count($xlk)."count ilk ".count($ilk)."<br>";
	if (count($xlk)>1)
	{
		$xrefdiv = '<!-- Start MC XREFS --><div align="left" class="mc-xrefs">
                    <h4>Resources</h4>';

		foreach ($xlk as $xk) {
			list ($label,$url) = explode ('!',$xk);
			list($url,$images) =rewrite_url($url);$src = find_src($url);if ($src!==false) $src = "title='via $src'";
			$xrefdiv.="<span class='mclink ext M'><a  $src target='_new' href='$url'>$label $images</a></span>&nbsp;\r\n";
		}


		$xrefdiv.="</div><!-- End MC XREFS -->";
		$topicsdiv .=$xrefdiv; // accumlate our output
	}
	if (false) //later
	{
		$tagsdiv = '<div id="mc-tags">';
		// check it out, sort the tags and make unique
		/*
		foreach ($tlk as $tk) {
		list ($label,$url) = explode ('!',$tk);
		$tagsdiv .= "<span class='mclink up S'><a href='$url?tag=$label'>$label</a>&nbsp;&nbsp;</span>\r\n";
		}
		*/
		$tagsdiv.="</div>";
	}
	if ($GLOBALS['resident_editors']){
		if ($phrcount==0)

		$mgmt = <<<XXX
	<p class='p2'>Note: Example cases can be added to this topic. $contactus</p>
XXX;
		else
		$mgmt = <<<XXX
	<p class='p2'>Note: The example cases for this topic are managed by medical residents. $contactus</p>
XXX;
}
else $mgmt = '';

	$topicsdiv .= $mgmt;
	//set up title
	$title = "Topic: $mylabel";
	//patch into template**{{keywords go here}}**
	$contents = str_replace('**{{title goes here}}**',$title,$template);
	$contents = str_replace('**{{topics div goes here}}**',$topicsdiv,$contents);
	$contents = str_replace('**{{keywords go here}}**',$keywords,$contents);
	$contents = str_replace('**{{phtml}}**','html',$contents); // make links show as straight html

	if ($outpre!== false) // output only if user specified a place to put it
	{
		if (substr($inlink,0,strlen($prefix))==$prefix)
		$inlink = substr($inlink,strlen($prefix)+1); // include slash
		//pull an add out of database
		//	$adsdiv = get_random_ad();
		//	$contents = str_replace('**{{ads div goes here}}**',$adsdiv,$contents);

		//	update_tag_tables($pageid, $tags); // only update these tables if doing the db
		$fn = $outpre.$inlink;
		if (file_exists($fn)){ // if we get to the page twice, dont bother re-writing it
			unlink ($fn);
			echo "<br>Replacing file $fn<br>";
		}
		else
		echo "Writing file $fn <br>";
		file_put_contents($outpre.$inlink,$contents);
	}
	else echo "$topicsdiv<br>";
}
//
// handles op=make and op=display, generates pages for main /interests site
//


// starts here
$masterindex = "healthtopics.html";

$GLOBALS['xsitelist'] = array(); // everything we want to skip over
$GLOBALS['xsitetags'] = array(); // everything we want to skip over
if (isset($_REQUEST['xsites']))
{
	$s = $_REQUEST['xsites']; // xsites file
	$skip = file($s); // entire file into array
	$skcount = count($skip)-1; // hack to skip over last line
	for ($i=0; $i<$skcount;$i++) {
		list ($GLOBALS['xsitetags'][],
		$GLOBALS['xsitelist'][])=
		explode('|',
		trim($skip[$i]));
	}
}
$GLOBALS ['xsitecnt'] = count($GLOBALS['xsitelist']);
$db = $_REQUEST['db'];


$op = $_REQUEST['op'];
$prefix = $_REQUEST['pre'];
if (!isset($_REQUEST['out'])) $outprefix=false; else $outprefix = $_REQUEST['out'];
$t = $_REQUEST['template'];
$template = file_get_contents($t); // template file

echo"<html><head><title>Topics Page Generator</title>
 <style type='text/css'>@import 'plain.css';</style>
 </head><body><h4>Topics Page Generator</h4>";
if ($outprefix!==false)
echo "<small>urls with a prefix of <i>$prefix</i> will be rewritten to start with <i>$outprefix</i><br>";
mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");

//get list of pages to work on
$urls=array();
$query = "select pageid,url,ilinks,xlinks,tags,keywords from mcdirpages";
$result = mysql_query($query) or die ($query.' '.mysql_error());
while ($r = mysql_fetch_object($result)) $urls[]=$r; mysql_free_result($result);

//if ($op=='make') ads_init();

//for each url
$iter=0;
foreach ($urls as $r)
{
	$url = $r->url;
	$timestart = microtime(true);

	$pageid = $r->pageid;
	$ilinks = stripslashes($r->ilinks);
	$xlinks = stripslashes($r->xlinks);
	$tags = $r->tags;

	if ($op=='make'){
		$masterpage = (strpos($url,$masterindex)!==false); // this is crude
		build_page ($masterpage, $pageid,$ilinks, $xlinks, $tags,$r->keywords,
		label_from_url($url),$url,$prefix,$outprefix,$template);
	}
	else if ($op=='display'){
		build_page (false, $pageid,$ilinks, $xlinks, $tags,$r->keywords,
		label_from_url($url),$url,$prefix,false,$template);
	}
	$timeend = microtime(true);
	$elapsed = $timeend-$timestart; //float
	$elapsed = round ($elapsed,3);
	if ($op=='make') {
		echo "mgen:  processed $url in $elapsed secs<br>";
		@ob_flush();
		flush();
	}
	$iter++;
}

// make the index page be a clone of healthtopics.html

if ($op=='make')
make_index($outprefix,$masterindex);
echo "mgen: fini with $iter pages";
?>
