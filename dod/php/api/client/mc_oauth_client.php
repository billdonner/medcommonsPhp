<?
/**
 * MedCommons PHP OAuth Client API
 *
 * Copyright MedCommons Inc. 2008
 *
 * $Id: mc_oauth_client.php 5293 2008-05-06 08:20:10Z ssadedin $
 */
require_once "./OAuth.php";
require_once "./JSON.php";

/**
 * Helper Class for Accessing the MedCommons Appliance API
 * 
 * Create an instance of ApplianceApi to access or update MedCommons accounts.
 *
 * To make API calls you need your registered consumer token details 
 * (key and secret) as well as an access token with authority to access 
 * the accounts you are accessing.
 *
 * The ApplianceApi class also gives some support for acquiring and authorizing 
 * access tokens.
 *
 * The sequence of calls over the lifetime of an oauth token look like so:
 *
 *   1. list(request_token,url) = ApplianceApi::authorize( key, secret, healthurl, callback ) 
 *
 *   2. ...(redirect to provided url for authorization page)...
 *
 *   3. // On callback page
 *      api = ApplianceApi::confirm_authorization(key, secret, request_token->key, request_token->secret, healthurl)
 *
 *   4. // On callback page or later, make api calls
 *      api->get_ccr(accid) ...
 *
 * @author Simon Sadedin <ssadedin@medcommons.net>
 */
class ApplianceApi {

  private $consumer = null;
  private $appliance_url = null;
  private $gateways = array();

  
  /**
   * Access token to be used in calls
   */
  public $access_token = null;

  /**
   * Create an ApplianceApi instance for accessing the given appliance.
   *
   * @param appliance_url   - base url of appliance to be accessed, eg. https://healthurl.myhealthespace.com
   * @param consumer_token  - token received from registering your application with the appliance
   * @param consumer_secret - token received from registering your application with the appliance
   * @param access_token    - access token received via OAuth
   * @param access_secret   - access secret received via OAuth
   */
  public function __construct($consumer_token, $consumer_secret, $appliance_url, $access_token=null, $access_secret=null) {
    $this->consumer = new OAuthConsumer($consumer_token, $consumer_secret);
    if($access_token != null) {
      $this->access_token = new OAuthToken($access_token, $access_secret);
    }
    $this->appliance_url = rtrim($appliance_url,"/");
  }


  /**
   * Redirect to authorization page for authorizing access to the given 
   * account, setting cookie with request token details.  Intended to 
   * be called in sequence prior to get_access_token().
   */
  public static function authorize($consumer_key, $consumer_secret, $healthurl, $callback) {
    list($base_url,$accid) = ApplianceApi::parse_health_url($healthurl);

    $api = new ApplianceApi($consumer_key, $consumer_secret, $base_url);
    $req_token = $api->get_request_token($accid);

    $loc = $base_url."api/authorize.php?oauth_token=".urlencode($req_token->key)."&oauth_callback=".urlencode($callback)."&accid=".urlencode($accid);
    return array($req_token,$loc);
  }

  /**
   * Exchanges a request token for an access token and returns
   * an instance of the API ready to use on the given healthurl.
   */
  static function confirm_authorization($consumer_key, $consumer_secret, $request_key, $request_secret, $healthurl) {
    
    list($base_url,$accid) = ApplianceApi::parse_health_url($healthurl);

    $api = new ApplianceApi($consumer_key, $consumer_secret, $base_url, $request_key, $request_secret);

    $result = $api->call($base_url . "api/access_token.php", array());

    if(preg_match("/oauth_token=[0-9a-zA-Z]{40}&oauth_token_secret=[0-9a-zA-Z]{40}/",$result)!==1)
      throw new Exception("Unable to exchange request token for access token: $result");

    // Parse the result to get the access token
    parse_str($result,$returned_token);

    return new ApplianceApi($consumer_key, $consumer_secret, $base_url, $returned_token['oauth_token'], $returned_token['oauth_token_secret']);
  } 

  /**
   * Find and return the gateway (storage location) for the given account id
   *
   * @return base URL to gateway for the given account
   * @throws Exception - if gateway cannot be resolved for account
   */
  public function find_storage($accid) {
    $result = $this->call_json($this->appliance_url."/api/find_storage.php",array("accid"=>$accid));
    if($result->status != "ok")
      throw new Exception("Failed to resolve storage for account $accid: ".$result->error);
    return $result->result;
  }

  /**
   * Find and return the Current CCR for the given account id
   *
   * @param accid -      Account ID of the user to return the CCR for
   * @return             json object representing requested ccr
   * @throws Exception - if request fails or if unable to locate storage 
   *                     for requested account.
   */
  public function get_ccr($accid) {
    if(!isset($this->gateways[$accid]))
      $this->gateways[$accid] = $this->find_storage($accid);

    return $this->call_json($this->gateways[$accid]."/ccrs/".$accid, array("json"=>"true"));
  }

  /**
   * Load the activity log for the requested account and return it as 
   * a JSON object.
   *
   * @param accid -      Account ID of the user to return the activity log for
   * @return             json object representing the activity log 
   *                     as an array of sessions, each with a list of events
   * @throws Exception - if request fails or if unable to locate storage 
   *                     for requested account.
   */
  public function get_activity($accid, $since = -1, $max = -1) {
    if(!isset($this->gateways[$accid]))
      $this->gateways[$accid] = $this->find_storage($accid);

    $params = array("json"=>"true","accid"=>$accid); 
    if($since >= 0) 
      $params["since"] = $since;

    if($max >= 0) 
      $params["max"] = $max;

    $result = $this->call_json($this->gateways[$accid]."/Activity.action", $params);
    if($result->status != "ok") {
      throw new Exception("Failed to load activity for account $accid from ".$this->appliance_url.":  ".$result->error);
    } 
    //error_log("got ".$result->status." sessions back");
    return $result->sessions;
  }

  /**
   * Share the given account's PHR using tracking number and PIN.
   *
   * @param accid - account to share (must be authorized to access with at least R permissions)
   * @param pin - PIN code to use to protect access (not sent to recipient)
   * @param email - email address to notify
   * @return object with 'trackingNumber' attribute equal to tracking number created
   */
  public function share_phr($accid, $pin, $email) {
    if(!isset($this->gateways[$accid]))
      $this->gateways[$accid] = $this->find_storage($accid);

    $result = $this->call_json($this->gateways[$accid]."/SharePHR.action", 
                array("fromAccount"=>$accid,"pin"=>$pin,"toEmail"=>$email,"auth"=>$this->access_token->key));

    if($result->status != "ok") 
      throw new Exception("Failed to share account $accid using pin $pin to email $email: ".$result->error);

    return $result;
  }

  /**
   * Disables / Deletes the given token along with all consents
   * that were granted to it.
   *
   * @param token - the token to be destroyed.  After this call token
   *                will no longer be useable, and rights associated 
   *                with the token will be rescinded.  A message
   *                will be written to activity log of accounts affected.
   */
  public function destroy_token($token) {
    $result = $this->call_json( $this->appliance_url."/api/destroy_token.php", array("token"=>$token));
    if($result->status != "ok") {
      throw new Exception("Failed to delete token $token from ".$this->appliance_url.":  ".$result->error);
    } 
  }

  /**
   * Return a request token for the given account.
   *
   * @param accid - MedCommons Account ID of request token to get
   */
  public function get_request_token($accid) {
    // Get a request token from the appliance
    $url = $this->appliance_url . "/api/request_token.php";
    $result =  $this->call($url, array());
    dbg("Received request token $result");
    $req_token = array();
    parse_str($result,$req_token);

    if(!isset($req_token['oauth_token'])) {
      //error_log("Failed to retrieve request token from ".$url." result = ".$result);
      throw new Exception("Unable to retrieve request token");
    }
    $token = new OAuthToken($req_token['oauth_token'], $req_token['oauth_token_secret']);
    return $token;
  }

  /**
   * Create a new HealthURL on the specified appliance.
   *
   * @return PHP object with attributes:
   *   patientMedCommonsId - MedCommons Account ID of created HealthURL account
   *   auth - access token for created patient
   *   secret - secret for access token
   *   
   * @throws Exception - if call fails or if HealthURL creation is not successful.
   */
  public function new_health_url($ln,$fn,$dob,$sex,$img) {
    $url = $this->appliance_url."/router/NewPatient.action";
    $result = call_json($url, array( "familyName" => $ln, "givenName" => $fn, "dateOfBirth" => $dob, "sex" => $sex ));
    if($result->status != "ok") 
      throw new Exception("Failed to create new HealthURL: ".$result->error);
    return $result;
  }
  
  /**
   * Execute the given url with given params, signed using the given access
   * token as an OAuth request.
   *
   * @return json object representing response
   * @throws Exception - if request fails (status code >= 400), or cannot be parsed
   */
  public function call_json($url, $params) {
    // Get the text content of the response
    $result = $this->call($url,$params);

    // Parse the JSON returned to us
    $json = new Services_JSON();
    $obj = $json->decode($result);
    if(!$obj) // failed to parse!
      throw new Exception("The format the object returned was invalid");
    return $obj;
  }

  /**
   * Execute the given url with given params, signed using the given access
   * token as an OAuth request.
   *
   * @return text content of response
   * @throws Exception - if request fails (status code >= 400)
   */
  public function call($url, $params) {
    $req = OAuthRequest::from_consumer_and_token($this->consumer, $this->access_token, "GET", $url, $params);
    $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, $this->access_token);
    error_log("executing OAuth call: ".$req->to_url());
    $result = @file_get_contents($req->to_url());

    list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);
    if($status_code >= 400)
      throw new Exception("Error ".$status_code." returned when attempting call $url");
    return $result;
  }

  /**
   * Signs a given url by adding oauth signature to it and returning the result.
   */
  public function sign($url) {
    $url_parts = parse_url($url);
    $name_value_pairs = isset($url_parts['query']) ? explode("&",$url_parts['query']) : array();
    $query_string_params = array();
    foreach($name_value_pairs as $nvp) {
      $param = explode('=',$nvp);
      if((count($param)==0) || ($param[0]===""))
        continue;
      $query_string_params[urldecode($param[0])] = count($param>1) ? urldecode($param[1]) : "";
    }

    if(!isset($url_parts['port'])) 
      $port = "";
    else 
      $port = ":".$url_parts['port'];

    $url_no_params = $url_parts['scheme']."://".$url_parts['host'].$port.$url_parts['path'];
    $req = OAuthRequest::from_consumer_and_token($this->consumer, $this->access_token, "GET", $url_no_params, $query_string_params);
    $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, $this->access_token);
    $signed_url = $req->to_url();
    // dbg("Authorized $url as $signed_url");
    return $signed_url;
  }

  /**
   * Returns HealthURL split into account id and base appliance url.
   */
  public static function parse_health_url($healthurl) {
    // Here we ASSUME the HealthURL is in the MedCommons proprietary format
    if(preg_match(",(http.*/)([0-9]{16}),i",$healthurl, $match)!=1) {
      throw new Exception("Provided HealthURL is not in expected format.");
    }
    return array($match[1],$match[2]);
  }
}
