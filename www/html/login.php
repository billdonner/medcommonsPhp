<? 

    include("dbparams.inc.php");

  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  $idUrl = $GLOBALS['Identity_Base_Url'];
  $trackingbox = $_REQUEST['accid'];
  if($_REQUEST['trackingbox']!='') {
    $trackingbox = $_REQUEST['trackingbox'];
  }
    ?><html>
  <head>
    <style type="text/css" media="all"> @import "main.css"; </style>
    <script type="text/javascript" src="MochiKit.js"> </script>    
    <script type="text/javascript">
      function initFocus() {
        if(document.loginForm.mcid.value != '') {
          document.loginForm.password.focus();
        }
      }
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
        if(!pin.match(/[0-9]{4}/) && f.createCover.checked) {
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
  </head>
  <body style='background: transparent; margin: 0px 5px;' onload='initFocus();'>
  <div id="supportingText">
      <div id="">
          <h3>
              <span>Account Access</span>       
          </h3>
          <br/>
        <form class="loginForm" name='loginForm' method='post' action='<? echo "$idUrl/login"?>'> 

        <!-- <p class="regheading">What would you like to do?</p> -->
          <span id="registration">
            <span class="acctRadio">Log In</span>
            <br/>
            <br/>
            <div class="label"><label>MedCommons ID or Email:</label>&nbsp;</div> <input name='mcid' size='19' value='<? echo "$trackingbox"?>'/>
            <br/>
            <div class="label"><label>Password:</label>&nbsp;</div><input name='password' type='password' />
            <br/> 
            <div class="label">&nbsp;</div><input type='submit' value='Sign On' /> 

            <? /* <div style="text-align: center; font-size: 12px;">
              <i>Forgot your password?</i> <a href='<?echo "$idUrl"?>/newpassword' title='Click here if you have lost your password'>Click Here</a>
            </div> */ ?>
            <br/>

           <div class="label" style="width: 30px;">&nbsp;</div> <i>No account?</i> <a class='logonLink' title="MedCommons Registration Page" href='<? echo "$idUrl/register"?>'>Click Here</a> to Register with MedCommons.
            <br/>
            <br/>
            <br/>
          </form>
          <span class="acctRadio">Print Information Request and Consent Page</span>
          <br/>
          <br/>
          <form name="coverPageForm" action="<?echo $GLOBALS['Accounts_Url']?>/cover.php" 
                method="post" target="cover">
              <div class="label"><label for="accid">Account ID:</label>&nbsp;</div> 
                <input name="accid" type="text" value="<?echo $trackingbox?>"/>
              <br/>
              <div class="label"><label>Include Fax Cover Sheet:</label>&nbsp;</div> 
                <input type="checkbox" name='createCover' onclick="coverclick();" checked="true"/>
              <br/>
              <div class="label"><label id="coverPinLabel" style="font-size: smaller;">Choose a PIN:</label>&nbsp;</div> 
                <input type="text" name='coverPin' size='6' value=''/>
              <br/>
              <div class="label"><label id="coverNotifyEmailLabel" style="font-size: smaller;">Notification Email (optional):</label>&nbsp;</div>
                <input name='coverNotifyEmail' type='text' />
              <br/>
              <div class="label"><label id="coverProviderCodeLabel" style="font-size: smaller;">Provider Code (optional):</label>&nbsp;</div>
                <input name='coverProviderCode' type='text' size='1' maxlength='1'  />
              <br/> 
              <div class="label">&nbsp;</div><input type='submit' onclick="return createCoverPage()" value='Create Page' /> 
            </form>

          </span>

        </div>
    </div>
      </body>
</html>
