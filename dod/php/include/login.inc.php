<?php

require_once 'template.inc.php';
require_once 'session.inc.php';
require_once 'urls.inc.php';
require_once 'JSON.php';
require_once 'settings.php';
require_once 'utils.inc.php';

/*
 * Redirect (HTTP 302) to another location.
 *
 * Expands $url to absolute if necessary.
 */
function redirect($url) {
  if (strncasecmp($url, "http://", 7) != 0 &&
      strncasecmp($url, "https://", 8) != 0) {

    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
      $prefix = 'https://';
    else
      $prefix = 'http://';

    $prefix .= $_SERVER['HTTP_HOST'];

    if ($url[0] != '/')
      $prefix .= rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';

    $url = $prefix . $url;
  }

  header("Location: " . $url);

  exit;
}


/**
 * Utility class to help with loading, logging in and verifying Users
 */
class User {

  /**
   * Flag in state bitmask indicating that user has not confirmed their email address
   */
  public static $UNCONFIRMED = 4;

  /**
   * Flag in state bitmask indicating that user has supplied a photo
   */
  public static $HASPHOTO = 8;

  /**
   * Flag in state bitmask indicating that user has supplied a photo
   */
  public static $HASSIMTRAK = 16;

  public $mcid;
  public $email;
  public $first_name = '';
  public $last_name = '';

  /* source of identity */
  public $source_name = 'MedCommons';
  public $user_id;
  public $authToken;

  public $hasServices = false;
  public $hasVouchers = false;
  public $hasPhoto = false;
  public $hasSimtrak = false;

  public static function compute_password($mcid,$pw1) {
    $sha1 = strtoupper(hash('SHA1', 'medcommons.net' . $mcid . $pw1));
    return $sha1;
  }

  /**
   * Connect to mysql
   * TODO: move it to PDO, get rid of this ugly code
   */
  private static function connect_db() {
    global $IDENTITY_HOST,$IDENTITY_DB,$IDENTITY_USER,$IDENTITY_PASS;
    mysql_pconnect($IDENTITY_HOST, $IDENTITY_USER, $IDENTITY_PASS)
      or die ("can not connect to mysql");
    mysql_select_db($IDENTITY_DB) or die ("can not connect to database $IDENTITY_DB");
  }

  /**
   * Attempts to load details of user with mcid = mcid
   *
   * @throws Exception - if fails to load user, or user not found
   * @return - a User object with fields initialized
   */
  public static function load($mcid) {
    User::connect_db();

    if(preg_match("/^[0-9]{16}$/",$mcid)!==1)
      throw new Exception("account id $mcid in unexpected format");

    $q="select u.* from users u where u.mcid = $mcid";
    $result = mysql_query($q);
    if(!$result) 
      throw new Exception("Failed to load user $mcid : ".mysql_error());

    $obj = mysql_fetch_object($result);
    if(!$obj) 
      throw new Exception("User $mcid not found");

   	$u = new User();
    $u->mcid = $mcid;
    $u->email = $obj->email;
    $u->first_name = $obj->first_name;
    $u->last_name = $obj->last_name;
    $u->acctype = $obj->acctype;
    $u->hasVouchers = ($obj->enable_vouchers == 1);
    $u->dashboard_mode = ($obj->active_group_accid == null ? 'patient' : 'group');
    $u->hasPhoto = ($obj->photoUrl != null) && ($obj->photoUrl != '');
    $u->hasSimtrak = ($obj->enable_simtrak != null) && ($obj->enable_simtrak != 0);
    return $u;
  }

  /**
   * Sets the cookie(s) that identify this logged-in user
   * to other pages, then, if provided, redirects to the specified url.
   */
  public function login($url = null) {
    global $SECRET, $acCookieDomain;

    if(isset($this->mcid) && $this->mcid &&  (!isset($this->authToken) || ($this->authToken == null) || ($this->authToken == ''))) {
      dbg("auth not set:  creating new auth for account $this->mcid");
      $t = new stdClass;
      $this->authToken = get_authentication_token($this->mcid, $t);
      dbg("Got new auth $this->authToken");
    }

    $mc = 'mcid=' . $this->mcid;
    $mc .= ',from=' . $this->source_name;

    if ($this->first_name)
      $mc .= ',fn=' . $this->first_name;
    if ($this->last_name)
      $mc .= ',ln=' . $this->last_name;
    $mc .= ',email=' . $this->email;
    $mc .= ',auth=' . $this->authToken;
    $state = 0;
    if($this->hasServices)
      $state |= 1;
    if($this->hasVouchers)
      $state |= 2;
    if(($this->email == null || $this->email == "") && ($this->acctype !== "VOUCHER")) 
      $state |= User::$UNCONFIRMED;
    if($this->hasPhoto) 
      $state |= User::$HASPHOTO;
    if($this->hasSimtrak)
      $state |= User::$HASSIMTRAK;

    dbg("state = $state , email = ".$this->email);

    $mc .= ',s=' . $state;

    $mcenc = 'mcid=' . $this->mcid;
    $mcenc .= '&from=' . $this->source_name;

    if ($this->first_name)
      $mcenc .= '&fn=' . $this->first_name;

    if ($this->last_name)
      $mcenc .= '&ln=' . $this->last_name;

    $mcenc .= '&email=' . $this->email;
    $mcenc .= '&auth=' . $this->authToken;

    $mcenc = encrypt_urlsafe_base64($mcenc, $SECRET);

    $mc .= ',enc=' . $mcenc;

    $cookie = 'Set-Cookie: mc=' . rawurlencode($mc) . '; path=/';

    if ($acCookieDomain)
      $cookie .= '; domain=' . $acCookieDomain;

    header($cookie);

    // Set patient mode cookie
    if((!is_practice_member($this->mcid) && !$this->hasVouchers) || ($this->dashboard_mode == 'patient')) {
      setcookie("mode","p",0, "/");
    }
    else
      setcookie('mode', False, 1, '/');

    if($url) {
      redirect($url);
      exit;
    }
  }
}

function show_login($next) {
  $m = template($GLOBALS['layout_tpl_php']);
  $m->set('__title', 'MedCommons - Login');
  $m->set('__description', 'MedCommons Login Form');
  $m->set('__keywords', '');
  $m->set('__topics', '');
  $m->set('__phtml', 'html');
  $m->set('__searchdef', '???');
  $t = template('login.tpl.php');
  $t->set('mcid', '');
  $t->set('next', $next);
  $m->set('content', $t->fetch());
  echo $m->fetch();
  exit;
}

/**
 * Gets the MCID of the logged-in user.
 *
 * Returns FALSE if not logged in.
 */
function get_login_mcid() {
  global $SECRET;

  if (isset($_COOKIE['mc'])) {
    parse_str(str_replace(',', '&', $_COOKIE['mc']), $values);

    if (isset($values['enc'])) {
      parse_str(decrypt_urlsafe_base64($values['enc'], $SECRET), $enc_values);
      return $enc_values['mcid'];
    }
  }

  return False;
}

/**
 * Tests if the user is logged in.
 *
 * If the user is logged in, returns the MCID.
 *
 * Otherwise, redirects to the login page.
 */
function login_required($page) {
  $mcid = get_login_mcid();

  if ($mcid) return $mcid;

  show_login($page);
}

/**
 * Calls the secure server to create an authentication token for the
 * specified user.  The token is returned.
 *
 * @param mcid - account id for which the token should be created, may be array of accounts
 * @param t  - template which will be set with error messages on failure under "error" key
 * @param u  - optional additional information about the user.  If supplied then will be
 *             recorded with the auth token for later reference.  Used when logging in from 
 *             3rd party IDPs (eg. Facebook).
 */
function get_authentication_token($accts, &$t, $u = null) {
  global $IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS;

  if(!is_array($accts)) {
    $accts = array($accts);
  }

  $json = new Services_JSON();

  $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS,
		$DB_SETTINGS);

  $stmt = $db->prepare("SELECT gi.accid ".
		       "FROM groupmembers gm, groupinstances gi ".
		       "WHERE gm.memberaccid = :mcid ".
		       "AND gm.groupinstanceid = gi.groupinstanceid");

  $groups = array();
  foreach($accts as $mcid) {
    if ($stmt && $stmt->execute(array("mcid" => $mcid))) {
      // Build array of groups
      $groups[]=$mcid;
      while ($group = $stmt->fetch())
        $groups[]="g:".$group[0];
    }
    else {
      $e = $stmt->errorInfo();
      error_log("Unable to query for groups for user $mcid: ".$e[2]);
      if($t)
        $t->set("error","Internal system error.");
      return false;
    }
  }

  $authServiceUrl = rtrim($GLOBALS['Commons_Url'],"/")."/ws/createAuthenticationToken.php?accountIds=".implode(",",$groups);
  if($u != null) {
    $authServiceUrl .= "&fn=".urlencode($u->first_name)."&ln=".urlencode($u->last_name);
    if($u->source_name) {
      $authServiceUrl .= "&idp=".urlencode($u->source_name);
      $authServiceUrl .= "&idp_id=".urlencode($u->user_id);
    }
  }
  dbg("creating token using url ".$authServiceUrl);
  $jsonResult = file_get_contents($authServiceUrl);
  $token = "";
  if($jsonResult) {
    dbg("auth token result: $jsonResult");
    $authResult = $json->decode($jsonResult);
    if(! $authResult) {
      error_log("Unable to parse JSON received from call:  ".$jsonResult);
      if($t)
        $t->set("error","Authentication system failure.");
      return false;
    }
    if($authResult->status == "ok") {
        $token = $authResult->result;
    }
    else {
      if($t)
        $t->set("error","Authentication service failure.");
      return false;
    }
  }
  else {
    error_log("authentication token service failed.");
    if($t)
      $t->set("error","Authentication system failure.");
    return false;
  }
  return $token;
}


$ID_IS_BLANK = '';
$ID_IS_MCID = 'mcid';
$ID_IS_TRACKING_NUMBER = 'tracking number';
$ID_IS_PIN = 'pin';
$ID_IS_EMAIL_ADDRESS = 'email address';
$ID_IS_OPENID_URL = 'openid url';
$ID_IS_PHONE = 'phone';

$MCID_RE = '^([0-9]{4}[\.,-_ ]?){3}[0-9]{4}$';
$TRACKING_NUMBER_RE = '^([0-9]{4}[\.,-_ ]?){2}[0-9]{4}$';
$PIN_RE = '^([0-9][\.,-_ ]?){5}$';
$EMAIL_ADDRESS_RE = '^[^/]+\@';
$PHONE_NUMBER_RE = '^\(?[0-9]{3}\)?[- \.]?[0-9]{3}[- \.]?[0-9]{4}';

function id_type($q) {
  global $ID_IS_BLANK, $ID_IS_MCID, $ID_IS_TRACKING_NUMBER, $ID_IS_PIN,
         $ID_IS_EMAIL_ADDRESS, $ID_IS_OPENID_URL, $ID_IS_PHONE;

  if ($q == '') return $ID_IS_BLANK;

  if (ctype_digit($q[0])) {
    if (is_mcid($q)) return $ID_IS_MCID;
    if (is_tracking_number($q)) return $ID_IS_TRACKING_NUMBER;
    if (is_pin($q)) return $ID_IS_PIN;
  }
  if (is_phone_number($q)) return $ID_IS_PHONE;
  if (is_email_address($q)) return $ID_IS_EMAIL_ADDRESS;

  return $ID_IS_OPENID_URL;
}

function is_mcid($q) {
  global $MCID_RE;
  return strlen($q) >= 16 && ctype_digit($q[0]) && ereg($MCID_RE, $q);
}

function is_tracking_number($q) {
  global $TRACKING_NUMBER_RE;
  return strlen($q) >= 12 && ctype_digit($q[0]) && ereg($TRACKING_NUMBER_RE, $q);
}

function is_pin($q) {
  global $PIN_RE;
  return strlen($q) >= 5 && ctype_digit($q[0]) && ereg($PIN_RE, $q);
}

function is_email_address($q) {
  global $EMAIL_ADDRESS_RE;
  return $q != '' && ereg($EMAIL_ADDRESS_RE, $q);
}

function is_phone_number($q) {
  global $PHONE_NUMBER_RE;
  dbg("Checking value $q against regex $PHONE_NUMBER_RE");

  if(ereg($PHONE_NUMBER_RE, $q)) {
    dbg("yes match!");
    return true;
  }
  else {
    dbg("no match!");
    return false;
  }
}

function is_openid_url($q) {
  global $ID_IS_OPENID_URL;
  return id_type($q) == $ID_IS_OPENID_URL;
}

?>
