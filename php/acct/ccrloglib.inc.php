<?php

// wld 2/17/06 - added tracking numbers to ccrlog so as to support PINS properly
require_once 'email.inc.php';
require_once 'settings.php';
require_once 'template.inc.php';

function prettyguid($guid)
{
	$size = strlen($guid);
	return ( substr($guid,0,4)."..".substr($guid,$size-4,4));
}

function prettytrack($track) {
	$out = "";
	if($track) {
		$out = substr($track,0,4)." ".substr($track,4,4)." ".substr($track,8,4);
	}
	return $out;
}

function prettyaccid($accid) {
	$out = "";
	if($accid) {
		$out = substr($accid,0,4)." ".substr($accid,4,4)." ".substr($accid,8,4)." ".substr($accid,12,4);
	}
	return $out;
}

function verify_logged_in()
{
	$mc = $_COOKIE['mc'];
	if ($mc =='')
	{ header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
	echo "Redirecting to MedCommons Web Site";
	exit;}
	return $mc;
}

/**
 * Enter description here...
 *
 * @param unknown_type $fn
 * @param unknown_type $ln
 * @param unknown_type $email
 * @param unknown_type $accid
 * @param unknown_type $einfo
 * @param unknown_type $emergencyCCRGuid
 * @param unknown_type $epinCleared
 * @return unknown
 */
function patientCard($fn,$ln,$email,$accid,$einfo,$emergencyCCRGuid, $epinCleared)
{
	$prettyaccid = prettyaccid($accid);
	$curl =$GLOBALS['Commons_Url'].'gwredirguid.php' ;
	$ro =" onfocus='highlight(this);'";
	if($epinCleared == "true") {
		$clearPinMsg = "&nbsp;&nbsp;PIN is cleared on your Emergency CCR.  <a href='#' onclick='javascript:restorePin();' title='Restore the PIN on your Emergency CCR'>Click Here to Restore PIN</a>";
	}
	else {
		$clearPinMsg = "&nbsp;&nbsp;This CCR is PIN protected <a href='clearPin.php' title='Clear the PIN on your Emergency CCR'>Clear PIN</a>";
	}

	//<div id="emergencyTab"><span id="etabLeft">&nbsp;</span><span id="etab">Emergency Information</span><span id="etabRight">&nbsp;&nbsp;</span></div>
	$x=<<<XXX
                <span style='white-space: nowrap; font-style: italic; font-size: 11px;'><img src='images/graylocked.gif' />$clearPinMsg</span>
                <br/>
                <br/>

                <ul class="nav">
                <li><span id="patientTab2">Emergency Information </span>
                    </li>
                </ul>
                <div id='patientCardBorder'>
                <div id='patientCard'>
                <div style="margin: 5px; position: relative;">

                <h4>Patient&nbsp;&nbsp;</h4>
                <a target='_new' class="emergencyccr" id='eccrEditLink' title="Click here to Edit your Emergency CCR" href="$curl?guid=$emergencyCCRGuid&free=true&mode=edit&context=eccr"><img border="0" title="Click here to Edit your Emergency Information" id="tabicon" src="images/b_edit.png"/></a>
                <span id="patientNameBlock">
                   <input title="First Name" readonly="true" class="formInput" $ro style="width: 120px;" id="patientGivenName" name="patientGivenName" value="" /> 
                   &nbsp;
                   <input title="Middle Name" readonly="true" class="formInput" $ro style="width: 50px;" id="patientMiddleName" name="patientMiddleName" value="" />
                   &nbsp;
                   <input title="Family Name" readonly="true" class="formInput" $ro style="width: 110px;" id="patientFamilyName" name="patientFamilyName" value="" />
                 </span>
                 <br style="clear:all"/>
                 <h4>ID&nbsp;&nbsp;</h4>
                   <input name="patientDateOfBirth" readonly="true" class="formInput" $ro size="28" style="" value="$prettyaccid"/>
                    <span class="smallPatientLabel"></span>
                 <br style="clear:all"/>
                 <h4>Age&nbsp;&nbsp;</h4>
                 <input name="patientAge"  readonly="true"class="formInput" onfocus="highlight(this);" size="4" style="text-align: center" value=""/>
                  &nbsp;<b>Sex</b>&nbsp;
                  <input class="formInput" onfocus="highlight(this);" name="patientGender" readonly="true" id="patientGender" value="Unknown" />                  
                 <br style="clear:all"/>
                 <h5>Email&nbsp;&nbsp;</h5>
                 <input class="formInput" onfocus="highlight(this);" style="width: 263px;" id="patientEmail" name="patientEmail" readonly="true" value="" />
                 <br style="clear:all"/>
                 <h5>Street&nbsp;&nbsp;</h5>
                 <input class="formInput" onfocus="highlight(this);" style="width:263px;" id="patientAddress1" name="patientAddress1" readonly="true" value="" />
                 <br style="clear:all"/>
                 <h5>City&nbsp;&nbsp;</h5>
                 <input class="formInput" onfocus="highlight(this);" id="patientCity" name="patientCity" readonly="true" value="" />
                 &nbsp;<span class="smallPatientLabel">State</span>
                 <input class="formInput" onfocus="highlight(this);" style="width:30px;" id="patientState" name="patientState" readonly="true" value="" />
                 &nbsp;<span class="smallPatientLabel">ZIP</span>
                 <input class="formInput" onfocus="highlight(this);" style="width:40px;" id="patientPostalCode" name="patientPostalCode" readonly="true" value="" />
                 <h5>Country&nbsp;&nbsp;</h5>
                 <input class="formInput" onfocus="highlight(this);" style="width:60px;" id="patientCountry" name="patientCountry" readonly="true" value="" />
                  <span style="position: absolute; right: 52px;">
                    <span class="smallPatientLabel">Phone</span>&nbsp;<input class="formInput" onfocus="highlight(this);" id="patientPhoneNumber" name="patientPhoneNumber" readonly="true" value="" />
                  </span>
                 <p class="p3" style="margin: 3px 0px 6px 13px;" id="eccr">
                    <span style='vertical-align: middle'><img src='images/RedCross_12.gif' /></span>
                    <span style='vertical-align: middle'>&nbsp;Your <a target='_new' id='eccrEditLink2' class="emergencyccr" href="$curl?guid=$emergencyCCRGuid&&free">Emergency CCR</a>
                      is available at www.medcommons.net</span>
                 </p>
               </div>
               
            </div><!-- end of patient card -->
            </div><!-- end of patient card border -->
XXX;
	return $x;
}

/**
 * Enter description here...
 *
 * @param unknown_type $guid
 * @return unknown
 */
function emergencyccr($guid)
{
	$curl =$GLOBALS['Commons_Url'].'gwredirguid.php' ;
	if ($guid=='') {
		$emit= "<div style='padding: 5px 10px;'><img style='float: left;' src='images/exclaim.png'/><div id='foo2' style='padding: 0px 15px; margin: 0px 15px;'>No Emergency CCR has been set for this account.
To set one, <a href='".$GLOBALS['BASE_WWW_URL']."/secureredir.php?p=gwredir&a=OpenCCR' title='Open an existing CCR' target='_new'>Open</a> or <a href='".$GLOBALS['BASE_WWW_URL']."/secureredir.php?p=gwredir&a=CreateCCR' title='Create a new CCR' target='_new'>Create</a> a CCR and 
then select it as your Emergency CCR.</div></div>";
	}
	else
	$emit = "";

	return ($emit);
}

function emailtablerow($miniview,$rowclass,$id,$idp,$date,$to,$subject,$guid,$tracking,$free)
{
	$prettyguid=prettyguid($guid);
	$curl =$GLOBALS['Commons_Url'].'gwredirguid.php' ;
	$href = $curl."?guid=$guid&tracking=$tracking&free=$free";

	// ssadedin: removed idp and changed guid to track#
	//$emit = "<tr $rowclass> <td>$idp</td> <td>$date</td> <td><a target='_new' href='$href'>$prettyguid</a></td> <td>$to</td> <td>$subject</td> </tr>";
	$emit = "<tr $rowclass><td>$date</td> <td><a target='_new' href='$href'>".prettytrack($tracking)."</a></td> <td>$to</td> <td>$subject</td> </tr>";
	return $emit;
}

function encode ($x) { return $x;}

function assembletabs($miniview, $count,$content,$tab,$tab0content)
{
	// for the ajax updater, the returned content represents all the tabs and is sent back to the browser
	if ($miniview == true) {
		$warn = <<<XXX
    <p class="p2">
      <a onclick="return privacylockclicked();" style="vertical-align: middle;"><img class="clickable" src="images/locked.gif"/></a>
      <span style="vertical-align: middle;"><b>Note:</b> To protect your privacy, some details below have been obscured.  Click the padlock
      to display full details.</span></p>
XXX;
}
else
$warn = <<<XXX
<p class="p2">
  <a style="vertical-align: middle;" onclick="return privacyunlockclicked();"><img class="clickable" src="images/unlocked.gif"/></a> 
  <span style="vertical-align: middle;"><b>Privacy Notice:</b> Click the padlock icon to hide details of your
  CCRs below.</span> 
</p>
XXX;
$buf = <<<XXX
    <div>$warn</div>
        <div class="wholetab" >\n
XXX;
//iterate thru each of the tabs
for ($i=0;  $i<$count; $i++)
{   //$content[$i]="test page $i";
	$p1 = $i+1;
	$buf.="<div id='tab$p1' style='position: relative;'>\n";
	if($count>2) {
		//$buf.="<div id='tabheader' style='position: absolute;'>";
		for ($j=0;  $j<$count; $j++) {
			$t=$tab[$j]; $tn=$j+1;
			if(!$miniview) {
				if($i==$j) {
					$buf.="    <span class='activehead' style='position: relative; top: 3px; z-index:100'><span class='inner'>$t</span></span> \n";
				}
				else {
					$buf.="    <span class='passivehead' style='z-index:0; position: relative; top: 3px;'><a href='#' onclick='showtab($tn); return false;'>$t</a></span> \n";
				}
			}
		}
		//$buf.="</div>";
	}
	if ($miniview == false) if ($i==0) $content[0].=$tab0content;// put exgtra goodies at the bottom of tab 0 if not miniview
	$buf .= "    <div class='tabbody' id='tabbody' style='position: relative; z-index: 50;'>\n ".$content[$i]."</div> \n</div>    \n"; // pour in the content
}
$buf .= "<!-- end of wholetab --></div>\n";
return $buf;
}



function tab0 ($disallowedit,$email,$mobile,$ln,$fn,$street1,$street2,$city,$state,$postcode)
{
	// bottom of tab0 stuff
	if ($street1=='')$streelist = ''; else $streetlist="<li><input type='text' readonly='true' onfocus='highlight(this);'  value='$street1' name='street1'/></li>";
	if ($street2!='') $streetlist .= "<li><input type='text' readonly='true' value='$street2' name='street2'/></li>";


	$ro =($disallowedit)? "readonly='true' ": "onfocus='highlight(this);'";

	if ($disallowedit==true) $savecancelbuttons=''; else
	$savecancelbuttons = <<<XXX
                        <table align='center'>
                        <tr><td><input type='button' onclick="submitpressed(this)" value='Save'></td>
                        <td><input type='reset' onclick="resetpressed(this)" value='Cancel'></td>
                        </tr>
                        </table>
XXX;


	$lockunlock = ($disallowedit)?
	"<a href='#' onclick='unlockaccount(this);'><img src = 'images/locked.gif' alt='unlock account details'></a>" :
	"<a href='#' onclick='lockaccount(this);'><img src = 'images/unlocked.gif' alt='lock account details'></a>" ;


	$tab0content = <<<XXX
<p class="p2">The private documents in this account may be accessed by
 entering a Tracking Number and PIN for a specific document (click above and  supply PIN).
 <b>For security purposes a copy of this page has been emailed to $email</b>
 </p>
                   
XXX;
	/*
	<form name="ownerinfo">
	<h5>For security purposes a copy of this page has been emailed to
	$email</h5>
	$savecancelbuttons
	</form>
	*/
	return $tab0content;
}


function notifyuser ($email, $accid,$fn,$ln,$emailbuf)
{
  global $acApplianceName;

  $homepageurl = $GLOBALS['Homepage_Url'];
  $remoteaddr = $_SERVER["REMOTE_ADDR"];

  $t = new Template();
  $t->set('accid', $accid);
  $t->set('fn', $fn);
  $t->set('ln', $ln);
  $t->set('email', $email);
  $t->set('remoteaddr', $remoteaddr);
  $t->set('homepageurl', $homepageurl);
  $t->set('emailbuf', $emailbuf);

  $text = $t->fetch(email_template_dir() . "accessText.tpl.php");
  $html = $t->fetch(email_template_dir() . "accessHTML.tpl.php");

  // the following would benefit from being moved to a separate routine as part of the parent class
  $time_start = microtime(true);// this is php5 only
  $srv = $_SERVER['SERVER_NAME'];
  $subjectline = "MedCommons Account Display Notification for Account $accid";
  $stat = send_mc_email($email, $subjectline,
			$text, $html,
			array('logo' => get_logo_as_attachment()));

  $time_end = microtime(true);
  $time = $time_end - $time_start;
  if(isset($GLOBALS['Disable_Account_Emails'] )&& ($GLOBALS['Disable_Account_Emails'] != 'true')) {
    if($stat) $stat = "ok $srv  elapsed $time"; else die( "send mail failure from $srv elapsed $time" );
  }

  return $stat;
}

function dodoc($l)
{ // handles non-CCR doc types encountered during scan
	if (!defined('docinited'))
	{// header
		define('docinited',1);
		echo "<h3>My Special Documents</h3>";
	};
	$doctype = $l['doctype'];
	$date = $l['date'];
	$docurl = $l['src']; // where to find the doc - this is a hack currently because the doc should be in the doctable
	$docdesc = $l['subject']; // what to display
	$dockind = $l['status']; // living will, dnr, etc
	echo "$doctype -- $date -- <a href = '$docurl' target='_NEW'>$docdesc</a> ($dockind)<br>";
}

function initdb() {
	// open database and get account info
	$db=$GLOBALS['DB_Database'];

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $/**
   * Enter description here...
   *
   * @param unknown_type $miniview
   * @param unknown_type $accid
   * @param unknown_type $from
   * @param unknown_type $content
   * @param unknown_type $tab
   * @param unknown_type $emailbuf
   * @param unknown_type $fn
   * @param unknown_type $ln
   * @param unknown_type $email
   * @param unknown_type $street1
   * @param unknown_type $street2
   * @param unknown_type $city
   * @param unknown_type $state
   * @param unknown_type $postcode
   * @param unknown_type $country
   * @param unknown_type $mobile
   * @param unknown_type $retemergencyccr
   * @param unknown_type $patientcard
   * @param unknown_type $einfo
   * @param unknown_type $trackerdb
   * @return unknown
   */
  db");
}

function readdb($miniview, $accid,$from,&$content,&$tab,&$emailbuf,&$fn,&$ln,&$email,&$street1,&$street2,
&$city,&$state,&$postcode,&$country,&$mobile,&$retemergencyccr,&$patientcard,&$einfo,&$trackerdb)
{
	$tab[0]="All Providers";
	$providers = array(); // found by phplint
	$content = array(); // found by phplint
	$all = array();// found by phplint // also svn 825 from simon on same day
  $content[0]=""; // e_strict requires this

	$count = 1; //will count the number of tabs we are making

	initdb();

	$query = "SELECT * from users where (mcid = '$accid')";

	$result = mysql_query ($query) or die("can not query table users - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) { echo "cant find account"; return false;}
	$a = mysql_fetch_array($result,MYSQL_ASSOC);



	$email = $a['email'];
	$fn = $a['first_name'];
	$ln = $a['last_name'];
	$mobile = $a['mobile'];
	//get extra address info from addresses table
$foundCurrentCCR = false; // from svn 824
	$query = "SELECT * from addresses where (mcid = '$accid')";

	$result = mysql_query ($query) or die("can not query table addresses - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	if ($rowcount == 0) {
		$country = "US";
		$street1 = "-street1-";
		$street2 = "";
		$city = "-city-";
		$state = "-state-";
		$postcode = "-zip-";
	}
	else {
		$a = mysql_fetch_array($result,MYSQL_ASSOC);
		$comment = $a['comment'];
		$street1 = $a['address1'];
		$street2 = $a['address2'];
		$city = $a['city'];
		$state = $a['state'];
		$postcode = $a['postcode'];
		$country = $a['country'];
	}

	// do the hard work now
	$idp = $from ; // found by phplint
	$emergencyccr = '';
	$einfo = '';
	$idpclause = "and (ccrlog.idp ='$idp') ";
	if ($idp=='')$idpclause=""; //wld - included doctype in query

	//echo "idp is $idp idpclause is $idpclause  ";
	// from svn 824
	    $query = "SELECT id,accid,idp,guid,status,DATE_FORMAT(date, '%c/%d/%Y %H:%i') as date,src,dest,subject,einfo,tracking,
              d.dt_type as doctype, d.dt_privacy_level as eccr_privacy 
              from ccrlog 
              left join document_type d on dt_account_id = accid  and ((dt_tracking_number = tracking) or (dt_guid = guid))
              where (accid = '$accid') and (status <> 'DELETED') $idpclause ORDER by id desc;";
	$result = mysql_query ($query) or die("can not query table ccrlog - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	//    echo "numrows is $rowcount";
	$errcount=0; $blurb = "";
	$emit = "";
	if ($result=="") {$emit= "?no accounts?"; return $emit;}

	while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
		$id = $l['id']; //record id
		$date = $l['date'];
		$idp = $l['idp'];

		$doctype = $l['doctype'];

		if (($miniview==false) and($idp!='')){
			// make a new tab if we've never seen this before
			$found = false;
			for ($i=0; $i<$count;$i++) {
				if ($tab[$i]==$idp){$found=true;
				break;}
			}
			if($found==false){ // open new tab
				$tab[$count++]=$idp;
			}
		}
		$from = $l['src'];
		$to= $l['dest'];
		/* if the destis too long, end it with ... */
		$sl = strlen($to);
		if ($sl>30) $to = substr($to,0,27)."...";
		$subject = $l['subject'];
		/* if the subject is too long, end it with ... */
		$sl = strlen($subject);
		if ($sl>50) $subject = substr($subject,0,47)."...";
		$guid = $l['guid'];
		$tracking = $l['tracking'];
		$status = $l['status'];
		$whereavailable = "only to the patient";
		if ($idp!='') $whereavailable = "to the patient and provider $idp";
		if ($status=='RED') {
			$emergencyccr = $guid;
			$einfo = $l['einfo'];
			$einfolen = strlen($einfo);
			// pin is "cleared" if the document is public in the document_type table
			$epinCleared = ($l['eccr_privacy'] && ($l['eccr_privacy'] == "Public")) ? "true" : "false";
			if($einfolen>0) {
				$einfo = substr($einfo,0,$einfolen-1).',"guid":"'.$guid.'", "epinCleared": '.$epinCleared.'}';
				//error_log($einfo); svn 824
			}
			$rowclass = "class='emergencyccr'
              title='this ccr will be offered on the back of your healthcare card for emergency use'"; 
		}
		else {
			$rowclass=" title = 'this ccr is available $whereavailable'";
		}
		$emailbuf .= emailtablerow($miniview,$rowclass,$id,$idp,$date,$to,$subject,$guid,$tracking,false);
		//plunk this into the correct tables, depending on the idp, put them all in table 0 as well
		$content[0].=$emit;
		for ($i=1;$i<$count;$i++) {
      if(!isset($content[$i])) { // required by e_strict
        $content[$i]="";
      }
      if ($tab[$i]==$idp) $content[$i].=$emit;
    }

		// Create row
    $row = new stdClass;
		$row->id = $id;
		$row->from = $from;
		$row->to = $to;
		$row->guid = $guid;
		$row->tracking = $tracking;
		$row->idp = $idp;
		$row->date = $date;
		$row->subject = $subject;
		$row->status = $status;

	
        $row->doctype = $doctype;

        if($doctype=="CURRENTCCR") {
          if(!$foundCurrentCCR) {
            $foundCurrentCCR = true;
            array_splice($all,0,0,array($row));
          }
        }
        else {
          // Add to ALL
          $all[] = $row;

          // Add to specific IDP
          $providers[$idp][] = $row;        
        }
		unset($row);
	}// end of processing each record

	$content = array(); // found by phplint
	$content[0]=ccrlog_table($providers,$all,$miniview); // and this .= bug found by phplint

	$i = 1;
	if(count($providers)>0) {
		foreach($providers as $provider) {
			$content[$i] = ccrlog_table($providers,$provider,$miniview);
			$i++;
		}
	}

	mysql_free_result($result);

	//errcount>0
	mysql_close();

	// special div will get poked with updated value here;
	$retemergencyccr = emergencyccr($emergencyccr); // prepare appropriate string based on whether we have one
	$epinCleared = "true"; // phpLint found this, not sure simon's intention
	$patientcard = patientCard($fn,$ln,$email,$accid,$einfo,$emergencyccr,$epinCleared);
	//
	return $count; // how many tabs we made
}

/**
 * This is the main display function for creating the Accounts CCR log table
 */
function ccrlog_table($providers,$rows,$miniview) {
	if($miniview) {
		$miniclass="miniview";
	}
	$providerCount = count($providers);
	ob_start();
  ?>
  
  <table class="ccrtable <?echo $miniclass?>" cellspacing="0" cellpadding="0">
    <tr>
      <th class='actioncell' width='12'>&nbsp;</th><th class='datecell'>Date</th><th class='guidcell'>Tracking</th>
      <? if(! $miniview) {
      	// if multiple providers, put them in the table
          if($providerCount > 1) { ?><th>Provider</th><?  } ?>
          <th>To</th><th>Subject</th>
      <? } ?>
    </tr>

    <? if(count($rows)>0) foreach($rows as $r) { 
    	$guid = $r->guid;
    	$tracking = $r->tracking;
    	$to = $r->to;
    	$subject = $r->subject;
    	$id = $r->id;
    	$prettyguid=prettyguid($guid);
    	$prettytrack=prettytrack($tracking);
    	$curl =$GLOBALS['Commons_Url'].'gwredirguid.php' ;
    	// This version asks for PIN
    	// $href = $curl."?guid=$guid&tracking=$tracking&free=$free";
    	// This version won't ask for PIN
    	$href =$GLOBALS['Commons_Url']."gwredirguid.php?guid=$guid&tracking=$tracking&free=true";


    	$whereavailable = "only to the patient";
    	if ($r->idp!='') $whereavailable = "to the patient and provider ".$r->idp;

    	if($r->status == "RED") {
    		$rowclass="class='emergencyccr' title='this ccr will be offered on the back of your healthcare card for emergency use'";
    		$redlink ="<a title='This is your emergency CCR, click here to remove' onclick='return clearccrpressed(\"$id\");'><img class='clickable' src='images/RedCross_16.gif' /></a>";
    	}
      else 
      if(isset($r->doctype) && ($r->doctype == "CURRENTCCR")) {
        $rowclass=" class='currentccr' title='this ccr is your current ccr'";
        $redlink="";
        $subject="Current CCR";
        $href.="&context=currentccr";
      }
      else {
        $rowclass=" title = 'this ccr is available $whereavailable'";
        $redlink="";
      }

    	// Write the actual table row
    	echo "
          <tr $rowclass>
                <td  class='actioncell' title='Click here to delete this CCR from your Account'>";
    	if(!$miniview) {
    		echo "<a  onclick='return trashpressed(\"$id\")'><img class='clickable' src='images/trash.gif' /></a> $redlink";
    	}
    	echo "</td>
                <td class='tndate'>$r->date</td>
                <td class='tncell'><a target='_new' href='$href'>$prettytrack</a></td>
                ";

    	if(! $miniview) {
    		if($providerCount > 1) { // if multiple providers, put them in the table
    			echo "<td class='prvcell'>".$r->idp."</td>";
    		}
          echo "<td>$to</td>
                <td class='subjcell'>$subject</td>
         </tr>"; //svn 824
}
     } ?>
  </table>
  <?
  $table = ob_get_contents();
  ob_end_clean();
  return $table;
}

/**
 * Queries for all 'special' documents associated with the users account and returns them
 * as an associative array indexed by type code.
 *
 * Note Emergency CCR is not returned as that is handled separately (for now).
 */
function getAccountDocuments($accid) {
	$types = array(); //phplint
	initdb();
	$query="select dt_id, dt_type, dt_guid, DATE_FORMAT(dt_create_date_time, '%c/%d/%Y %H:%i') as dt_create_date_time, dt_comment
          from document_type 
          where dt_account_id = '$accid' and dt_privacy_level <> 'Deleted' and dt_type <> 'Emergency CCR'
          order by dt_create_date_time desc";
	$result = mysql_query ($query) or die("can not query document_type table - ".mysql_error());
	while($dt = mysql_fetch_object($result)) {
		if(!$types[$dt->dt_type])
		$types[$dt->dt_type]=$dt;
	}
	mysql_free_result($result);
	return $types;
}
?>
