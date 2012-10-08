<?php
require_once "urls.inc.php";
?>
<html>
  <head>
    <title>MedCommons HealthURL Appliance - Console</title>
    <link type='text/css' rel='Stylesheet' href='admin.css' />
    <style type='text/css'><!--

th, td {
  text-align: left;
  vertical-align: top;
}

table td {
  border-bottom: solid #cccccc;
}

h3, .icon_label {
  font-weight: bold;
  font-size: 14pt;
}

.main_icon {
  vertical-align: bottom;
}

.icon {
  vertical-align: middle;
}

// --></style>
  </head>

  <body>

    <div id='header'>
      <h1><?php echo $Secure_Url; ?> &mdash Console</h1>
      <div id='user_tools'>
      	Logged in as <b><?php echo $_SERVER['PHP_AUTH_USER']; ?></b>
      </div>
    </div>

    <div id='content'>
     <center>
      <table border='0'>
        <tbody>
	  <tr>
	    <td>
      <a href='admins.html'>
        <img src='images/Gearwheel.png' width='20' height='20' border='0'
	     alt='Administrators' class='main_icon' /></a>
      <a href='admins.html' class='icon_label'>Administrators</a>
            </td>
	    <td>
<h3>Manage Administrators</h3>
<p>
Administrators are special separate user accounts that
have the authority to use this console, and manage the system.
</p>
<form method='get' action='admins.php'>
<a href='add_admin.html'>
    <img src='images/icon_addlink.gif' width='10' height='10' border='0'
         alt='Add Administrator' class='icon' /></a>
  <a href='add_admin.html'>add</a>
&nbsp;|&nbsp;
<a href='list_admins.html'>
  <img src='images/icon_list.gif' width='16' height='16' border='0'
       alt='List Administrators' class='icon' /></a>
<a href='list_admins.html'>list</a>
&nbsp;|&nbsp;

  <input type='text' name='q' value='search'/>

  <input type='image' src='images/icon_searchbox.png' width='18' height='16'
         name='search' alt='Search Users' />
</form>
            </td>
	  </tr>

          <tr>
            <td>
      <a href='users.html'>
        <img src='images/User.png' width='20' height='20' border='0'
	     alt='Manage Users' class='main_icon' /></a>

      <a href='users.html' class='icon_label'>Users</a>
            </td>
            <td>
<h3>Manage Users</h3>
<p>
Manage  Patient and Doctor  Accounts
</p>

<form method='get' action='user.php'>
<a href='add_user.html'>
    <img src='images/icon_addlink.gif' width='10' height='10' border='0'
         alt='Add User' /></a>
  <a href='add_user.html'>add</a>
&nbsp;|&nbsp;
<a href='list_users.html'>
  <img src='images/icon_list.gif' width='16' height='16' border='0'
       alt='List Users' class='icon' /></a>
<a href='list_users.html'>list</a>
&nbsp;|&nbsp;

  <input type='text' name='q' value='search' />

  <input type='image' src='images/icon_searchbox.png' width='18' height='16'
         name='search' alt='Search Users' />
</form>
            <td>
          </tr>

          <tr>
            <td>
      <a href='groups.html'>
        <img src='images/Users.png' width='20' height='20' border='0'
	     alt='Manage Groups' class='main_icon' /></a>
      <a href='groups.html' class='icon_label'>Groups</a>
            </td>
            <td>
<h3>Manage Groups</h3>
<p>
Manage  Patient and Doctor  Groups
</p>

<form>
 <a href='add_group.html'>
    <img src='images/icon_addlink.gif' width='10' height='10' border='0'
         alt='Add Group' /></a>
    <a href='add_group.html'>add</a>

&nbsp;|&nbsp;
<a href='list_groups.html'>
  <img src='images/icon_list.gif' width='16' height='16' border='0'
       alt='List Groups' class='icon' /></a>
<a href='list_groups.html'>list</a>
&nbsp;|&nbsp;

  <input type='text' name='search' value='search' />

  <input type='image' src='images/icon_searchbox.png' width='18' height='16'
         name='search' alt='Search Groups' />

</form>
            </td>
          </tr>

	  <tr>
            <td>
              <a href='fax.html'>
	        <img src='images/Print.png' width='20' height='20' border='0'
		     alt='Fax Management' class='main_icon' />
              </a>
	      <a href='fax.html' class='icon_label'>
	        Fax
	      </a>
            </td>
            <td>
<h3>Manage Fax Services</h3>
<p>
</p>
            </td>
          </tr>

	  <tr>
	    <td>
<a href='keys.html'>
  <img src='images/Key.png' width='20' height='20' border='0'
       alt='Account Keys' class='main_icon' /></a>
<a href='keys.html' class='icon_label'>Keys</a>
            </td>
            <td>
<h3>Account Keys</h3>
<p>
Backup and Restore Patient and Doctor Account Keys
</p>

            </td>
          </tr>

	  <tr>
	    <td>
<a href='log.html'>
  <img src='images/EditBook.png' width='20' height='20' border='0'
       alt='Access Logs' class='main_icon' /></a>
<a href='log.html' class='icon_label'>Logs</a>
            </td>
            <td>
<h3>Review Access Logs</h3>
<p>
</p>
            </td>
          </tr>

	  <tr>
	    <td>
<a href='bill.html'>
  <img src='images/Calculator.png' width='20' height='20' border='0'
       alt='MedCommons Bill' class='main_icon' /></a>
<a href='bill.html' class='icon_label'>Bill</a>
	    </td>
	    <td>
<h3>Review your MedCommons Bill</h3>
<p>
</p>
            </td>
	  </tr>
        </tbody>
      </table>



     </center><!-- jeez -->
    </div>
    
  </body>
</html>

