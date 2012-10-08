<?require_once "ccrloglib.inc.php";?>
<?require_once "urls.inc.php";?>
  <form name="coverForm" action="cover.php?createCover=true" method="post" target="_new">
    <table>
      <tr><th>PHR Account Number:</th>
          <td><input type="text" name="accid" id="hippaPatientId" class="text" size="20" maxlength="19"
                     value="<?if(!isset($info->practice)):?><?=prettyaccid($info->accid)?><?endif;?>"/></td></tr>
      <tr><th>Provider/Practice:</th>
          <td><input type="text" name="coverProviderCode" class="text"
                    value="<?if(isset($info->practice)):?><?=hsc($info->practice->practicename)?><?endif;?>"/></td>
      </tr>
      <tr><th>Notification (opt):</th><td><input type="text" name="coverNotifyEmail" class="text"/></td></tr>
      <tr><th>PIN (opt):</th>
          <td><input type="text" class="text" size="5" name="coverPin" maxlength="5"/>&nbsp;&nbsp;&nbsp;
              <input type="submit" value="Preview"/></td></tr>
		  
    </table>
  </form>
<?/*
  <hr/>
  <form name="interestsForm" action="javascript:updatePrimaryInterest();">
    <table>
    <tr><th width="125">Principal Topic</th><td><input type="text" name="interest" class="text" size="16" value="<?if(count($interests)>0)echo $interests[0];?>"/></td></tr>
    <tr><th>&nbsp;</th><td><input type="submit" value="Change"/></td></tr>
    </table>
  </form>
*/?>
  <span id="ccrCheckSpan">
  <script type="text/javascript">
    ce_connect('newPatient', checkCurrentPatient);
    ce_connect('closeCCR', clearHipaaPatient);
  </script>
  </span>
