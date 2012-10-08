{% extends "email/base.text" %}
{% block content %}
You've been invited to join a {{ ApplianceName }} group!

To confirm your invitation, click the link below:

<?=$url?>

You may need to cut and paste this link into your preferred
web browser.

Thank you for using {{ CommonName }},
{% endblock content %}

