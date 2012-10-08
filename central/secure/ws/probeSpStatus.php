<?PHP

$GLOBALS['SW_Version']='1.0.0.7';
$GLOBALS['SW_Revision']='101A';


require_once "../dbparams.inc.php";
//this code is almost, but not quite the same as that used in /idp and should be resolved by the xio and idp releases are independent

// classes to support probe functionality from remote sites

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

	abstract function xmlbody ($type);

	function xmlreply ()
	{
		// generate headers
		$mimetype = 'text/xml';
		$charset = 'ISO-8859-1';
		header("Content-type: $mimetype; charset=$charset");
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

	abstract function handlews($servicetag,$type);


}





abstract class dbrestws extends restws {


	// add connect/disconnect to sql database
	function dbexec($query,$errstr){
		//$query = "SELECT * from user WHERE (email_address='$username') and (hpass='$hpass')";
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
	function handlews($servicetag,$type)
	{

		$this->set_servicetag($servicetag);
		$this->dbconnect();
		// do standard processing for all web services
		$this->xmltop();
		$this->xmlbody($type);
		$this->xmlend("success");
	}
}

class probestatus extends dbrestws {

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

	function z($table,$tag){
		$x=$this->rowcount($table);
		$this->xm("<$tag>$x</$tag>");
	}


	function xmlbody($type){
		// get general info about this instance of central

		$this->xm("<generalinfo>");
		$this->p("probe_type",$type);
		$this->p("name",$_SERVER['SERVER_NAME']);
		$this->p("ip_addr",$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']);
		$this->p("host", $_ENV['HOSTNAME']);
		$this->p("certauth",$_SERVER['SSL_SERVER_S_DN_OU']);
		$this->p("referer",$_SERVER ['HTTP_REFERER']);
		$this->p("time",gmstrftime("%b %d %Y %H:%M:%S")." GMT");
		$this->p("apache_admin",$_SERVER['SERVER_ADMIN']);
		$this->xm("</generalinfo>");

		// get medcommmons parameters about this instance of central
		$this->xm("<mcinfo>");
		$this->p("sw_version",$GLOBALS["SW_Version"]);
		$this->p("sw_revision",$GLOBALS["SW_Revision"]);
		$this->p("db_connection",$GLOBALS["DB_Connection"]);
		$this->p("db_database",$GLOBALS["DB_Database"]);
		$this->xm("</mcinfo>");

		// get record counts from interesting tables
		
		$this->xm("<spinfo>");
	
		$this->z("document","documentcount");
		$this->z("tracking_number","trackingcount");
			$this->z("node","nodecount");
		$this->z("forensic_log","logcount");

		$this->xm("</spinfo>");
		$count++;
	
		//
		// return outputs
		//
		$this->xmfield("status","ok");
	}
}



//main

$x = new probestatus();
$x->handlews("sp_probe_response","Sp");



?>