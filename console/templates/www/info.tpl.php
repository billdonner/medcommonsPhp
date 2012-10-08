{% extends "www/base.html" %}

{% block head %}
<style type='text/css'><!--
table td, table th {
  text-align: left;
  padding: 5px 30px;
}

// --></style>
{% endblock head %}

{% block main %}
<br style='clear:both;'/>
<h2>Appliance Status</h2>
<table border='0'>
  <tbody>
    <tr>
      <td>
<img src="/ops/mpng.php?t=mc&amp;url=http://mcid.internal:1080/status&amp;name=Locals" alt="Locals" />
      </td>
      <td>
<img src='/ops/mpng.php?t=db&amp;url={{ Site }}/centralstatus.php&amp;name=DB' alt='DB' />

      </td>
      <td>
<img src='/ops/mpng.php?t=ap&amp;url={{ Site }}/appsrvstatus.php&amp;name=AP' alt='AP' />
      </td>
      <td>
<img src="/ops/mpng.php?t=gw&amp;url={{ Site }}/router/status.do?fmt=xml&amp;name=GW" alt="GW" />
      </td>
    </tr>
<!--
    <tr>
      <td></td>
      <td><a href='status'>apache</a>
<a href='php.php'>php</a>
      </td>
      <td></td>
    </tr>
-->
  </tbody>

</table>
<h2>Appliance Version Information</h2>
<p style='text-align: center;'>
  <table border='1'>
    <thead>
      <tr>
	<th>Component</th>
	<th>Revision</th>
	<th>Revision Timestamp</th>
      </tr>
    </thead>
    <tbody>
      <tr>
	<td>Console</td>
	<td><?= $console_revision ?></td>
	<td><?= $console_timestamp ?></td>
      </tr>
      <tr>
	<td>Account Service</td>
	<td><?= $account_revision ?></td>
	<td><?= $account_timestamp ?></td>
      </tr>
      <tr>
	<td>Secure Service</td>
	<td><?= $secure_revision ?></td>
	<td><?= $secure_timestamp ?></td>
      </tr>
      <tr>
	<td>Gateway</td>
	<td><?= $gateway_revision ?></td>
	<td><?= $gateway_timestamp ?></td>
      </tr>
    </tbody>
  </table>
</p>
{% endblock main %}
