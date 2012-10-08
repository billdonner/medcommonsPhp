<div class=homecoupon>
  <h2>Temporary Voucher Account</h2>

  <p>Before <?=hsc($voucherExpirationDate)?> you can:
  <form name='signin' action='<?=$GLOBALS['Site_Url']?>/personal.php' method='get'>
  <input type='hidden' name='src' value='<?=$GLOBALS['Secure_Url']?>/<?=$info->accid?>'/>
  <input type='hidden' name='srcauth' value='<?=$voucherAuth?>'/>
  <input type='hidden' name='tid' value='<?=$voucherCouponum?>'/>
  <input type='hidden' name='otp' value='$shaotp'/>
  <ul class='bodylist'>
  <li>View, Print and Save the information  
    <a  target='_new' title='open records in new window' href='/<?=$info->accid?>?c=v'>
      <img border='0' src='/images/icon_healthURL.gif' alt='hurlimg'/>Open CCR</a>
    </li>
    <li>Copy to your HealthURL .
    <a href='<?=$GLOBALS['Site_Url']?>/personal.php' onclick='document.signin.submit(); return false;'>Sign In</a>
      or
      <a href='<?=$GLOBALS['Site_Url']?>/personal.php' onclick='document.signin.submit(); return false;'>Register</a>
     </li>
    <li>Share the information under the New Password.
        <a href='/acct/settings.php?page=password' >Change Password</a>
    </li>
  </ul>
  </form>
</div>
