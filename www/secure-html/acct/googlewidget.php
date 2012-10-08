<?php
// goolge gadget framework for medcommons account page access
// $switches = those sections on the users page that are present
$__switches = $_REQUEST['up_mcswitches'];
$GLOBALS['__mckey'] = $_REQUEST['up_mckey']; //needs to be seen everywhere
$__width = $_REQUEST['up_mcwidth'];
$__type = $_REQUEST['up_mctype'];
$__noheader = true;
$__nofooter = false;
$__flat = true;
// $valid = those sections that can be present in this user's page
$valid = 'abcdefghijklmnopqrstuvwxyz';
// $startpage == where to go when user sets this page as admin page
$startpage = '';
require_once "mypage.inc.php";
?>