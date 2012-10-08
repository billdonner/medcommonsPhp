<?

require_once 'session.inc.php';
require_once 'settings.php';

/**
 * Returns true if the current user is logged on, false otherwise.
 */
function is_logged_in() {
  global $SECRET;

  if (isset($_COOKIE['mc'])) {
    parse_str(str_replace(',', '&', $_COOKIE['mc']), $values);

    if (isset($values['enc'])) {
      parse_str(decrypt_urlsafe_base64($values['enc'], $SECRET), $enc_values);

      return isset($enc_values['mcid']);
    }
  }

  return false;
}

/**
 * Returns account information about the current user, derived
 * from the mc cookie.
 */
function get_account_info() {
  if(!is_logged_in()) {
    return false;
  }
  $result = new stdClass;
  $result->accid="";
  $result->fn="";
  $result->ln = "";
  $result->email = "";
  $result->idp = "";
  
  $props = explode(',',$_COOKIE['mc']);

  for ($i=0; $i<count($props); $i++) {
    list($prop,$val)= explode('=',$props[$i]);
    switch($prop)
    {
      case 'mcid': $result->accid=$val; break;
      case 'fn': $result->fn = $val; break;
      case 'ln': $result->ln = $val; break;
      case 'email'; $result->email = $val; break;
      case 'from'; $result->idp = stripslashes($val); break;
      case 'auth'; $result->auth = $val; break;
    }
  }
	return $result;
}

/**
 * Returns true if the given account is a member of a practice group
 *
 * @throws Exception
 */
function is_practice_member($accid) {
  global $pdo,$IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS;
  if($pdo === null) {
    $pdo = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);
    $pdo->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  $sql = "SELECT count(*)
          from (practice q, groupmembers p, groupinstances i , users u)
          left join groupadmins ga on ga.adminaccid = u.mcid
          where p.memberaccid=? 
          and  q.providergroupid=i.groupinstanceid  
          and i.parentid>0 
          and  p.groupinstanceid= i.groupinstanceid 
          and p.memberaccid=u.mcid";
  $s = $pdo->prepare($sql);
  if(!$s) {
    $a = $pdo->errorInfo();
    throw new Exception("query $sql failed with Error Info: ".$a[2]);
  }

  $s->bindParam(1,$accid);

  if($s->execute()) {
    $row = $s->fetch();
    error_log("account $accid is member of ".$row[0]." accounts");
    return ($row[0]>0);
  }
  else {
    $a = $s->errorInfo();
    throw new Exception("query $sql failed with Error Info: ".$a[2]);
  }
}

/**
 * Checks if a parameter exists indicating that the currently set gateway should be
 * cleared, and if so, removes the associated cookie.
 *
 * @return true if the gateway was cleared, false otherwise.
 */
function check_clear_gw() {
  $cleargw = false;
  if(isset($_REQUEST['cleargw'])) {
    $cleargw = true;
    if(isset($GLOBALS['Script_Domain']) && ($GLOBALS['Script_Domain']!="") ) {
      setcookie("mcgw", "", 1,'/','.'.$GLOBALS['Script_Domain']);
    }
    else
      setcookie("mcgw", "", 1, '/');
  }

  return $cleargw;
}

/**
 * Checks if there is a currently active gateway page.  If so,
 * returns the URL for that gateway, otherwise returns false.
 */
function get_current_gateway_url() {
  $mcgw = isset($_COOKIE['mcgw']) && ($_COOKIE['mcgw']!='') ? $_COOKIE['mcgw'] : false;

  if($mcgw === false) {
    return false;
  }

  // Check it is for the right account
  $mcgws = explode(";",$mcgw);
  
  $accid = "00000000000000";
  if(is_logged_in()) {
    $info = get_account_info();
    $accid = $info->accid;
  }

  if(isset($mcgws[1]) && ($accid === $mcgws[1]) && ($mcgws[0]!=="null")) {
    return $mcgws[0];
  }
  else
    return false;
}

/**
 * Sets a cookie denoting the currently active gateway
 */
function set_current_gateway($url) {
  $accid = "00000000000000";
  if(is_logged_in()) {
    $info = get_account_info();
    $accid = $info->accid;
  }
  $gw=$url.";".$accid;
  if(isset($GLOBALS['Script_Domain']) && ($GLOBALS['Script_Domain']!="") ) {
    setcookie("mcgw", $gw, null,'/','.'.$GLOBALS['Script_Domain']);
  }
  else
    setcookie("mcgw", $gw, null,'/');
}

/**
 * Checks for a gateway cookie, if any, and if found, parses the
 * gateway host, port and path portion from the cookie and returns it.
 *
 * Note: currently depends on the gateway url having '/router" in the 
 * path - TODO: fix this.
 */
function current_gw_host() {
  // If no cookie then no gateway
  if(!isset($_COOKIE['mcgw'])) {
    return false;
  }

  // Cookie set, but so is param to clear the cookie - no gateway
  if(isset($_REQUEST['cleargw'])) {
    return false;
  }

  $mcgw = explode(";",$_COOKIE['mcgw']);
  $gwUrl = $mcgw[0];

  preg_match(",^https*://.*/router,",$_COOKIE['mcgw'], $hosts);
  if(count($hosts)==0) {
    error_log("Unable to parse host name from gateway cookie ".$_COOKIE['mcgw']);
    return false; 
  }
  return $hosts[0];
}


/**
 * Abbreviation for htmlspecialchars
 */
function hsc($x) {
  return htmlspecialchars($x);
}

/**
 * Abbreviation for escaping html special chars, *including*
 * quotes.
 *
 * @param String $x
 * @return quoted value
 */
function ent($x) {
    return htmlentities($x,ENT_QUOTES);
}

/**
 * Removes trailing slashes from a url
 */
function detrail($url) {
  return rtrim($url,"/");
}

/**
 * Return path from global settings in normalized fashion,
 * whereby trailing slashes are always removed.
 */
function gpath($path) {
  return rtrim($GLOBALS[$path],"/");
}

/**
 * Convenience function to get a clean variable from request
 * without any escaping.
 *
 * Returns null if not set.
 */
function req($x,$default=null) {

  $val = false;
  if(isset($_POST[$x]))
    $val = $_POST[$x];
  else
  if(isset($_GET[$x]))
    $val = $_GET[$x];

  if($val !== false) {
    if(is_array($val)) // don't try and strip stuff from array params
      return $val;
    else
      return get_magic_quotes_gpc() ? stripslashes($val) : $val;
  }
  else
    return $default;
}

/**
 * Return the input if the file exists, if not, resolve to parent directory
 */
function resolveUp($f) {
  if(!file_exists($f)) {
    $f = '../'.$f;
  }
  return $f;
}

/**
 * Send headers to prevent caching
 */
function nocache() {
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
}

/**
 * Renders a standard error page and then exits
 * Private message is sent to log file, not the 
 * page unless debug flags are set.
 *
 * Either the message or the privateMsg can be passed as an Exception
 * object.  In that case the actual message information is
 * extracted from the exception object.  This makes responding to
 * exceptions easy, as in:
 *
 * try {
 *   ...
 * }
 * catch(Exception $e) {
 *   error_page("Something went wrong, sorry!", $e);
 * }
 */
function error_page($msg,$privateMsg="") {

  require_once "template.inc.php";

  global $acApplianceMode;

  if($msg instanceof Exception) {
    $e = $msg;
    $msg = $e->getMessage();
    $privateMsg = $e->getMessage() . " in file " . $e->getFile() . " at line " . $e->getLine() . " Full trace: ".$e->getTraceAsString();
  }

  if($privateMsg instanceof Exception) {
    $e = $privateMsg;
    $privateMsg = $e->getMessage() . " in file " . $e->getFile() . " at line " . $e->getLine() . " Full trace: ".$e->getTraceAsString();
  }


  if(($acApplianceMode == 0) && ($privateMsg != ""))
    $msg .= "\n\nPrivate Message (DEBUG MODE):\n\n    ".$privateMsg;

  $_REQUEST["msg"]=$msg;
  $errorid = rand()."-".substr(time(),3);
  $_REQUEST["errorid"]=$errorid;
  error_log("SYSTEM ERROR: $privateMsg - (Error reported to user: $msg, error id $errorid)");

  ob_start();
  include ("error.php");
  $errorHTML = ob_get_contents();
  ob_end_clean();

  echo template("base.tpl.php")->set("content",$errorHTML)->fetch();
  exit;
}

set_exception_handler('error_page');

function validate_query_string() {
  $mc = $_COOKIE['mc'];
  $enc = req('enc');
  if(sha1($mc) != $enc) {
    error_log("## Invalid encoding ".$enc." received.  Expected ".sha1($mc));
    header("HTTP/1.0 400 Bad Request");
    echo "Bad Request";
    exit;
  }
}

/**
 * A wrapper around file_get_contents for getting URLs.  It specifically
 * checks the HTTP return code and throws exceptions if the call fails.
 */
function get_url($url) {
  dbg("fetching: $url");
  $result = @file_get_contents($url);
  list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);
  if($status_code >= 400)
    throw new Exception("Error ".$status_code." returned when attempting call $url");
  return $result;
}

/**
 * Send specified data using POST to specified url
 *
 * @param  $url         URL to send data to
 * @param  $postdata    data to send
 */
function post_url($url, $postdata) {
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	
	if(!isset($acVerifyGatewayCerts) || !$acVerifyGatewayCerts)
	  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	
	dbg("Sending ".strlen($postdata)." bytes of post data to $url");
	
	$output = curl_exec ($c);
	$info = curl_getinfo($c);
	
	if(($output === false) || ($info['http_code'] != 200)) {
      $curl_error = curl_error($c) ?  " : ".curl_error($c) : "";
	  curl_close ($c);     
	  throw new Exception("Error ".$info['http_code']." returned when attempting to call $url $curl_error");
	}	
	curl_close ($c);     
	return $output;
}

/**
 * Returns true if the given string appears to be a url
 */
function is_url($url) {
  return (preg_match(",^https*://,",$url) > 0);
}

/**
 * Return true if the given array of parsed url parts matches the given
 * pattern url.
 *
 * @param parsed_url - url to test for match, in array from as returned 
 *                     from parse_url function.
 * @param pattern_url - pattern url containing wildcards 
 */
function match_openid_url_pattern($parsed_url, $pattern_url) {

    $id_segments = array_reverse(explode(".", $parsed_url['host']));

    // Check match of all segments
    $parsed_pattern_url = @parse_url($pattern_url);
    if(!$parsed_pattern_url)
      return false;

    if(@$parsed_pattern_url['path'] !== @$parsed_url['path']) {
      @dbg("path {$parsed_pattern_url['path']} mismatched");
      return false;
    }

    if(@$parsed_pattern_url['query'] !== @$parsed_url['query']) {
      dbg("query mismatched");
      return false;
    }

    if(@$parsed_pattern_url['fragment'] !== @$parsed_url['fragment']) {
      dbg("fragment mismatched");
      return false;
    }

    $pattern_segments = array_reverse(explode(".",$parsed_pattern_url['host']));

    $i = 0;
    foreach($pattern_segments as $p) {
      if(($p !== "*") && ($p !== $id_segments[$i])) {
        dbg("segment $i mismatches ( $p != {$id_segments[$i]} )");
        return false;
      }
      ++$i;
    }
    return true;
}

/**
 * Verify the current request to check it's signature and tokens
 */
function verify_oauth_url() {
  $ruri = $_SERVER['REQUEST_URI'];
  $suri = $_SERVER['SCRIPT_URI'];
  $ruri_parts = explode("?",$ruri);

  // Get the query string from the ruri and append it to the script uri
  if(count($ruri_parts)<2)
    throw new Exception("Invalid OAuth request - missing query string");

  $client_url = $suri."?".$ruri_parts[1];

  dbg("Client url $client_url calculated from $ruri / $suri");

  // Call the verification service
  $result = get_url(gpath('Commons_Url')."/../api/ws/verifyOAuthRequest.php?url=".urlencode($client_url));
  if(preg_match(",<status>ok</status>,",$result)===0) {
    throw new Exception("OAuth verification call failed");
  }
  if(preg_match(",<verified>ok</verified>,",$result)===0) {
    throw new Exception("OAuth verification failed");
  }
  return true;
}


function check_email_address($email) {
  // First, we check that there's one @ symbol, and that the lengths are right
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
      return false;
    }
  }
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
      return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}

function dump($x) {
  ob_start();
  var_dump($x);
  $result = ob_get_contents();
  ob_end_clean();
  return $result;
}


/**
 * Send a given line to the log.  For now this is sending to the error
 * log, but we hopefully will figure out a better logging system in the
 * future (syslog?) once anybody gets time to look into it
 */
function dbg($m) {
  error_log("XXX: $m");
}

/**
 * Returns the permissions that the given auth token has to 
 * the specified account.  Makes web service call to 
 * /secure service to do this, so this is not a cheap 
 * operation.
 *
 * @throws Exception - if /secure call fails
 */

function getPermissions($auth,$toAccount) {
  $contents  = get_url(gpath('Commons_Url')."/ws/getPermissions.php?toAccount=$toAccount&auth=$auth");
  if(preg_match(",<rights>(.*)</rights>,",$contents,$rights)===0)
    throw new Exception("getPermissions call failed for account $toAccount and $auth with output ".$contents);
  return count($rights>0) ? $rights[1] : "";
}

/**
 * Adjust billing counters for the requested account, and
 * return current values after adjustment was made.
 *
 * @param  $btk     billing token. May be null, if so, billing token will be
 *                  inferred from account id and new billing token will be 
 *                  created if necessary.
 * @param  $faxin   number of fax credits to add / subtract
 * @param  $dicom   number of dicom credits to add / subtract
 * @param  $acc     number of account credits to add / subtract
 * @return array of fax, dicom and account credits
 */
function  adjust_billing_counters($btk,$faxin,$dicom,$acc) {
    
	require_once 'urls.inc.php';
	global $Secure_Url;
    
	if(!$btk)
		$btk=$acc;
		
    $str = get_url($Secure_Url."/modpay/wsCounters.php?btk=$btk&faxin=$faxin&dicom=$dicom&acc=$acc");
    $xml = simplexml_load_string($str);
    if(!$xml)
        throw new Exception("Unparseable result returned from call to billing counters service: ".$str);
        
    $counters = $xml->counters;
    $status = $counters->status;
    if($status!='1')
        throw new Exception("Bad status $status returned from billing counters service");
      
    return array ($faxin,$dicom,$acc);
}


/**
 * Utility class for handling validation failures
 */
class ValidationFailure extends Exception {

  public $errors = array();

  function __construct($msg) {
    $this->message = $msg;
    $errors[]=$msg;
  }
}

?>
