<?
  include "setup.inc.php";
  require_once "utils.inc.php";
  require_once "template.inc.php";

  nocache();

  // Defaults
  $GLOBALS['DB_Connection'] = "mysql.internal";
  $GLOBALS['DB_Database'] = "mcx";
  $GLOBALS['DB_User']= "medcommons";

  // Overrides
  if(file_exists("modpay_conf.inc.php")) {
    include "modpay_conf.inc.php";
  }

  if(!isset($GLOBALS['enable_console']) || ($GLOBALS['enable_console'] !== "true")) {
    echo "<p>This function is disabled.</p>";
    exit;
  }

  require_once "db.inc.php";

  $db = DB::get();
  if(isset($_REQUEST['update'])) {
    switch($_REQUEST['counter']) {
      case 'Fax': $counter = 'faxin'; break;
      case 'DICOM': $counter = 'dicom'; break;
      case 'Accounts': $counter = 'acc'; break;
    }
    $value = req('value');
    if(preg_match("/^[0-9]{1,9}$/",$value)!==1) {
      echo "bad value $value";
      exit;
    }

    $accid = req('accid');
    if($accid) {
      $billacc = $db->first_row("select * from billacc where accid = ?",array($accid));
      $billingid = $billacc->billingid;
    }
    else
      $billingid = $_REQUEST['billingId'];

    $db->execute("update prepay_counters set $counter = ? where billingid = ?",array($value,$billingid));

    echo "ok";
  }
  else {
    $tokens = $db->query("select * from billacc b
                      left join prepay_counters c on b.billingid = c.billingid
                      left join users u on b.accid = u.mcid
                      order by b.billingid");
    $t = template('console.tpl.php');
    $t->set("tokens",$tokens);
    echo $t->fetch();
  }
?>
