<?php

/*
 * Web service to retrieve the home base for a particular MCID.
 *
 * Runs on the 'global' services.
 *
 * Input:
 *     GET /acct/ws/mcidHost.php?mcid=XXXX-XXXX-XXXX-XXXX
 *
 * Output:
 *     A query string containing either:
 *       status=OK&url=https://hostname/
 *     or
 *       status=ERR&msg=Error message
 *
 * Example:
 *    <?php
 *       $u = 'https://ci.myhealthespace.com/acct/ws/mcidHost.php';
 *
 *       $r = file_get_contents($u . '?mcid=9033545428087559');
 *
 *       parse_str($r, $o);
 *
 *       if ($o['status'] == 'OK')
 *         echo $o['url'];
 *       else
 *         echo $o['msg'];
 *    ?>
 */
require 'settings.php';
require 'mc.inc.php';

$demo_mcids = array('1012576340589251', '1013062431111407', '1035582511657478',
		    '1082155036018986', '1087997704966332', '1088448116240388',
		    '1106558614028065', '1117658438174637', '1135142322127072',
		    '1162164444007929', '1166439538173659', '1172619833385984',
		    '1175376381039160', '1192791741379853', '1259366818364933');

if (isset($_REQUEST['mcid'])) {
  $q = $_REQUEST['mcid'];

  if (strncasecmp($q, 'http://', 7) == 0 ||
      strncasecmp($q, 'https://', 8) == 0) {
    $q = parse_url($q, PHP_URL_PATH);
    if ($q[0] == '/') $q = substr($q, 1);
  }

  if (is_valid_mcid($q)) {
    $mcid = clean_mcid($q);

    if (in_array($mcid, $demo_mcids)) {
      echo 'status=OK&url=https://healthurl.myhealthespace.com&demo=1';
      exit;
    }

    $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

    $sql = "SELECT url ".
      "FROM alloc_log, appliances, alloc_numbers ".
      "WHERE alloc_log.numbers_id = alloc_numbers.id AND ".
      "      alloc_numbers.name = 'mcid' AND ".
      "      alloc_log.seed = ($mcid - alloc_numbers.base) div ".
      "                       alloc_numbers.leap AND ".
      "      appliances.id = alloc_log.appliance_id";

    $s = $db->prepare($sql);

    if (!$s) {
      $e = $db->errorInfo();
      $error = $e[2];
    }
    else if (!$s->execute()) {
      $e = $s->errorInfo();
      $error = $e[2];
    }
    else {
      $row = $s->fetch();

      if ($row) {
        echo 'status=OK&url=' . $row['url'];
	exit;
      }

      $error = "Unknown MCID";
    }
  }
  else
    $error = 'Unknown tracking number or MCID';
}
else
  $error = 'No MCID in query';

echo 'status=ERR&msg=' . $error;
?>
