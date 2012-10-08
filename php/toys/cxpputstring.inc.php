<?PHP
function CxpPutString ($remoteurl,$callerskey,$ccrstring)
{

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
		else { if ($prot == "https:") $port=443; else $port = 80; $host = substr($spec,$pos,$slashloc-$pos); }
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
/*
		$p = strpos($response,"<cxp>");
		if ($p===false) {
			$p=strpos($response,"Server:"); // if a bad response grab error code
			if ($p!='') $response = substr($response,0,$p);

			$response = "<cxp><host>$ipaddr:$port</host><error>INVALID REMOTE RESPONSE $response</error></cxp>";
		} else
		
		//good response, just return the xml part
		$response = substr($response,$p);
*/
		return $response;

	}

	function prepare_xmldatablock($oc)
	{
		return "<xmldata><opcode>$oc</opcode>";
	}
	function property($tag,$url)
	{
		return"<$tag>$url</$tag>";
	}
	function attach($file,$type,$name)
	{
		$s = @file_get_contents($file);
		if ($s===false) return false;

		$z = base64_encode($s);
		return "<File><FileName>$name</FileName><FileType>$type</FileType><FileContents>$z</FileContents></File>";
	}


	// start here


	// get address of remote part
	if (parseurl ($remoteurl,$prot,$host,$port,$filespec)=== false)
	die ("Can't parse remote url - $remoteurl");
	if ($prot=='https:') $host = 'ssl://'.$host;
	// start to assemble the xml data block as per the spec
	// if no opcode then don't make an xmldatablock


	$xmldata = prepare_xmldatablock('PUT');
	$xmldata .= property("CallersKey",$callerskey);
	$xmlencoded = urlencode($xmldata);

	$ccrencoded = urlencode($ccrstring);


	$request = time();

	$ret = "";

	$response = postcall($host,$port,$filespec,$xmlencoded,$ccrencoded);
	//echo $form; dont echo, instead contact inline
	// write out the xml
	//file_put_contents("clientdata/req$request.response.xml",$response);

	return "<cxpresponses>$response</cxpresponses>";
}
?>