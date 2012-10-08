<?php
/**
 * Returns messages for a group as JSON
 */
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "mc.inc.php";
require_once "JSON.php";

nocache();
$json = new Services_JSON();
$result = new stdClass;
try {

    validate_query_string();

    $info = get_validated_account_info();
    if(!$info)
        throw new Exception("You must be logged in");

    if(!$info->practice)
        throw new Exception("You must be be a member of a group");

    $key = req('key');
    if(!$key)
        throw new Exception("Missing parameter 'key'");

    if(preg_match("/^[0-9a-z]{40}$/", $key)!==1)
        throw new Exception("Bad format for parameter 'key'");

    // Figure out the patient for the transfer
    $ts = pdo_first_row("select ts_account_id from transfer_state where ts_key = ?", array($key));

    dbg("Cancelling transfer $key for patient ".$ts->ts_account_id);

    // Must have access to patient
    $consents = get_user_permissions($ts->ts_account_id);
    if(strpos($consents,"R")===false)
        throw new Exception("You do not have consent to access the specified account.");

    pdo_execute("update transfer_state set ts_status = 'Cancelling', ts_version = ts_version + 1 where ts_key = ?",array($key));

    $result->status = "ok";
}
catch(Exception $e) {
    $result->status = "error";
    $result->error = $e->getMessage();
}
echo $json->encode($result);
?>
