<div class='f'>
  <label for='openid_url'>&nbsp;</label>
  <span class='q'>
    <input class='infield' type='text' name='openid_url' size='30'
{% if OpenIDMode %} id='openid_url' {% endif %} />
    <input type='submit' value='Sign In' />
    <em class='r'>
{% if OpenIDMode %}
      <div>http://user.myopenid.com</div>
{% endif %}
      <div>user@email.com</div>
      <div>1283-2124-7623-1981</div>
    </em>
  </span>
</div>
<input type='hidden' id='idptype' name='idptype' value=''/>
