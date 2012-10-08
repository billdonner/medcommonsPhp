{% extends "email/base.html" %}
{% block title %}{{ ApplianceName }} - Verifying Your Email{% endblock %}
{% block content %}
<p>
To complete your {{ ApplianceName }} registration, please use this link:<br />
 <a href='<?php echo $url; ?>'><?php echo $url; ?></a>
</p>
{% endblock content %}
