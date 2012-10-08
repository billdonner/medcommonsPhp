<?php

require 'login.inc.php';
require 'mc.inc.php';

$CENTRAL_PDO = 'mysql:host=mysql.internal;dbname=mcglobals';
$CENTRAL_USER = 'mc_globals';
$CENTRAL_PASS = '';
$DB_SETTINGS = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);

if (isset($_POST) && isset($_POST['openid_url']))
  $q = trim($_POST['openid_url']);
else if (isset($_REQUEST['q']))
  $q = trim($_REQUEST['q']);
else
  $q = '';

$error = False;

function query_sql($sql, $args) {
  global $error, $CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS;

  $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);
  $s = $db->prepare($sql);
  if (!$s) {
    $e = $db->errorInfo();
    $error = $e[2];
  }
  else if (!$s->execute($args)) {
    $e = $s->errorInfo();
    $error = $e[2];
  }
  else {
    $row = $s->fetch();

    if ($row) return $row;

    $error = 'Unknown name';
  }

  return False;
}

function query_key($key) {
  $sql = <<<EOF
SELECT appliances.name, appliances.url, appliance_users.mcid
FROM   alloc_log, appliances, alloc_numbers, appliance_users
WHERE  appliance_users.name = :key AND
       alloc_numbers.name = 'mcid' AND
       alloc_log.seed = (appliance_users.mcid - alloc_numbers.base) DIV
                         alloc_numbers.leap AND
       alloc_log.numbers_id = alloc_numbers.id AND
       appliances.id = alloc_log.appliance_id
EOF;

  return query_sql($sql, array('key' => $key));
}

function query_number($q, $type) {
  $sql = <<<EOF
SELECT appliances.name, appliances.url
FROM   alloc_log, appliances, alloc_numbers
WHERE  alloc_numbers.name = :type AND
       alloc_log.seed = (cast(:q as decimal(16)) - alloc_numbers.base) DIV
                         alloc_numbers.leap AND 
       alloc_log.numbers_id = alloc_numbers.id AND
       appliances.id = alloc_log.appliance_id
EOF;

  return query_sql($sql, array('q' => $q, 'type' => $type));
}

$type = id_type($q);

function redirect_row($row, $uri) {
    if ($row) {
        $url = $row['url'] ? $row['url'] : 'https://' . $row['name'];
        redirect($url . $uri);
    }
}

$next = isset($_REQUEST['next']) ? urlencode($_REQUEST['next']) : '';

if ($type === $ID_IS_MCID) {
  $mcid = clean_mcid($q);
  redirect_row(query_number($mcid, 'mcid'),
               "/acct/login.php?mcid=${mcid}&next=${next}");
}
else if ($type === $ID_IS_TRACKING_NUMBER) {
  $tn = clean_tracking_number($q);

  redirect_row(query_number($tn, 'tracking_number'),
               "/secure/trackemail.php?a=${tn}");
}

else if ($type === $ID_IS_EMAIL_ADDRESS) {
  redirect_row(query_key($q),
               '/acct/login.php?email=' . urlencode($q)."&next=$next");
}
else if ($type === $ID_IS_OPENID_URL) {
    if (strncasecmp($q, 'http://', 7) == 0 ||
        strncasecmp($q, 'https://', 8) == 0)
        $canonical = $q;
    else
        $canonical = 'http://' . $q;

    if (substr_compare($canonical, '/', -1) != 0)
        $canonical .= '/';

    redirect_row(query_key($canonical),
                 '/acct/login.php?openid_url=' . urlencode($q) . "&next=${next}");
}

$t = template('login.tpl.php');
$t->esc('openid_url', $q);
$t->esc('error', $error);

echo $t->fetch();
?>
