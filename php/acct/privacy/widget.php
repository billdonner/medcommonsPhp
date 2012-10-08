<?
/**
 * Displays a table showing the user's consents 
 */
require_once "../utils.inc.php";
require_once "template.inc.php";
require_once "../alib.inc.php";
require_once "../GxGroup.php";
nocache();
$info = false;
$isPracticeMember = false;
if(is_logged_in()) {
  $info = get_account_info();
  $isPracticeMember = is_practice_member($info->accid);
}
if(is_logged_in() ):
  if(!isset($info->auth)) {
    $info->auth = "";
  }

  $activeGateway = isset($_COOKIE['mcgw']) && ($_COOKIE['mcgw']!='') ? $_COOKIE['mcgw'] : false;
  if(isset($_REQUEST['cleargw'])) { // If flag set to clear, ignore active gateway
    $activeGateway = false;
  }
  if($isPracticeMember && ($activeGateway == false) ) { // No gateway active - tell them to open a patient CCR
    echo template("../widget.tpl.php")->set("content",template("../emptyConsentsGadget.tpl.php"))->fetch();
  }
  else { // There is an active gateway
    $returnUrl = gpath('Accounts_Url')."/privacy/widget.php?cleargw=true";
    // Redirect to gateway
    header("Location: ".gpath('Default_Repository')."/AccountSharing.action?accid=".
        $info->accid."&returnUrl=".urlencode($returnUrl)."&auth=".$info->auth);
  }
else:
ob_start(); ?>
<h3>MedCommons is in business to protect your privacy.</h3>
<p class="spotlightText">In this world of private health insurance,
direct-to-consumer advertising and fee-for-service health care, consumer
privacy rights seem to depend on who you ask. On the one hand, you are asked to
compromise privacy to get employment, health insurance, and even as a condition
for treatment. On the other, privacy is misused to restrict access to your own
medical information.
</p><p>
MedCommons offers you and your caregivers a better, more polite alternative by
allowing you to control your private information as much or as little as you
see fit. Ready access to your personal health record on-line means that your
copy of your medical information can be more accurate and more up-to-date than
the information any single caregiver or institution has about you. Our
integrated group, worklist, messaging, scanned document and diagnostic imaging
features are designed to make use of your PHR easier and faster for your
caregivers wherever they may be.
</p>
<?
// Grab the buffer and send it to the widget template for display
$contents = ob_get_contents();
ob_end_clean();
$tpl = new Template("../widget.tpl.php");
$tpl->set("content", $contents);
echo $tpl->fetch();
?>
<?endif;?>

