<? 
  /**
   * Renders a form where the user can enter details for a fax cover page.
   */
  require_once("urls.inc.php");
  require_once("../utils.inc.php");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  $idUrl = $GLOBALS['Identity_Base_Url'];
  $accountInfo = is_logged_in() ? get_account_info() : null;
  $accountId = $accountInfo != null ? $accountInfo->accid : "9999999999999999";
?><script type="text/javascript">
      function createCoverPage() {
        var f = document.coverPageForm;

        // Check the account id is present
        var accid = f.accid.value;
        if(!accid.replace(/[ \t]/g,'').match(/[0-9]{16}/)) {
          alert("Please enter a valid Account ID (16 Digits)");
          document.coverPageForm.accid.style.border="solid 2px red";
          document.coverPageForm.accid.focus();
          document.coverPageForm.accid.select();
          return false;
        }

        var pin = f.coverPin.value;
        if((pin!='') && !pin.match(/[0-9]{5}/) && f.createCover.checked) {
          alert("The PIN you entered was not valid. Please enter a valid PIN (5 Digits)");
          document.coverPageForm.coverPin.style.border="solid 2px red";
          document.coverPageForm.coverPin.focus();
          document.coverPageForm.coverPin.select();
          return false;
        }

        // Make the window
        var coverWindow = window.open('about:blank','cover','scrollbars=1,width=720,height=550,resizable=1');
        coverWindow.focus();
        return true;
      }

      function coverclick() {
        var f = document.coverPageForm;
        f.coverNotifyEmail.disabled = f.coverPin.disabled = !f.createCover.checked;
        $('coverPinLabel').style.color= f.createCover.checked ? '#444' : 'gray';
        $('coverNotifyEmailLabel').style.color= f.createCover.checked ? '#444' : 'gray';
      }
    </script>
    <h3>Consent and Fax In</h3>
    <p>Personalized and coded cover sheets make updating a personal health record as easy as your fax machine. Registered users and their caregivers can print fax cover sheets that update the PHR with standardized PDF-format scans. You and users with access to your PHR can have the documents automatically update your Current CCR. Faxes from others will be kept separate pending your review. Incoming faxes trigger Notification by email and update the Worklist page.

    <p>Use the form below to create a personalized cover sheet for updating your or your patient's health care record.</p>
    <form name="coverPageForm" action="<?echo $GLOBALS['Accounts_Url']?>/cover.php" 
          method="post" target="cover">
        <div class="label"><label for="accid">Account ID:</label>&nbsp;</div> 
        <input name="accid" type="text" value="<?echo $accountId?>" <?if(!is_logged_in()) {?>readonly="true" style="color: gray;"<?}?>/>
        <br/>
        <span  style="display:none;">
          <div class="label"><label>Include Fax Cover Sheet:</label>&nbsp;</div> 
            <input type="checkbox" name='createCover' onclick="coverclick();" checked="true"/>
          <br/>
        </span>
        <div class="label"><label id="coverPinLabel" style="font-size: smaller;">Choose a PIN (optional):</label>&nbsp;</div> 
          <input type="text" name='coverPin' size='6' maxlength='5' value=''/>
        <br/>
        <div class="label"><label id="coverNotifyEmailLabel" style="font-size: smaller;">Notification Email (optional):</label>&nbsp;</div>
          <input name='coverNotifyEmail' type='text' size='50' />
        <br/>
<?/*
        <div class="label"><label id="coverProviderCodeLabel" style="font-size: smaller;">Provider Code (optional):</label>&nbsp;</div>
          <input name='coverProviderCode' type='text' size='1' maxlength='1'  />
        <br/> 
 */?>
        <div class="label">&nbsp;</div><input type='submit' onclick="return createCoverPage()" value='Create Page' /> 
      </form>
    </span>
    <br style="clear:both;"/>
    <p>
        <div class="prevLink"/>Previous: <a href="../mygroups">Groups and Interests</a></div>  <div class="nextLink">Next: <a href="../mydevices">Devices</a></div>
       <div style="height: 30px;">&nbsp;</div>
    </p>
