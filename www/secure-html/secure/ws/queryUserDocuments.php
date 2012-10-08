<?PHP
require_once "../ws/wslibdb.inc.php";
// see spec at ../ws/wsspec.html
class queryUserDocumentsWs extends dbrestws {
/// need to return guids
	function z($l,$v){	if ($l[$v]!="") $this->xm ("<$v>".$l[$v]."</$v>");}
	function xmlbody(){

		$mcid=$this->cleanreq('mcid');
		$query = "SELECT rights.account_id,rights.document_id,rights.rights_id,rights.accepted_status,document.guid  from rights INNER JOIN document
							ON (document.id = rights.document_id)  WHERE (account_id='$mcid')";
		$result = $this->dbexec($query,"can not select from table user - ");
		if ($result===false) return ($this->xmfield("user", $result));
		$count=0;

		while ($l = mysql_fetch_row($result)) {

			$this->xm("<entry >");
			$this->xm("<mcid>".$l[0/*'account_id'*/]."</mcid>"); // preserve compatibility
			$this->xm("<docid>".$l[1/*'document_id'*/]."</docid>"); // preserve compatibility
			$this->xm("<rightsid>".$l[2/*'rights_id'*/]."</rightsid>"); // preserve compatibility
			$this->xm("<accepted_status>".$l[3/*'accepted_status'*/]."</accepted_status>"); // preserve compatibility
			$this->xm("<guid>".$l[4/*'accepted_status'*/]."</guid>"); // preserve compatibility

			$this->xm("</entry>");
			$count++;
		}

		if ($count==0) $this->xmlend("failure"); else $this->xmlend("success");
	}
}
//main
$x = new queryUserDocumentsWs();
$x->handlews("response_UserInfo");

?>
