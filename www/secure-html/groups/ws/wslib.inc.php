<?php

// rest web service - outerframework

abstract class restws {

	private $outbuf;
	private $servicetag;

	function set_servicetag ($s) { $this->servicetag = $s;} // sets outer tag

	function cleanreq($fieldname)
	{
		// take an input field from the command line or POST
		// and clean it up before going any further
		$value = $_REQUEST[$fieldname];
		$value = htmlspecialchars($value);
		return $value;
	}

	abstract function xmlbody ();
	
	function xmlreply ()
	{
		// generate headers
		$mimetype = 'text/xml';
		$charset = 'ISO-8859-1';
		header("Content-type: $mimetype; charset=$charset");
		echo ('<?xml version="1.0" ?>'."\n");
		echo $this->outbuf; // this is where we can trace
	}

	function xm($s)
	{ $this->outbuf.= $s;}

	function xmfield($tag,$val)
	{//just returns a string, must go thru xm() to be seend
	return "<$tag>".$val."</$tag>";}
	//
	//outer frame of XML document response is implemented by
	//   calling xmltop {calls to xm}  calling xmlend()
	//
	function xmltop()
	{
		$this->outbuf="";
		$this->xm("<".$this->servicetag.">\n");//outer level
		$srva = $_SERVER['SERVER_ADDR'];
		$srvp = $_SERVER['SERVER_PORT'];
		$gmt = gmstrftime("%b %d %Y %H:%M:%S");
		$uri = htmlspecialchars($_SERVER ['REQUEST_URI']);
		$this->xm("<details>$srva:$srvp $gmt GMT</details>");
		$this->xm("<referer>".htmlspecialchars($_SERVER ['HTTP_REFERER'])."</referer>\n");
		$this->xm("<requesturi>\n".$uri."</requesturi>\n");
	}

	function xmlend( $xml_status)
	{
		$this->xm("<summary_status>".$xml_status."</summary_status>\n");
		$this->xm("</".$this->servicetag.">\n");//outer level
		$this->xmlreply(); // show its all good
		exit;
	}

	function handlews($servicetag)
	{

		$this->set_servicetag($servicetag);
		$this->xmltop();
		$this->xmlbody();
		$this->xmlend("success");

	}

}




?>