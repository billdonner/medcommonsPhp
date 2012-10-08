<?
require_once "dbparamsidentity.inc.php";
require_once "wslib.inc.php";
require_once "settings.php";
require_once "JSON.php";

abstract class dbrestws extends restws {

	private $nodeid;

		// add connect/disconnect to sql database
		function dbexec($query,$errstr){
	        error_log("$query");
			$result = mysql_query ($query) or $this->xmlend("$errstr".mysql_error());
			if ($result=="") {$this->xmlend("failure"); if(!$this->test) exit;}
			return $result;
		}
		
        function dbconnect()
        {
            global $IDENTITY_HOST,$IDENTITY_DB,$IDENTITY_USER,$IDENTITY_PASS;
            mysql_connect($IDENTITY_HOST, $IDENTITY_USER, $IDENTITY_PASS) or $this->xmlend ("can not connect to mysql");
            mysql_select_db($IDENTITY_DB) or $this->xmlend ("can not connect to database= $IDENTITY_DB");
        }
		
		function dbdisconnect()
		{
			mysql_close();
		}

		function handlews($servicetag)
		{
			$this->set_servicetag($servicetag);
			// do standard processing for all web services
			$this->xmltop();
			$this->dbconnect();

			// the xmlbody routine is always overriden
			$this->xmlbody();
			$this->xmlend("success");
			$this->dbdisconnect();
		}
}

/**
 * An extension of dbrestws to make it render JSON instead of
 * XML.  
 *
 * In addition to other services provide by dbrestws, child classes
 * can simply throw exceptions to handle errors, or they can just:
 *
 *   return $this->error("some message")
 */
abstract class jsonrestws extends dbrestws {
  /**
   * Convenience method - sets error message and returns failure status.
   */
  function error($msg) {
    $this->message = $msg;
    return false;
  }

  /**
   * Override since th default writes XML
   */
  function dbexec($query,$errstr){
    error_log("$query");
    $result = mysql_query ($query);
    if(!$result)
      return $this->error("$errstr".mysql_error());
    else
      return $result;
  }

  /**
   * Dummy method
   */
  function xmlbody() {
    return $this->jsonbody();
  }

  /**
   * Handler to execute web service
   */
  function handlews($servicetag) {
			$this->set_servicetag($servicetag);
			$this->dbconnect();

      try {
        $result = $this->jsonbody();
      }
      catch(Exception $e) {
        $this->error($e->getMessage());
        $result = false;
      }

      // Ensure the content type indicates javascript
      header ("Content-type: text/javascript");
      $json = new Services_JSON();
      $out = new stdClass;
      if($result !== false) {
        // If user has set the $result variable on the class, use that
        // as the whole response rather than the returned value.
        // this allows child class to override the whole response if 
        // desired.
        if(isset($this->result))
          $out = $this->result;
        else {
          $out->status = "ok";
          $out->result = $result;
        }
      }
      else {
        $out->status = "failed";
        if(isset($this->message)) {
          $out->message = $this->message;
        }
      }
      echo $json->encode($out);
			$this->dbdisconnect();
  }
}
?>
