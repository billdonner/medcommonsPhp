<?
  /*
   * Home page for users who are not logged in.  Displays demo buttons, other stuff. 
   *
   * Designed to be rendered inside home.tpl.php.
   */

require_once 'login.inc.php';
require_once 'settings.php';

$t = template($acTemplateFolder . 'login.tpl.php');
$t->set('acOnlineRegistration', $acOnlineRegistration);
$t->set('mcid', '');
?>
<div id='featureboxes' style="min-width: 430px;">
  <div class="featurebox" id="privacy">
    <?=template("featureHeader.tpl.php")->set("id","welcome")->set("title","Welcome to MedCommons")->fetch()?>
    <p>Login to your account or try one of our demo accounts in the menu bar above!</p>
    <?=$t->fetch();?>
  </div>
</div>
