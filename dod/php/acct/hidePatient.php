<?
/**
 * AJAX / JSON Service to hide patients.  Requires signed request from client.
 */
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "JSON.php";

validate_query_string();
$info = get_account_info();
$practices = q_member_practices($info->accid);
$practice = $practices[0];
$patientId = req('patientId');
echo hide_patient($practice->practiceid, $patientId);
?>
