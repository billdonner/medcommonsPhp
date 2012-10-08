<?
 /**
  * Redirects to the gateway for a specified account, passing through to the specified url and
  * appending appropriate accid, auth and other parameters
  *
  * @param dest - relative url on gateway to redirect to
  * @author ssadedin
  */
require_once "utils.inc.php";
require_once "alib.inc.php";

aconnect_db();
$url=req('dest');
$mcid = req('accid','');
$info = get_account_info();

// Logged in?  Send them to the gateway
if($info && (($mcid=='') || ($info->accid == $mcid))) {
  
  $gw=allocate_gateway($info->accid);

  if(strstr($url,"?"))
    $url .= "&";
  else 
    $url .="?";

  $url.="accid=".$info->accid;
  $url.="&auth=".$info->auth;
  header("Location: ".$gw."/".$url);
}
else {
  header("Location: login.php?mcid=$mcid&next=".urlencode("gwredir.php?dest=".urlencode($url)));
}
