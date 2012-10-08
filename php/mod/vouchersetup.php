<?php
require_once 'common.php';
require_once 'OAuth.php';
require_once 'mc_oauth_client.php';
require_once "modpay.inc.php";
require_once "mc.inc.php";
require_once "db.inc.php";
require_once "template.inc.php";
require_once "utils.inc.php";
require_once "JSON.php";

 
function make_hurl ($fn,$ln,$sex,$dob,$parent_mcid, $parent_auth, $consents, $purpose)
{
	dbg("making new patient account with parent auth = $parent_auth");
  try {
      // if none supplied, then make one
      $remoteurl = $GLOBALS['appliance_gw']."NewPatient.action?familyName=$ln&givenName=$fn&dateOfBirth=$dob".
        "&sex=$sex&auth=".urlencode($parent_auth)."&sponsorAccountId=".urlencode($parent_mcid).
        "&purpose=".urlencode($purpose); 

      // If the parent account has an RLS then use that
      $db = DB::get();
      $rls = $db->first_row("select p.practiceRlsUrl as rlsUrl
                             from practice p, users u, groupmembers m
                             where m.memberaccid = u.mcid
                             and m.groupinstanceid = p.providergroupid
                             and u.mcid = ?", array($parent_mcid));

      if($rls) {
        dbg("user $parent_mcid has rls configured {$rls->rlsUrl}");
        $remoteurl .= "&registryUrl=".urlencode($rls->rlsUrl);
      }

      if($consents)
        $remoteurl .= "&consents=".urlencode(implode(",",$consents));

      // consumer token when creating patient
      $file = get_url($remoteurl);
      $json = new Services_JSON();
      $result = $json->decode($file);
      if(!$result)
        throw new Exception("Unable decode JSON returned from URL ".$remoteurl.": ".$file);

      if($result->status != "ok")
        throw new Exception("Bad status '".$result->status."' error='".$result->error."' returned from JSON call ".$remoteurl);

      $mcid = $result->patientMedCommonsId;
      $auth = $result->auth;
      $secret = $result->secret;
      $healthurl = $GLOBALS['appliance'].$mcid;
    }
    catch(Exception $ex) {
       error_page("Failed to create new patient.", $ex);
    }

    dbg("created healthurl $healthurl auth $auth secret $secret ");
    $access_token = ",";
    dbg("access token: $access_token");
    return array ($mcid,$healthurl,$auth,$secret, $access_token);
}

if(isset($_GET['test'])) {
	// Create a voucher id
	$server =substr($GLOBALS['appliance_gw'],8); // aha this was wrong - parse out where we are making them !!! not $_SERVER['HTTP_HOST'];
	$results = array(1,6262,9998);
	$i = 0;
	foreach(array('s0001.myhealthespace.com','s6262.myhealthespace.com','s9998.myhealthespace.com') as $sv) {
		$vid = generate_voucher_id($sv);
		echo "<p>Generated voucher id $vid</p>";
		$serverid = decode_voucher_id($vid);
		echo "<p>Decoded voucher id to server $serverid</p>";
		if($results[$i] != $serverid)
		throw new Exception("Failed to decode server id correctly");
		$i++;
	}
	exit;
}

if (isset($_POST['cancel'])){
	header ("Location: svcsetup.php?usercancelled");
	die("Location: sycsetup.php?usercancelled");
}
if (isset($_POST['editslot']))	
  $editslot = $_POST['editslot']; else $editslot = -1;

$mess ='';
list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

$v = new stdClass;
$v->err = $v-> servicename_err = $v->patientname_err = $v->patientemail_err = $v->addinfo_err =
$v->patientprice_err = $v->expirationdate_err = $v->supportphone_err = '';
$v->patientname = $v->patientemail = $v->addinfo = $v->patientprice = '';
$v->duration =0; $v->asize=$v->dcredits=$v->fcredits=0;

$patientdisabled = ''; $emailverified='optional, will notify 3 days before expiry'; $mobile='';$mobileverified='';$acctype='';$mcid='';
// error check these args
$errs = array (); $errstring ='';

if (isset($_REQUEST['mcid']))   // if set, then enhance
{
	$TESTNETWORK = "http://globals.myhealthespace.com";
	$PRODNETWORK = "http://globals.medcommons.net";

	$q = $_REQUEST['mcid']; // get the mcid
	// no domain specified use mcid
	if ($q>9000000000000000) $g = $TESTNETWORK; else $g = $PRODNETWORK;
	$appliance = get_appliance_info($g,$q);
	if (!$appliance) $err="Cant find any appliance with mcid $q in $g";
	else
	{
		$r = get_user_demographics($appliance,$q);
		if ($r) 
	{
			$v->patientname = $r->first_name.' '.$r->last_name;
			$v->patientemail = $r->email;
			$patientdisabled ='readonly';// 'disabled'; // inserted into html at very end
			$acctype = "($r->acctype acct on $appliance)  ";
			$emailverified = ($r->emailverified=='')? 'email is unverfied':'email is verified';
			$mobile = ($r->mobile=='')? '':" ph: $r->mobile";
			if ($r->mobileverified!='') $mobile.=' verified';
			$photourl = $r->photoURL;
			$mcid = $q; //put back into input field
			
		}
	}
}

if(isset($_POST['roirId'])) {
  dbg("Populating voucher from request id");
  $v->patientname = $_POST['patientname'];
  $v->patientemail = $_POST['patientemail'];
  $v->addinfo = $_POST['patientnote'];
}
else
if (isset($_POST['patientname']))
{
	// this section handles the post back into here

	$v->patientname = $_POST['patientname'];
	$v->patientemail = $_POST['patientemail'];
	$v->addinfo = $_POST['addinfo'];
	$v->patientprice = $_POST['patientprice'];
	$v->duration = $_POST['duration'];
	$v->asize = 0;
	$v->fcredits= $_POST['fcredits'];
	$v->dcredits = $_POST['dcredits'];
  $v->consents = (isset($_POST['consents']) ? $_POST['consents'] : array());
	$svcnum = $_POST['svcnum'];
	if (strlen($v->patientname)<5) $errs[] = array('patientname_err',"Name of patient must be at least 5 characters");
	if (strlen($v->patientname)>64) $errs[] = array('patientname_err',"Name of patient must be no more than 64 characters");
	if (strlen($v->patientemail>0)) if (!checkEmail($v->patientemail)) $errs[] = array('patientemail_err',"Invalid email address ");
	$money =validateMoney($v->patientprice);
	if (!$money) $errs[] = array('patientprice_err',"patient prices must be precisely specified e.g. 8.27 ");
	else $v->patientprice=$money;
	if (0.00>($v->patientprice)) $errs[] = array('patientprice_err',"Price is below 0.00");

	if((count($errs)==0) && !isset($_GET['redisplay'])) {
		$now=time();
		list($first,$last) =    split(" ", $v->patientname, 2);

    $result = sql ("Select * from modservices where svcnum = '$svcnum' ");
    if(!$result) die ("cant select modservices " . mysql_error());
    $svc = mysql_fetch_object ($result);
    if (!$svc) die ("cant fetch modservices " . mysql_error());

		list($mcid,$healthurl,$auth,$secret, $access_token)= make_hurl($first,$last,'','',$accid,$auth,$v->consents,$svc->servicename);

		// all fine, try to add to database
		// dont trust $auth or $secret
		$healthurl = mysql_escape_string($healthurl);
		$auth = mysql_escape_string($auth);
		$secret = mysql_escape_string($secret);
		$access_token = mysql_escape_string($access_token);
		$dbprice = $v->patientprice*100;

		$server =substr($GLOBALS['appliance_gw'],8); // aha this was wrong - parse out where we are making them !!! not $_SERVER['HTTP_HOST'];
		$voucherid = generate_voucher_id($server);
		$expdate = calculate_voucher_expiry_date($v->duration);
		$otp = rand(10001,99999);// make a otp because newpatient isnt returning one

		$status = sql  ("insert into modcoupons set patientname='$v->patientname', patientemail = '$v->patientemail', addinfo='$v->addinfo',
				patientprice='$dbprice', expirationdate='$expdate',  status = 'issued',  otp='$otp',
				voucherid = '$voucherid', mcid = '$mcid', auth='$auth', secret='$secret', accesstoken='$access_token',
				 asize = '$v->asize', fcredits='$v->fcredits',dcredits='$v->dcredits',   duration = '$v->duration', 
 				svcnum = '$svcnum', hurl ='$healthurl' , issuetime='$now'  ");

		if (!$status)
		$errs[] = array('err',mysql_error());

		if (count($errs)==0) {
			// all fine, go to print coupons
			$couponnum = mysql_insert_id();

      // bump the counters in the services record
      $ip1 = $svc->createcount+1;
      $result = sql ("Update modservices set createcount = '$ip1' where svcnum = '$svcnum' ");
      if (!$result) die ("cant update modservices " . mysql_error());
			header ("Location: voucherprint.php?a=p&c=$couponnum");
			die("Location: voucherprint.php?a=p&c=$couponnum");
		}
	}
}

function load_services($svcnum, $masteraccid) {
  $result = sql ("Select * from modservices where accid='$masteraccid'  and svcnum='$svcnum' ");
  $r2 = mysql_fetch_object($result);
  if($r2===false){
    $result = sql ("Select * from modservices where accid='$masteraccid'  ");
    $r2 = mysql_fetch_object($result);
  }
  return $r2;
}


// okay there are errors, just fall ack into the regular code

//here

list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
$masteraccid = get_master_services_accid($accid);
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];
$footer = page_footer(); $extrasvcdetails='';
if (isset ($_REQUEST['roi'])) {  
  $result = sql ("Select * from modservices where accid='$accid' and servicename='Patient ROI Request' ");
  $r3 = mysql_fetch_object($result);
	$svcnum =$r3->svcnum; ;  
	$roi = base64_decode($_REQUEST['roi']);
	list ($v->patientname,$v->patientemail,$v->addinfo,$svcvec)=explode('|',$roi);
	$svcnum = $r3->svcnum;
	$extrasvcdetails = "<div class=extrainfo>The requesting party wants you to perform these services. 
	<br/>You can also enter them each as separate
	vouchers.<br/> ".requested_services($accid,$svcvec)."</div>" ; // fix the si
}
else
if (isset($_REQUEST['i'])) 
  $svcnum=$_REQUEST['i']; 
else 
if(isset($_POST['svcnum'])) {
  $svcnum = $_POST['svcnum'];
}
else 
  $svcnum=-1; // sets top level select

$v->consents = array();
if($svcnum >= 0) {
  $db = DB::get();
  $consents = $db->query("select * from modservice_consents where svcnum = ?",array($svcnum));
  foreach($consents as $c) {
    $v->consents[]=$c->accid;
  }
  if(!in_array($accid, $v->consents))
    $v->consents[]=$accid;
}

$svcnum=mysql_escape_string($svcnum);//play it safe

dbg("incoming svcnum = $svcnum");

// Try to load - are they there?
$r2 = load_services($svcnum,$masteraccid);

if($r2===false)  { // No services defined yet
  make_svcs_from_templates($masteraccid); // See if we can make default services
  $r2 = load_services($svcnum,$masteraccid);
}

if($r2===false)  { // Still no services?  Give up and go to service setup
  header("Location: svcsetup.php"); 
  exit; 
}

$v->patientprice = '$'.$v->patientprice;
$svcnum = $r2->svcnum;

// Only display service chooser if they are coming from a voucher
$svcchooser = "";
if(isset($_POST['roirId'])) {
  if(($_POST['svcnum'] == 'null') && isset($_POST['servicename'])) {
    $servicename = req('servicename');
    dbg("service from post");
  }
  else {
    $servicename = $r2->servicename;
    dbg("service from svc num $r2->svcnum");
  }

  dbg("servicename = $servicename");

  $svcchooser = "
  <div class=field><span class=n>Service</span>
      <span class=q>".servicechooser($accid,$servicename)."</span>
  </div>
  <script type='text/javascript'>document.getElementById('svcnum').onchange = function() { document.voucherform.action='vouchersetup.php?redisplay'; document.voucherform.submit(); }</script>
";
}

$header = page_header("page_voucher" ,"Create Voucher $r2->servicename - MedCommons on Demand");
$suggestedprice = monynf($r2->suggestedprice/100.);
$free = ($r2->suggestedprice == 0);

$duration = durationchooser($r2->duration);
$dcredits = dicomchooser($r2->dcredits);
$fcredits = faxinchooser($r2->fcredits);
$addresses = load_address_book($accid);

// Removed mcid lookup code
// <span class=r><input type=submit class='altshort' value='Lookup' />&nbsp; if patient already has a MedCommons Account</span>

$contents = template("voucherform.tpl.php")->set("extrasvcdetails",$extrasvcdetails)
  ->set("r2",$r2)
  ->set("free",$free)
  ->set("acctype",$acctype)
  ->set("mobile",$mobile)
  ->set("addresses",$addresses)
  ->set("v",$v)
  ->set("emailverified",$emailverified)
  ->set("duration",$duration)
  ->set("dcredits",$dcredits)
  ->set("fcredits",$fcredits)
  ->set("patientdisabled",$patientdisabled)
  ->set("suggestedprice",$suggestedprice)
  ->set("svcchooser",$svcchooser)
  ->set("svcnum",$svcnum)
  ->set("mcid",$mcid)
  ->fetch();

echo $header.$contents.$footer;
?>
