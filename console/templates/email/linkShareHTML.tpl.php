{% extends "email/hipaa.html" %}
{% block title %}{{ ApplianceName }} Invitation{% endblock %}
{% block head %}
  <style type='text/css'><!--
body {
  background-color: white;
  color: black;
}
// --></style>
{% endblock head %}
{% block content %}
<p>
A MedCommons member has shared a HealthURL with you.

You can access the shared content via the following link:
</p>
<p> <a href='<?=$link?>'><?=htmlentities($link)?></a> </p>
{% endblock content %}
