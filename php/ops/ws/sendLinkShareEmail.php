<?
  /**
   * Sends an email notifying a user about an email share link
   */

  require 'email.inc.php';
  require 'template.inc.php';
  require 'utils.inc.php';
  require 'JSON.php';

  $result = new stdClass;

  try {
    $to = req('to');
    if(!$to || ($to == ""))
      throw new Exception("Missing parameter 'to'");
    
    $subject = req('subject');
    if(!$subject || ($subject == ""))
      throw new Exception("Missing parameter 'subject'");

    $link = req('link');
    if(!$link || ($link == ""))
      throw new Exception("Missing parameter 'link'");

    $t = new Template();
    $t->set('link', $link);

    dbg("Using email templates from : " . email_template_dir());

    $text = $t->fetch(email_template_dir() . "linkShareText.tpl.php");
    $html = $t->fetch(email_template_dir() . "linkShareHTML.tpl.php");

    $stat = send_mc_email($to, $subject, $text, $html, array('logo' => get_logo_as_attachment()));
    $result->status = "ok";
  }
  catch(Exception $e) {
    $result->status = "failed";
    $result->error = $e->getMessage();
  }
  $json = new Services_JSON();
  echo $json->encode($result);
?>
