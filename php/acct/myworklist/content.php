<?
require_once "template.inc.php";
?>
<?if(! $loggedIn):?>
<h3>Worklist</h3>
<p>Shared worklists help professionals improve service to their patients. A
family can share a worklist to keep track of increasingly complex and
distributed treatments.
<?
  $tpl = new Template("../widgetlogin.tpl.php");
  $tpl->set("next", $rlsPage);
  echo $tpl->fetch();
?>
<?else:?>
  <iframe src='<?=$rlsPage?>' width='98%' allowtransparency='true' background-color='transparent' frameborder='0' scrolling='no' height='300px'>Your browser doesn't support iframes.</iframe>
<?endif;?>
<?if($showFooter):?>
<br/>
<p>
    <div class="prevLink">Previous: <a href="../currentccr">Current CCR</a></div>  <div class="nextLink">Next: <a href="../mygroups">Groups and Interests</a></div>
   <div style="height: 30px;">&nbsp;</div>
</p>
<?endif;?>
