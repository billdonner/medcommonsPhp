{% if members %}
<p>
This group has the following members:
</p>

<table>
  <thead>
    <th>MCID</th>
    <th>Name</th>
    <th></th>
  </thead>
  <tbody>
{% for u in members %}
    <tr class='{% cycle a,b %}'>
      <td>{{ u.pretty_mcid }}</td>
      <td>{{ u.first_name }} {{ u.last_name }}</td>
      <td><form action='{{ action }}' method='post'>
      <input type='hidden' name='mcid' value='{{ u.mcid }}' />
      <input type='image' name='remove' src='{{ media }}img/icon_deletelink.gif'
             width='10' height='10' alt='Remove User from Group' />
	     </form></td>
    </tr>
{% endfor %}

  </tbody>
</table>

{% else %}
<p>
This group has no members.
</p>

{% endif %}

<p>
You can add members in the following list.

If the user is already a member of another group,
   that user will be removed from that group, and added to this group.
</p>

<table>
  <thead>
    <tr>
      <td>MCID</td>
      <td>Name</td>
      <td>Email</td>
      <td>Group</td>
      <td>Account Created</td>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan='5'>
	<span style='float: left'>
{% if has_previous %}
<a href='{{ action }}&amp;page=1'>&laquo; first</a>
<a href='{{ action }}&amp;page={{ previous }}'>&lt; prev</a>
{% endif %}
	</span>
	<span style='float: right'>
{% if has_next %}
<a href='{{ action }}&amp;page={{ next }}'>next &gt;</a>
<a href='{{ action }}&amp;page={{ pages }}'>last &raquo;</a>
{% endif %}
	</span>
      </td>
    </tr>
    <tr>
      <td colspan='5'>
	<span style='float: right'>
Page {{ page }} of {{ pages }}.
	</span>
      </td>
    </tr>
    <tr>
      <td colspan='5'>
<form method='get' action='{{ action }}'>
  <label>Search Users:
    <input type='text' name='q' value='{{ q }}' /></label>
  <input type='hidden' name='id' value='{{ id }}' />
  <input type='image' src='{{ media }}img/icon_searchbox.png' width='18' height='16'
         name='search' alt='Search Users' />
</form>
      </td>
    </tr>
  </tfoot>

  <tbody>
{% for entry in object_list %}
    <tr class='{% cycle a,b %}'>
      <td>
        <a href='../users/user?mcid={{ entry.mcid }}'>{{ entry.pretty_mcid }} </a>
      </td>
      <td>{{ entry.first_name }} {{ entry.last_name }}</td>
      <td>{{ entry.email }}</td>
      <td>{% if entry.groupname %}{{ entry.groupname }}{% endif %}</td>
      <td>{{ entry.since }}</td>
      <td><form method='post' action='{{ action }}'>
      <input type='hidden' name='mcid' value='{{ entry.mcid }}' />
      <input type='image' name='add' src='{{ media }}img/icon_addlink.gif'
             width='10' height='10' alt='Add User to this Group' />
	     </form></td>
    </tr>
{% endfor %}
  </tbody>
</table>

