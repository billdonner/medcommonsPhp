<?php
require_once "dbparamsidentity.inc.php";

// rest web service - outerframework

abstract class restws {

	private $outbuf;
	private $servicetag;

	function set_servicetag ($s) { $this->servicetag = $s;} // sets outer tag

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
abstract class dbrestws extends restws {
	private $nodeid;
			// add connect/disconnect to sql database
		function dbexec($query,$errstr){
			//$query = "SELECT * from user WHERE (email_address='$username') and (hpass='$hpass')";
      error_log("$query");
			$result = mysql_query ($query) or $this->xmlend("$errstr".mysql_error());
			if ($result=="") {$this->xmlend("failure"); exit;}
			return $result;
		}
		//overrides handlews to add db connect/disconnect
		function dbconnect()
		{
			mysql_connect($GLOBALS['DB_Connection'],
			$GLOBALS['DB_User'],
			$GLOBALS['DB_Password']
			) or $this->xmlend ("can not connect to mysql");

			$db = $GLOBALS['DB_Database'];
			mysql_select_db($db) or $this->xmlend ("can not connect to database $db");
		}
		function dbdisconnect()
		{
			mysql_close();
		}
		function handlews($servicetag)
		{

			$this->set_servicetag($servicetag);
			$this->dbconnect();
			// do standard processing for all web services
			$this->xmltop();


			// the xmlbody routine is always overriden
			$this->xmlbody();
			$this->xmlend("success");
			$this->dbdisconnect();

		}
}
?>
