<?
/**
 * Shows the expanded current ccr gadget
 */
require_once "../utils.inc.php";
require_once "template.inc.php";
require_once "../alib.inc.php";
aconnect_db();
$info = testif_logged_in();
$guid = false;
if($info) {
  $guid = getCurrentCCRGuid($info[0]);
}

$displayUrl = false;

$cleargw = check_clear_gw();
$returnUrl = urlencode(gpath('Accounts_Url')."/currentccr/widget.php?cleargw=true");
if($gwhost = current_gw_host()) { // user is logged in at this gateway - show whatever ccr is active there
  //$displayUrl = $gwhost."/CurrentCCRWidget.action?displayUpdates&returnUrl=$returnUrl";
  $displayUrl = $gwhost."/CurrentCCRWidget.action?displayActivity&returnUrl=$returnUrl";
}
else 
if($guid) { // not logged in at any gateway, show user's own current ccr if they have one
  //$displayUrl = $GLOBALS['Commons_Url']."/gwredirguid.php?guid=$guid&dest=".urlencode("access?displayUpdates");
  $displayUrl = gpath('Commons_Url').
    "/gwredirguid.php?guid=$guid&nopage&dest=".
    urlencode("CurrentCCRWidget.action?displayActivity&returnUrl=$returnUrl&accid=".$info[0]."&auth=".$info[6]);
}

ob_start();                    
?>
<?if(is_logged_in()):?>
  <script type="text/javascript">
    var patientAccountId = null;
    var noFrame = true;
    ce_connect('patientActivityOpened', function(accountId,firstName,lastName,age,sex,mcId) {
      log("currentccr.widget: patientActivityOpened - " + mcId);
      if(sex == "Male")
        sex = "M";
      else
      if(sex == "Female") 
        sex = "F";
      else 
        sex = "";

      patientAccountId = mcId;
      setMainSectionHeading(lastName + " " + firstName + " " + age + sex + " " + accountId + " PHR Activity" );    
    });
    ce_connect('closeCCR', function() {
      setMainSectionHeading('Current PHR Activity');
    });
    ce_connect('newPatient', function(accountId) {
      log("currentccr.widget: newPatient event received patient " + accountId + " (old patient = " + patientAccountId+")");
      if(noFrame || (patientAccountId && (patientAccountId != accountId))) {
        var gw = getCookie('mcgw');
        $('activityLogContainer').innerHTML=
          "<iframe name='gwactivitylog' src='"+gw+"/CurrentCCRWidget.action?displayActivity&returnUrl=<?=$returnUrl?>' width='98%' allowtransparency='true' background-color='transparent' frameborder='0' scrolling='no' height='200px'>Your browser doesn't support iframes.</iframe>";
      }
    });
    addHeightSync();

  </script>
  <div id="activityLogContainer">
  <?if($displayUrl):?>
    <script type="text/javascript">noFrame = false;</script>
    <?error_log("ZZZ: displayUrl = $displayUrl");?>
    <?=template("../iframe.tpl.php")->set("name","gwactivitylog")->set("src",$displayUrl)->set("height",200)->fetch()?>
  <?else:?>
  <br/>
  <p>No Current CCR is available to display. Open or Create a CCR to see details here.</p>
  <br/>
  <?endif;?>
<?/* eccr part no longer displayed
  <?if(!$cleargw):// Hack: if cleargw is set then this was a redirect back from the gw, so do not display eccr as it is already shown. ?>
  <?=template("../iframe.tpl.php")->set("src",$GLOBALS['Accounts_Url']."/gadgets/eccr")->set("name","eccr")->set("height","50")->fetch()?>
  <?endif;?>
 */?>
<?else:?>
<h4>Your Personal Health Record</h4>
<p>
The Current CCR is your personal health record as it would be shown to your 
primary care doctor or a typical specialist. Think of it as the information one 
practice would most likely transfer to another if you were moving to a new 
state. MedCommons accepts messages to your PHR to and from your caregivers and 
applies simple rules to decide when the content of the message can be safely 
used to update your Current CCR. To keep things clear and simple, all messages 
are stored in their original form and all updates to the Current CCR are logged 
for easy inspection.
</p>
<?endif;?>
<?
// Grab the buffer and send it to the widget template for display
$contents = ob_get_contents();
ob_end_clean();
$tpl = new Template("../widget.tpl.php");
$tpl->set("content", $contents);
echo $tpl->fetch();
?>

