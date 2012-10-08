{% extends "email/hipaa.html" %}
{% block title %}Emergency CCR Reset Notification{% endblock %}
{% block content %}
<p>
The Emergency CCR for account <?php echo $accid; ?> has been reset.
</p>
{% endblock content %}
