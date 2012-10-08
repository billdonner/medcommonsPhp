<?PHP
require_once "version.inc.php";
require "../ws/wslibdb.inc.php";
/*

returns xml status about the central system

*/

class statusws extends dbrestws {

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
		// get general info about this instance of central
		$this->xm("<generalinfo>");
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
		$this->p("default_repository", $GLOBALS['Default_Repository']);
		$this->xm("</mcinfo>");

		// get record counts from interesting tables
		$this->xm("<tableinfo>");
		//		z("hipaa");
		//	z("hipaa_trace");
		$this->z("user");
		// moved these tables to another database, really should reconnect to the other db to get them

		//		z("faxstatus");
		//		z("ccstatus");
		//		z("emailstatus");

		$this->xm("</tableinfo>");
		$count++;
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