<?php

require_once 'mc.inc.php';
require_once 'settings.php';

/*
 * class Attachment         - contains binary data for email attachments
 * get_logo_as_attachment() - retrieves a logo as an email attachment
 * send_mc_email()          - sends rich text
 */

class Attachment {
  public $content_type = 'image/gif';
  public $content_transfer_encoding = 'base64';
  public $content = '';

  public function __construct($content_type, $content) {
    $this->content_type = $content_type;
    $this->content = $content;
  }
}

function get_logo_as_attachment() {
  global $acLogo;

  $f = fopen(combine_urls(get_request_url(), $acLogo), 'r');
  $a = stream_get_meta_data($f);

  $contentType = 'image/png';

  foreach ($a['wrapper_data'] as $h) {
    if (substr_compare($h, 'content-type:', 0, 13, true) == 0)
      $contentType = trim(substr($h, 14));
  }

  $base64 = chunk_split(base64_encode(stream_get_contents($f)));

  return new Attachment($contentType, $base64);
}

/*
 * Send a multi-part MIME email message.
 * 
 * send_mc_email(recipient, subject,
 *               text, html,
 *               attachments);
 *
 * recipient should be an email address, or {name} <email>
 * can be a comma-separated list
 *
 * examples: "terry@wayforward.net" or
 *           "Terence Way <terry@wayforward.net>"
 *
 * subject must not have any newlines
 *
 * text is the pure text version of the email.  Please include
 * a text version of the email, so people who have mail clients
 * that cannot understand HTML, or those people who have set up
 * their clients to only display text, can see something.
 *
 * html is the HTML formatted version of the mail.  It can
 * contain images and links to cid:name attachments.  Please
 * do not link to external images, as nearly every mail
 * client filters this out as a privacy violation.
 *
 * attachments is an associative array of name to Attachment
 * objects.
 *
 * To use attachments, create an Attachment object with its
 * MIME Content-Type, and the content.  An encoding type
 * of Base64 is assumed.  Then, give it a name and put it
 * into an associative array.  The name can be used in the
 * HTML as cid:name.
 *
 * For example:
 *
 * send_mc_email("terry@wayforward.net", "Test message",
 *               "Plain text message: Hi!",
 *               "<html>\n<img src='cid:logo'>",
 *               Array('logo' => Attachment('image/gif', '...')));
 *
 */
function send_mc_email($recipient, $subject, $text, $html, $attachments) {
  global $acFromName, $acFromEmail;

  $msg = <<<EOF

This is a multi-part message in MIME format.
--==boundary1==
Content-Type: text/plain; charset=ISO-8859-1; format=flowed
Content-Transfer-Encoding: 7bit

$text

--==boundary1==
Content-Type: multipart/related; boundary="==boundary2=="

--==boundary2==
Content-Type: text/html; charset=ISO-8859-1
Content-Transfer-Encoding: 7bit

$html

EOF;

  foreach ($attachments as $id => $a) {
    $content_type = $a->content_type;
    $content_transfer_encoding = $a->content_transfer_encoding;
    $content = $a->content;

    $msg .= <<<EOF
--==boundary2==
Content-Type: $content_type
Content-Transfer-Encoding: $content_transfer_encoding
Content-ID: <$id>

$content

EOF;


  }

  $msg .= <<<EOF
--==boundary2==--

--==boundary1==--
EOF;

  $headers = <<<EOF
From: $acFromName <$acFromEmail>
Reply-To: $acFromEmail
User-Agent: MedCommons Mailer 1.0
MIME-Version: 1.0
Content-Type: multipart/alternative;
 boundary="==boundary1=="
EOF;

 return @mail($recipient, $subject, $msg, $headers);
}

function slash_dir($dir) {
  if (substr_compare($dir, '/', -1, 1) == 0)
    return $dir;
  else
    return $dir . "/";
}

function email_template_dir() {
  global $acTemplateFolder, $acEmailTemplateFolder;

  if (substr_compare($acEmailTemplateFolder, '/', 0, 1) == 0)
    return slash_dir($acEmailTemplateFolder);
  else
    return slash_dir($acTemplateFolder) . slash_dir($acEmailTemplateFolder);
}


?>
