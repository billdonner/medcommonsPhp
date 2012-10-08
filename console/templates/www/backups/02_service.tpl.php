{% extends "www/base.html" %}

{% block meta %}
<base href='{{ Site }}/' />
{% endblock %}

{% block header %}
</h2>
<div id='breadcrumbs'>
<a href='/acct/backup_01.php'>1) Set up encryption</a> &gt;
<b>2) Choose service</b> &gt;
3) Configure service
</div>
<h1>User Backups</h1>
<h2>2) Choose service</h2>
{% endblock header %}

{% block main %}
<div id='content'>
  <p>
Your backups can be sent to a number of different storage providers.
  </p>
  <p>
Choose one:
  </p>

<form method='post' action='/acct/backup_02.php'>
 <table>
  <tr>
   <td>
    <input type='radio' name='service' value='s3' <?= $s3 ?>>S3</input>
   </td>
   <td>
    <a href='http://aws.amazon.com/s3'>Amazon's Simple Storage Service</a>
   </td>
  </tr>

  <tr>
   <td>
    <input type='radio' name='service' value='ftp' <?= $ftp ?>>FTP</input>
   </td>
   <td>
    Internet standard File Transfer Protocol
   </td>
  </tr>

  <tr>
   <td>
    <input type='radio' name='service' value='http' <?= $http ?>>HTTP</input>
   </td>
   <td>
    Internet standard, includes WebDAV
   </td>
  </tr>

  <tr>
   <td>
    <input type='radio' name='service' value='scp' <?= $scp ?>>scp</input>
   </td>
   <td>
    SCP - SSH file copy
   </td>
  </tr>
 </table>

 <input type='submit' name='next' value='Next &gt;&gt;' />
</form>
</div>
{% endblock main %}
