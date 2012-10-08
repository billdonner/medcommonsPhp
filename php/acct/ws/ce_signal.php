<?
/**
 * Cookie Event Signaler
 *
 * Sends a notification of an event to the account server.  Allows the account server
 * UI to be aware of events occuring in the gateway context.
 */
require_once "urls.inc.php";
require_once "utils.inc.php";

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-Type: text/javascript");
$e=$_REQUEST['e'];

// Don't delete cookies that are still potentially live
$ce = isset($_COOKIE['ce']) ? $_COOKIE['ce'] : "";

//dbg("existing ce is ".strlen($ce)." chars");

// First, remove old events
$new_ce = array(); 
$now = time();
$old = explode(';',$ce);
foreach($old as $o) {
  $d = substr($o, 0, strpos($o, ','))/1000;
  if($now - $d > 10)
    dbg("Found old event $o created at ".strftime("%Y-%m-%d %H:%M:%S",$d));
  else
    $new_ce[]=$o;  
}

$events = explode(';',$e);
foreach($events as $evt) {
  if(strpos($ce,$evt) === false) {
    $new_ce[]=$evt;
  }
}

$ce = implode($new_ce,';');

//dbg("new ce = $ce");
//dbg("new ce is ".strlen($ce)." chars");
setcookie("ce", $ce, null,'/');

// A bit of a hack: we layer also into this call the auth token
// If the user has no mc cookie then we create a dummy one 
// that has the auth token.  This serves to allow redirectors 
// to get the user back to the gateway with the token if they
// hit the gateway first and then click links that get redirected
// through the clean URLs that hit apache first
if(!isset($_COOKIE['mc']) && isset($_GET['auth'])) {
  log("setting anon auth cookie = ".$_GET['auth']);
  setcookie("mc_anon_auth",$_GET['auth'], null, '/');
}

//error_log("Received signal: $e, set $ce");
?>
var ceRegisteredAt="<?=time()?>";
