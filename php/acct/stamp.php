<?php
require_once 'mc.inc.php';
require_once 'settings.php';
require_once 'login.inc.php';

$WIDTH = 260;
$HEIGHT = 60;

// generate a png image
function color($c) {
	return array (
	$GLOBALS[$c][0],
	$GLOBALS[$c][1],
	$GLOBALS[$c][2]
	);
}

function mimage($name, $row, $lines, $pixoffsetx, $pixoffsety,
$font, $boldfont, $lineheightpix, $boldlineheightpix,
$bc, $tc) {
	global $WIDTH, $HEIGHT;

	$ypix = $pixoffsety;
	$im = @imagecreatetruecolor($WIDTH, $HEIGHT)
	or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, $bc[0], $bc[1],$bc[2]);
	imagefill($im, 0, 0, $background_color);
	$text_color = imagecolorallocate($im, $tc[0], $tc[1], $tc[2]);

	if ($row['acctype'] == 'VOUCHER')
	{
		$url = $_SERVER['DOCUMENT_ROOT'] . '/images/Hourglass_Icon_Check.gif';
		$photo = imagecreatefromgif($url);
	}  else if (count($lines) <2)
	{
		$url = $_SERVER['DOCUMENT_ROOT'] . '/images/Hourglass_Icon.gif';
		$photo = imagecreatefromgif($url);
	}  else
	if ($row['acctype'] == 'VOUCHER')
	{
		$url = $_SERVER['DOCUMENT_ROOT'] . '/images/Hourglass_Icon_Check.gif';
		$photo = imagecreatefromgif($url);
	}  else
	{
		$url = $row['photoUrl'];
		if (!$url) {
			$url = $_SERVER['DOCUMENT_ROOT'] . '/images/unknown-user.png';
			$photo = imagecreatefrompng($url);
		}
		else {
			$mcid = $row['mcid'];
			$url = $_SERVER['DOCUMENT_ROOT'] . "/user-icons/{$mcid}.jpg";
			$photo = imagecreatefromjpeg($url);
		}
	}


	$pixoffsetx = 0;
	imagestring($im, $boldfont, $pixoffsetx, $ypix,  $name, $text_color);
	$ypix += $boldlineheightpix;

	foreach ($lines as $line) {
		imagestring($im, $font, $pixoffsetx, $ypix,
		$line, $text_color);
		$ypix += $lineheightpix;
	}
	if ($photo) {
		$x = imagesx($photo);
		imagecopy($im, $photo, $WIDTH-$x, 0, 0, 0,
		$x, imagesy($photo));
		imagedestroy($photo);
		$pixoffsetx += $x;
	}
	header("Content-type: image/png");
	imagepng($im);
	imagedestroy($im);
}


// start here, get all parameters, supply reasonable defaults

$GLOBALS['black'] = array (0,0,0);
$GLOBALS['white'] = array (255,255,255);
$GLOBALS['red'] = array (255,102,0);
$GLOBALS['blue'] = array (187,255,255);
$GLOBALS['yellow'] = array (255,255,204);
$GLOBALS['offwhite'] = array (245,245,245);



if (isset($_REQUEST['font']))
$font = $_REQUEST['font']; else $font=2;
if (isset($_REQUEST['boldfont']))
$boldfont = $_REQUEST['boldfont']; else $boldfont=5;

if (isset($_REQUEST['pixoffsetx']))
$pixoffsetx = $_REQUEST['pixoffsetx']; else $pixoffsetx=5;
if (isset($_REQUEST['pixoffsety']))
$pixoffsety = $_REQUEST['pixoffsety']; else $pixoffsety=5;
if (isset($_REQUEST['lineheightpix']))
$lineheightpix = $_REQUEST['lineheightpix']; else $lineheightpix=12;
if (isset($_REQUEST['boldlineheightpix']))
$boldlineheightpix = $_REQUEST['boldlineheightpix']; else $boldlineheightpix=17;
if (isset($_REQUEST['bc']))
$bc = explode(',',$_REQUEST['bc']); else $bc = color('white'); // render invisible if not logged on
if (isset($_REQUEST['tc']))
$tc = explode(',',$_REQUEST['tc']); else $tc = color('white');

$mcid = get_login_mcid();

$lines = array();

if ($mcid) {
	$db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
	$DB_SETTINGS);

	$stmt = $db->prepare("SELECT photoUrl, email, last_name, first_name, mcid, acctype ".
	"FROM users ".
	"WHERE mcid = :mcid");

	$stmt->execute(array("mcid" => $mcid));
	$row = $stmt->fetch();
	$stmt->closeCursor();

	$name = $row['first_name'] . ' ' . $row['last_name'];


	// Hack: check for facebook users and render them differently
	if(preg_match("/^fbid:\/\//",$mcid)!==0) { // facebook
		$fbid = substr($mcid,7, strlen($mcid)-7);
		$lines[] = "Facebook User $fbid";
	}
	else
	$lines[] = pretty_mcid($mcid);

	if ($row['email'])
	$lines[] = $row['email'];
}
else {
	$name = "Not logged on";
	$row = array("photoUrl" => False);
}

$bc = color('offwhite'); //make background white for now
$tc = color('black');

if(isset($_GET['hash'])) {
  header('Cache-Control: public');
  header('Expires: Tue, 01 Jul 2025 00:00:00 GMT');
}

// build the png and send it back
$result = @mimage($name, $row, $lines, $pixoffsetx,$pixoffsety,
$font,$boldfont,$lineheightpix,$boldlineheightpix,
$bc,$tc) or die ('Bad parameters');
?>
