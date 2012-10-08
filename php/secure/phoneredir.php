<?php

require_once "utils.inc.php";
require_once "securelib.inc.php";
require_once "db.inc.php";

// Get auth from cookie
$auth = $_COOKIE['mc_anon_auth'];

dbconnect();

// Find the authorized accounts - we hope there is only 1
$accounts = get_authorized_accounts($auth);

if(count($accounts) == 0)
  error_page("No account could be identified for the telephone number you entered."); // no return

if(count($accounts) > 1)
  dbg("Found multiple accounts authorized for phone number auth = $auth");

$rights = $accounts[0]->getRights();

if(!$rights)
  error_page("Internal Error - Phone number not associated with patient",
             "No active entry returned for external share ". $accounts[0]->esId);


// The rights MUST be to a whole storage account
if(!$rights->storage_account_id)
  error_page("Internal Error - Inconsistent phone number association to patient record",
             "Rights entry {$rights->id} is not associated to a storage account");

header("Location: ".gpath('Accounts_Url')."/cccrredir.php?accid=".$rights->storage_account_id);

?>
