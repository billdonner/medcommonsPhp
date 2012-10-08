{% extends "email/base.text" %}
{% block content %}
To complete your {{ ApplianceName }} registration, please use this link:
    <?php echo $url; ?>

You may need to cut and paste this link into your preferred
web browser.
{% endblock content %}
