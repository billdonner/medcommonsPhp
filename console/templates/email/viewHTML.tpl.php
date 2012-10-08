{% extends "email/hipaa.html" %}
{% block title %}{{ ApplianceName }} Invitation{% endblock %}
{% block head %}
  <style type='text/css'><!--
body {
  background-color: white;
  color: black;
}
// --></style>
{% endblock head %}
{% block content %}
<p>
  You have been invited to view a Personal Health Record (PHR) on
  {{ ApplianceName }} with Tracking Number <?php echo $trackinghtml; ?>.

  Log in to your an authorized account or alternatively supply a PIN to access the PHR. The PIN is normally communicated privately to you, via phone or fax, but may be included as part of the subject line for this email.
</p>
  <?php if (isset($b)) { ?>
<p>
Sender Comment:
<br />
<?php echo $b; ?>
</p>
<?php } ?>
{% endblock content %}
