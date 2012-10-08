<?
require_once "dbparamsidentity.inc.php";
require_once "wslib.inc.php";

abstract class dbrestws extends restws {

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
