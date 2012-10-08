<?PHP


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
		if ($colonloc != false){ $port = trim(substr($spec,$colonloc+1,$slashloc-$colonloc-1));
		$host = trim(substr($spec,$pos,$colonloc-$pos));}
		else { if ($prot == "https:") $port=443; else $port = 80; $host = trim(substr($spec,$pos,$slashloc-$pos)); }
		$file = trim(substr($spec,$slashloc));//include / as leader in filespec
		//echo "parseurl: $prot $host $port $file\r\n";
		return true;
	}

	function postcall($ipaddr,$port,$file,$guid)
	{
		//echo "calling $ipaddr:$port $file with $guid\r\n";
		$postdata = "Command=GET&guid=".$guid;
		$contlen = strlen($postdata);

		$out = "POST $file HTTP/1.1\r\n";
		$out .= "Host: cxp\r\n"; //donot remove
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-length: $contlen\r\n";
		$out .= "Connection: Close\r\n\r\n";

		$out .= $postdata;
		// send the post
		if ($port=='')$port=443; //juggle
		
		$fp = fsockopen($ipaddr, $port, $errno, $errstr, 30);
		if (!$fp) {
			$retstr =  "$errstr ($errno)<br />\n"; return false;
		} ;

		fputs($fp, $out);   //header
/*
		$response = "";
		while (!feof($fp)) {
			$block = fgets($fp, 8192);
			$len = strlen($block);
			$first20 = substr($block,0,20);
			$last20 = substr($block,$len-20);
			echo "Block size $len First 20: $first20  Last 20: $last20\r\n";
			$response.=$block;
		};
		*/
		$response = stream_get_contents($fp); // new with php5
		fclose($fp);
//		echo $response; //see whats coming
		$sz = strlen('<?xml version="1.0" encoding="UTF-8"?>');
		$where = strpos($response,'<?xml version="1.0" encoding="UTF-8"?>');
		$response = substr($response,$where+$sz);
		$sz = strlen('</ContinuityOfCareRecord>');
		$where = strpos($response,'</ContinuityOfCareRecord>');
		$response = substr($response,0,$where+$sz);
	return trim($response);

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

/**
 * Enter description here...
 *
 * @param unknown_type $remoteurl
 * @param unknown_type $args
 * @return unknown
 */
function cxppostget ($remoteurl,$args)
{
	// get address of remote part
	if (parseurl ($remoteurl,$prot,$host,$port,$filespec)=== false)
	return false;
	if ($prot=='https:'){$host = 'ssl://'.$host; }// see php manual
	
	// start to assemble the xml data block as per the spec
	// if no opcode then don't make an xmldatablock
	return postcall($host,$port,$filespec,$args);
}
?>