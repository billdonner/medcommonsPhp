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
  A fax was received into a MedCommons HealthURL. Access is restricted to 
  authorized users. The Tracking Number for this transaction is <?php echo $trackinghtml; ?>.
  A PIN may be required to access this Tracking Number.
</p>

  <?php if (isset($b)) { ?>
<p>
Sender Comment:
<br />
<?php echo $b; ?>
</p>
<?php } ?>
{% endblock content %}
