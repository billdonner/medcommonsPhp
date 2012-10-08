<?
 /**
  * Redirects to a requested guid, or falls back to an alternate URL if not found
  *
  * @param  g - guid to open
  * @param  alt - alternate URL to show if not found
  * @author ssadedin
  */
require_once "utils.inc.php";
require_once "template.inc.php";
require_once "alib.inc.php";

aconnect_db();
$a = req('accid');
$g = req('guid');
$nf = req('nf');
$info = get_account_info();

// Logged in?  Send them to the gateway
if(($nf !== "true") && $info) {
  header("Location: ".gpath('Commons_Url')."/gwredirguid.php?guid=$g&nf=".urlencode($_SERVER['REQUEST_URI'].'&nf=true'));
}
else {
  $loginTpl = template("login_tn.tpl.php")->set("next",$_SERVER['REQUEST_URI']);
  if($nf === "true") {
    $loginTpl->esc("msg","The requested PHR was not able to be accessed with the account you are currently logged into.");
  }

  echo template("base.tpl.php")->set("title","Welcome to MedCommons")
                               ->set("head","")
                               ->set("content", $loginTpl)->fetch();
}
