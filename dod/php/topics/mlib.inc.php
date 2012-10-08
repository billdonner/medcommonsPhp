<?php
//dump the links db in a hierarchical fashion
// assumes the target direcvtory is clear
function find_src($url)
{
	$url = urldecode($url);
	// find whatever is in the first part of the domain
	$pos1 = strpos ($url,'//');
	if ($pos1===false) return false;
	$pos2 = strpos ($url,'/',$pos1+2);
	if ($pos2==false) return false;
	$match = substr ($url, $pos1+2,$pos2-$pos1-2);
	//echo "matching $match against ".$GLOBALS['xsitelist'][1]." <br>";
	for ($i=0; $i<$GLOBALS['xsitecnt']; $i++)
	// have it, return corresponding entry
	if ($match==$GLOBALS['xsitelist'][$i]) return $GLOBALS['xsitetags'][$i];

	return false;
}
function images_from_nurl($nurl)
{   $nurl = urldecode($nurl);
//echo "images_from_nulr($nurl);\r\n";
$url = str_replace('http://','http___',$nurl);
$pos = strpos($url,'/');
if ($pos===false) return false;
$url = substr($url,0,$pos);
$pos = strpos ($url,'http___');
if ($pos===false) return false;
$url = substr($url,$pos);
$url = "images/$url"."_.jpeg";
if (file_exists($url))
return  "<img src='$url' />";
else
return false;
}
function ximages_from_nurl($nurl)
{
	//echo "ximages_from_nurl($nurl)\r\n";
	$pos = strpos($nurl,'http://');
	if ($pos===false) return false;
	return substr($nurl,$pos);
}
function xlinks_url($url)
{
	$url = urldecode($url);
	$st = "http://www.nlm.nih.gov/cgi/medlineplus/leavemedplus.pl?";
	if (strpos($url,$st)!==false) {
		$nurl = substr($url,strlen($st));
		$nurl = str_ireplace(array('theurl','theorg'),array('u','o'),$nurl);
		//echo "Url is $url nurl is $nurl<br>";
		return ximages_from_nurl($nurl);
	}
	$st = "http://www.nlm.nih.gov/cgi/medlineplus/pubmedsearch.pl?";
	if (strpos($url,$st)!==false) {
		$nurl = substr($url,strlen($st));
		$nurl = str_ireplace(array('theurl','theorg'),array('u','o'),$nurl);
		//echo "Url is $url nurl is $nurl<br>";
		return ximages_from_nurl($nurl);
	}
	return (false);
}
function rewrite_url($url)
{
	$st = "http://www.nlm.nih.gov/cgi/medlineplus/leavemedplus.pl?";
	if (strpos($url,$st)!==false) {
		$nurl = substr($url,strlen($st));
		$nurl = str_ireplace(array('theurl','theorg'),array('u','o'),$nurl);
		//echo "Url is $url nurl is $nurl<br>";
		return array("mxremote.php?$nurl",images_from_nurl($nurl));
	}
	$st = "http://www.nlm.nih.gov/cgi/medlineplus/pubmedsearch.pl?";
	if (strpos($url,$st)!==false) {
		$nurl = substr($url,strlen($st));
		$nurl = str_ireplace(array('theurl','theorg'),array('u','o'),$nurl);
		//echo "Url is $url nurl is $nurl<br>";
		return array ("mxremote.php?$nurl",images_from_nurl($nurl));
	}
	return array($url,false);
}
function prep_link($mylink,$prefix)
{
	if (strpos($mylink, $prefix) ==0) //if we have a match
	return substr($mylink,strlen($prefix)+1); // rewrite the link
	else return $mylink;
}
function label_from_url($url)
{
	$query = "Select label from mcdirlinks where link='$url' limit 1";
	$result = mysql_query($query) or die($query.' '.mysql_error());
	$obj = mysql_fetch_object($result);
	mysql_free_result($result);
	if (!$obj) return false; else
	$label = $obj->label;
	return fixupm($label);
}
function fixupm ($label)
{
	$medline = "medlineplus:";
	if (strtolower(substr($label,0,strlen($medline)))==$medline) return substr($label,strlen($medline)+1);
	$medline = "medline plus:";
	if (strtolower(substr($label,0,strlen($medline)))==$medline) return substr($label,strlen($medline)+1);
	return $label;
}

?>