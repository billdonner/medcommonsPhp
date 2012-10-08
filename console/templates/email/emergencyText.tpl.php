{% extends "email/hipaa.text" %}
{% block content %}
The Emergency CCR for your account <?php echo $accid; ?> registered to
<?php echo $fn . ' ' . $ln; ?> (<?php echo $email; ?>) has been accessed from <?php echo $remoteaddr; ?>.
{% endblock content %}
