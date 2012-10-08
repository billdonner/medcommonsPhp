<?PHP
require_once "../ws/securewslibdb.inc.php";
// see spec at ../ws/wsspec.html
class queryUserInfoWs extends dbrestws {

	function z($l,$v){	if ($l[$v]!="") $this->xm ("<$v>".$l[$v]."</$v>");}

	function xmlbody(){

		$username=$this->cleanreq('username');
		$hpass=$this->cleanreq('hpass');

		if ($username=="") $this->xmlend("username must be non-null");

		$query = "SELECT * from user WHERE (email_address='$username') and (hpass='$hpass')";
		$result = mysql_query ($query) or $this->xmlend("can not query table User - ".mysql_error());
		if ($result===false) {$this->xmlend("failure"); exit;}
		$count=0;
		while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {

			$this->xm("<entry >");
			$this->xm("<email>".$l['email_address']."</email>"); // preserve compatibility
			$this->xm("<mcid>".$l['medcommons_user_id']."</mcid>"); // preserve compatibility

			$this->z($l,'hpass');$this->z($l,'name');
			$this->z($l,'gateway1'); $this->z($l,'gateway2');

			$this->z($l,'serial'); $this->z($l,'identity_provider');
			//		z($l,'cert_url');
			//		z($l,'cert_checked');
			$this->z($l,'status');
			$this->xm("<username>".$l['name']."</username>"); // preserve compatibility

			$this->xm("</entry>");
			$count++;
		}

		if ($count==0) $this->xmlend("failure"); else $this->xmlend("success");
	}
}
//main

$x = new queryUserInfoWs();
$x->handlews("getUserInfo_Response");


?>
