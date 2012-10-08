<?
require_once "../dbparams.inc.php";
require_once "wslib.inc.php";

abstract class dbrestws extends restws {

	private $nodeid;

	function generate_tracking() {

		// reworked on 102105 to allow pre-allocation in database
		// try to find a free slot a limited number of times, and clean it out

		$good = false;
		$i=0;
		while (($i<5) && ($good===false))
		{
			$tn = rand(100000,999999).rand(100000,999999);
			//$tn = '914900114030';
			$good = true;
			$insert="INSERT INTO tracking_number (tracking_number,rights_id,encrypted_pin) ".
			"VALUES('$tn','','999999999999')";
			$result = mysql_query($insert) or $good = false;
			$i++;
		}
		if ($good===false) return '';
		return $tn;

	}

	function gethostarg(){
		$node= ($this->cleanhost($this->cleanreq('host'))); // get host name as supplied or use own ip address
		$this->nodeid =$node->node_id;}

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

		function findnodebyip ($ip)
		{
			// see comment in registerNode.php for why we mush the ip
			$mushedip = $this->muship($ip);
			error_log("Looking for mushed ip $mushedip");
			$select="SELECT * FROM node WHERE (fixed_ip= '$mushedip')";
			$result = $this->dbexec($select,"can not select from table node - ");
			$node = mysql_fetch_object($result);
			if ($node ==FALSE) $this->xmlend("internal failure to find record in node table");
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
		function finddocument($guid) //wld 11/22/05 - get document id given a guid
		{
			$select = "SELECT id FROM document WHERE (guid='$guid')";
			$result = $this->dbexec($select,"can not select from document table by guid $guid");

			$dobj = mysql_fetch_object($result);
			if (dnobj===FALSE) return "";
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
			
			if (dnobj===FALSE) return "";
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
		function cleanhost($h)
		{
			if ($h=="")
			{
				//if no host name then get the ip address from the request
				$ip = $_SERVER['REMOTE_ADDR'];
				$node = $this->findnodebyip($ip);
			}
			else
			{
				//host supplied
				$node = $this->findnodebyhost($h);
				return $node; //use the hostname in there
			}


			if(($node!=false)&& ($node->fixed_ip==$this->muship($ip)))
			{
				// ok, found
				return $node;
			}

			// if here, we failed
			$this->xmlend("bad incoming ip");
		}


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
