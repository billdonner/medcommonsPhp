{% extends "www/base.html" %}

{% block head %}


{% endblock head %}

{% block main %}

<div id='content'>
<h2>Personal Profile</h2>

<fieldset>
  <legend>Details</legend>

  <table>
    <tbody>
      <tr>
	<th>Name:</th>
	<td><?= $first_name ?> <?= $last_name ?></td>
      </tr>
      <tr>
	<th>Email:</th>
	<td>
	  <?= $email ?><br />
	  <a href='setemail.php'>change</a>
	</td>
      </tr>
    </tbody>
  </table>
</fieldset>

<form method='post' action='picture.php' enctype='multipart/form-data'
      id='picture'>
  <fieldset>
    <legend>Your Picture</legend>

    <a href='<?= $photoUrl ?>'>
      <img src='<?= $photoUrl ?>' 
	   align='left' style='border: 0; margin: 10px' alt='User Photo' />
    </a>

    <p id='p_picture'>
      <label>Upload Image File:<br />
        <input class='infield'  type='file' name='picture' id='picture' />
      </label>
    </p>
    <input  type='submit' value='Change Picture' />
  </fieldset>
</form>

<form method='post' action='password.php' id='password'>
  <fieldset>
    <legend>Change Password</legend>

    <p id='p_pw0'>
      <label>Current Password:
        <input class='infield'  type='password' name='pw0' id='pw0' />
      </label>

<?php
	if (isset($error)) {
?>
<div class='error'><?php echo $error; ?></div>
<?php
	}
?>
    </p>

    <p id='p_pw1'>
      <label>New password:
        <input class='infield'  type='password' name='pw1' id='pw1' />
      </label>

<?php
	if (isset($pw1_error)) {
?>
<div class='error'><?php echo $pw1_error; ?></div>
<?php
	}
?>
    </p>

    <p id='p_pw2'>
      <label>New password (again):
        <input class='infield'  type='password' name='pw2' id='pw2' />
      </label>

<?php
	if (isset($pw2_error)) {
?>
<div class='error'><?php echo $pw2_error; ?></div>
<?php
	}
?>
    </p>

<?php if (isset($next)) { ?>
    <input type='hidden' value='<?php echo $next; ?>' />
<?php } ?>

    <input type='submit' value='Change Password' />
  </fieldset>
</form>
{% if idps %}
<fieldset>
  <legend>Linked External Identities</legend>
<?php if (count($external_users) > 0) { ?>
  <table>
    <thead>
      <tr>
	<th></th>
	<th><acronym title="Identity Provider">IdP</acronym></th>
        <th>External Username</th>
      </tr>
    </thead>
    <tbody>
<?php
foreach ($external_users as $i) {
  list($prefix, $suffix) = explode('%', $i['format'], 2);
  $username = $i['username'];
  $prefix_len = strlen($prefix);
  $suffix_offset = strlen($username) - strlen($suffix);
  if (substr($username, 0, $prefix_len) == $prefix &&
      substr($username, $suffix_offset) == $suffix)
    $username = substr($username, $prefix_len, $suffix_offset - $prefix_len);
?>
<tr>
  <td>
    <img src="/images/idps/<?= $i['source_id'] ?>.png" width='16' height='16'
	 alt="<?= $i['name'] ?>" />
  </td>
  <td>
    <a href="<?= $i['website'] ?>">
      <?= $i['name'] ?>
    </a>
  </td>
  <td>
    <a href="<?= $i['username'] ?>"><?= $username ?></a>
  </td>
  <td>
    <form method='post' action='unlink_user.php'>
      <input type='hidden' name='idp' value='<?= $i['id'] ?>' />
      <input type='hidden' name='username' value='<?= $i['username'] ?>' />
      <input type='image' src='/images/unlink.png' width='24' height='24'
	     alt='Unlink External User' />
    </form>
  </td>
</tr>
<?php } ?>
    </tbody>
  </table>
<?php } ?>
<form method='post' action='link_user.php' id='login'>
  <label>Link New External Account:<br />
    <input class='infield' type='text' name='user' id='user' /><br />
  </label>
<?php
	if (isset($idp_error)) {
?>
<div class='error'><?php echo $idp_error; ?></div>
<?php
	}
?>
 <input type='hidden' name='next' value='/acct/password.php' />

{% for i in idps %}
<input type='image' src='/images/idps/{{ i.source_id }}.png'
       width='16' height='16' alt='{{ i.name }} OpenID'
       name='idp' value='{{ i.source_id }}' class='logo' />
link {{ i.name }} account<br />
{% endfor %}
</form>
</fieldset>
{% endif %}

</div>
<!-- p>
<a href='{{ Site }}{{ RootPath }}/version.php'>Version Information</a> about this MedCommons Appliance
</p -->
<form id='phrs'>
   <fieldset>
    <legend>My Linked PHRs</legend>
<table>
  <tbody>
    <tr>
      <td align='right'>HealthFrame</td>
      <td><input type=text class='infield' value='4871bc3829DKSL3920eilwi2093jgpoweopijweopij' name=key></td>
    <td><a href='#'>reset</a></td>
    </tr>

    <tr>
      <td align='right'>WebMd</td>
     	 <td><input type=text   class='infield' value='4979ab2983298398398283r884ygh202839028392c' name=key></td>
           <td><a href='#'>reset</a></td>

    </tr>
 
  </tbody>
</table>
    </fieldset>
</form>
<form id='downloads'>
   <fieldset>
    <legend>Plug-in Components</legend>
<table>
  <tbody>
    <tr>
      <th>Personal Backup:</th>
    <td>
	<a href='/acct/backup_01.php'>configure</a>
      </td>
    </tr>

    <tr>
      <th>DDL:</th>
      <td>
	<a href='/DDL/'>install</a>
      </td>
 
    </tr>
    <tr>
      <th>OsiriX:</th>
      <td>
	<a href='/osirix.html'>install</a>
      </td>

    </tr>
  </tbody>
</table>
    </fieldset>
</form>

{% endblock main %}

