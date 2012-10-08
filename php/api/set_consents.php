<?
/**
 * set_consents.php
 *
 * Sets consents for access to an account by a list of accounts or openids
 */
require_once "urls.inc.php";
require_once "utils.inc.php";
require_once "mc_oauth.inc.php";
require_once "JSON.php";

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

    dbg("para: $k");
  }
  $total[] = "auth=".$token->key;

  $queryString = implode("&", $total);
  $result = file_get_contents(gpath('Commons_Url')."/ws/updateAccess.php?".$queryString);

  // Check HTTP status code to make sure  we got the CCR back
  list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);
  if($status_code >= 400)
    throw new Exception("Internal Error ".$status_code." returned from internal consents call");

  echo $result;
} 
catch (Exception $e) {
  @$qs = $queryString;
  error_log("Failure while setting consents ($qs): ".$e->getMessage());

  $result = new stdClass;
  $result->status="failed";
  $result->message=$e->getMessage();
  $json = new Services_JSON();
  echo $json->encode($result);
  die();
}
?>
