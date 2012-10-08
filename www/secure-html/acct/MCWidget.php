<?php
// gerneral interface for 3rd party calles
// $switches = those sections on the users page that are present
$__switches = $_REQUEST['s'];
$GLOBALS['__mckey'] = $_REQUEST['mckey']; //needs to be seen everywhere
$__width = $_REQUEST['width'];
$__type = $_REQUEST['type'];
$__noheader = ($_REQUEST['header']==0);
$__nofooter = ($_REQUEST['footer']==0);
$__flat = true;
// $valid = those sections that can be present in this user's page
$valid = 'abcdefghijklmnopqrstuvwxyz';
// $startpage == where to go when user sets this page as admin page
$startpage = '';
require_once "mypage.inc.php";

?>