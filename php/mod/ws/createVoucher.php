<?php
/**
 * Web service for creating vouchers
 */
require_once '../db.inc.php';
require_once 'utils.inc.php';
require_once 'mc.inc.php';
require_once 'JSON.php';
require_once '../modpay.inc.php';

$json = new Services_JSON();

function handle_service() {
  $result = new stdClass;
  try {
    $db = DB::get();
    $firstName = trim(req('fn'));
    if(!$firstName || (strlen($firstName) <= 1))
      throw new Exception("Invalid or missing value for parameter 'fn': $firstName");

    $lastName = trim(req('ln'));
    if(!$lastName || (strlen($lastName) <= 1))
      throw new Exception("Invalid or missing value for parameter 'ln'");
      
    $sex = trim(req('sex'));
    if($sex && (in_array($sex,array("Male","Female"))))
      throw new Exception("Invalid value for parameter 'sex'");

    $accid = req('accid');
    if(!is_valid_mcid($accid))
      throw new Exception("Invalid or missing value for parameter 'accid'");

    $auth = req('auth');
    if(preg_match('/^[a-f0-9]{40}$/',$auth) !== 1)
      throw new Exception("Invalid or missing value for parameter 'auth'");

    $svcname = trim(req('svc'));
    if(!$svcname || (strlen($svcname) < 1))
      throw new Exception("Invalid or missing value for parameter 'svc'");
      
    $storageId = req('storageId');
    if($storageId && !is_valid_mcid($storageId,true))
      throw new Exception("Invalid value for parameter 'storageId'");

    // Find the service
    $svc = get_dicom_service($accid, $svcname);

    // Get the group / practice of the user
    $practice = get_practice($accid);
    if($practice === false)
      throw new Exception("Account $accid is not a member of a group.  Only group members can create vouchers.");

    if($storageId) 
        $patient = get_existing_patient($auth, $storageId);
    else 
	    $patient = create_patient($firstName, $lastName, $practice, $auth, $practice->accid, $sex);
    
    $expdate = calculate_voucher_expiry_date($svc->duration);
    $healthUrl = $GLOBALS['appliance'].$patient->patientMedCommonsId;
    $server =substr($GLOBALS['appliance_gw'],8);
    $voucherid = generate_voucher_id($server);
    $otp = rand(10001,99999);
    $status = 'issued';
    
    // Check the type of voucher - if the patient is issueing the voucher
    // for themselves then make it be complete automatically
    if($svc->accid == $patient->patientMedCommonsId) {
        $status = 'completed';
    }
     
    // Create voucher
    $db->execute("insert into modcoupons (couponum,svcnum,patientname,patientemail,addinfo,
                  patientprice,expirationdate,hurl,status,otp,mcid,auth,secret,accesstoken,paytype,
                  paytid,timeofexpiry,voucherid,issuetime,fcredits,dcredits,asize,duration)
                  values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                 array(NULL,$svc->svcnum,"$firstName $lastName",'','',0,$expdate,$healthUrl,$status,
                       $otp,$patient->patientMedCommonsId, $patient->auth,$patient->secret,'','',
                       '',0,$voucherid,time(),0,0,0,$svc->duration));

    $result->status = "ok";
    $result->voucherid = $voucherid;
    $result->otp = $otp;
    $result->patientMedCommonsId = $patient->patientMedCommonsId;
    $result->patientAuth = $patient->auth;
  }
  catch(Exception $e) {
    $result->status = "failed";
    $result->error = $e->getMessage();
  }
  return $result;
}

/**
 * Query for the active practice associated with the specified accountid
 * 
 * @param String $accid   account id to query for
 * @return object representing practice database row
 */
function get_practice($accid) {
  $db = DB::get();
  return $db->first_row("SELECT q.*,i.accid from practice q, groupmembers p, users u, groupinstances i
              where p.memberaccid=? 
              and q.providergroupid=i.groupinstanceid  
              and i.parentid>0 
              and p.groupinstanceid= i.groupinstanceid 
              and p.memberaccid=u.mcid
              and u.active_group_accid = i.accid",array($accid));
}

/**
 * Creates a patient on the gateway configured for this MOD instance
 * and returns an object with attributes describing the details.

 * @param String $fn      first name of patient
 * @param String $ln      last name of patient
 * @param String $practice practice of creator 
 * @param String $auth     parent auth to grant consents to
 * @return Object {patientMedCommonsId, auth, secret}
 */
function create_patient($fn, $ln, $practice, $auth, $sponsorAccId, $sex = null) {

  // Make URL to new patient service
  $remoteurl = $GLOBALS['appliance_gw'].
               "NewPatient.action?familyName=$ln&givenName=$fn".
               "&auth=".urlencode($auth)."&sponsorAccountId=".urlencode($sponsorAccId);

  $remoteurl .= "&registryUrl=".urlencode($practice->practiceRlsUrl);
  
  if($sex)
    $remoteurl .= "&sex=".urlencode($sex);

  $response = get_url($remoteurl);
  $json = new Services_JSON();
  $result = $json->decode($response);
  if(!$result)
    throw new Exception("Unable decode JSON returned from URL ".$remoteurl.": ".$response);

  return $result;
}

/**
 * Checks consents to ensure given auth is allowed to
 * access given storage id and if so returns a 'mock'
 * patient object that emulates the object returned
 * by the patient creation service.
 *
 * @param $auth         auth of user accessing patient
 * @param $storageId    storage id to create patient for
 */
function get_existing_patient($auth, $storageId) {
    
	dbg("Creating voucher for existing patient $storageId");
	        
	// Check consent
	$permissions = getPermissions($auth, $storageId);
	if((strpos($permissions, "R")===FALSE)  ||  (strpos($permissions, "W")===FALSE)) 
	    throw new Exception("Provided authentication token does not  have RW permission to provided storage account $storageId");
	
	// Make patient object based on existing user
	$p = new stdClass;
	$p->patientMedCommonsId = $storageId;
	$p->auth = $auth; // not sure if this is right - we should really be making a new auth?
	$p->secret = '';
    return $p;
}

/**
 * Searches for a unique service matching the provided name.  Throws
 * an exception if multiple services are found.  If no service
 * is found, creates a default service with the requested name
 * for the user.
 * 
 * @param String $accid     owner of service
 * @param String $svcname   name of service 
 * @return modservices database record object
 */
function get_dicom_service($accid, $svcname) {
  $db = DB::get();
  $svcs = $db->query("select * from modservices where servicename = ? and accid = ?",
                     array($svcname,$accid));

  if(count($svcs) === 0) { 
      
    dbg("Creating new service named $svcname due to no existing service of that name being found");
    
    $db->execute("insert into modservices (svcnum,accid,servicename,servicedescription,serviceemail,supportphone,duration,time,voucherprinthtml,voucherdisplayhtml,consentblob,asize,suggestedprice,servicelogo,createcount,utilizedcount,cashreceived,cashpaidout,fcredits,dcredits)
                  values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                 array(NULL,$accid,$svcname,$svcname,'','',1,time(),'','','',1,0,'',0,0,0,0,0,1));
                 
    return get_dicom_service($accid,$svcname);
  }

  if(count($svcs) > 1)
    throw new Exception("Multiple services matching name '$svcname' found for account $accid. ".
                        "Please ensure the service name identifies a unique service");
  return $svcs[0];
}

//////////////////////////  Test Code ////////////////////////
if(isset($_GET['test_create_voucher'])) {
    require_once "../../acct/testdata_ids.inc.php";
    global $doctorId;
    $_GET['fn']="Unit";
    $_GET['ln']="Test";
    $_GET['accid']=$doctorId;
    $_GET['auth']='d5d813d968b8ae64088b37be1d1ff82addfbab41';
    $_GET['svc']='DICOM Upload';
    $db = DB::get();

    // Delete all services from doctor
    $db->execute("delete m.* from modcoupons m, modservices s
                  where s.accid='$doctorId' and m.svcnum = s.svcnum");

    $db->execute("delete from modservices where accid = ?",array($doctorId));

    // Add a service
    $db->execute("insert into modservices (svcnum,accid,servicename,servicedescription,serviceemail,supportphone,duration,time,voucherprinthtml,voucherdisplayhtml,consentblob,asize,suggestedprice,servicelogo,createcount,utilizedcount,cashreceived,cashpaidout,fcredits,dcredits)
                  values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                 array(NULL,$doctorId,'DICOM Upload','Unit Test DICOM Upload','','',0,0,'','','',0,0,'',0,0,0,0,0,0));

    $result = handle_service();

    echo "Basic voucher service returned ".$json->encode($result);
    
    
    // Try getting existing patient with storage id 
    $p = get_existing_patient($user1Auth, $user1Id);
    
    echo "<p>get_existing_patient returns - ".$p->patientMedCommonsId."</p>";
    
    exit;
}
//////////////////////////  End Test Code ////////////////////////

// Main entry point
echo $json->encode(handle_service());
?>
