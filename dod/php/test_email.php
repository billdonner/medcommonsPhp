#!/usr/bin/env php
<?php

require 'email.inc.php';

$text = <<<EOF
MedCommons

You have been invited to view a Personal Health Record on MedCommons 
<http://www.medcommons.net/> with Tracking Number 2512 6994 4599
<https://www.medcommons.net/secure/trackemail.php?a=251269944599>. You 
will need to supply a PIN to access the PHR. The PIN is normally 
communicated privately to you, via phone or fax, but may be included as 
part of the subject line for this email, or as part of special comment 
from the Sender.

Once you have opened the PHR, you can annotate it and/or forward it to 
another user with the big SEND button located on the right side of your 
screen demo <http://medcommons.net/tour.html>

Your PHR is maintained for free by MedCommons for thirty days. To keep 
your PHR beyond that time please register 
<http://www.medcommons.net/register> for a free account.

------------------------------------------------------------------------

HIPAA Security and Privacy Notice: The Study referenced in this 
invitation contains Protected Health Information (PHI) covered under the 
HEALTH INSURANCE PORTABILITY AND ACCOUNTABILITY ACT OF 1996 (HIPAA). The 
MedCommons user sending this invitation has set the security 
requirements for your access to this study and you may be required to 
register with MedCommons prior to viewing the PHI. Your access to this 
Study will be logged and this log will be available for review by the 
sender of this invitation and authorized security administrators.

For more information about MedCommons privacy and security policies, 
please visit http://www.medcommons.net

EOF;

$html = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html;charset=ISO-8859-1" http-equiv="Content-Type">
</head>
<body bgcolor="#ffffff" text="#000000">
<img src="cid:logo" />
<br>
<p> You have been invited to view a Personal Health Record on <a
 href="http://www.medcommons.net/">MedCommons</a> with Tracking Number
 <a href=https://www.medcommons.net/secure/trackemail.php?a=251269944599>2512 6994 4599</a>. You will need to supply a PIN to access the PHR. The PIN
is normally communicated privately to you, via phone or fax, but may be
included as part of the subject line for this email. </p>

<p> Once you have opened the PHR, you can annotate it and/or forward it
to another user with the big SEND button located on the right side of
your screen </p>
<p> Your PHR is maintained for free by MedCommons for thirty days. To
keep your PHR beyond that time please <a
 href="http://www.medcommons.net">register</a> for a free
account. </p>
<p> </p>
<hr>
<p> HIPAA Security and Privacy Notice: The Study referenced in this
invitation contains Protected Health Information (PHI) covered under
the HEALTH INSURANCE PORTABILITY AND ACCOUNTABILITY ACT OF 1996
(HIPAA). The MedCommons user sending this invitation has set the
security requirements for your access to this study and you may be
required to register with MedCommons prior to viewing the PHI. Your
access to this Study will be logged and this log will be available for
review by the sender of this invitation and authorized security
administrators. </p>
<p> <small> For more information about MedCommons privacy and security
policies, please visit <a href="http://www.medcommons.net">http://www.medcommons.net</a>

</small> </p>

</body>
</html>
EOF;

send_mc_email("Terence Way <terry@wayforward.net>",
	      "MedCommons Notification for Giggles", $text, $html,
	      Array("logo" => get_logo_as_attachment()));
?>
