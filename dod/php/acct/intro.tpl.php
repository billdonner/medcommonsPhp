<?
/**
 * Intro Page to get a user quickly started by selecting their role
 *
 * @author ssadedin@medcommons.net
 */
// <link rel='stylesheet' href='main.css' type='text/css'/>

include "patient_monitor_javascript.inc.php";

?>

<h2>Welcome to MedCommons</h2>
<?if(isset($msg)):?>
  <div class='dashboardmsg'>
    <?=$msg?>
  </div>
<?endif?>

<table id='introoptions'>
  <tr>
    <td width='40%'>
        <div class='patientintro rounded'>
          <div class='introcontent'>
            <h4>Get Started as a Patient</h4>
            <p>Are you a patient or carer 
               looking to create and manage a health record?</p>
               <form action='<?=new_ccr_url($info->accid,$info->auth, "new")."&am=p"?>' method='post' target='ccr'>
               <input type='submit' value='Create a Current CCR' class='mainlarge'/>
               </form>
          </div>
        </div>
    </td>
    <td width='40%'>
        <div class='providerintro rounded'>
          <div class='introcontent'>
            <h4>Get Started as a Provider</h4>
             <p>Are you a health services provider who needs to provide
                records to others?</p>
            <?if(!$isPracticeMember):?>
            <form action='create_group.php' method='post'>
              <input type='hidden' name='next' value='enable_mod.php?enable_mod=true&next=home.php'/>
              <input type='submit' value='Enable Services' class='mainlarge'/>
            </form>
            <?else:?>
              <form action='enable_mod.php?enable_mod=true&next=home.php' method='post'>
                <input type='submit' value='Enable Services' class='mainlarge'/>
              </form>
            <?endif;?>
          </div>
        </div>
    </td>
  </tr>
</table>
<script type='text/javascript'>
addLoadEvent(function() {
  roundClass('div','rounded');
  $('introoptions').style.visibility = 'visible';
});
ce_connect('openCCRUpdated',function() {
  document.location.reload();
});
</script>
