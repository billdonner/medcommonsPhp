{% extends "email/base.text" %}

{% block content %}
A password change has been requested for your
{{ ApplianceName }} account<?= $plural ?>.

If you requested this change, click on the following link<?= $plural ?> and choose
a password.

<?php

foreach ($rows as $row) {
  echo $row['mcid'];
  echo ' ';
  echo $row['url'];
  echo "\n";
}

?>

It is safe to ignore this email if you haven't requested a password
change.  Your password<?= $plural ?> will not be changed.
{% endblock %}
