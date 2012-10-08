{% extends "email/hipaa.text" %} 
{% block content %}
Account <?php echo $accid; ?> registered to <?php
echo $fn . ' ' . $ln; ?> (<?php echo $email; ?>)
has been accessed from <?php echo $remoteaddr; ?>.
{% endblock content %}
