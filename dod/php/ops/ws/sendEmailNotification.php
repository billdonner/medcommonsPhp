<?php
require_once "ns.inc.php";

require 'email.inc.php';
require 'template.inc.php';

class erefnotifier extends notifier {

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
		$trackingnum = $a; // must be first if present

		$homepageurl = $GLOBALS['Homepage_Url'];

		$homepagehtml= "<a href=$homepageurl>$homepageurl</a>";
		
//		if ($f!="") $trackingurl =$f; else
		$trackingurl = $GLOBALS['Tracking_Url'];
		
		$prettytracking = $this->pretty_tracking($trackingnum);
				if ($c!="") $subjectline = $c;

				$sl = urlencode($subjectline);
		$trackinghtml = 
		"<a href=$trackingurl?a=$trackingnum".//&from=$mcid&to=$recipient&subject=$sl
		">$prettytracking</a>";		
		$mcidhtml = $this->pretty_mcid($mcid);

		$t = new Template();
		$t->set('b', $b);
		$t->set('prettytracking', $prettytracking);
		$t->set('trackingnum', $trackingnum);
		$t->set('trackingurl', $trackingurl);
		$t->set('trackinghtml', $trackinghtml);
		$text = $t->fetch(email_template_dir() . "viewText.tpl.php");
		$html = $t->fetch(email_template_dir() . "viewHTML.tpl.php");

		$time_start = microtime(true);// this is php5 only

		$stat = send_mc_email($recipient, $subjectline,
				      $text, $html,
				      array('logo' => get_logo_as_attachment()));

		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if($stat) return "ok elapsed $time"; else return "send mail failure elapsed $time";
	}

}

//main program
$e = new erefnotifier();
$e->handlews("notifierservice");


?>
