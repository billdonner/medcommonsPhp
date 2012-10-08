{% extends "www/base.html" %}

{% block meta %}
<base href='{{ Site }}/' />
{% endblock %}

{% block header %}
</h2>
<div id='breadcrumbs'>
<b>1) Set up encryption</b> &gt;
2) Choose service &gt;
3) Configure service
</div>
<h1>User Backups</h1>
<h2>1) Set up encryption</h2>
{% endblock header %}

{% block main %}
<div id='content'>
  <p>
Your backups can be encrypted so only you can recover the data.
  </p>
  <p>
This requires either:
  </p>
<ol>
 <li>a <b>personal certificate</b>, issued by a
<b>Certificate Authority</b>,</li> or
 <li>a <b>PGP</b> public key, or</li>
 <li>a password shared by you and this Appliance.
</ol>

<form method='post' action='/acct/backup_01.php'
      enctype='multipart/form-data'>

  <label>Certificate or password:
  <textarea name='cert' style='font-family: courier, monospace'
	    cols='72' rows='14'><?= $cert ?></textarea>
  </label>

  <label>Upload file:
  <input type='file' name='file' /></label>
  <input type='submit' name='upload' value='Upload file'>
  <br />

  <input type='submit' name='next' value='Next &gt;&gt;' />
</form>
</div>
{% endblock main %}
