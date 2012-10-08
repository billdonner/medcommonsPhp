{% extends "email/hipaa.html" %}

{% block title %}Emergency CCR Deletion Notification{% endblock %}

{% block content %}
<p>
A CCR from account <?php echo $accid; ?> has been deleted.
</p>
{% endblock content %}
