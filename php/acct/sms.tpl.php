<style type='text/css'>
  .middle { position: relative; top: -2px; padding-left: 4px;}
</style>
<div id='ContentBoxInterior'>
  <h2>Phone Access</h2>
  <p>Please enter your phone number and Access Code to access this HealthURL:</p>
  <form class='p' method='post' action='login.php' id='login' name='login'>
  <input type='hidden' name='next' value='cccrredir.php?mcid=<?=urlencode($mcid)?>'/>
      
      <div id='p_openid_url'>
        <label class='n' for='openid_url'>Phone Number</label>
        &nbsp;
        &nbsp;
          <input class='infield' 
                 type='text' 
                 name='openid_url' 
                 size='30' value="<?=htmlentities($phoneNumber)?>" />
            <span class='instr middle'>Your 10 digit phone number</span>

            <?if(isset($error)): ?>
              <div class='errorAlert'>
                <?= $error ?>
              </div>
            <?endif;?>
      </div>
      <br/>
      <div id='p_password'>
        <label class='n' for='password'>Access Code</label>
        &nbsp;
        &nbsp;
        <input 
              class='infield' 
              type='password'
              name='password' 
              id='password'
              size='7' 
          />
         <span class='instr middle'>Access Code you received in your SMS</span>
      </div>

      <div class='f'>
        <span class='n'>&nbsp;</span>
        <div class='q'>
        <input type='submit' value='Sign In' name='loginsubmit'/>
        </div>
      </div>

  </form>
  <br/>
  <br/>
  <br/>
  <br/>
</div>
