{% extends "email/hipaa.text" %}
{% block content %}
The Emergency CCR for account <?php echo $accid; ?> has been reset.
{% endblock %}
