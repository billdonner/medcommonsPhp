{% extends "www/base.html" %}

{% block head %}
<link rel='openid.server' href='http://{{ Domain }}/openid/server.php' />
{% endblock head %}

{% block main %}
<div id='content'>
  <p>
This account does not have a Current CCR.
  </p>
</div>
{% endblock main %}
