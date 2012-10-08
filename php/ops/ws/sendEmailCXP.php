<?php
require_once "ns.inc.php";
require 'email.inc.php';
require 'template.inc.php';
require 'utils.inc.php';

class ccrnotifier extends notifier {

  public $templatePrefix = "view";

	// send an eref notification, conform to old argument list
	function send_message 
	( &$message,
	$mcid,
	$template,
	$recipient,
	$subjectline,
	$a,$b,$c,$d,$e,$f,$g
	)

	{
    dbg("Sending email: $subjectline");

    // Check for a user specified template
    $prefix = req('template',$this->templatePrefix);
    if($prefix && ($prefix != ""))
      $this->templatePrefix = $prefix;

		$trackingnum = $a; // must be first if present
		
		$homepageurl = $GLOBALS['Homepage_Url'];
		if ($b!='') $b = "<p>Sender's Comment: <br>$b";

		$homepagehtml= "<a href=$homepageurl>$homepageurl</a>";
		
		$trackingurl = $GLOBALS['Tracking_Url'];
		
		$prettytracking = $this->pretty_tracking($trackingnum);
		if ($c!="") $subjectline = $c;
		$sl = urlencode($subjectline);
		$trackinghtml = 
		"<a href=$trackingurl?a=$trackingnum".//&from=$mcid&to=$recipient&subject=$sl
		">$prettytracking</a>";

		$mcidhtml = $this->pretty_mcid($mcid);

		$t = new Template();
		$t->set('prettytracking', $prettytracking);
		$t->set('trackingurl', $trackingurl);
		$t->set('trackingnum', $trackingnum);
		$t->set('trackinghtml', $trackinghtml);

		$text = $t->fetch(email_template_dir() . $this->templatePrefix."Text.tpl.php");
		$html = $t->fetch(email_template_dir() . $this->templatePrefix."HTML.tpl.php");

    dbg("Sending email text $text");

		$time_start = microtime(true);// this is php5 only

		if ($c!="") $subjectline = $c;
		$stat = send_mc_email($recipient, $subjectline,
				      $text, $html,
				      array('logo' => get_logo_as_attachment()));

		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if($stat) return "ok elapsed $time"; else return "send mail failure elapsed $time";
	}
}

// main program
if(!isset($ccrnotifier_child_class)) {
  $e = new ccrnotifier();
  $e->handlews("notifierservice");
}

?>
