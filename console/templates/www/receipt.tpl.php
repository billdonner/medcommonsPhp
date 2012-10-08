{% extends "www/base.html" %}

{% block head %}
<script type='text/javascript'>
var printed = false;
window.onbeforeunload=function() {
  if(!printed) {
    return 'Please make sure you have printed or saved this page.\n\n'
      +'You will need the the keys displayed below to access support for your account.\n\n'
      +'Your support '
      +'keys cannot be displayed to you again after you leave this page.'
      ;
  }
};
</script>
<style type='text/css'>
table tr th {
  font-size: 13px;
  text-align: right;
  padding-right: 2em;
}

@media print {
  .noprint {
    display: none; 
  }
  #info {
   display: none;
  }
}


</style>
{% endblock head %}

{% block main %}
<div id='ContentBoxInterior'>
<h2>Thank you!</h2>

<p>
Please <strong>print this page out</strong> for your records.
This is your MedCommons <strong>Registration Receipt</strong>.
</p>
<p>
Each one of the <strong>Recovery Passwords</strong> listed below can only
be used once, in order.
</p>
<p>
Cross out each one as you use them.  This protects against someone
using your account without you knowing it!
</p>

<table>
 <tbody>
  <tr>
   <th>For:</th>
   <td><?php echo $first_name . ' ' . $last_name; ?></td>
  </tr>
  <tr>
   <th>Email:</th>
   <td><?php echo $email?></td>
  </tr>
  <tr>
   <th>Domain:</th>
   <td><?php echo $domain?></td>
  </tr>
  <tr>
   <th><acronym title='MedCommons ID'>MCID</acronym>:</td>
   <td><?php echo $mcid; ?></td>
  </tr>
  <tr>
   <th>Recovery Passwords:</th>
   <td>

<?php

	$i = 1;
	while ($skey) {
	  echo $i;
	  echo ". ";
	  echo array_pop($skey);
	  echo "<br />\n";
	  $i++;
	}
?>

    </td>
   </tr>
   <tr><th>&nbsp;</th><td>&nbsp;</td></tr>
   <tr class='noprint'><th>&nbsp;</th><td><button style='width: 100%;' onclick='window.print();printed=true;'>Print Page</button></td></tr>
  </tbody>
 </table>
</div>
{% endblock main %}
{% block footer %}{% endblock %}
