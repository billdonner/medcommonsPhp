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
		"<a href=$trackingurl?a=$trackingnum".//&from=$mcid&to=$recipient&subject=$sl
		">$prettytracking</a>";

		$mcidhtml = $this->pretty_mcid($mcid);

		$message = <<<XXX

An incoming CCR is available at MedCommons with tracking number $trackingnum.  

Please visit http://www.medcommons.net to view this message.
--==boundary-1
Content-Type: text/html; charset=us-ascii
Content-Transfer-Encoding: 7bit

		
    
<HTML><HEAD><TITLE>CCR arrival $trackingnum</TITLE>
<META http-equiv=Content-Type content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<img src="cid:image1" />
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

--==boundary-1
Content-Type: image/gif
Content-ID: <image1>
Content-Transfer-Encoding: base64
Content-Disposition: inline; filename="smallwhitelogo.gif"

R0lGODlh9gAyAPcAAAAAAP///1lZXFdXWlNTVUlJS/v7/Pn5+vf3+PX19vHx8uzs7evr7OXl5mFi
ZV9gY1pbXmdoa3V2eXJzdm9wc8LDxlFSVFBRU09QUk1OUFxdX1tcXlpbXVlaXFhZW1dYWlZXWVVW
WFRVV1NUVmVmaGBhY11eYN/g4trb3dLT1c7P0Xp8f4aIi5ianZSWmZGTlo+RlIyOkbGztqqsr6Sm
qba4u7W3um1vcWpsbnh6fICChH+Bg31/gZ2foZeZm8TGyL7Awrq8vrm7va+xs8nLzenq6+bn6OLj
5ODh4t7f4NbX2KCjpfv8/Pj5+fb39/T19fLz8+zw7vb49/X39omcko+imI2glouelJuso6++tq28
tKu6sqq5sbbDvLTBuoebkIWZjoSYjYOXjJGjmZCimI+hl46glpiqoJepn5SmnMbRy8TPydPc19Lb
1omckYibkIeaj4aZjo2glYyflIuek4qdkpOmm5KlmqKyqaGxqKCwp56upae3rqa2raS0q6OzqrbE
vLXDu7TCurLAuK+9tcDMxcLOx8HNxuDn49/m4t7l4ZannZyto5qroaO0qqGyqJ+wpp6vpbC/tq++
ta28s6y7sqm4r6i3rrHAt8vVz8rUzsnTzdHb1dDa1M/Z087Y0szW0Orv7Onu6+fs6eXq5+Tp5pKk
mZSmm7TBub3KwrnGvtnh3Nff2tTc18nUzdXe2NLb1ePp5d7k4PP29PL18/H08vDz8e/y8O7x793k
39zj3u7y7+zw7ff59/X39fv8+/r7+vn6+f///f///v78+f37+LN8NbaAO7V/OrZ/PLaAPbeBPreC
P7iDQbeCQbmEQ7qGRbqFRbmFRLqGR7yISb2KTL6MT8OTWMaYYMicZcqeacygbM+mdNGqedSuf9ay
hdezh9i0idm2jNu4j9y7k929l+LFo+PIqOXMrenTuOzZwvXq3fbt4vjx6Pv28N+/muDCn+fPs+7c
yPDgzvLj0/Pm2PDey/br4Pfu5fny6/z49P/9/P7+/vz8/Pr6+v///yH5BAEAAP8ALAAAAAD2ADIA
AAj/AAMIHEiwoMGDCAMIS8iwocOHECNKnEixosWLGDMKdMeO3UKNIEOKHEmypMBi+0xC5GiPHr5i
KmPKnEkzo75662DWHFgPnTp6+XYKHUpUaLF16eTtlFcOXr2iUKNKHZnPXDl9Mu19A6d0qtevYCOe
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

--==boundary-1--
XXX;
// the following would benefit from being moved to a separate routine as part of the parent class
		$time_start = microtime(true);// this is php5 only
		
		$srv = $_SERVER['SERVER_NAME'];
        if ($b=="") $b = "From: MedCommons@{$srv}\r\n" ."Reply-To: cmo.medcommons.net\r\n";
		if ($c!="") $subjectline = $c;
$stat = @mail($recipient, $subjectline,
		$message,$b.
		"bcc: cmo@medcommons.net\r\n".
		"Content-Type: multipart/related; boundary=\"==boundary-1\"; type=\"text/html\";\r\n"
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
