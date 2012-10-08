<?php
// wld 08-12-08 always return post succesful to dataoncall

require 'mc.inc.php';
require 'JSON.php';
require 'settings.php';

$CENTRAL_DB = 'mcglobals';

$IDENTITY_HOST = $CENTRAL_HOST;
$IDENTITY_DB = $CENTRAL_DB;
$IDENTITY_USER = $CENTRAL_USER;
$IDENTITY_PASS = $CENTRAL_PASS;

$DB_SETTINGS = array(
		     // PDO::ATTR_PERSISTENT => true,
		     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		     );
		     
		     

$CENTRAL_PDO  = 'mysql:host=' . $CENTRAL_HOST  . ';dbname=' . $CENTRAL_DB;
$IDENTITY_PDO = 'mysql:host=' . $IDENTITY_HOST . ';dbname=' . $IDENTITY_DB;



function decode_fax($code) {
  // Use 'url-safe' base64 encoding
  
  $code = str_replace(array('-', '_'), array('+', '/'), $code);
  $code = base64_decode($code);
  error_log("fax.info.data $code");
  $a = unpack('N*v', $code);
  $mcid = sprintf("%08d%08d", $a['v1'], $a['v2']);
  return array("mcid" => $mcid, "n" => $a['v3']);
}

function gateway_url($mcid, $defaultUrl) {
  global $CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS;

  $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

  $sql = "SELECT appliances.url, appliances.name ".
    "FROM alloc_log, appliances, alloc_numbers ".
    "WHERE alloc_log.numbers_id = alloc_numbers.id AND ".
    "      alloc_numbers.name = 'mcid' AND ".
    "      alloc_log.seed = ($mcid - alloc_numbers.base) div ".
    "                       alloc_numbers.leap AND ".
    "      appliances.id = alloc_log.appliance_id";

  $s = $db->prepare($sql);

  if (!$s) {
    $e = $db->errorInfo();
    throw Exception($e[2]);
  }

  if (!$s->execute()) {
    $e = $s->errorInfo();
    throw Exception($e[2]);
  }

  $row = $s->fetch();

  if ($row) {
    $url = $row['url'];

    if (!$url)
      $url = 'https://' . $row['name'];

    $url = combine_urls($url,
			'acct/ws/queryAccountNode.php?accid=' . $mcid);

    $json = new Services_JSON();
    $o = $json->decode(file_get_contents($url));

    if ($o->status == 'ok')
      return $o->gw;
  }

  return $defaultUrl;
}

header('Content-Type: text/html');

if (!isset($_POST['xml'])) {
  error_log('fax.error.post: Error: requires "xml" post variable');
  echo "Post Successful";
  exit;
}

$dom = @simplexml_load_string($_POST['xml']);

if (!$dom) {
  error_log('fax.error.xml: Error: cannot parse XML');
  echo "Post Successful";
  exit;
}

try {
  $key = $dom->FaxControl->BarcodeControl->Barcodes->Barcode->Key;
} catch (Exception $ex) {
  error_log('fax.error.barcode: Error: no barcode');
  echo "Post Successful";
  exit;
}

$data = decode_fax($key);
$mcid = $data['mcid'];
$dom->FaxControl->BarcodeControl->Barcodes->Barcode->Key = 'MC/' . $data['n'];

$url = gateway_url($mcid, 'http://tenth.medcommons.net/');
$url = combine_urls($url, 'router/DataOnCallServlet');

error_log("fax for $mcid posted to $url");
http_post_fields($url, array("xml" => $dom->asXML()));

echo "Post Successful";

?>
