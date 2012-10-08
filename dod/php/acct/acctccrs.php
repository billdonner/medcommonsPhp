<?
require_once "dbparamsidentity.inc.php";
require_once "urls.inc.php";
require_once "template.inc.php";
require_once "alib.inc.php";

$tpl = new Template("widget.tpl.php");

function error() {
  global $tpl;
  $tpl->set("content","<p>An error occurred while listing your account</p>");
  echo $tpl->fetch();
  exit;
}

list($accid,$fn,$ln,$email,$idp,$coookie) = aconfirm_logged_in (); // does not return if not logged on
$db = aconnect_db() // connect to the right database
 or error("Database error:  ".mysql_error());


$result = mysql_query("select * from ccrlog where accid = $accid")
 or error("Database error:  ".mysql_error());

$rows = array();
while($row=mysql_fetch_object($result)) {
  $rows[]=$row;
}
$contentTpl = new Template("ccrtable.tpl.php");
$contentTpl->set("rows",$rows);
$contentTpl->set("fn",$fn);
$contentTpl->set("ln",$ln);
$contentTpl->set("accid",$accid);
$tpl->set("content",$contentTpl );
echo $tpl->fetch();
?>
