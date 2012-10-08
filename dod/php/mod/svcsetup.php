<?php

require_once "modpay.inc.php";
require_once "template.inc.php";
require_once 'Crypt/HMAC.php'; #see http://pear.php.net/package/Crypt_HMAC
require_once 'HTTP/Request.php'; #see http://pear.php.net/package/HTTP_Request
require_once 'db.inc.php';
require_once "utils.inc.php";


$accessKey = "075Q8TW5Y9HFW4ZZAG02";
$secretKey = "IMBRcy/Lb/uqrOLF7GTWI7emGKt120o+BDWgzcIa";

$base = '0.04'; $rate = '8';

function agreerules($base,$rate,$email,$nextbutton)
{
$header = page_header("page_setup","Services - MedCommons on Demand Marketplace Rules"  );
$footer = page_footer();
$markup =<<<XXX
$header

<div id="ContentBoxInterior" mainTitle="Agree to Marketplace Rules" >
<h3>You will be paid via your  Amazon Account $email </h3>
<p>Please agree to Amazon and MedCommons Marketplace Rules</p>
<p>For each  Voucher sold, MedCommons will deduct $base and $rate percent.</p>
<p>Be sure to specify $email as your Amazon email id, otherwise someone else's account may be credited when your patients pay.</p>
<hr/>
$nextbutton
$footer
</div>
XXX;
return $markup;

}

function getAcceptMarketplaceFeeForm($referenceId, $returnUrl, $fixedMarketplaceFee, $variableMarketplaceFee) {
    global $accessKey,$secretKey;
    $formHiddenInputs['callerKey'] = $accessKey;
    $formHiddenInputs['pipelineName'] = "Recipient";
    $formHiddenInputs['recipientPaysFee'] = "True";
    $formHiddenInputs['collectEmailAddress'] = "True";
    if ($referenceId) $formHiddenInputs['callerReference'] = $referenceId;
    if ($returnUrl) $formHiddenInputs['returnUrl'] = $returnUrl;
    if ($fixedMarketplaceFee) $formHiddenInputs['maxFixedFee'] = $fixedMarketplaceFee;
    if ($variableMarketplaceFee) $formHiddenInputs['maxVariableFee'] = $variableMarketplaceFee;

    ksort($formHiddenInputs);
    $stringToSign = "";

    foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
        $stringToSign = $stringToSign . $formHiddenInputName . $formHiddenInputValue;
    }

    $formHiddenInputs['signature'] = getSignature($stringToSign, $secretKey);

    $form = "<form action=\"https://authorize.payments.amazon.com/cobranded-ui/actions/start\" method=\"post\">\n";
    foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
        $form = $form . "<input type=\"hidden\" name=\"$formHiddenInputName\" value=\"$formHiddenInputValue\" >\n";
    }
    $form = $form . "<input type=\"image\" src=\"https://authorize.payments.amazon.com/pba/images/MarketPlaceFeeWithLogo.png\" border=\"0\" >\n";
    $form = $form . "</form>\n";
    return $form;
}

/**
 * Sets the accounts to which consent should be granted
 * for new vouchers.
 */
function set_service_consents($svcnum, $consents) {
  dbg("Setting ".count($consents)." consents for service $svcnum");
  $db = DB::get();
  $db->execute("delete from modservice_consents where svcnum = ?",array($svcnum));
  foreach($consents as $c) {
    $db->execute("insert into modservice_consents (svcnum,accid) values (?,?)",array($svcnum,$c));
  }
}


function servicelist($accid, $svc)
{
    // accid might be either our account, or that of the "master" voucher account

    $counter=0;$createtotal=$utilizedtotal=0; $cashreceivedtotal=0;$cashpaidouttotal=0;
    $durs = array ('3 days','5 days','1 week','1 month','3 months','6 months' );
    $sizes = array ('.1MB','1MB','10MB','100MB','500MB');

    // see if we have an email address for amazon, if not, disable all the buttons
    //$disablevoucher='';
    //$result =sql("SELECT * from modservices  where accid= '$accid' and servicename= '__default__' ") or die("cant query modservices ". mysql_error());
    //$r1 = mysql_fetch_object($result);
    //if ($r1===false )$disablevoucher='disabled f1 ';
    //else {
        // this should be the amazon email
        //if ($r1->serviceemail=='') $disablevoucher='disabled f2';
        // alright buttons should be set correctly or not
    //}
    // returns a big select statement or FALSE
    $outstr = <<<XXX
    <table id='svctable' title="services defined by account $accid">
    <tr><th>&nbsp;</th><th>service</th><th>description of services rendered</th><th>voucher parameters</th>
    <th>per voucher<br/>price</th><th>total vouchers<br/>issued [claimed]</th>
    <th>total service<br/>revenue</th><th>actions..</th></tr>
XXX;
    $result =sql("SELECT * from modservices  where accid= '$accid' and servicename != '__default__' ") or die("cant query modservices ". mysql_error());
    while ($r2 = mysql_fetch_object($result))
    {
        $suggestedprice=mony($r2->suggestedprice/100.);
        $duration = $durs[$r2->duration]; $counter++;
        $asize = $sizes [$r2->asize];
        list  ($netpractice, $netmc, $amazonfee) = figure_money($r2->suggestedprice/100.,$r2->duration,$r2->asize,$r2->fcredits,$r2->dcredits);
        $netpractice = mony($netpractice);
        $cashreceived = mony($r2->cashreceived/100.);
        $cashpaidout = mony($r2->cashpaidout/100.);

        $totalprofit = mony(($r2->cashreceived-$r2->cashpaidout)/100.);
        $createtotal+=$r2->createcount; $utilizedtotal+=$r2->utilizedcount; $cashreceivedtotal+=$r2->cashreceived;$cashpaidouttotal+=$r2->cashpaidout;
        $faxcred = (0!=$r2->fcredits)? "<br/>$r2->fcredits fax pages":'';
        $dicomcred = (0!=$r2->dcredits)? "<br/>$r2->dcredits dicom uploads":'';

        if (($r2->suggestedprice==0)) // free services can always get a button
        $cell = "<td title='Create Free Voucher for this service '><form method=post action='vouchersetup.php'> 
                    <input type=hidden value=$r2->svcnum name=i />
                    <input type='submit' class='mainmicro'  name='Voucher'  value='Free' />
                 </form> 
                 </td>"; 
        else 
        if (($r2->serviceemail!='')) // free services can always get a button
            $cell = "<td title='Create  Voucher for payment to $r2->serviceemail '><form method=post action='vouchersetup.php'> 
              <input type=hidden value=$r2->svcnum name=i />
              <input type='submit' class='mainmicro'  name='Voucher'  value='Voucher' />
           </form> 
           </td>"; 
        else 
          $cell = "<td title='Before you can issue vouchers for this service you must enter your Amazon ID and agree to the Amazon Marketplace Terms and Conditions'>
             <a href='#sharewith' onclick='return showAmzEmail();'>Setup&nbsp;<img style='position: relative; top: 3px;' src='/site/images/cog.png'/></a></td>";

        $outstr .="<tr>
$cell
    <td style='white-space: nowrap;'>
      ".htmlentities($r2->servicename)."</a>
    </td>
        <td title='for service:$r2->serviceemail $r2->supportphone' >$r2->servicedescription</td>
        <td>$duration $faxcred $dicomcred</td>
        <td>$suggestedprice</td>
        <td class=actual >$r2->createcount [$r2->utilizedcount]</td>
        <td class=actual >$cashreceived</td>
                <td>
            <a href='svcsetup.php?edit=$r2->svcnum#edit' title='edit'><img title='edit' src='/images/editpadw.gif'/></a> <a class=deleteLink href=svcsetup.php?del=$r2->svcnum >X</a>
            <a href=voucherlist.php?i=$r2->svcnum title='Show patients who have a ".htmlentities($r2->servicename)." voucher' style='white-space: nowrap;' >list</a>
        </td>
    </tr>
        ";
    }
    $outstr.="</table>";

    if (($counter==0)&&!isset($_GET['autoserv']))
    {
        // bill - aug 09 2008  -  if we have no services at all, then scan the templates and make some services
        make_svcs_from_templates($accid);

        // header("Location: svcsetup.php?autoserv"); // redirect to self
        // die ("Repainting svcsetup.php");
        return servicelist($accid,$svc);
    }


    $profit = mony (($cashreceivedtotal-$cashpaidouttotal)/100.);
    $cashreceivedtotal = mony($cashreceivedtotal/100.);
    $cashpaidouttotal = mony($cashpaidouttotal/100.);

    $totals = <<<XXX
    <h2>Services</h2>
XXX;
    return $totals.$outstr;

}

$t = template("svcsetup.tpl.php");

if (isset($_POST['editslot']))    $editslot = $_POST['editslot']; else $editslot = -1;

$v = new stdClass;
$v->err = $v-> servicename_err = $v->serviceemail_err = $v->supportphone_err = $v->suggestedprice =  $v->suggestedprice_err = $v->servicelogo_err = $v->servicedescription_err = $v->servicename = $v->servicelogo=  $v->supportphone = $v->servicedescription= $v->voucherprinthtml=$v->voucherdisplayhtml=$v->consentblob = '';

$v->duration = $v->asize = -1;
$v->fcredits = $v->dcredits = 0;
$v->consents = array();

// error check these args
$errs = array (); $errstring ='';

list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();
$masteraccid=get_master_services_accid($accid);
$sharerr =''; $mcidlist=''; // apease the sharewith section
$q = "select * from modfriends where mcid='$masteraccid' ";
$result = sql($q) or die ("Cant $q " . mysql_error());
while ($r2=mysql_fetch_object($result)) {
    if ($mcidlist!='') $mcidlist.=',';
    $mcidlist .= $r2->friendmcid;

}

$db = DB::get();
$v->serviceemail=$email;

dbg("emai = $email");

dbg("X1");
if(isset($_REQUEST['amzconfirm'])) {
  // A return from Amazon - the user has agreed to market place contract
  // TODO: how to verify this URL?  Do we sign it before sending it out
  // or will amz attach a sig?

  $requestEmail = $_GET['e'];
  if(!$requestEmail)
    throw new Exception("Bad email address after confirming market place contract at Amazon");

  // Copy it into all the user's services
  $db->execute("update modservices set serviceemail = ? where accid = ?", array($requestEmail, $masteraccid));

  // Redirect so the user gets fresh display
  header("Location: svcsetup.php");
  exit;
}
else
if(isset($_REQUEST['cancel']))
{
    // all fine, go to list the services again
    header ("Location: svcsetup.php?cancelled");
    die("Redirecting to svcsetup.php?cancelled");
}
if(isset($_REQUEST['del']))
{
    $svcnum = $_REQUEST['del'];
    $status = sql  ("Delete from  modservices where accid='$masteraccid' and svcnum='$svcnum' ");
    if (!$status)
    $e = '?e='.mysql_error();  else $e='?=delete is good';
    // all fine, go to list the services again
    header ("Location: svcsetup.php$e");
    die("Redirecting to svcsetup.php$e");
}
else if (isset($_REQUEST['edit']))
{
    // an edit of an existing record was
    $svcnum = $_REQUEST['edit'];
    $t->set("showform",true);
    $editslot = $svcnum; // set this up
    $result =sql("SELECT * from modservices  where accid= '$masteraccid' and svcnum='$svcnum' ") or die("cant query modservices ". mysql_error());
    $r2 = mysql_fetch_object($result);
    if($r2==false) {
        header ("Location: svcsetup.php?err=badidx");
        die("Redirecting to svcsetup.php?err=badidx ");
    }
    // load current values
    $v->servicename = $r2->servicename;
    $v->serviceemail = $r2->serviceemail ;
    $v->servicelogo = $r2->servicelogo ;
    $v->voucherprinthtml = $r2->voucherprinthtml;
    $v->voucherdisplayhtml = $r2->voucherdisplayhtml;
    $v->consentblob = $r2->consentblob;
    $v->servicedescription = $r2->servicedescription;
    $v->supportphone = $r2->supportphone ;
    $v->suggestedprice = '$'.money_format('%i',$r2->suggestedprice/100.) ;
    $v->duration = $r2->duration;
    $v->asize = $r2->asize;
    $v->fcredits = $r2->fcredits;
    $v->dcredits = $r2->dcredits;

    $consents = $db->query("select * from modservice_consents where svcnum = ?",array($svcnum));
    $v->consents = array();
    foreach($consents as $c) {
      $v->consents[]=$c->accid;
    }
    dbg("loaded ".count($v->consents)." consents for service $svcnum");
}
else { // not the edit case
    if (isset($_POST['servicelogo']))
    {
        // this section handles the post back into here

        $v->serviceemail = ($_POST['serviceemail']);
        $v->servicelogo = ($_POST['servicelogo']);
        $v->supportphone = ($_POST['supportphone']);
        $v->voucherdisplayhtml = ($_POST['voucherdisplayhtml']); // needs safety check and scrubbing
        //$v->consentblob = ($_POST['consentblob']); // needs safety check and scrubbing

        if (false===checkEmail($v->serviceemail)) $errs[] = array('serviceemail_err',"Invalid email address ");
        if (strlen($v->supportphone)>0) if (strlen($v->supportphone)<10) $errs[] = array('supportphone_err',"Support phone must be 10 or more digits");


        //comma separated list, just
    //    $mcidlist = $_REQUEST['mcidlist'];
    //    $mcids = explode(',',$mcidlist);
    //    $mcidlist = '';
    //    remove_friends ($accid) ; // get rid of all friends, we will re-add via check_add_friend
    //    foreach ($mcids as $mcid)
    //    {
    //        $mcid = trim($mcid);
    //        $status = check_add_friend ($mcid,$accid);
    //        if (!$status)
    //        {
    //            if ($mcidlist!='') $mcidlist.=',';
    //            $errs[] = array('err', 'One or more mcids is invalid. The valid mcids remain in the list');
    //        }

    //    }


        if (count($errs)==0)
        {
            $se = mysql_escape_string($v->serviceemail);
            $sl = mysql_escape_string($v->servicelogo);
            $sp = mysql_escape_string($v->supportphone);
            $vd = mysql_escape_string($v->voucherdisplayhtml);
            $cb = mysql_escape_string($v->consentblob);
            $now=time();

            //***************
            $time = time();
            $AcceptMarketplaceFeeForm = 
              getAcceptMarketplaceFeeForm("Mod Services $time",
                $GLOBALS['mod_base_url']."/svcsetup.php?amzconfirm&e=".urlencode($v->serviceemail), // see callback return point earlier in page
                "$base", "$rate") ;

            dbg("Editing with editslot = $editslot");
            if($editslot == -1) { // a new record - all fine, try to add to database
                
                $status = sql  ("Replace into modservices set  servicename = '__default__', 
                                                                 supportphone='$sp', 
                                                                  consentblob = '$cb',
                                                                  servicelogo = '$sl',
                                                                   voucherdisplayhtml = '$vd',
                                                                 accid='$masteraccid', time='$now'  ");

                if($status ) { 
                  echo agreerules($base,$rate,$v->serviceemail,$AcceptMarketplaceFeeForm); exit; 
                } 
                else 
                  die ("Cant insert into modservices ".mysql_error());
            } 
            else { // this is just an update to an existing record
                $status = sql ("Update modservices set voucherdisplayhtml = '$vd', servicelogo = '$sl',
                                                                         consentblob = '$cb', 
                                                                         supportphone='$sp',   time='$now'   
                                                                         where accid='$masteraccid' and svcnum ='$editslot' ");
                if(!$status) 
                  die ("Cant insert into modservices ".mysql_error());

                echo agreerules($base,$rate,$v->serviceemail,$AcceptMarketplaceFeeForm); 
                exit; 
            }

            // ssadedin: can never get here?
            if(!$status) { 
              if (1062==mysql_errno()) 
                $errs[]=array('servicename_err','Duplicate Service Name'); 
              else
                $errs[] = array('err',mysql_error());
            }

            if(count($errs)==0) {
                // all fine, go to print coupons
                //header ("Location: printcoupon.php");
                //die("Redirecting to printcoupon.php");
            }
        }
        // okay there are errors, just fall ack into the regular code
    }
    else
    if (isset($_POST['servicename']))
    {
        // this section handles the post back into here
        $v->servicename = ($_POST['servicename']);;
        $v->suggestedprice = ($_POST['suggestedprice']);
        $v->servicedescription = ($_POST['servicedescription']);
        $v->duration = ($_POST['duration']);
        $v->fcredits = ($_POST['fcredits']);
        $v->dcredits = ($_POST['dcredits']);
        $v->voucherprinthtml = ($_POST['voucherprinthtml']);   // needs safety check and scrubbing
        $v->consents = (isset($_POST['consents']) ? $_POST['consents'] : array());

        if (strlen($v->servicename)<4) $errs[] = array('servicename_err',"service name is too short");
        if (strlen($v->servicename)>32) $errs[] = array('servicename_err',"service must be 32 or fewer characters");
        $money =validateMoney($v->suggestedprice);
        if (!$money) $errs[] = array('suggestedprice_err',"Suggested prices must be precisely specified e.g. 8.27 ");
        else $v->suggestedprice=$money;

        if (count($errs)==0)
        {
            // read the default record if any
            $se=$sp=$cb=$sl=$vd=''; // set defai;ts

            $result = sql ("Select * from modservices where accid='$masteraccid' and  servicename='__default__' ");

            // if we find the default record then get values from there
            if ($result) {
                $r3 = mysql_fetch_object($result);
                if ($r3!==false)
                {
                    $se = $r3->serviceemail;
                    $sp= $r3->supportphone;
                    $cb = $r3->consentblob;
                    $sl = $r3->servicelogo;
                    $vd = $r3->voucherdisplayhtml;
                }

            }
            $sn = mysql_escape_string($v->servicename);
            $sd = mysql_escape_string($v->servicedescription);
            $vp = mysql_escape_string($v->voucherprinthtml);
            $sgb = mysql_escape_string($v->suggestedprice*100);
            $now=time();

            if ($editslot == -1) {
                // all fine, try to add to database
                $status = sql  ("Insert into modservices set servicename='$sn', serviceemail = '$se', servicedescription='$sd',
                                                                 supportphone='$sp', 
                                                                 voucherprinthtml = '$vp',
                                                                  consentblob = '$cb',
                                                                  servicelogo = '$sl',
                                                                   suggestedprice = '$sgb', fcredits='$v->fcredits',dcredits='$v->dcredits',
                                                                   duration = '$v->duration', 
                                                                   voucherdisplayhtml = '$vd',
                                                                 accid='$masteraccid', time='$now'  ");

                $svcnum = mysql_insert_id();

                if(!$status) 
                  throw new Exception("Unable to create your service: ".mysql_error());

                set_service_consents($svcnum,$v->consents);

                // Success
                header("Location: svcsetup.php"); die ("Redirecting to self");
            } 
            else {
                // this is just an update to an existing record
                $status = sql ("Update modservices set servicename='$sn', serviceemail = '$se',
                                servicedescription='$sd', voucherprinthtml = '$vp', voucherdisplayhtml = '$vd', servicelogo = '$sl',
                                consentblob = '$cb', suggestedprice = '$sgb',  fcredits='$v->fcredits',dcredits='$v->dcredits',
                                supportphone='$sp',   duration = '$v->duration', time='$now'   
                                where accid='$masteraccid' and svcnum ='$editslot' ");

                if(!$status ) 
                  throw new Exception("Unable to update your service.","Cant insert into modservices ".mysql_error());

                set_service_consents($editslot, $v->consents);
                 
                header("Location: svcsetup.php");
                die ("Redirecting to self");
            }

            if(!$status) { 
              if (1062==mysql_errno()) 
                  $errs[]=array('servicename_err','Duplicate Service Name'); 
              else
                  $errs[] = array('err',mysql_error());
            }

            if(count($errs)==0) {
                // all fine, go to print coupons
                //header ("Location: printcoupon.php");
                //die("Redirecting to printcoupon.php");
            }
        }
        // okay there are errors, just fall ack into the regular code
        dbg("showing service form");
        $t->set("showform",true);
        $t->set("scrolltoform",true);
    }
}
// regular path on cold start, or if errors are still present

for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];

$btk = wsGetBillingId($masteraccid);

if (count($errs)==0)
$sharewithstyle = 'style="display:none" '; else
$sharewithstyle = 'style="display:block" ';
list ($faxin,$dicom,$acc) =wsGetCounters($btk);
$header = page_header("page_setup","Services - MedCommons on Demand"  );
$footer = page_footer();
$svclist = servicelist($masteraccid,-1);
if ($svclist===false) $svclist = "<span id=noservices><h2>No Services are Defined for this Account</h2></span>";
if (isset($_REQUEST['edit'])) $v->err = "<span >please edit</span>";
if ($v->err=='&nbsp;') $v->err='<span >please enter new service info</span>';
else $v->err = "<span class=inperr id=err>$v->err</span>";



if (isset($_REQUEST['t']))
{
    $t->set("showform",true);
    $templatenum = $_REQUEST['t'];
    $result = sql("Select * from modsvctemplates where templatenum = '$templatenum' ") or die ("can select from modsvctemplate ".mysql_error());
    $r2 = mysql_fetch_object($result);
    if ($r2==false) die ("Bad templatenumber");
    $v->servicename = $r2->servicename;
    $v->servicedescription = $r2->servicedescription;
    $v->voucherprinthtml = $r2->printhtml;
    $v->voucherdisplayhtml = $r2->displayhtml;
    $v->duration = $r2->duration;
    $v->fcredits = $r2->fcredits;
    $v->dcredits = $r2->dcredits;
    $v->consents = array();
}          
  else $templatenum = false;

$duration = durationchooser($v->duration);
$dcredits = dicomchooser($v->dcredits);
$fcredits = faxinchooser($v->fcredits);

if ($svclist===false)
$svcprompt = 'No Services Are Currently Defined';
else
$svcprompt = 'Services'; $bluec = "<img src='/images/bluecycle.gif' />";

if(($editslot == -1) && (count($errs)==0) && (!isset($_REQUEST['t']))) {
    $t->set("showform",false);
}

if($editslot == -1) {
    $buttval = 'Add Service';
    $svctemplatechooser = svctemplatechooser($templatenum);
    $h2 = <<<XXX
<h2><a name='editsvc'/><a onclick='toggle("addsvc")' >$bluec </a>Add Services &nbsp; &nbsp;$svctemplatechooser </h2>
XXX;
    $tinst = "To setup a service, first choose a template, enter a name, description, and suggested price.
    <br/>You can put a custom layout together for printed vouchers and notification emails.
<br/>Changing the duration and approximate size directly affects pricing.";
}
else {

    $buttval = 'Modify';
    $t->set("showform",true);
    $h2 = <<<XXX
<h2><a name='editsvc'/><a onclick='toggle("addsvc")' >$bluec </a>Edit Voucher Service: $v->servicename</h2>
XXX;


    $tinst ="To edit a service, change the name, description, custom layout information, duration and /or suggested price. <br/>Your service name must be unique.
<br/>Changing the duration and approximate size directly affects pricing.";
}
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

// how many friends are there?
$countAdmins = $db->first_row("select count(*) as cnt from modfriends where mcid = ?",array($accid));
if($countAdmins)
  $countAdmins = $countAdmins->cnt;


$addresses = load_address_book($accid);

$self ='http://'.$_SERVER['HTTP_HOST'];
$sharewith = <<<XXX

<script type='text/javascript'>
function $(id) {
  return document.getElementById(id);
}
function showAmzEmail() {
  if($('serviceemail').value != '') { // already have email, just need to agree
    document.adminForm.submit();
    return false;
  }
  else {
    $('sharewith').style.display="block";  
    $('serviceemail_err').innerHTML='Please enter a valid email linked to your Amazon account';
  }
}
</script>
<hr/>
<h2><a onclick='toggle("sharewith")' >$bluec</a> Practice Parameters and Options</h2>
<div id=sharewith class=fform  $sharewithstyle>
<form name='adminForm' action='svcsetup.php' method='post'>
$v->err

<div  class=field><span class=n>Who Else Can Administer These Services?</span>
<span class=q ><a href='svcadmin.php'>$countAdmins Administrators share these services</a>
<span class=r>these users will share your pool of services and vouchers</span></span>
</div>

<div class=field><span class=n>Services Logo URL</span><span class=q>
<input type=text name=servicelogo value='$v->servicelogo' /><span class=r>shown on voucher</span>
<div class=inperr id=servicelogo_err>$v->servicelogo_err</div></span></div>
<div class=field><span class=n>Services Email</span><span class=q>
<input type=text id='serviceemail' name=serviceemail value='$v->serviceemail' /><span class=r>Now Required Pls Supply Amazon Email</span>
<div class=inperr id=serviceemail_err>$v->serviceemail_err</div></span></div>
<div class=field><span class=n>Services Support Phone</span><span class=q>
<input type=text name=supportphone value='$v->supportphone' /><span class=r>optional, will be printed on voucher</span>
<div class=inperr id=supportphone_err>$v->supportphone_err</div></span></div>

<div  class=field><span class=n>Outbound Email to Patients</span><span class=q ><a onclick='toggle("vdh")'> $bluec </a></span>
<span class=r><a href='vouchercustomize.php'>Customization Instructions</a></span><span class=closed id=vdh style="display:none;" >
<textarea  name="voucherdisplayhtml"   cols=60 rows=15 maxlength="1250">$v->voucherdisplayhtml</textarea></span>
</div>

<div class=field><span class=n>&nbsp;</span><span class=q><input type=submit class=primebutton value='Update' />&nbsp;
<input type=submit class='altshort' name=cancel  value='Cancel' /></span></div>
</form>
<table class=tinst>
<td class=lcol >Instructions</td><td class=rcol >These parameters apply to every service and voucher created under this account. 
You can add Users from within your practice in the Adinistration section.</td></tr>
</table>
</div>
XXX;

$t->set("svclist",$svclist)
->set("h2",$h2)
->set("v",$v)
->set("accid",$accid)
->set("addresses",$addresses)
->set("editslot",$editslot)
->set("bluec",$bluec)
->set("duration",$duration)
->set("dcredits",$dcredits)
->set("fcredits",$fcredits)
->set("fcredits",$fcredits)
->set("buttval",$buttval)
->set("tinst",$tinst)
->set("sharewith",$sharewith)
->set("is_postback",isset($_POST['servicename']));

echo $header.$t->fetch().$footer;

?>
