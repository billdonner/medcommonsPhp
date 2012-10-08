{% extends "email/hipaa.html" %}

{% block title %}Emergency CCR Access Notification{% endblock %}
{% block content %}	
<p>
The Emergency CCR for your account <?php echo $accid; ?> registered to
<?php echo $fn . ' ' . $ln; ?>
(<?php echo $email; ?>) has been accessed from
<?php echo $remoteaddr; ?>.
</p>
{% endblock content %}
