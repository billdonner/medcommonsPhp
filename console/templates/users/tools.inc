{% if mcuser.claimable %}
  <li><a href='claim?mcid={{ mcuser.mcid }}'>Claim Account</a></li>
{% else %}
  <li><a href='password?mcid={{ mcuser.mcid }}'>Change Password</a></li>
{% endif %}
{% ifnotequal mcuser.acctype 'DISABLED' %}
  <li><a href='login_as?mcid={{ mcuser.mcid }}'>Login As</a></li>
  <li><a href='disable?mcid={{ mcuser.mcid }}'>Disable Account</a></li>
{% endifnotequal %}
