{% extends "email/base.html" %}

{% block title %}{{ ApplianceName }} Password Recovery{% endblock %}

{% block content %}
<p>
A password change has been requested for your
{{ ApplianceName }} account<?= $plural ?>.
</p>
<p>
If you requested this change, click the following link<?= $plural ?> and
choose a password.
</p>
<ul>
<?php foreach ($rows as $row) { ?>
  <li>
   <a href='<?php echo $row['url']; ?>'>
     <?php echo $row['mcid']; ?>
   </a>
  </li>
<?php } ?>
</ul>

<p>
It is safe to ignore this email if you haven't requested a new password.
Your password<?= $plural ?> will not be changed.
</p>

{% endblock content %}
