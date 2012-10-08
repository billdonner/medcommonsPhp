{% extends "email/hipaa.text" %}
{% block content %}
A fax was received into a MedCommons HealthURL. 
Access is restricted to authorized users. The Tracking Number for 
this transaction is <?php echo $trackinghtml; ?>. A PIN may 
be required to access this Tracking Number.

<?php if (isset($b)) { ?>
Sender Comment:
---------------
<?php echo $b; ?>

---------------
<?php } ?>
{% endblock content %}
