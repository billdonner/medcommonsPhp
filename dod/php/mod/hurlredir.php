<?
/**
 * A redirection script that will sign a healthurl upon request and redirect to it
 * using the previously recorded oauth token for that account.
 *
 * @param cnum - coupon number of healthurl to sign and redirect to
 * @todo - need to apply security to this
 */
require_once "setup.inc.php";
require_once "mc_oauth_client.php";
require_once "db.inc.php";
require_once "utils.inc.php";

nocache();
// bill 2 july - temporarily allow not logged in users to access
//list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

try {
  $cnum = req('cnum');
  if(!$cnum || (preg_match("/^[0-9]{1,12}$/",$cnum)!==1))
    throw new Exception("Invalid value for cnum: $cnum");

  $db = DB::get();

  // Find the coupon specified
  // Join to accid is to ensure only correct user can access the voucher healthurl
  
  // removed extra test for accid temporarily to allow not logged on users to access
  //  and s.accid = ?
  $cpns = $db->query("select * 
                      from modcoupons m, modservices s
                      where m.couponum = ?
                    
                      and m.svcnum = s.svcnum",array($cnum));
  if(count($cpns)==0) 
    throw new Exception("Unknown coupon $cnum");

  $c = $cpns[0];
  $hurl_parts = ApplianceApi::parse_health_url($c->hurl);
  $api = new ApplianceApi($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],
                          $hurl_parts[0], $c->auth, $c->secret);

  // If accessed by patient on voucher page, pass context parameter
  // to gateway, which tells it to offer upgrade / copy link
  $hurl = $c->hurl;
  if(isset($_GET['c'])) {
    $hurl .= "?c=".$_GET['c'];
  }
  if(isset($_GET['m'])) {
    $hurl.="?mode=".urlencode($_GET['m']);
  }
  $signed_url = $api->sign($hurl);

  dbg("Redirecting to $signed_url");

  header("Location: $signed_url");
}
catch(Exception $e) {
  echo "<html><body><h3>Error Occurred</h3><p>{$e->getMessage()}</p></body></html>";
}
?>
