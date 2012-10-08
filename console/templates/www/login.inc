      <tr>
	<td width='225' align='right'>
{% if OpenIDMode %}
	MCID, Email, or OpenID:
{% else %}
	MCID or Email:
{% endif %}
	</td>
	<td width='200' align='left'>
	  <input class='infield' type='text' name='openid_url'
{% if OpenIDMode %} id='openid_url' {% endif %} />
	</td>
      </tr>
      <tr>
	<td></td>
	<td class='user-tools'>
<input type='image' src='{{ Site }}/images/favicon.gif' width='16' height='16'
       class='icon' alt='Sign in using MedCommons' />sign in using MedCommons
<input type='hidden' id='idptype' name='idptype' value=''/>
{% if OpenIDMode %}
<div>
<input type='image' src='{{ Site }}/acct/images/openid-icon-tiny.gif' alt='OpenID' name='idp' value='openid' width='16' height='16' class='icon' />sign in using OpenID URL</div>
{% endif %}
	</td>
      </tr>
{% if OpenIDMode %}
      <tr>
 	<td align='right'>
{% ifequal OpenIDMode 1 %}
You must have an OpenID URL
provided by one of these OpenID Identity Providers:
{% endifequal %}
{% ifequal OpenIDMode 2 %}
We recommend these OpenID Identity Providers:
{% endifequal %}
	</td>
	<td>
{% for i in idps %}
  {% if i.display_login %}
  <div>
  <input type='image' src='/images/idps/{{ i.source_id }}.png' alt='{{ i.name }} OpenID'
         name='idp' value='{{ i.source_id }}' width='16' height='16'
         onclick='document.getElementById("idptype").value="{{ i.source_id }}";'
         class='icon' />{{ i.name }}</div>
  {% endif %}
{% endfor %}
	</td>
      </tr>
{% endif %}
