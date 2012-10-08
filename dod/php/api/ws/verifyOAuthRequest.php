<?php
require_once "wslibdb.inc.php";
require_once "../mc_oauth.inc.php";
require_once "utils.inc.php";

/**
 * verifyOAuthRequestWs 
 *
 * Verifies the signature on the given request and that the token is a valid
 * OAuth token.
 *
 * @param url - client supplied url (may contain oauth parameters or not)
 * @param oauth_consumer_key - oauth parameter provided by client
 * @param oauth_token - oauth parameter provided by client
 * @param oauth_signature_method - oauth parameter provided by client
 * @param oauth_signature - oauth parameter provided by client
 * @param oauth_timestamp - oauth parameter provided by client
 * @param oauth_nonce - oauth parameter provided by client
 * @param oauth_version - oauth parameter provided by client
 *
 * @author ssadedin@medcommons.net
 */
class verifyOAuthRequestWs extends dbrestws {

  function xmlbody(){

    $url = req('url');
    $verified = "ok";

    try {
      $this->validate($url);
    }
    catch(Exception $ex) {
      dbg("Failed to validate url $url: ".$ex->getMessage());
      $verified = "failed";
    }

    // echo inputs
    $this->xm($this->xmfield ("inputs",
      $this->xmfield("url", htmlentities($url)).

      // return outputs
      $this->xmfield ("outputs", $this->xmfield("status","ok").$this->xmfield("verified", "$verified"))));
  }

  function validate($url) {

    dbg("validating url $url");
    $url_parts = parse_url($url);

    dbg("scheme is ".$url_parts['scheme']);

    $name_value_pairs = explode("&",$url_parts['query']);
    $query_string_params = array();
    foreach($name_value_pairs as $nvp) {
      $param = explode('=',$nvp);

      // ssadedin: ignore facebook signature parameters - they are the work of the devil
      if(preg_match("/^fb_sig.*$/",$param[0])===0) {
        $query_string_params[urldecode($param[0])] = count($param>1) ? urldecode($param[1]) : "";
      }
    }

    // Check that the token exists and that it 
    $port = $url_parts['port'];
    if(!isset($url_parts['port']) || ($port === "80") || ($port == "443") || ($port == "")) 
      $port = "";
    else 
      $port = ":$port";

    $url_no_params = $url_parts['scheme']."://".$url_parts['host'].$port.$url_parts['path'];

    dbg("Validating request for url $url_no_params with ".count($query_string_params)." query parameters, consumer=".$query_string_params['oauth_consumer_key']);

    $oauth = new OAuthServer(new AuthTokenOAuthDataStore());
    $oauth->add_signature_method(new OAuthSignatureMethod_HMAC_SHA1());
    $req = new OAuthRequest("GET", $url_no_params, $query_string_params);
    list($consumer, $token) = $oauth->verify_request($req);

    return array($consumer,$token);
  }
}

nocache();
$x = new verifyOAuthRequestWs();

if(!isset($_REQUEST['mc_unittest'])) {
  $x->handlews("verifyOAuthRequest_Response");
}
else {
  $cons_key = "e0f2e36173ff6f79f8d3aa6f5f00bb87c324099f";
  $cons_secret = "unit test";
  $req_key = "123456789012345678901234567890";
  $req_secret  = "secret";

  $consumer = new OAuthConsumer($cons_key, $cons_secret);
  $req_token = new OAuthToken($req_key, $req_secret);

  $req = OAuthRequest::from_consumer_and_token($consumer, $req_token, "GET", "http://yowie:7080/mctest/1013062431111407", array("cat"=>"dog"));
  $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $req_token);
  $url = $req->to_url();
  list($result_consumer, $result_token) = $x->validate($url);
?>
  <html><body>
    <p>URL for OAuth call is <?=$url?></p>
    <p>Result token is <?=$result_token->key?></p>
  </body>
  </html>
<?
}
?>
