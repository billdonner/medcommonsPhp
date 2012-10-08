<?
/**
 * CCR Gadget Redirection Script.
 *
 * Checks if the user currently has a CCR open and if so, redirects to
 * appropriate gateway to display it.
 *
 * If no CCR currently open, checks if user logged in and has 
 * a current CCR.  If so, redirects to appropriate gateway.
 *
 * Otherwise displays a benign message.
 *
 * @author ssadedin@medcommons.net
 */
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  require_once "urls.inc.php";
  require_once "alib.inc.php";
  require_once "utils.inc.php";
  require_once "template.inc.php";

  check_clear_gw();

  $returnUrl = urlencode(gpath('Accounts_Url')."/CCCRGadget.php?cleargw=true");

  if($gwhost = current_gw_host()) {
      header("Location: ".$gwhost."/CurrentCCRWidget.action?returnUrl=$returnUrl");
      exit;
  }
  
  $info = is_logged_in() ? get_account_info() : false;
  $isPracticeMember = $info ? is_practice_member($info->accid) : false;
  if($isPracticeMember) {
    $practices = q_member_practices($info->accid);
    $info->practice = $practices[0];
  }

  aconnect_db();
  $cccrGuid = $info ? getCurrentCCRGuid($info->accid) : false;
  if($cccrGuid && $info) { // logged in: show current CCR
      // TODO:  fix for multiple gateways
       header("Location: ".gpath('Default_Repository')."/currentccr?widget&a=".$info->accid."&returnUrl=$returnUrl&auth=".$info->auth);
      // header("Location: ".$GLOBALS['Commons_Url']."gwredirguid.php?dest=".urlencode("CurrentCCR.action?widget&returnUrl=$returnUrl"));
      exit;
  }
  $enableCombinedFiles = (isset($GLOBALS['use_combined_files']) && ($GLOBALS['use_combined_files']==true));
  $httpUrl = $enableCombinedFiles ? rtrim($GLOBALS['Acct_Combined_File_Base'],"/") : ".";
?>
<html>
<?if(!$enableCombinedFiles):?>
  <link rel="stylesheet" href="main.css" type="text/css"/>
  <script type="text/javascript" src="MochiKit.js"></script>
  <script type="text/javascript" src="utils.js"></script>
<?else:?>
    <link rel="stylesheet" href="<?=$httpUrl?>/acct_all.css" type="text/css"/>
    <script type="text/javascript" src="<?=$httpUrl?>/acct_all.js">This page needs Javascript to work properly.</script>
<?endif;?>
  <script type="text/javascript">
    function checkCookie() {
      if(getCookie('mcgw')) {
        window.location.href='CCCRGadget.php';
      }
    }
  </script>
  <style type="text/css">
    div.demobutton {
      bottom: -1px;
    }
  </style>
  <body style="font-family: verdana; font-size: 12px; background-color: transparent;" onload="setInterval(checkCookie,2000);">
    <?=template("emptyCCRGadget.tpl.php")->set("isPracticeMember",$isPracticeMember)->set("info",$info)->fetch()?>
  </body>
</html>


