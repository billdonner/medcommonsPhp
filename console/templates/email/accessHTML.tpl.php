{% extends "email/hipaa.html" %}

{% block title %}Account Display Notification{% endblock %}

{% block head %}
    <style type="text/css"><!--
table {font-size: 12px;}
th {font-size: 14px;
background: #ccc url("http://www.medcommons.net/images/ringradient.jpg");
font-weight: normal;}
tr.emergencyccr {color: red;}
p {font-size: 12px;}
// --></style>
{% endblock head %}

{% block content %}
<p>
 Account <?php echo $accid; ?> registered to
 <?php echo $fn . ' ' . $ln; ?>
 (<?php echo $email; ?>)
 has been accessed from <?php echo $remoteaddr; ?>.
</p> 

<p>The Following CCR's were displayed on the page:
</p>

<div>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Tracking</th>
        <th>To</th>
        <th>Subject</th>
      </tr>
    </thead>
    <tbody>

<?php echo $emailbuf; ?>

    </tbody>
  </table>
</div>
{% endblock content %}
