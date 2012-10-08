{% extends "email/hipaa.text" %}

{% block content %}
A CCR from account <?php echo $accid; ?> has been deleted.
{% endblock %}
