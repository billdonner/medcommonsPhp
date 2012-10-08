<?php
/**
 * Renders a HTML snippet containing messages from a specifc transfer
 * <p>
 * Intended for display inside a div, dialog or panel.
 */
require_once "utils.inc.php";
require_once "alib.inc.php";
require_once "mc.inc.php";

nocache();
try {

    validate_query_string();

    $info = get_validated_account_info();
    if(!$info)
        throw new Exception("You must be logged in");

    if(!$info->practice)
        throw new Exception("You must be be a member of a group");

    $accid = req('accid');
    if(!$accid)
        throw new Exception("missing expected parameter 'accid'");

    if(!is_valid_mcid($accid,true))
        throw new Exception("Bad value $accid for parameter 'accid'");

    $patient = pdo_first_row("select * from users where mcid = ?",array($accid));
    if(!$patient)
      throw new Exception("Unknown patient account $accid");

    $messages = pdo_query("select * from transfer_message tm, transfer_state ts
                           where ts.ts_account_id = ? and ts.ts_key = tm.tm_transfer_key",array($accid));
    if(count($messages)==0)
      throw new Exception("No messages for patient $accid found.");

    // Must have access to patient
    $consents = get_user_permissions($accid);
    if(strpos($consents,"R")===false)
        throw new Exception("You do not have consent to access the specified account.");

    // Group by transfer
    $transfers = array();
    foreach($messages as $m) {
      if(!isset($transfers[$m->ts_key])) {
          $transfers[$m->ts_key] = array();
      }
      $transfers[$m->ts_key][]=$m;
    }

}
catch(Exception $e) {
    echo "<div><p>A problem occurred loading message details:</p>
          <pre>".htmlentities($e->getMessage())."</pre></div>";
    exit;
}
?>
<?$first = true; foreach($transfers as $key => $messages):?>
    <?$t = $messages[0];?>
    <h3>Messages for patient <?=htmlentities($patient->first_name." ".$patient->last_name)?> 
        <?if($first):?><a href='javascript:displayGroupMessages();' title='Close Messages'>X</a><?endif;?>
    </h3>
    <table>
      <thead>
        <tr><th colspan='2'>Transfer - <?=$t->ts_type?> at <?=$t->ts_create_date_time?></th></tr>
      </thead>
      <tbody>
        <?foreach($messages as $m):?>
        <tr>
            <th><?=htmlentities($m->tm_message_category)?></th> <td><?=htmlentities($m->tm_message)?></td>
        </tr>
        <?endforeach;?>
      </tbody>
    </table>
    <? $first = false; ?>
<?endforeach;?>
