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

    $since = req('since',0);
    if(preg_match("/^[0-9][0-9]*$/",$since) !== 1) 
        throw new Exception("Invalid value for parameter 'since'");

    dbg("querying messages for group ".$info->practice->accid." since ".$since);

    $messages = pdo_query("select * from transfer_message tm
                           where tm.tm_account_id = ? 
                           and (NOW() - tm.tm_create_date_time) < 3600
                           and tm.tm_id >= ? order by tm_id desc",array($info->practice->accid, $since));

    $result->status = "ok";
    $result->messages = $messages;
    $result->timestamp = time();
}
catch(Exception $e) {
    $result->status = "error";
    $result->error = $e->getMessage();
}
echo $json->encode($result);
?>
