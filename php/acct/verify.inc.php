<?php

require_once 'urls.inc.php';
require_once 'settings.php';
require_once 'email.inc.php';
require_once 'mc.inc.php';

/*********************************************
 * Regular expression test for valid emails...
 *
 */

// allowed characters for part before "at" character
$atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';

// allowed characters for part after "at" character
$domain = '([a-z]([-a-z0-9]*[a-z0-9]+)?)';

$regex = '^' . $atom . '+' . // One or more atom characters.
'(\.' . $atom . '+)*'.       // Optional dot separated atom chars.
'@'.                         // Followed by an "at" character.
'(' . $domain . '{1,63}\.)+'.// Followed by one or max 63 domain chars
$domain . '{2,63}'.          // Must be followed by 1 set with a period of 2
'$';                         // or max 63 domain chars.

function is_valid_email($email) {
  global $regex;

  return eregi($regex, $email);
}

/*
 * ...regular expression test for valid emails
 *********************************************/

/**
 * Sends a verify message
 *
 * If this appliance mode is demo or production ($acApplianceMode >= 2)
 * bcc's to $acFromEmail (defaults to cmo@medcommons.net)
 */
function verify_new_email($mcid, $email) {
  global $acApplianceMode, $acFromEmail, $acSite, $acApplianceName;

  verify_email($mcid, $email);

  if ($acApplianceMode >= 2) {
    $pretty = pretty_mcid($mcid);

    $text = <<<EOF
New Commons Appliance User

Appliance: ${acApplianceName} <${acSite}>
Email:     ${email}
MCID:      ${pretty}
EOF;

    $html = <<<EOF
<html>
  <body>
    <h1>New Medcommons Appliance User</h1>
    <table>
      <tr>
        <th>Appliance</th>
        <td><a href='${acSite}'>${acApplianceName}</a> &lt;${acSite}&gt;</td>
      </tr>
      <tr>
        <th>Email</th>
        <td><a href='mailto:${email}'>${email}</a></td>
      </tr>
      <tr>
        <th>MCID</th>
        <td>${pretty}</td>
      </tr>
    </table>
  </body>
</html>
EOF;

    send_mc_email($acFromEmail, 'New Medcommons Appliance User', $text, $html,
                  array());
  }
}

/**
 * Sends a verify message
 */
function verify_email($mcid, $email) {
  global $SECRET, $acApplianceName;

  $hmac = hash_hmac('SHA1', $mcid . $email, $SECRET);
  $url = $GLOBALS['Accounts_Url'];
  $url .= "verify.php?mcid=$mcid&email=".urlencode($email)."&hmac=$hmac";

  $t = new Template();

  $t->esc('url', $url);
  $t->esc('mcid', pretty_mcid($mcid));
  $t->esc('email', $email);

  send_mc_email($email, "$acApplianceName - Verify Your Email Address",
                $t->fetch(email_template_dir() . "verifyText.tpl.php"),
                $t->fetch(email_template_dir() . "verifyHTML.tpl.php"),
                array('logo' => get_logo_as_attachment()));
}

?>
