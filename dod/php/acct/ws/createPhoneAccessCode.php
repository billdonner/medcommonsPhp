<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
require_once "wslibdb.inc.php";
require_once "../alib.inc.php";
require_once "utils.inc.php";
require_once "email.inc.php";
require_once "mc.inc.php";
require_once "urls.inc.php";

/**
 * Creates an access code that can be used to authenticate a
 * phone user, and sends the user an SMS indicating the
 * access code to use.
 *
 * @param phoneNumber   phone number to authenticate
 * @param carrier       carrier associated with phone number
 * @param accessTo      account id of user being accessed
 * @return access code
 */
class createPhoneAccessCode extends jsonrestws {

  function jsonbody() {

    global $Secure_Url;

    $phoneNumber = req('phoneNumber');
    if(preg_match("/^[0-9]{10}$/",$phoneNumber)!==1)
      throw new Exception("Bad value for parameter 'phoneNumber'");

    $accessTo = req('accessTo');
    if(!is_valid_mcid($accessTo, true))
      throw new Exception("Bad value for parameter 'accessTo'");

    // $carrier = req('carrier');
    $carrier = "att";

    pdo_begin_tx();

    try {

      // Check if already have an access code for this phone - if so, use that
      $codes = pdo_query("select * from phone_authentication where pa_phone_number = ?",
                         array($phoneNumber));

      // Need to decide what should really happen here.  I think it should 
      // probably deactivate old codes rather than keep sending the same 
      // code over and over.
      if(count($codes) !== 0) { // Access code already exists
        $accessCode = $codes[0]->pa_access_code;
      }
      else { // Does not exist, make a new one

        // Generate an access code
        $accessCode = "";
        for($i = 0; $i<6; $i++) {
          $accessCode .= rand(0, 9);
        }

        // Insert it
        pdo_execute("insert into phone_authentication (pa_id, pa_phone_number, pa_access_code)
                     values (NULL,?,?)", array($phoneNumber,$accessCode));
      }
      pdo_commit();

      // Send email
      // $emailAddress = "ssadedin@gmail.com";

      if($carrier == "att")
        $emailAddress = $phoneNumber."@txt.att.net";
      else
      if($carrier == "vrzn")
        $emailAddress = $phoneNumber."@vtext.com";
      else
      if($carrier == "sprintpcs")
        $emailAddress = $phoneNumber."@messaging.sprintpcs.com";
      else
      if($carrier == "tmob")
        $emailAddress = $phoneNumber."@tmomail.net";
      else
        throw new Exception("Bad SMS carrier: $carrier");

      $text = "MedCommons HealthURL Alert - PIN $accessCode\n".
              $Secure_Url."/".$accessTo;

      dbg("Sending SMS to $emailAddress");

      if(!@mail($emailAddress, "MedCommons Access Code", $text, ""))
        throw new Exception("Unable to send email to notify $phoneNumber of access code");

      return $accessCode;
    }
    catch(Exception $e) {
      pdo_rollback();
      throw $e;
    }
  }
}

$x = new createPhoneAccessCode();
$x->handlews("createPhoneAccessCode");
?>
