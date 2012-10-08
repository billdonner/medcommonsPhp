<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "../alib.inc.php";
require_once "wslibdb.inc.php";
require_once "utils.inc.php";
require_once "login.inc.php";
require_once "mc.inc.php";

/**
 * Creates a new fax cover sheet and returns the id
 *
 * @param accid - account id for account to receive faxes
 * @param notifyEmail  - email to notify when faxes are received (optional)
 * @param pinHash  - sha1 hash of PIN to add for access to received documents (optional)
 */
class createFaxCoverWs extends jsonrestws {
	function jsonbody() {

    $mcid = clean_mcid(req('accid'));
    $coverNotifyEmail = req('notifyEmail');
    $encryptedPin = req('pinHash');
    $title = req('title');
    $note = req('note');
    $coverProviderCode = "0";  // obsolete

    // Add row to fax cover table
    $coverId = pdo_execute("insert into cover (cover_id, cover_account_id, cover_notification, cover_encrypted_pin, cover_provider_code, cover_title, cover_note)
                              values (NULL, ?, ?, ?, ?,?,?)", array($mcid,$coverNotifyEmail,$encryptedPin,$coverProviderCode,$title,$note));

    dbg("cover = $coverId");

    $result = new stdClass;
    $result->cover_id = $coverId;
    return $result;
  }
}

$x = new createFaxCoverWs();
$x->handlews("response_createFaxCover");
?>
