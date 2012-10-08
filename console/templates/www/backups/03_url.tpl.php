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
  <input type='hidden' name='service' value='<?= $service ?>' />

  <table>
    <tbody>
      <tr>
	<th>
	  <label for='id_host'>Host:</label>
	</th>
	<td>
	  <input type='text' name='host' id='id_host' />
	</td>
      </tr>

      <tr>
	<th>
	  <label for='id_path'>Path:</label>
	</th>
	<td>
	  <input type='text' name='path' id='id_path' />
	</td>
      </tr>

      <tr>
	<th>
	  <label for='id_user'>Username:</label><br />
	</th>
	<td>
	  <input type='text' name='user' id='id_user' />
	</td>
      </tr>

      <tr>
	<th>
	  <label for='id_pass'>Password:</label>
	</th>
	<td>
	  <input type='password' name='pass' id='id_pass' />
	</td>
      </tr>
    </tbody>
  </table>

  <input type='submit' value='Next &gt;&gt;' />
</form>
</div>
{% endblock main %}
