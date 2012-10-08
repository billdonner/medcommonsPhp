<?php
require 'mc.inc.php';
require_once "modpay.inc.php";
require_once "template.inc.php";
require_once "db.inc.php";

$v = new stdClass;
$v->err = $v->mcid = $v->mcid_err= $v->username = $v->username_err = $v->useremail = $v->useremail_err =  $v-> servicename_err = $v->serviceemail_err = $v->supportphone_err = $v->suggestedprice =  $v->suggestedprice_err = $v->servicelogo_err = $v->servicedescription_err = $v->servicename = $v->servicelogo=  $v->supportphone = $v->servicedescription= $v->voucherprinthtml=$v->voucherdisplayhtml=$v->consentblob = '';
$v->duration = $v->asize = -1; $v->fcredits = $v->dcredits = 0;
// error check these args
$errs = array (); $errstring =''; $photoURL= $acctype=$emailverified=$mobile=$mobileverified='';
list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
$masteraccid=get_master_services_accid($accid);
$sharerr ='';
$adddisabled = '';

$v->serviceemail=$email;
if (isset($_REQUEST['cancel']))
{
	// all fine, go to list the services again
	header ("Location: svcsetup.php?cancelled");
	die("Redirecting to svcsetup.php?cancelled");
}
if (isset($_REQUEST['del']))
{
	$friendmcid= $_REQUEST['del'];
	$status = sql  ("Delete from  modconsents where mcid='$masteraccid' and friendmcid='$friendmcid' ");
	if (!$status)
	$e = '?e='.mysql_error();  else $e='?=delete is good';
	// all fine, go to list the services again
	header ("Location: svcconsents.php$e");
	die("Redirecting to svcconsents.php$e");
}


if (isset($_REQUEST['submit']))   // come back on postback
{

	if ('Lookup'==($_REQUEST['submit']) )  // if set, then enhance
	{
		$TESTNETWORK = "http://globals.myhealthespace.com";
		$PRODNETWORK = "http://globals.medcommons.net";

		$q = $_REQUEST['mcid']; // get the mcid
		// no domain specified use mcid
		if ($q>9000000000000000) $g = $TESTNETWORK; else $g = $PRODNETWORK;
		$appliance = get_appliance_info($g,$q);
		if (!$appliance) $errs= array('err',"Cant find any appliance with mcid $q in $g");
		else
		{
			$r = get_user_demographics($appliance,$q);
			if($r) {
				$v->username = $r->first_name.' '.$r->last_name;	$v->useremail = $r->email;
				$acctype = "($r->acctype acct on $appliance)  ";
				$emailverified = ($r->emailverified=='')? 'email is unverfied':'email is verified';
				$mobile = ($r->mobile=='')? '':" ph: $r->mobile";
				if ($r->mobileverified!='') $mobile.=' verified';
				$photourl = $r->photoURL;
				$v->mcid = $q; //put back into input field
				if (( ($q>9000000000000000) &&
				($accid<9000000000000000) ) ||
					( ($q<9000000000000000) &&
				($accid>9000000000000000) ) )
				
				$errs[] = array('err',"this user is not on the same medcommons network as you!");
			}
		}
	}
	else	if ('Add Provider'==($_REQUEST['submit']) )  // if set, then enhance
	{
		if (trim($_REQUEST['mcid'])!='') {
			$q = clean_mcid($_REQUEST['mcid']); // get the mcid

			$status = check_add_consent($q,$masteraccid);
			if (!$status)
			$errs[] = array('err',"could not add this user ").mysql_error();
		}
	}
	else die ("unknown submit value");
}

// normal case
$mcidlist=''; // apease the sharewith section
	// returns a big select statement or FALSE
$outstr = <<<XXX
	<table id='svctable' title="providers with access to healthurls produced by service account $accid">
	<tr><th>Provider ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
XXX;

$db = DB::get();
$consents = $db->query("select m.mcid,m.friendmcid, u.first_name, u.last_name, u.email, u.acctype, g.name as groupname
                    from modconsents m, 
                    users u left join groupinstances g on g.accid = u.mcid
                    where m.mcid=? and u.mcid=m.friendmcid ", array($masteraccid));

// regular path on cold start, or if errors are still present
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];

$btk = wsGetBillingId($masteraccid);

if (count($errs)>0)$adddisabled = 'readonly';

list ($faxin,$dicom,$acc) =wsGetCounters($btk);
$header = page_header("page_setup","Administer Provider Access to Vouchers and HealthURLs  - MedCommons on Demand"  );
$footer = page_footer();
$result = sql ("Select * from modservices where accid='$masteraccid' and  servicename='__default__' ");
// if we find the default record then get values from there
if ($result) {
	$r3 = mysql_fetch_object($result);
	if ($r3!==false)
	{
		$v->serviceemail = $r3->serviceemail;
		$v->supportphone= $r3->supportphone;
		$v->consentblob = $r3->consentblob;
		$v->servicelogo = $r3->servicelogo;
		$v->voucherdisplayhtml = $r3->voucherdisplayhtml;
	}
}

$t = template('svcconsents.tpl.php');
$t->set("consents",$consents)
  ->set("accid",$accid)
  ->set("v",$v)
  ->set("acctype",$acctype)
  ->set("mobile",$mobile)
  ->set("emailverified",$emailverified)
  ->set("adddisabled",$adddisabled);

  echo $header.$t->fetch().$footer;
?>
