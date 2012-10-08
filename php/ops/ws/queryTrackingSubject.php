<?PHP
require_once "wslibextio.inc.php";
// see spec at ../ws/wsspec.html
class queryTrackingSubjectWs extends dbextiorestws {

	function z($l,$v){	if ($l[$v]!="") $this->xm ("<$v>".$l[$v]."</$v>");}

	function xmlbody(){

		$tracking=$this->cleanreq('tracking');
		$hpin=$this->cleanreq('hpin');

		if ($tracking=="") $this->xmlend("tracking must be non-null");

		$query = "SELECT * from emailstatus WHERE (arga='$tracking')";
		$result = mysql_query ($query) or $this->xmlend("can not query table emailstatus - ".mysql_error());
		if ($result===false) {$this->xmlend("failure"); exit;}
		$count=0;
		while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {

			$this->xm("<entry >");
			$this->xm("<tracking>".$l['arga']."</tracking>"); // preserve compatibility
			$this->xm("<subject>".$l['argc']."</subject>"); // preserve compatibility
			$this->xm("</entry>");
			$count++;
		}

		if ($count==0) $this->xmlend("failure"); else $this->xmlend("success");
	}
}
//main

$x = new queryTrackingSubjectWs();
$x->handlews("queryTrackingSubject_Response");


?>
