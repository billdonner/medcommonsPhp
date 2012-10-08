{% extends "www/base.html" %}

{% block meta %}
<base href='{{ Site }}/' />
{% endblock %}

{% block header %}
</h2>

  <div id='breadcrumbs'>
<a href='/acct/backup_01.php'>1) Set up encryption</a> &gt;
<a href='/acct/backup_02.php'>2) Choose service</a> &gt;
<b>3) Configure service</b>
  </div>
<h1>User Backups</h1>
<h2>3) Configure service</h2>
{% endblock header %}

{% block main %}
<div id='content'>
<form method='post' action='acct/backup_03.php'>
  <table>
    <tbody>
      <tr>
	<td colspan='2'>
The MedCommons backup service will store your compressed, encrypted
backup in the S3 bucket you specify.
	</td>
      </tr>

      <tr>
	<th>
	  <label for='id_bucket'>S3 Bucket:</label>
	</th>
	<td>
	  <input type='text' name='bucket' id='id_bucket' />
	</td>
      </tr>

      <tr>
	<td colspan='2'>
We recommend that you give
write access to this bucket to <?= $s3_user ?>.
Then this appliance does not have your secret key.
<br />
To do this, leave the <b>S3 Key ID</b> and <b>S3 Key</b> fields blank.
	</td>
      </tr>

      <tr>
	<th>
	  <label for='id_key_id'>S3 Key ID:</label><br />
	</th>
	<td>
	  <input type='text' name='key_id' id='id_key_id' />
	</td>
      </tr>

      <tr>
	<th>
	  <label for='id_key'>S3 Key:</label>
	</th>
	<td>
	  <input type='text' name='key' id='id_key' />
	</td>
      </tr>
    </tbody>
  </table>

  <input type='submit' value='Next &gt;&gt;' />
</form>
</div>
{% endblock main %}
