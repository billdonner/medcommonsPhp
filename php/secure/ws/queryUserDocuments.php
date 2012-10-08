<?PHP
require_once "../ws/securewslibdb.inc.php";
require_once "utils.inc.php";

// see spec at ../ws/wsspec.html
class queryUserDocumentsWs extends dbrestws {
/// need to return guids
	function z($l,$v){	if ($l[$v]!="") $this->xm ("<$v>".$l[$v]."</$v>");}
	function xmlbody(){

		$mcid=$this->cleanreq('mcid');
    $query = "SELECT rights.account_id,rights.document_id,rights.rights_id,document.guid,document.storage_account_id
              from rights INNER JOIN document
              on (document.id = rights.document_id)
              WHERE (account_id='$mcid') and active_status = 'Active'";
		$result = $this->dbexec($query,"can not select from table user - ");

    if($result===false)  {
      return ($this->xmfield("user", $result));
    }

		$count=0;

		while ($l = mysql_fetch_row($result)) {

			$this->xm("<entry >");
			$this->xm("<mcid>".$l[0/*'account_id'*/]."</mcid>"); // preserve compatibility
			$this->xm("<docid>".$l[1/*'document_id'*/]."</docid>"); // preserve compatibility
			$this->xm("<rightsid>".$l[2/*'rights_id'*/]."</rightsid>"); // preserve compatibility
			$this->xm("<guid>".$l[3/*'accepted_status'*/]."</guid>"); // preserve compatibility
			$this->xm("<storageId>".$l[4/*'storage_account_id'*/]."</storageId>"); 
			$this->xm("</entry>\n");
			$count++;
		}

    // ssadedin: why would it be a failure to have no documents?  this causes
    // random failures in some tests if the target account is freshly minted.
		// if ($count==0) $this->xmlend("failure"); else $this->xmlend("success");
    $this->xmlend("success");
	}
}
//main
$x = new queryUserDocumentsWs();
$x->handlews("response_UserInfo");

?>
