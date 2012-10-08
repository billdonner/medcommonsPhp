<?php
require_once 'mlib.inc.php';
// writes a file of external page links to process for the html to jpeg converter
function 	one_page ($ilinks,$xlinks,$tlinks)
{
	$ilks = array();
	$ilk = explode ('|',$ilinks);
	$xlks = array();
	$xlk = explode ('|',$xlinks);
	//	echo "ilinks is $ilinks ";
	$tlks = array();
	$tlk = explode ('|',$tlinks);
	$urls=array();
	if (count($ilk)>0)
	foreach ($ilk as $ik) {
		list ($label,$url) = explode ('!',$ik);
		// build list of unique urls

		$found=false;
		for ($i=0; $i<count($urls); $i++)
		{
			if ($urls[$i][0]==$url) {$found=true; break;}
		}
		if (!$found) {$urls[]=array($url,$label);}
	}
	// play them out
	foreach ($urls as $hurl)
	{
		$url = $hurl[0]; $label = fixupm($hurl[1]);
		$xlink  =xlinks_url($url);
		if ($xlink!==false)
		$GLOBALS['xsitelinks'][]=$xlink;

	}
	if (count($xlk)>1)
	foreach ($xlk as $xk) {
		list ($label,$url) = explode ('!',$xk);
		$xlink  =xlinks_url($url);
		if ($xlink!==false)
		$GLOBALS['xsitelinks'][]=$xlink;
	}
}


// starts here

$GLOBALS['xsitelinks'] = array(); // everything we want to skip over
$db = $_REQUEST['db'];
$outpre = $_REQUEST['out'];

mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");



//get list of pages to work on
$urls=array();
$query = "select pageid,url,ilinks,xlinks,tags from mcdirpages";
$result = mysql_query($query) or die ($query.' '.mysql_error());
while ($r = mysql_fetch_object($result)) $urls[]=$r; mysql_free_result($result);
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

	//		make_page( $iter++, $id,$parentid, $label, $link, $mykids,$prefix,$outprefix,$template);
	one_page ($ilinks, $xlinks, $tags);

	$timeend = microtime(true);
	$elapsed = $timeend-$timestart; //float
	$elapsed = round ($elapsed,3);

	echo "mlinks:  processed $url in $elapsed secs<br>";
	@ob_flush();
	flush();

	$iter++;
}

$mylink = 'sites.txt';
$fn = $outpre.$mylink;
if (file_exists($fn))
{ // if we get to the page twice, dont bother re-writing it
	unlink ($fn);
	echo "mlinks: replacing file $fn<br>";
}
else
echo "mlinks: writing file $fn <br>";
sort($GLOBALS['xsitelinks']); // sort this array
$count=count($GLOBALS['xsitelinks']);

$contents = ''; $last=''; $linkcount = 0;
for ($i=0; $i<$count; $i++)
{
	$pro = $GLOBALS['xsitelinks'][$i];
	$pos1 = strpos($pro,'http://');
	if ($pos1!==false)
	{
		$pos2 = strpos($pro,'/',$pos1+8);
		if ($pos2!==false)
		$me = substr($pro,$pos1,$pos2-$pos1+1);
		else $me = substr($pro, $pos1);
		if ($me !=$last)
		{
			// dont do pdfs irrelevant if stripping
			//if (strpos($me,'.pdf')===false)
			{
				$contents.= $me."\r\n";
				$last = $me;
				$linkcount++;
			}
		}
	}
}

file_put_contents($outpre.$mylink,$contents);

echo "mlinks: fini with $iter pages wrote $linkcount links\r\n";
?>
