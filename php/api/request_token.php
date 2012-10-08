<?
/**
 * request_token.php
 *
 * returns a request token for a consumer to begin the authorization process
 */
require_once "urls.inc.php";
require_once "utils.inc.php";
require_once "mc_oauth.inc.php";
require_once "JSON.php";

$oauth = new OAuthServer(new AuthTokenOAuthDataStore());
$oauth->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());

try {
  $req = OAuthRequest::from_request();
  $token = $oauth->fetch_request_token($req);
  echo $token;
} catch (OAuthException $e) {
  print($e->getMessage() . "\n<hr />\n");
  print_r($req);
  die();
}

