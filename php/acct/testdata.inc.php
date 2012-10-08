<?
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

require_once "settings.php";
require_once "urls.inc.php";
require_once "utils.inc.php";

$insertBuffer="";
function err($msg) {
  throw new Exception("Failed to create data: ".$msg);
  exit;
}
function warn($msg) {
  echo "<div style='border:1px solid black; background-color: #eee; padding: 5px; margin:2px;'><p style='color: red;'>$msg</p>";
  echo "<pre>".mysql_error()."</pre></div>";
}
function insert($sql) {
  global $insertBuffer;
  $insertBuffer.="<li>$sql</li>";
  return mysql_query($sql);
}

function pwhash($id) {
  return strtoupper(sha1("medcommons.net".$id."tester"));
}

function insertUser($id,$firstName,$lastName, $email) {
  $phash = pwhash($id);
  insert("INSERT INTO users ( mcid, email, sha1, server_id, since, first_name, last_name, mobile, smslogin, updatetime, ccrlogupdatetime, chargeclass, rolehack, affiliationgroupid, startparams, stylesheetUrl, picslayout, photoUrl, acctype, persona, validparams) VALUES ('$id','$email','$phash',1,'2006-09-20 01:19:15','$firstName','$lastName',NULL,NULL,0,1158568912,NULL,'hmwl',NULL,NULL,NULL,NULL,NULL,'USER',NULL,NULL)")
    or warn("Error creating user $email");
}

function insertDoctor($id, $lastName, $email) {
  insertUser($id,'Doctor',$lastName,$email);
}

mysql_connect("$IDENTITY_HOST", $IDENTITY_USER, $IDENTITY_PASS) or err("Error connecting to database.");
mysql_select_db($IDENTITY_DB) or err("can not connect to database $db");

class Patient {
  function Patient($id, $email, $first, $last,$currentCcr=null, $currentCcrTitle=null, $ccr2=null) {
    $this->id = $id;
    $this->email = $email;
    $this->first = $first;
    $this->last = $last;
    $this->ccr = $currentCcr;
    $this->title = $currentCcrTitle;
    $this->ccr2 = $ccr2;
  }
}

?>
