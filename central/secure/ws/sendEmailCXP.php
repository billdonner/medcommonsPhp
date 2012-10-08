<?php
require_once "ns.inc.php";
class ccrnotifier extends notifier {

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
		"<a href=$trackingurl?a=$trackingnum&from=$mcid&to=$recipient&subject=$sl>$prettytracking</a>";

		$mcidhtml = $this->pretty_mcid($mcid);

		$message = <<<XXX

		
<HTML><HEAD><TITLE>CCR arrival $trackingnum</TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<img src=http://www.medcommons.net/images/smallwhitelogo.gif />
<p>
An incoming CCR with tracking number $trackinghtml is available at MedCommons. Please click on the tracking number link to access. Be prepared to supply a PIN or a valid user registration to access patient information.

<p>    
HIPAA Security and Privacy Notice: The Study referenced in this 
invitation contains Protected Health Information (PHI) covered under 
the HEALTH INSURANCE PORTABILITY AND ACCOUNTABILITY ACT OF 1996 (HIPAA).
The MedCommons user sending this invitation has set the security 
requirements for your access to this study and you may be required to 
register with MedCommons prior to viewing the PHI. Your access to this 
Study will be logged and this log will be available for review by the 
sender of this invitation and authorized security administrators. 
<p><small>For more information about MedCommons privacy and security policies, 
please visit $homepagehtml </small>
</BODY>
</HTML>
XXX;
// the following would benefit from being moved to a separate routine as part of the parent class
		$time_start = microtime(true);// this is php5 only
		
		$srv = $_SERVER['SERVER_NAME'];
        if ($b=="") $b = "From: MedCommons@{$srv}\r\n" ."Reply-To: cmo.medcommons.net\r\n";
		if ($c!="") $subjectline = $c;
$stat = @mail($recipient, $subjectline,
		$message,$b.
		"bcc: cmo@medcommons.net\r\n".
		"Content-Type: text/html; charset= iso-8859-1;\r\n"
		);
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		if($stat) return "ok $srv  elapsed $time"; else return "send mail failure from $srv elpased $time";
	}

}

//main program
$e = new ccrnotifier();
$e->handlews("notifierservice");


?>