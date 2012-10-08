<?

$ccrnotifier_child_class = true;
require_once "sendEmailCXP.php";

class faxnotifier extends  ccrnotifier {

  public function __construct() {
    $this->templatePrefix = "fax";
  }
}

$e = new faxnotifier();
$e->handlews("notifierservice");

?>

