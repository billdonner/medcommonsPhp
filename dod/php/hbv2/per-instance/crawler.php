<?php
require_once "healthbook.inc.php";


// whitelist of public medcommons appliances that are acceptable hosts
$GLOBALS['whitelist'] = array(
'http://healthurl.myhealthespace.com/',
'https://healthurl.myhealthespace.com/'
);
/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}
function isdig16($s)
{
	for ($i=0;$i<16;$i++)
	{
		$c = substr($s,$i,1);
		if (('0'>$c)|| ($c>'9')) return false;
	}
	return true;
}
function addhurl ($hurl, $backlink)
{
	$len = count ($GLOBALS['hurlbag']);
	for ($i=0; $i<$len; $i++)	if ($GLOBALS['hurlbag'][$i]==$hurl) return;
	$GLOBALS['hurlbag'][]=array($hurl,$backlink); // add if not already there
}
function scanpage ($pagedata,$pageurl)
{
	// search for hurls with in this page, and add them to the bag
	$len = strlen($pagedata);

	// crudely for now, match against each name on the whitelist
	$wlc = count($GLOBALS['whitelist'] );
	for ($jj=0; $jj<$wlc; $jj++)
	{
		echo "url: $pageurl jj: $jj wlc: $wlc <br/>";
		$pat=$GLOBALS['whitelist'][$jj];
		$patlen = strlen($pat);
		$pos=0;
		while ($pos<$len)
		{
			
			$match = strpos($pagedata,$pat,$pos);
			if ($match) $m = "match: true"; else $m = "match: false";
			echo "pat: $pat pos: $pos  len: $len $m<br/>";
			if ($match!==false){
				$num = substr($pagedata,$match+$patlen,16);
				echo "match num: $num <br/>";
				//see if next 16 also work
				if (isdig16($num)) addhurl($pat.$num,$pageurl);
				$pos = $match;
			} else break;
		}
	}
}
$GLOBALS['hurlbag'] = array(); // place to store hurls already found
$gid = $_REQUEST['gid'];

$data= get_web_page("http://www.facebook.com/group.php?gid=$gid");
print_r($data);
$pagedata = $data['content'];
scanpage ($pagedata,"http://www.facebook.com/group.php?gid=$gid");

print_r ($GLOBALS['hurlbag']);


?>
