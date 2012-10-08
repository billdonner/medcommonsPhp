{% extends "email/base.html" %}
{% block title %}{{ ApplianceName }} Invitation{% endblock %}
{% block content %}
<p>You've been invited to join a {{ ApplianceName }} group!</p>
<p>To accept the invitation, click the link below:</p>
<p>
  <a href='<?=$url?>'><?=$url?></a>
</p>
{% endblock content %}
