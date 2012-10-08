<?
/**
 * Accepts form describing order details and creates a patient to become the target of the order.
 */
require_once "utils.inc.php";
require_once "template.inc.php";
require_once "alib.inc.php";
require_once "login.inc.php";
require_once "JSON.php";
require_once "mc.inc.php";
require_once "email.inc.php";

nocache();

global $Secure_Url;
global $acApplianceMode;

$uploadGroupName = "MedCommons DICOM Upload";

$gwUrl = allocate_gateway(null);
$startDDLUrl =  $gwUrl."/ddl/start";
$json = new Services_JSON();
$result = new stdClass;
    
try {
    
    $groupAccountId = req('groupAccountId');
    if($groupAccountId && !is_valid_mcid($groupAccountId, true))
        throw new Exception("Supplied group account id $groupAccountId is not valid");
       
    // Call the gateway to create the patient
    if($groupAccountId)
	    $jsonResult = get_url($gwUrl."/NewPatient.action?sponsorAccountId={$groupAccountId}");
    else
	    $jsonResult = get_url($gwUrl."/NewPatient.action");
    
    dbg("Got json result $jsonResult from new patient call");
    
    $patient = $json->decode($jsonResult);
    if($patient->status !== "ok")
      throw new Exception("Unable to create patient for DICOM upload: ".$patient->error);
    
    // Give the created account 20 dicom credits (enough, hopefully, to handle 
    // a large patient upload).  May need better solution in future -
    // disable billing on a per-account basis?
    adjust_billing_counters($patient->patientMedCommonsId, 0,20,0);
    
    // If order details were entered then add them into the CCR
    if(strlen(trim(req("procedure").req("procedureType").req("accessionNumber").req("history"))) > 0) {
	    // Now merge in the supplied information
	    $ccr = template("plan_of_care_order.tpl.php")
	            ->set("patient",$patient)
	            ->set("procedure", req("procedure"))
	            ->set("procedureType", req("procedureType"))
	            ->set("accessionNumber", req("accessionNumber"))
	            ->set("history", req("history"))
	            ->fetch();
    }
    else {
 	    $ccr = template("plan_of_care_order.tpl.php")
	            ->set("patient",$patient)
	            ->fetch();
    }
	   
    dbg("Sending CCR to patient account: ".$ccr);
    
    $desc = "Added Order Details";
    $putUrl = $gwUrl."/put/{$patient->patientMedCommonsId}?auth={$patient->auth}&description=".urlencode($desc);
    $output = post_url($putUrl, "ccr=".urlencode($ccr));
        
    $putResult = $json->decode($output);
    if(!$putResult)
        throw new Exception("Unparseable result returned from CCR PUT call: ".$output);
        
    if($putResult->status != "ok")
        throw new Exception("CCR Put failed: ".$putResult->error);
    
    
    $t = new stdClass;
    $patient->accessAuth = get_authentication_token(array("0000000000000000",$patient->patientMedCommonsId), $t);
    
    $orderReference = req('callers_order_reference');
    if($orderReference) {
        if(!$groupAccountId) 
            throw new Exception("Group account id must be specified if order associated with DICOM upload");
       
        dbg("Updating order $orderReference to status MATCHED");
        pdo_execute("update dicom_order set ddl_status = 'DDL_ORDER_MATCHED', mcid = ?, group_account_id = ? where callers_order_reference = ?",
                     array($patient->patientMedCommonsId,$groupAccountId, $orderReference));
    }
    
    send_notification_email($patient->patientMedCommonsId);
        
    $result->authToken = $patient->auth;
    $result->gwUrl = $gwUrl;
    $result->accid = $patient->patientMedCommonsId;
    $result->healthUrl = $Secure_Url . "/" . $patient->patientMedCommonsId;
    $result->status = "ok";
    $result->patient = $patient;
}
catch(ValidationFailure $v) {
    $result->status = "validation failed";
    $result->error = $v->getMessage();
    $result->errors = $ex->errors;
}
catch(Exception $ex) {
    $result->status = "failed";
    $result->error = $ex->getMessage();
}
echo $json->encode($result);

function send_notification_email($accid) {
    
    global $Secure_Url;
    global $acApplianceMode;
    
    // No emails for development mode!
    if(($acApplianceMode == '0') || isset($GLOBALS['NO_DICOM_UPLOAD_EMAILS']))
        return;
    
    $clientIpAddress = $_SERVER['REMOTE_ADDR'];
    $clientHostName = gethostbyaddr($clientIpAddress);
    
    $hurl = $Secure_Url."/".$accid;
    
	$plain_text = "DICOM Upload to new patient account $accid";
	$html = "<html><p><b>DICOM Upload to New Patient Account $accid</b></p>
	          <table>
	            <tr><th>HealthURL:</th><td><a href='$hurl'>$hurl</a></td></tr>
	            <tr><th>Client Ip:</th><td>".$clientIpAddress."</td></tr>
	            <tr><th>Client Hostname:</th><td>".$clientHostName."</td></tr>
	            <tr><th>Procedure:</th><td>".htmlentities(req("procedure"))."</td></tr>
	            <tr><th>Procedure Type:</th><td>".htmlentities(req("procedureType"))."</td></tr>
	            <tr><th>Accession Number:</th><td>".htmlentities(req("accessionNumber"))."</td></tr>
	            <tr><th>History:</th><td>".htmlentities(req("history"))."</td></tr>
	          </table>
	          <p>Please visit the <a href='".gpath('Secure_Url')."/console/'>console</a> for more information about this account.</p>
	          </html>";

	send_mc_email("cmo@medcommons.net", "DICOM Upload Notification",
                 $plain_text,
                 "$html",
                 array());
    
}
?>
