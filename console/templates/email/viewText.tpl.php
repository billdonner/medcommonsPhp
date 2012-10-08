{% extends "email/hipaa.text" %}
{% block content %}
You have been invited to view a Personal Health Record (PHR) on
{{ ApplianceName }} with Tracking Number <?php echo $prettytracking; ?>
(<?php echo $trackingurl . '?a=' . $trackingnum; ?>).

Log in to your {{ ApplianceName }} Account or alternatively supply a PIN
to access the PHR. The PIN is normally communicated privately to
you, via phone or fax, but may be included as part of the subject
line for this email.

Once you have opened the PHR, you can annotate it and/or forward
it to another user using the SEND button located on the right side
of your screen. 

Unregistered PHRs are retained for free for thirty
days.  Please register to store the PHR beyond that time.  Visit
<{{ Site }}> for a free account.

<?php if (isset($b)) { ?>
Sender Comment:
---------------
<?php echo $b; ?>

---------------
<?php } ?>
{% endblock content %}
