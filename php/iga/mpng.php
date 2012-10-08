<?php
// generate a png image
function color($c) {
	return array (
	$GLOBALS[$c][0],
	$GLOBALS[$c][1],
	$GLOBALS[$c][2]
	);
}
function mimage($lines, $width,$height,$pixoffsetx,$pixoffsety,
$font,$boldfont,$lineheightpix,$boldlineheightpix,
$name,$url,$bc,$tc)
{
	header("Content-type: image/png");
	$ypix = $pixoffsety;
	$im = @imagecreate($width, $height)
	or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, $bc[0], $bc[1],$bc[2]);
	$text_color = imagecolorallocate($im, $tc[0], $tc[1], $tc[2]);
	imagestring($im, $boldfont, $pixoffsetx, $ypix,  $name, $text_color);
	$time=strftime('%R');
	imagestring($im, $font, $width - 44, $ypix, ' '.$time, $text_color);
	$ypix += $boldlineheightpix;

	foreach ($lines as $line)
	{

		imagestring($im, $font, $pixoffsetx,$ypix,
		$line, $text_color);
		$ypix += $lineheightpix;
	}
	imagepng($im);
	imagedestroy($im);
}

function pollserver($type, $repeat,$url)
{
	function getval ($s,$tg)
	{
		$tag = '<'.$tg.'>';
		$tagl = strlen($tag);
		$pos1 = strpos($s,$tag);
		if ($pos1===false) return false;
		$tag = '</'.$tg.'>';
		$pos2 = strpos($s,$tag,$pos1+$tagl);
		if ($pos2===false) return false;
		$val = substr($s,$pos1+$tagl,$pos2-$pos1-$tagl);
		return trim($val);

	}
	function gwparsexml($s)
	{
		$lines = array();
		$val=getval($s,'free-repository-space');
		if ($val)$lines []= "repository free: $val".'B';
		$val=getval($s,'free-memory');
		if ($val)$lines []= "memory free: $val";
		$val=getval($s,'cxp-transactions');
		if ($val)$lines []= "cxp-transactions: $val";
		$val=getval($s,'images-encoded');
		if ($val)$lines []= "images-encoded: $val";
		return $lines;
	}
	function apparsexml($s)
	{
		$lines = array();
		$val=getval($s,'host');
		if ($val)$lines []= "host: $val";
		$val=getval($s,'diskfreespace');
		if ($val){ $val = round($val,1); $lines []= "disk free: $val"."GB";}
		$val=getval($s,'disktotalspace');
		if ($val){$val = round($val,1);$lines []= "disk total: $val"."GB";}
		$val=getval($s,'time');
		if ($val)$lines []= "gmt: $val";
		return $lines;
	}
	function dbparsexml($s)
	{
		$lines = array();
		$val=getval($s,'host');
		if ($val)$lines []= "host: $val";
		$val=getval($s,'diskfreespace');
		if ($val){ $val = round($val,1); $lines []= "disk free: $val"."GB";}
		$val=getval($s,'disktotalspace');
		if ($val){$val = round($val,1);$lines []= "disk total: $val"."GB";}
		$val=getval($s,'time');
		if ($val)$lines []= "gmt: $val";
		return $lines;
	}

	// start here


	$lines = array();
	for ($i=1; $i<=$repeat; $i++)
	{
		$time1 = microtime(true);
		$str = @file_get_contents($url);
		if ($str===false) {
			$lines[] ="bad: $url";
			return array($lines,false);
		}
		$time2 = microtime(true);
		$elapsed = round($time2-$time1,2);
		$len = round(strlen ($str)/1024,2);
		$lines []= "$type ping: $elapsed"." secs $len"."KB";
		switch ($type)
		{
			case 'gw': $more =  gwparsexml($str); break;
			case 'ap': $more =  apparsexml($str); break;
			case 'db': $more =  dbparsexml($str); break;
			default: $more = array("Unknown server type $type");
		}

		if ($more) foreach ($more as $m) $lines[] = $m;
	}
	if ($elapsed>.5) $state='slow'; else
	$state='normal';
	return array($lines,$state);
}

// start here, get all parameters, supply reasonable defaults

$GLOBALS['black'] = array (0,0,0);
$GLOBALS['white'] = array (255,255,255);
$GLOBALS['red'] = array (255,102,0);
$GLOBALS['blue'] = array (187,255,255);
$GLOBALS['yellow'] = array (255,255,204);


if (isset($_REQUEST['t']))
$t = $_REQUEST['t']; else $t='gw';
if (isset($_REQUEST['repeat']))
$repeat = $_REQUEST['repeat']; else $repeat=1;
if (isset($_REQUEST['font']))
$font = $_REQUEST['font']; else $font=2;
if (isset($_REQUEST['boldfont']))
$boldfont = $_REQUEST['boldfont']; else $boldfont=5;
if (isset($_REQUEST['width']))
$width = $_REQUEST['width']; else $width=160;
if (isset($_REQUEST['height']))
$height = $_REQUEST['height']; else $height=120;
if (isset($_REQUEST['pixoffsetx']))
$pixoffsetx = $_REQUEST['pixoffsetx']; else $pixoffsetx=5;
if (isset($_REQUEST['pixoffsety']))
$pixoffsety = $_REQUEST['pixoffsety']; else $pixoffsety=5;
if (isset($_REQUEST['lineheightpix']))
$lineheightpix = $_REQUEST['lineheightpix']; else $lineheightpix=15;
if (isset($_REQUEST['boldlineheightpix']))
$boldlineheightpix = $_REQUEST['boldlineheightpix']; else $boldlineheightpix=20;
if (isset($_REQUEST['name']))
$name = $_REQUEST['name']; else $name='TestPattern';
if (isset($_REQUEST['url']))
$url = $_REQUEST['url']; else $url="http://www.medcommons.net/";
if (isset($_REQUEST['bc']))
$bc = explode(',',$_REQUEST['bc']); else $bc = color('black');
if (isset($_REQUEST['tc']))
$tc = explode(',',$_REQUEST['tc']); else $tc = color('white');
if (strlen($url)<10) // weed out really poor urls
{
	$lines = array();
	$bc = color('white');
}
else {
	// poll the device, returning several lines to display
	list($lines,$state) = pollserver($t,$repeat,$url);
	// alter the colors based on the state
	if (!$state)
	{
		$bc = color('red');
	}
	else {
		switch ($state)
		{
			case 'normal':
				$bc = color('blue'); $tc=color('black'); break;
			case 'slow':
				$bc = color('yellow'); $tc=color('black'); break;
			default : $bc = color('black');
		}}
}
// build the png and send it back
$result = @mimage($lines,$width,$height,$pixoffsetx,$pixoffsety,
$font,$boldfont,$lineheightpix,$boldlineheightpix,
$name,$url,$bc,$tc) or die ('Bad parameters');
?>