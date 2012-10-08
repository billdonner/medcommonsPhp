<?php
require_once "dbparams.inc.php";

function strong_url($url)
{
$qmpos = strpos($url,'?');
$root = substr($url, 0,$qmpos); 
$qs = substr($url,$qmpos + 1);
$url = timestamp_url($url);
$url = add_encrypted_query_string($root, $qs, 'secret');
return sign_query_string('secret', $url);
	
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

  // Use 'url-safe' base64 encoding, cuz + and / interfere with URL encoding
  return add_to_url($url, "enc",
		    str_replace(array('+', '/'), array('-', '_'),
				base64_encode(encrypt($query, $key, $iv))));
}

function encrypt($data, $key, $iv=False) {
  if ($iv === False) {
    //$iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
    if (isset($GLOBALS['MCryptRandom']))
    $iv = mcrypt_create_iv(16, $GLOBALS['MCryptRandom'] ? $GLOBALS['MCryptRandom'] :MCRYPT_DEV_URANDOM );
    else 
        $iv = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM );
  }

  // PKCS #5/RFC 1423 padding
  $len = 16 - strlen($data) % 16;
  $data .= str_repeat(chr($len), $len);
  return $iv.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, // AES is 128-bit Rijndael
			    substr(hash('sha1', $key, TRUE), 0, 16), // key
			    $data, MCRYPT_MODE_CBC, $iv);
}

function get_encrypted_query_string($query_string, $key) {
  parse_str($query_string, $values);
  return decrypt(base64_decode(str_replace(array('-', '_'), array('+', '/'),
					   $values['enc'])), $key);
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

?>
