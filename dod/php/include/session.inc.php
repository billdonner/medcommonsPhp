<?php

  /*
   * Function synopsis:
   *
   * strong_url ................. ($url)
   *
   * timestamp_url .............. ($url, $now = 0)
   * is_query_string_current      ($query, $seconds = 30, $now = 0)
   *
   * add_encrypted_query_string . ($url, $query, $key, $iv=False)
   * encrypt_urlsafe_base64       ($data, $key, $iv=False)
   * encrypt .................... ($data, $key, $iv=False)
   * get_encrypted_query_string   ($query_string, $key)
   * decrypt_urlsafe_base64 ..... ($data, $key)
   * decrypt                      ($data, $key)
   *
   * sign_query_string .......... ($secret, $url)
   * is_signed_query_string_valid ($secret, $query_string)
   *
   * add_to_url ................. ($url, $name, $value)
   */

require 'settings.php';  /* for $MCRYPT_RAND_SOURCE and $SECRET */

function strong_url($url) {
  global $SECRET;

  $qmpos = strpos($url, '?');
  $root = substr($url, 0, $qmpos); 
  $qs = substr($url, $qmpos + 1);
  $url = timestamp_url($url);
  $url = add_encrypted_query_string($root, $qs, $SECRET);
  return sign_query_string($SECRET, $url);
}

function timestamp_url($url, $now = 0) {
  if ($now === 0) $now = time();

  return add_to_url($url, 'ts', $now);
}

function is_query_string_current($query, $seconds = 30, $now = 0) {
  if ($now === 0) $now = time();

  parse_str($query, $d);

  return abs($now - (0 + $d['ts'])) <= $seconds;
}

function add_encrypted_query_string($url, $query, $key, $iv=False) {
  return add_to_url($url, "enc", encrypt_urlsafe_base64($query, $key, $iv));
}

function encrypt_urlsafe_base64($data, $key, $iv=False) {
  // Use 'url-safe' base64 encoding, cuz + and / interfere with URL encoding
  return str_replace(array('+', '/'),
		     array('-', '_'),
		     base64_encode(encrypt($data, $key, $iv)));
}

function encrypt($data, $key, $iv=False) {
  global $MCRYPT_RAND_SOURCE;
  if ($iv === False)
    $iv = mcrypt_create_iv(16, $MCRYPT_RAND_SOURCE);

  // PKCS #5/RFC 1423 padding
  $len = 16 - strlen($data) % 16;
  $data .= str_repeat(chr($len), $len);
  return $iv.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, // AES is 128-bit Rijndael
			    substr(hash('sha1', $key, TRUE), 0, 16), // key
			    $data, MCRYPT_MODE_CBC, $iv);
}

function get_encrypted_query_string($query_string, $key) {
  parse_str($query_string, $values);
  return decrypt_urlsafe_base64($values['enc'], $key);
}

function decrypt_urlsafe_base64($data, $key) {
  return decrypt(base64_decode(str_replace(array('-', '_'),
					   array('+', '/'),
					   $data)), $key);
}

function decrypt($data, $key) {
  $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,  // AES is 128-bit Rijndael
			 substr(hash('SHA1', $key, TRUE), 0, 16), // key
			 substr($data, 16), MCRYPT_MODE_CBC,
			 substr($data, 0, 16)); // initialization vector

  $len = strlen($data);
  return substr($data, 0, $len - ord($data{$len - 1}));
}

function sign_query_string($secret, $url) {
  $p = parse_url($url);
  $qs = $p['query'];
  return add_to_url($url, "hmac", hash_hmac('SHA1', $qs, $secret));
}

function is_signed_query_string_valid($secret, $query_string) {
  $i = strrpos($query_string, "hmac=");
  if ($i === false)
    $qs = '';
  else if ($i >= 0)
    $qs = substr($query_string, 0, $i - 1);
  else
    return False;

  $sig = substr($query_string, $i + 5);

  return strcasecmp($sig, hash_hmac('SHA1', $qs, $secret)) == 0;
}

function add_to_url($url, $name, $value) {
  if (strpos($url, '?') === FALSE)
    return "{$url}?{$name}={$value}";
  else
    return "{$url}&{$name}={$value}";
}

/**
 * Sign a url as a third party application.  Appends a hmac
 * parameter to the end of the url containing the signature as well
 * as a second parameter containing the application code.
 * <p/>
 * The returned URL will return true if passed to 
 * verify_external_application_url() provided the key and appcode
 * are registered on the appliance where the verification is performed.
 *
 * @param appcode - unique code for the application
 * @param key - secret key to sign with
 * @param url - url to sign
 * @return - signed url
 */
function sign_application_url($appcode,$key,$url) {
  $url = add_to_url($url,"APPCODE",urlencode($appcode));
  return sign_query_string($key,$url);
}

/**
 * Verify that the given 3rd party URL is valid.  The URL is expected
 * to contain a parameter 'APPCODE' specifying the unique code
 * for the application.
 *
 * @param url - url to verify, optional.  If null, will reconstruct
 *              current page's url from PHP predefined variables.
 */
function verify_external_application_url($url=null) {
  try {

    if($url == null) {
      $url = sprintf('http%s://%s%s',
        (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE ? 's': ''),
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI']
      );
    }

    global $pdo,$IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS;
    $pdo = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);
    $pdo->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Figure out the code from the url
    $p = parse_url($url);
    if(!$p || !isset($p['query']))
      return false;

    $qs = $p['query'];

    // HACK: remove any facebook signature params
    $qs = preg_replace("/&fb_sig[^&]*/","",$qs);

    if(preg_match("/APPCODE=([^&]*)/",$qs,$matches)==0)
      return false;

    $code = urldecode($matches[1]);

    $s = $pdo->prepare("select * from external_application where ea_code = ?");
    $s->bindParam(1, $code);
    $s->execute();

    $r = $s->fetch(PDO::FETCH_OBJ);
    if(!$r) {
      error_log("Url $url failed validation");
      return false;
    }

    $key = $r->ea_key;

    return is_signed_query_string_valid($key,$qs);
  }
  catch(Exception $e) {
    error_log("Exception while verifying url $url: ".$e->getMessage());
    return false;
  }
}

?>
