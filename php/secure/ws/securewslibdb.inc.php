<?
require_once "settings.php";
require_once "../securelib.inc.php";
require_once "wslib.inc.php";
require_once "JSON.php";

abstract class dbrestws extends restws {

	protected $nodeid;

	function generate_tracking() {
    global $TRACKING_URL;

	  /*
	   * TTW April 30, 2007
	   * Use the mc_locals soap/rest server to worry about global
	   * tracking number allocation.
	   *
	   * There must be a mc_locals service running at the IP address
	   * 'mcid.internal' (put the name in the /etc/hosts file)
	   */
    $trackingUrl = isset($TRACKING_URL) ? $TRACKING_URL : 'http://mcid.internal:1080/tracking_number'; // default
	  $tn = file_get_contents($trackingUrl);
	  mysql_query("INSERT INTO tracking_number (tracking_number, encrypted_pin) ".
		      " VALUES('$tn', '999999999999')");
	  return $tn;
	}

	function gethostarg(){
		$node= ($this->cleanhost($this->cleanreq('host'))); // get host name as supplied or use own ip address
    $this->nodeid =$node->node_id;
  }

		function getnodeid()
		{
			return $this->nodeid;
		}

		function muship($ip) {
			$mushedip = ereg_replace("\.", "","$ip");
			if(strlen($mushedip)>10) {
				$mushedip = substr($mushedip, strlen($mushedip)-9,10);
			}
			return $mushedip;
		}

    function findnodebykey($nodeKey) {
        $result = $this->dbexec("select * from node where client_key = '".mysql_real_escape_string($nodeKey)."'", " - failed to select from node table");
        if($result) {
          $node =  mysql_fetch_object($result);
          return $node;
        }
        return false;
    }

		function findnodebyip ($ip)
		{
			// see comment in registerNode.php for why we mush the ip
			$mushedip = $this->muship($ip);
			//error_log("Looking for mushed ip $mushedip");  // commented out by wld 06 sep 06 was compaining
			$select="SELECT * FROM node WHERE (fixed_ip= '$mushedip')";
			$result = $this->dbexec($select,"can not select from table node - ");
			$node = mysql_fetch_object($result);
      if ($node ==FALSE) {
        // Hack - we need to figure out right way to do this
        // but for now, just return the first node in the table
        $node_result = $this->dbexec("select * from node", "unable to select from node table -");
        $node = mysql_fetch_object($node_result);
        if($node)
          return $node;

        // No nodes at all!
        $this->xmlend("internal failure to find record in node table");
      }
			//		echo "ip: $node->fixed_ip host:$node->hostname $select";
			return $node;
		}


		function findnodebyhost ($host)
		{
			$select="SELECT * FROM node WHERE (hostname= '$host')";
			$result = $this->dbexec($select,"can not select from table node - ");
			$node = mysql_fetch_object($result);
			if ($node ==FALSE) $this->xmlend("internal failure to find record in node table");
			//		echo "ip: $node->fixed_ip host:$node->hostname $select";

			return $node;

		}

   /**
    * Legacy wrapper - see securelib.inc.php
    *
    * Note: throws Exception for failures.
    */
   function get_authorized_rights($auth, $toAccount) {
     return get_rights($auth, $toAccount);
   }


   /**
    * Attempts to resolve a tracking number from the given document id
    */
   function resolveDocumentTrackingReference($docid) {
     $result = $this->dbexec("select t.* 
                       from tracking_number t
                       where t.doc_id = $docid"," - failed to select from tracking_number,rights");
     if($result) {
       // If the tracking number is ambiguous, return false
       if(mysql_num_rows($result) == 1) 
         return mysql_fetch_object($result);
       else
         return false;
     }
     else {
       return false;
     }
   }

		/*

		--
		-- Table structure for table `document`
		--

		CREATE TABLE `document` (
		`id` bigint(20) unsigned NOT NULL auto_increment,
		`guid` varchar(64) NOT NULL default '',
		`creation_time` timestamp(14) NOT NULL,
		`rights_time` timestamp(14) NOT NULL,
		`encrypted_hash` varchar(64) default NULL,
		`attributions` varchar(255) default NULL,
		PRIMARY KEY  (`id`),
		KEY `guid` (`guid`)
		) TYPE=MyISAM AUTO_INCREMENT=1 ;

		--
		-- Table structure for table `document_location`
		--

		CREATE TABLE `document_location` (
		`id` bigint(20) NOT NULL auto_increment,
		`document_id` varchar(64) NOT NULL default '0',
		`node_node_id` bigint(20) NOT NULL default '0',
		`integrity_check` timestamp(14) NOT NULL,
		`integrity_status` int(10) unsigned default NULL,
		`encrypted_key` varchar(64) default NULL,
		`copy_number` int(10) unsigned default NULL,
		PRIMARY KEY  (`id`),
		UNIQUE KEY `docid` (`document_id`,`node_node_id`)
		) TYPE=MyISAM AUTO_INCREMENT=1 ;

		*/
		function finddocument($storageId, $guid) //wld 11/22/05 - get document id given a guid
		{
			if($storageId!="") {
				$select = "SELECT id FROM document WHERE (guid='$guid') and (storage_account_id = '$storageId')";
			}
			else {
				/* Uncomment this to ensure hard failure when storageId not provided
				echo "

				BLANK STORAGE ID - PLEASE FIX THIS BUG.

				";
				exit;
				*/
				$select = "SELECT id FROM document WHERE (guid='$guid')";
			}
			$result = $this->dbexec($select,"can not select from document table by guid $guid");

			$dobj = mysql_fetch_object($result);
			if ($dobj===FALSE) return ""; // there was a typo here, said dnobj===false, was always broken wld 07sep06
			return $dobj->id;
		}
		/**
		 * Returns the decryption key for the specified document.
		 * Note that the guid argument is used only for the error message to avoid
		 * reporting the docid back to the user.
		 */
		function finddocumentDecryptionKey($guid, $docid, $node) //wld 11/22/05 - get document id given a guid
		{
			$select="SELECT encrypted_key FROM document_location WHERE (document_id = '$docid') and (node_node_id ='$node')";
			$result = $this->dbexec($select,"No row in table document_location for $guid, $node)" );

			$dobj = mysql_fetch_object($result);

			if ($dobj===FALSE) return "";
			return $dobj->encrypted_key;
		}
		/*
		function finddocumentlocation($guid, $node)
		{
		$docid = $this->finddocument($guid);
		$select = "SELECT id FROM document_location WHERE (guid='$guid')";
		$result = $this->dbexec($select,"can not select from document table by guid $guid");

		$dobj = mysql_fetch_object($result);
		if (dnobj===FALSE) return "";
		return $dobj->id;
		}
		*/
		function updatedocumentlocation($docid, $nodeid, $ekey, $intstatus) //wld 11/22/05
		{

			$insert="UPDATE document_location SET
			encrypted_key = '$ekey', integrity_status = '$intstatus', integrity_check = NOW()
			WHERE (document_id='$docid') AND (node_node_id = '$nodeid')";

			$this->dbexec($insert,"can not update into table document_location - ");
			return "ok";
		}

		function deletedocumentlocation($docid, $nodeid)
		{

			$delete="DELETE from document_location
			WHERE (document_id='$docid') AND (node_node_id = '$nodeid')";

			$this->dbexec($delete,"can not delete from table document_location - ");
			return "ok";
		}


		function adddocumentlocation($docid, $nodeid, $ekey, $intstatus) // wld 11/22/05
		{

			$insert="INSERT INTO document_location (document_id,node_node_id,copy_number,
			encrypted_key, integrity_check,integrity_status) ".
			" VALUES('$docid','$nodeid','1','$ekey', NOW(),'$intstatus')";
			$this->dbexec($insert,"can not insert into table document_location - ");
			//
			//pick up the id we just created
			return mysql_insert_id();
		}

		/**
     * Return the first document location accessible for the given guid and storage id
     * with the given rights
     */
		function resolveGuid($storageId, $guid, $auth="") {
			return resolve_guid($storageId, $guid, $auth);
		}

		/**
     * Return the first document location accessible for the given tracking number and PIN
     */
		function resolveTracking($tracking, $pinHash=null, $auth=null) {
      return resolve_tracking($tracking,$pinHash, $auth);
		}

		function cleanhost($h = "")
		{
      // If there is a node key provided, look up node using that
      $nodeKey = req('node_key');
      if($nodeKey) {
        $node = $this->findnodebykey($nodeKey);
        if($node) {
          dbg("Found node {$node->node_id} using node_key $nodeKey");
          return $node;
        }
      }

			if($h=="") {
				//if no host name then get the ip address from the request
				$ip = $_SERVER['REMOTE_ADDR'];
				$node = $this->findnodebyip($ip);
			}
			else {
				//host supplied
				$node = $this->findnodebyhost($h);
				return $node; //use the hostname in there
			}


      // ssadedin: disabling this check.  It fails on multi-homed
      // hosts.
			// if(($node!=false)&& ($node->fixed_ip==$this->muship($ip)))
			//{
				// ok, found
			if($node!=false)
				return $node;
			//}

			// if here, we failed
			$this->xmlend("bad incoming ip");
		}

		// add connect/disconnect to sql database
		function dbexec($query,$errstr){
			//$query = "SELECT * from user WHERE (email_address='$username') and (hpass='$hpass')";
			//error_log("$query");
			$result = mysql_query ($query) or $this->xmlend("$errstr".mysql_error());
			if ($result=="") {
				error_log("$query");
				$this->xmlend("failure");
				exit;
			}
			return $result;
		}
		
		function dbconnect()
		{
			global $CENTRAL_HOST,$CENTRAL_DB,$CENTRAL_USER,$CENTRAL_PASS;
			mysql_connect($CENTRAL_HOST, $CENTRAL_USER, $CENTRAL_PASS) or $this->xmlend ("can not connect to mysql");
			mysql_select_db($CENTRAL_DB) or $this->xmlend ("can not connect to database $CENTRAL_DB");
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
    //error_log("$query");
    $result = mysql_query ($query);
    if(!$result){
      error_log("$query");
      error_log("$errstr");
      return $this->error("$errstr".mysql_error());
    } else
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
