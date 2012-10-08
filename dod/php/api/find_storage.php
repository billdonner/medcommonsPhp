<?
/**
 * set_consents.php
 *
 * Sets consents for access to an account by a list of accounts or openids
 */
require_once "urls.inc.php";
require_once "utils.inc.php";
require_once "mc_oauth.inc.php";

$oauth = new OAuthServer(new AuthTokenOAuthDataStore());
$oauth->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());

try {
  $req = OAuthRequest::from_request();
  list($consumer, $token) = $oauth->verify_request($req);

  // list back the non-OAuth params
  $total = array();
  foreach($req->get_parameters() as $k => $v) {
    if(substr($k, 0, 5) == "oauth") 
      continue;
    $total[] = urlencode($k) . "=" . urlencode($v);
  }
  $queryString = implode("&", $total);
  // $queryString = "accid=1013062431111407";
  $result = file_get_contents(gpath('Accounts_Url')."/ws/resolveGateway.php?".$queryString);
  echo $result;
} 
catch (OAuthException $e) {
  print($e->getMessage() . "\n<hr />\n");
  print_r($req);
  die();
}
?>
