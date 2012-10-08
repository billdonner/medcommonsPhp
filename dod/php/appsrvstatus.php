<?PHP
// report status of medcommons central db

require_once "dbparams.inc.php";
require_once "settings.php";

$GLOBALS["SW_Version"]=123;
$GLOBALS["SW_Revision"]=456;

abstract class restws {
	private $outbuf;
	private $servicetag;

	function set_servicetag ($s) { $this->servicetag = $s;} // sets outer tag

	function cleanreq($fieldname)
	{
		// take an input field from the command line or POST
		// and clean it up before going any further
		if (!isset($_REQUEST[$fieldname])) return false; // yikes, always fails tough checking
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
			//	$this->xm("<referer>".htmlspecialchars($_SERVER ['HTTP_REFERER'])."</referer>\n");
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

class statusws extends restws {

	function rowcount ($table)
	{ 	$query = "SELECT COUNT(*) from $table";
	$result = mysql_query ($query) or $this->xmlend("can not query table $table ".mysql_error());
	if ($result=="") {$this->xmlend("failure"); exit;}
	$l = mysql_fetch_array($result,MYSQL_NUM);
	mysql_free_result($result);
	return $l[0];
	}
	function p($tag,$value){
		$this->xm("<$tag>$value</$tag>");
	}

	function z($table){
		$x=$this->rowcount($table);
		$this->xm('<table name="'.$table.'" rowcount="'.$x.'" errors="0" />');
	}


	function xmlbody(){
		global $acGatewayRoot;

		// get general info about this instance of central
		$this->xm("<generalinfo>");
		$this->p("name",$_SERVER['SERVER_NAME']);
		$this->p("ip_addr",$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']);
		$this->p("host", $_ENV['HOSTNAME']);
		if (isset($_SERVER['SSL_SERVER_S_DN_OU']))
		$this->p("certauth",$_SERVER['SSL_SERVER_S_DN_OU']);
		if (isset($_SERVER ['HTTP_REFERER']))
		$this->p("referer",$_SERVER ['HTTP_REFERER']);
		$this->p("time",gmstrftime("%b %d %Y %H:%M:%S")." GMT");
		$this->p("apache_admin",$_SERVER['SERVER_ADMIN']);
		$data = "$acGatewayRoot/data";
		$this->p("diskfreespace",disk_free_space("$data")/(1024*1024*1024));
		$this->p("disktotalspace",disk_total_space("$data")/(1024*1024*1024));
		$this->xm("</generalinfo>");

		// get medcommmons parameters about this instance of central
		$this->xm("<mcinfo>");
		$this->p("sw_version",$GLOBALS["SW_Version"]);
		$this->p("sw_revision",$GLOBALS["SW_Revision"]);
		$this->p("db_connection",$GLOBALS["DB_Connection"]);
		$this->p("db_database",$GLOBALS["DB_Database"]);
		if (isset($GLOBALS['Default_Repository']))
		$this->p("default_repository", $GLOBALS['Default_Repository']);
		$this->xm("</mcinfo>");

		// get record counts from interesting tables
		//$this->xm("<tableinfo>");
		//		z("hipaa");
		//	z("hipaa_trace");
		//$this->z("user");
		// moved these tables to another database, really should reconnect to the other db to get them

		//		z("faxstatus");
		//		z("ccstatus");
		//		z("emailstatus");

		//$this->xm("</tableinfo>");
		//$count++;
		//
		// return outputs
		//
		$this->xmfield("status","ok");
	}
}

//main

$x = new statusws();
$x->handlews("status_Response");



?>
