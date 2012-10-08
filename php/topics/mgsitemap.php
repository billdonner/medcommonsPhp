<?php
require_once 'mlib.inc.php';

function 	build_sitemap_entry ($pageid,$mylabel,$inlink,$prefix,$outpre)
{

	if ($outpre!== false) // output only if user specified a place to put it
	{
		if (substr($inlink,0,strlen($prefix))==$prefix)
		$inlink = substr($inlink,strlen($prefix)+1); // include slash
		$fn = $outpre.$inlink;
		$url = $fn;
	} else $url = $inlink;
	$lastmod =  date('c');//  iso 8601 filemtime('/interests/'.$inlink));
	//$url = urlencode($url);

	$thisurl = <<<XXX
<url>
    <loc>$url</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.5</priority>
</url> 
XXX;
	return $thisurl;
}


//
// handles op=make and op=display, generates pages for main /interests site
//


// starts here


$db = $_REQUEST['db'];

$prefix = $_REQUEST['pre'];
if (!isset($_REQUEST['out'])) $outprefix=false; else $outprefix = $_REQUEST['out'];
//$t = $_REQUEST['template'];
//$template = file_get_contents($t); // template file

mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");

// clean up any tags we already have if not in preview mode

//get list of pages to work on
$urls=array();
$query = "select pageid,url,ilinks,xlinks,tags from mcdirpages";
$result = mysql_query($query) or die ($query.' '.mysql_error());
while ($r = mysql_fetch_object($result)) $urls[]=$r; mysql_free_result($result);

//for each url
$iter=0; $urlist='';
foreach ($urls as $r)
{
	$url = $r->url;
	$pageid = $r->pageid;

	$urlist .=	build_sitemap_entry ($pageid,label_from_url($url),$url,$prefix,$outprefix);

	$iter++;
}
$outemplate=<<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84
http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
$urlist 
</urlset> 
XXX;
file_put_contents('gsitemap.xml',$outemplate); //gzcompress($outemplate));
header ("Conent-type: text/plain");
echo "Wrote gsitemap.xml.gz";

?>
