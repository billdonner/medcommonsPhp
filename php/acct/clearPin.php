<?
  $tryagain = $_REQUEST['tryagain'];
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <style type="text/css" media="all"> 
      @import "acctstyle.css"; 
      * {
        font-size: 12px;
      }
    </style>
  </head>
  <body style='background: transparent;'>
    <h3>Clear PIN on Emergency CCR</h3>
    <p>Clearing your PIN allows people to access your emergency 
    Medical information by entering only your Account ID.  </p>
    <p><i>Note: After clearing your PIN you can restore
    it at any time from your Account page.</i></p>
    <form name="clearPinForm" action="processClearPin.php">
      Please enter the PIN for your Emergency CCR and click on the "Clear PIN" button:<br/>
      <div style="margin: 15px 60px;">
        <? if($tryagain == 1) { ?>
          <p style="color: red; font-style: bold;">Your PIN could not be verified.  Please try again.</p>
        <? } ?>
        <label for="pin">PIN:</label>&nbsp;&nbsp;
        <input type="text" name="pin" size="5"/>&nbsp;&nbsp;
        <input type="submit" value="Clear PIN"/>
      </div>
    </form>
  </body>
</html>

