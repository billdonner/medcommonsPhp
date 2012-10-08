<?php
require 'mc.inc.php';
require_once "modpay.inc.php";
require_once "utils.inc.php";

$v = new stdClass;
$v->err = $v->mcid = $v->mcid_err= $v->username = $v->username_err = $v->useremail = $v->useremail_err =  $v-> servicename_err = $v->serviceemail_err = $v->supportphone_err = $v->suggestedprice =  $v->suggestedprice_err = $v->servicelogo_err = $v->servicedescription_err = $v->servicename = $v->servicelogo=  $v->supportphone = $v->servicedescription= $v->voucherprinthtml=$v->voucherdisplayhtml=$v->consentblob = '';
$v->duration = $v->asize = -1; $v->fcredits = $v->dcredits = 0;
// error check these args
$errs = array (); 
$errstring =''; 
$photoURL= $acctype=$emailverified=$mobile=$mobileverified='';
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
	$status = sql  ("Delete from  modfriends where mcid='$masteraccid' and friendmcid='$friendmcid' ");
	if (!$status)
	$e = '?e='.mysql_error();  else $e='?=delete is good';
	// all fine, go to list the services again
	header ("Location: svcadmin.php$e");
	die("Redirecting to svcadmin.php$e");
}


if (isset($_REQUEST['submit']))   // come back on postback
{

	if ('Lookup'==($_REQUEST['submit']) )  // if set, then enhance
	{
		$TESTNETWORK = "http://globals.myhealthespace.com";
		$PRODNETWORK = "http://globals.medcommons.net";

		$q = $_REQUEST['mcid']; // get the mcid
    $q = clean_mcid($q);
		// no domain specified use mcid
    if(preg_match("/9[0-9]{15}/",$q)===1)
      $g = $TESTNETWORK; 
    else 
      $g = $PRODNETWORK;

		$appliance = get_appliance_info($g,$q);
		if (!$appliance) $errs[]= array('err',"Cant find any appliance with mcid $q in $g");
		else
		{
			$r = get_user_demographics($appliance,$q);
			if ($r)
			{
				$v->username = $r->first_name.' '.$r->last_name;	$v->useremail = $r->email;
				$acctype = "($r->acctype acct on $appliance)  ";
				$emailverified = ($r->emailverified=='')? 'email is unverfied':'email is verified';
				$mobile = ($r->mobile=='')? '':" ph: $r->mobile";
				if ($r->mobileverified!='') $mobile.=' verified';
				$photourl = $r->photoURL;
				$v->mcid = $q; //put back into input field
				if (strcmp($_SERVER['HTTP_HOST'],$appliance)!=0)
				$errs[] = array('err',"this user is not running on your appliance and hence can not share vouchers");
			}
		}
	}
	else	if ('Add User'==($_REQUEST['submit']) )  // if set, then enhance
	{
		if (trim($_REQUEST['mcid'])!='') {
			$q = clean_mcid($_REQUEST['mcid']); // get the mcid

			$status = check_add_friend($q,$masteraccid);
			if (!$status)
			$errs[] = array('err',"could not add this user ").mysql_error();
		}
	}
	else die ("unknown submit value");
}

dbg("got ".count($errs)." errors");

// normal case
$mcidlist=''; // apease the sharewith section
	// returns a big select statement or FALSE
$outstr = <<<XXX
	<table id='svctable' title="users with access to service account $accid">
	<tr><th>mcid</th><th>name</th><th>email</th><th>actions..</th></tr>
XXX;
$q = "select m.mcid,m.friendmcid, u.first_name, u.last_name, u.email from modfriends m, users u where m.mcid='$masteraccid' and u.mcid=m.friendmcid ";
$result = sql($q) or die ("Cant $q " . mysql_error());
while ($r2=mysql_fetch_object($result))
{
	$dellink = ($r2->friendmcid==$r2->mcid)?"can't delete":"<a href='?del=$r2->friendmcid' >delete</a>";
	$outstr.= <<<XXX
	<tr><td>$r2->friendmcid</td><td> $r2->first_name $r2->last_name</td>
	<td> $r2->email</td><td>$dellink</td></tr>
XXX;

}
$outstr.="</table>";
// regular path on cold start, or if errors are still present
for ($i=0; $i<count($errs); $i++) {
  // dbg("error: ".$errs[$i][0]);
  $v->$errs[$i][0]=$errs[$i][1];
}


$btk = wsGetBillingId($masteraccid);

if (count($errs)>0)$adddisabled = 'readonly';

list ($faxin,$dicom,$acc) =wsGetCounters($btk);
$header = page_header("page_setup","Administer Service and Voucher Sharing  - MedCommons on Demand"  );
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

$sharewith = <<<XXX
$header
<div id="ContentBoxInterior"  mainId='page_setup' mainTitle="Services - MedCommons on Demand"  >

<h2>Voucher Administrators</h2>
$outstr
<hr/>

<h2>Add  New Voucher Administrator</h2>
<div id=sharewith class=fform   >
<form action=svcadmin.php method=post>

<div class=inperr id=err>$v->err</div>

<div class=field><span class=n>MedCommons ID</span>
<span class=q><input type=text name=mcid value='$v->mcid' />
<span class=r><input type=submit name=submit class=altshort value='Lookup' /></span>
</span></div>

<div class=field><span class=n>User Name</span>
<span class=q><input disabled type=text name=username value='$v->username' />
<span class=r>$acctype $mobile</span>
<div class=inperr id=username_err>$v->username_err</div></span></div>
<div class=field><span class=n>User Email</span>
<span class=q><input disabled type=text name=useremail value='$v->useremail' />
<span class=r>$emailverified</span>
<div class=inperr id=useremail_err>$v->useremail_err</div></span></div>

<div class=field><span class=n>&nbsp;</span><span class=q><input type=submit $adddisabled name='submit' class=primebutton value='Add User' />&nbsp;
<input type=submit class='altshort' name=cancel  value='Cancel' /></span></div>
</form>
<table class=tinst>
<td class=lcol >Instructions</td><td class=rcol >These are the users who can access and manipulate Voucher Services for this account $accid.<br/>
You can add new users who are on the same MedCommons Appliance.</td></tr>
</table>
</div>
</div>
$footer
XXX;

echo $sharewith;

?>
