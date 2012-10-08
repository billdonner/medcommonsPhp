<?php

// return a message as an xml string
function prepare_cxpresponse($outertag,$opcode,$callerskey,$txn,$response)
{return "<$outertag>
<opcode>$opcode</opcode>
<callerskey>$callerskey</callerskey><txid>$txn</txid>
<status>$response</status>
</$outertag>";
}
function parseurl ($spec,&$prot,&$host,&$port,&$file)
{ // shred a spec into its constituent pieces
//echo "parseurl: $spec\r\n";
$httpsloc = strpos($spec,"https://");
$httploc = strpos($spec, "http://");
if (($httpsloc===false) && ($httploc===false)) return false;
if ($httpsloc===false) {$pos = $httploc + strlen("http://"); $prot = "http:";} else
{$pos = $httpsloc + strlen("https://"); $prot = "https:";}
$colonloc = strpos ($spec,":",$pos);
$slashloc = strpos ($spec,"/",$pos);
if ($slashloc === false) return false;
if ($colonloc != false){ $port = substr($spec,$colonloc+1,$slashloc-$colonloc-1);
$host = substr($spec,$pos,$colonloc-$pos);}
else { $port = 80; $host = substr($spec,$pos,$slashloc-$pos); }
$file = substr($spec,$slashloc);//include / as leader in filespec
//echo "parseurl: $prot $host $port $file\r\n";
return true;
}

function postcall($ipaddr,$port,$file,$xml,$ccr){

	$postdata = "ccrdata=".$ccr."&xmldata=".$xml;
	$contlen = strlen($postdata);

	$out = "POST $file HTTP/1.1\r\n";
	$out .= "Host: cxp\r\n"; //donot remove
	$out .= "Content-type: application/x-www-form-urlencoded\r\n";
	$out .= "Content-length: $contlen\r\n";
	$out .= "Connection: Close\r\n\r\n";

	$out .= $postdata;
	// send the post

	$fp = fsockopen($ipaddr, $port, $errno, $errstr, 30);
	if (!$fp) {
		$retstr =  "$errstr ($errno)<br />\n"; return $retstr;
	} ;

	fputs($fp, $out);   //header

	$response = "";
	while (!feof($fp)) {
		$response.= fgets($fp, 128);
		$len = strlen($response);
		//		echo "got data from remote side $len";
	}
	fclose($fp);
	//    echo "socket close ok";

	$p = strpos($response,"<cxp>");
	if ($p===false) {
		$p=strpos($response,"Server:"); // if a bad response grab error code
		if ($p!='') $response = substr($response,0,$p);

		$response = "<cxp><host>$ipaddr:$port</host><error>INVALID REMOTE RESPONSE $response</error></cxp>";
	} else
	//good response, just return the xml part
	$response = substr($response,$p);

	return $response;

}

function form($ipaddr,$port,$file,$xmldata,$ccrdata)
{
	$x=<<<XXX
<html><head><title>CXP Post Test</title></head>
<body><form method=POST action="http://$ipaddr:$port$file">
<input type=hidden value=$xmldata name=xmldata>
<input type=hidden value=$ccrdata name=ccrdata>
<input type=submit value="Push to Post http://$ipaddr:$port$file">
</form></body></html>
XXX;
	return $x;
}

?>