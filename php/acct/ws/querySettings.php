<?php
require_once "wslibdb.inc.php";
require_once "../alib.inc.php";
require_once "utils.inc.php";
require_once "mc.inc.php";
require_once "AccountSettings.inc.php";

/**
 * querySettingsWs 
 *
 * Returns account settings for the requested account.
 *
 * Inputs:
 *    accid - account id to check
 *
 * @author ssadedin@medcommons.net
 */

// NOTE: the xml below is probably going to end up needing to be escaped.
// In fact this should probably happen for every XML field.  Because the PHP
// is not regression tested I'm not brave enough to just plunk this into the higher
// level code as any places that are already escaping their xml will then break.
function xmlentities($string)
{
   return str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string );
}

class querySettingsWs extends dbrestws {

  function xmlbody(){
    // pick up and clean out inputs from the incoming args
    $accid = $this->cleanreq('accid');

    if(!is_valid_mcid($accid,true))
      $this->xmlend("Invalid account id format: $accid");

    // get basic account info
    $result = $this->dbexec(
      "select u.mcid as accid, u.first_name, u.last_name,
        u.email, u.photoUrl, u.enable_vouchers, u.active_group_accid,
        u.amazon_user_token, u.amazon_product_token, u.amazon_pid,
        p.practiceRlsUrl, p.providergroupid, p.practicename,
        gi.createdatetime as group_create_date_time
      from users u
      left join groupinstances gi on gi.accid = u.active_group_accid
      left join practice p on p.providergroupid = gi.groupinstanceid
      where mcid = '$accid'","cannot query users -");

    $user = mysql_fetch_object($result);

    $settings = ($user !== false) ? AccountSettings::load($user) : new AccountSettings();
    if($settings->coupon) {
      $voucher_details = $this->xmfield("voucherId",  $settings->coupon->voucherid). 
                           $this->xmfield("expirationDate",  $settings->coupon->expirationdate). 
                           $this->xmfield("otpHash",  sha1($settings->coupon->otp)). 
                           $this->xmfield("status",  $settings->coupon->status). 
                           $this->xmfield("providerAccId",  $settings->coupon->providerAccId). 
                           $this->xmfield("couponNum",  $settings->coupon->couponum); 
    }

    $logicalDocuments = "";
    foreach($settings->logicalDocuments as $doc) {
      $logicalDocuments.=$this->xmfield("document",$this->xmfield("type",$doc['type']).$this->xmfield("guid",$doc['guid']));
    }

    if(isset($GLOBALS['Directory_Url'])) {
      $todir=$GLOBALS['Directory_Url']."/ws/queryToDir.php?ctx=".$accid;
    }
    else {
      // ssadedin: a hack - we know the todir is hosted on the account server - 
      // compute the url from the account server url.
      $todir=$GLOBALS['Accounts_Url']."../groups/ws/queryToDir.php?ctx=".$accid;
    }

    if(isset($GLOBALS['Directory_Url'])) {
      $todir=$GLOBALS['Directory_Url']."/ws/queryToDir.php?ctx=".$accid;
    }

    $appsXML = "<applications>";
    foreach($settings->applications as $app) {
      $appsXML .= "<app><code>".xmlentities($app->ea_code)."</code><key>".xmlentities($app->ea_key)."</key><name>".xmlentities($app->ea_name)."</name></app>";
    }
    $appsXML.="</applications>\n";

    $this->xm(
      $this->xmfield ("outputs",
        $this->xmfield("status","ok").
        $this->xmfield("groupInstanceId",$settings->groupinstanceid). // For now first group only TODO: change protocol to return all groups
        $this->xmfield("groupAccountId",$settings->accid). // For now first group only TODO: change protocol to return all groups
        $this->xmfield("groupName",$settings->name). 
        $this->xmfield("groupCreateDateTime",isset($settings->createdatetime) ? $settings->createdatetime : ""). 
        $this->xmfield("firstName",$user?$user->first_name:""). 
        $this->xmfield("lastName",$user?$user->last_name:""). 
        $this->xmfield("email",$user?$user->email:""). 
        $this->xmfield("photoUrl",$user?$user->photoUrl:""). 
        $this->xmfield("amazonUserToken",$user?$user->amazon_user_token:""). 
        $this->xmfield("amazonProductToken",$user?$user->amazon_product_token:""). 
        $this->xmfield("amazonPid",$user?$user->amazon_pid:""). 
        $this->xmfield("photoUrl",$user?$user->photoUrl:""). 
        $this->xmfield("vouchersEnabled",($user && $user->enable_vouchers)?"true":"false"). 
        $this->xmfield("registry",$settings->practiceRlsUrl).
        $this->xmfield("statusValues",$settings->statusValues).
        $this->xmfield("directory","$todir").
        $this->xmfield("emergencyCcrGuid",$settings->emergencyCcrGuid). 
        $this->xmfield("creationRights", $this->xmfield("accountId",$settings->accid)). // send back group account for access rights
        (isset($voucher_details) ?  $this->xmfield("voucher", $voucher_details) : "").
        $this->xmfield("documents",$logicalDocuments).
        $appsXML
      )
    );
	}
}

// main
$x = new querySettingsWs();
$x->handlews("querySettings_Response");
?>
