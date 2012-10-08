<?
  /*
   * Home page for logged in users.  Displays various gadgets, designed to be rendered inside home.tpl.php.
   */
include "patient_monitor_javascript.inc.php";
?>

<div id='featureboxes' style="min-width: 430px;">
    
    <?if(isset($msg)):?>
      <div class='dashboardmsg'>
        <?=$msg?>
      </div>
    <?endif?>

    <?=$accountTypePanel?>

    <?if(!$info->practice):// do not show patient details if user has vouchers?>
      <h2>Dashboard</h2>
    <?endif;?>
    <div id='patientDetails'>
      <? include('patientDetailsFrame.tpl.php'); ?>
    </div>
</div>
