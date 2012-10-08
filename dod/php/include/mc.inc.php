<?

/*
 * /var/www/php/mc.inc.php
 * Copyright(c) 2007, MedCommons, Inc.
 *
 * MedCommons utility functions
 *
 * URL functions
 * -------------
 * get_request_url()    -- returns a best guess of URL used to access this
 * combine_urls($r, $u) -- returns an absolute url to $u, defaults from $r
 *
 * MCID functions
 * --------------
 * is_valid_mcid($str, $strict)  -- tests if a user-entered string is a valid MCID
 * clean_mcid($str)              -- returns a 16-digit string, no matter the format
 * pretty_mcid_$str)             -- punctuates a clean MCID
 *
 * Tracking Number functions
 * -------------------------
 * is_valid_tracking_number($str)  -- tests if a user-entered string is valid
 * clean_tracking_number($str)     -- returns a 12-digit string
 * pretty_tracking_number($str)    -- punctuates a clean tracking number
 */

/*
 * get_request_url()
 *
 * Retrieves the url most likely used by the browser to get this
 * script.
 *
 * Combines various _SERVER variables to build a sensible URL.
 *
 * Does NOT include fragments ( #foo ) or query strings ( ?bar )
 *
 * Examples:
 *
 * "http://localhost/foo.php"        => "http://localhost/foo.php"
 * "http://localhost:80/foo.php"     => "http://localhost/foo.php"
 * "http://localhost:81/bar.php"     => "http://localhost:81/bar.php"
 * "http://localhost/foo.php?a=2"    => "http://localhost/foo.php"
 */
function get_request_url() {
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
    $defaultPort = '443';
    $url = 'https://';
  }
  else {
    $defaultPort = '80';
    $url = 'http://';
  }

  $url .= $_SERVER['SERVER_NAME'];

  $port = $_SERVER['SERVER_PORT'];
  if ($port != $defaultPort)
    $url .= ':' . $port;

  return $url . $_SERVER['REQUEST_URI'];
}

/*
 * combine_urls($root, $url)
 *
 * Makes an absolute url out of $url, given the context $root.
 *
 * If $url is already a complete URL, with protocol schemes, hosts, etc.
 * then that is simply returned.
 *
 * Otherwise, the components of $root are used to construct a fully-
 * qualified URL.
 *
 * Examples:
 *
 * combine_urls("http://www.medcommons.net/foo/bar", "a.php")
 *    ==> "http://www.medcommons.net/foo/a.php"
 *
 * combine_urls("http://www.medcommons.net/foo/bar", "/a.php")
 *    ==> "http://www.medcommons.net/a.php"
 *
 * combine_urls("http://www.medcommons.net/foo/bar", "http://a.com/b")
 *    ==> "http://a.com/b"
 */
function combine_urls($root, $url) {
  /* if $url has protocol bit, ie 'http://' */
  if (preg_match('|^[a-z]+://|', $url) == 1)
    return $url;

  $p = parse_url($root);

  $result = $p['scheme'] . '://' . $p['host'];
  if (isset($p['port'])) $result .= ':' . $p['port'];

  /* is absolute? */
  if (substr_compare($url, '/', 0, 1) == 0)
    return $result . $url;

  if (isset($p['path'])) {

    $path = $p['path'];
    if(!preg_match("|/$|",$path)) {
      $path = dirname($path);
    }
    $result .= $path;
  }

  if(!preg_match("|/$|",$result))
    $result .= '/';

  return $result . $url;
}

/*
 * is_valid_mcid($str, $strict)
 *
 * Tests if a user-entered string is a valid MCID.
 *
 * By default ignores leading and trailing spaces, and correct mcid 'punctuation'.
 * If strict flag set true, requires exactly 16 digits.
 *
 * Examples:
 * is_valid_mcid("   0123-4567.8901 2345 ")   => True
 */
function is_valid_mcid($str, $strict=false) {

  if($strict)
    return preg_match("/^[0-9]{16}$/",$str) === 1;

  return preg_match("/^[ \t]*[0-9]{4}([\\. \t-]?[0-9]{4}){3}[ \t]*$/",
		    $str) == 1;
}

/*
 * clean_mcid($str)
 *
 * Returns a 16-digit string, no matter the format entered by the user
 *
 * Examples:
 * clean_mcid("0")                     => "0000000000000000"
 * clean_mcid("0123-4567 8901.2345")   => "0123456789012345"
 * clean_mcid("123-4567 8901.2345")    => "0123456789012345"
 */
function clean_mcid($str) {
  return substr('0000000000000000' . str_replace(array(' ', '.', '-', "\t"),
						 "", $str), -16);
}

/*
 * pretty_mcid($str)
 *
 * Formats a 16-digit string as an MCID.
 *
 * Example:
 * pretty_mcid("0123456789012345")   => "0123-4567-8901-2345"
 * pretty_mcid("123456789012345")    => "0123-4567-8901-2345"
 */
function pretty_mcid($str) {
  $mcid = clean_mcid($str);
  return substr(chunk_split($mcid, 4, "-"), 0, 19);
}

/*
 * is_valid_tracking_number($str)
 *
 * Tests if a user-entered string is a valid tracking number.
 *
 * Ignores leading and trailing spaces, and correct 'punctuation'
 *
 * Examples:
 * is_valid_tracking_number("   0123-4567.8901 ")   => True
 */
function is_valid_tracking_number($str) {
  return preg_match("/^[ \t]*[0-9]{4}([\\. \t-]?[0-9]{4}){2}[ \t]*$/",
		    $str) == 1;
}

/*
 * clean_tracking_number($str)
 *
 * Returns a 12-digit string, no matter the format entered by the user
 *
 * Examples:
 * clean_tracking_number("0")                => "000000000000"
 * clean_tracking_number("0123-4567 8901")   => "012345678901"
 * clean_tracking_number("123-4567 8901")    => "012345678901"
 */
function clean_tracking_number($str) {
  return substr('000000000000' . str_replace(array(' ', '.', '-', "\t"),
					     "", $str), -12);
}

/*
 * pretty_tracking_number($str)
 *
 * Formats a 12-digit string as an MCID.
 *
 * Example:
 * pretty_tracking_number("012345678901")   => "0123-4567-8901"
 * pretty_tracking_number("12345678901")    => "0123-4567-8901"
 */
function pretty_tracking_number($str) {
  $tn = clean_tracking_number($str);
  return substr(chunk_split($tn, 4, "-"), 0, 14);
}


/*
 * is_valid_guid($str)
 *
 * Tests if a user-entered string has valid format for a guid
 *
 * Examples:
 * is_valid_guid("abcd")   => false
 * is_valid_guid("2715e40210467ef092c81614a401cbcfa4af026a")   => true
 */
function is_valid_guid($str) {
  return preg_match("/^[a-f0-9]{40}$/",$str) === 1;
}

/*
 * is_safe_string($str1,$str2,$str3,...)
 *
 * Returns true iff each string is made up of a limited range of "safe" characters
 * including only alpha numeric characters, spaces underscores. The purpose
 * is to allow simple validation of 3rd party supplied data.
 *
 * Examples:
 * is_safe_string("abcd")   => true
 * is_safe_string("'%; delete from users;")   => false
 */
function is_safe_string() {
  $args = func_get_args();
  foreach($args as $a) {
    if(preg_match("/[a-zA-Z0-9_ ]*/",$a) === false)  {
      //error_log("input $a failed validation");
      return false;
    }
  }
  return true;
}

/**
 * decode_voucher_id($str)
 *
 * Extracts the index of the appliance from a voucher id, or 
 * returns false if the index could not be decoded.
 */
function decode_voucher_id($vid) {
  $v1 = ord(substr($vid,2,1))-ord('A');
  $v2 = ord(substr($vid,4,1))-ord('A');
  $v3 = ord(substr($vid,6,1))-ord('A');

  if(substr($vid,2,1)=='Z') {
    return false;
  }
  else {
    $serverid = ($v3*26+$v2)*26+$v1; // otherwise get embedded server id as number

    //echo "v1=$v1 v2=$v2 v3=$v3 $serverid";
    if($serverid <= 9999) {
      return $serverid;
    }
    else 
      die ("impossible vid $serverid");
  }
}
