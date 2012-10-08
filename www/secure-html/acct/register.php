<? 
require_once "dbparamsidentity.inc.php";
if($GLOBALS["Enable_Local_Register"] != "true") { ?>
  <html><body><p>This page is disabled</p></body></html>
<?
  exit;
}?>
<html>
  <head>
    <style type="text/css">
      body { 
        font-family: arial;
      }
    </style>
  </head>
  <body onload="if(window.init) init();">
  <?
  if(isset($HTTP_POST_VARS['mcid'])) {
    $mcid = $HTTP_POST_VARS['mcid'];
    $db=$GLOBALS['DB_Database'];
    mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']) or die ("can not connect to mysql");
    mysql_select_db($db) or die ("can not connect to database $db");
    $result = mysql_query("select * from users where mcid = '$mcid' ") or die("Unable to select from users table");
    $acct = mysql_fetch_object($result);
    if($acct) {
      echo "<script type='text/javascript'>
           function init() {
             var expire = new Date();
             expire.setTime(new Date().getTime() + 3600000*24*1);
             document.cookie = 
                'mc=' + escape('mcid=$mcid,from=Test,fn=$first_name,ln=$last_name,email=$email')
                             + ';expires='+expire.toGMTString()
                             + ';path=/';
             document.location.href = 'goStart.php';
           }
           window.init = init;
           </script>
           <h4 id='heading'>You have been logged in with id $mcid.</h4>
           ";
      }
      else {
        echo "<p style='color: red;'>Unable to log in with account id $mcid.  Please check the account exists.</p> ";
      }
  }
  else
  if(isset($HTTP_POST_VARS['email'])) { 
    $email = $HTTP_POST_VARS['email'];
    $first_name = $HTTP_POST_VARS['first_name'];
    $last_name = $HTTP_POST_VARS['last_name'];
    
    // find an entry in the CCR log for this account and status
    $db=$GLOBALS['DB_Database'];
    mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User'], $GLOBALS['DB_Password']) or die ("can not connect to mysql");
    mysql_select_db($db) or die ("can not connect to database $db");
    
    // hack learned from bill: make a 16 digit string.
    $mcid =  rand(1000,9999).rand(1000,9999).rand(1000,9999).rand(1000,9999);
    $result = mysql_query("insert into users (mcid, email, sha1,server_id, since, first_name, last_name, updatetime)
                 values('$mcid', '$email', '2CEE3C63210829F3E9D3768DBE4C4D12AD784B31', 1, NOW(), '$first_name', '$last_name', NOW());");
    if($result) {
      echo "
      <script type='text/javascript'>
      function login() {
         var expire = new Date();
         expire.setTime(new Date().getTime() + 3600000*24*1);
         document.cookie = 
            'mc=' + escape('mcid=$mcid,from=Test,fn=$first_name,ln=$last_name,email=$email')
                         + ';expires='+expire.toGMTString()
                         + ';path=/';
         document.getElementById('heading').innerText='You have been logged in with id $mcid';
      }

      function ccrlog() {
        login();
        document.location.href = 'goStart.php';
      }
      </script>
      <h4 id='heading'>User $email was successfully created with medcommons id $mcid</h4>
      <p><a href='javascript:login();'>login</a>&nbsp;<a href='javascript:ccrlog();'>CCR Log</a></p>
      ";
    }
    else 
      echo "<h4>Unable to create user.   Hmmmm.</h4>";
  }
  
?>
    <h3>Enter account data here:</h3>
      <form method='post' action='register.php'>
	      <fieldset>
		<legend>Account</legend>

		<table border='0'>
		  <tr>
		    <td colspan='2'>
		      <label>Email:<br />
        <input name='email' size='36' value='<?echo"$email";?>'/>
		      </label>

		      
		    </td>
		  </tr>
		  <tr>
		    <td>
		      <label>First name:<br />
        <input name='first_name' size='16' value='<?echo"$first_name";?>' />
		      </label>
		    </td>
		    <td>
		      <label>Last name:<br />
        <input name='last_name' size='16' value='<?echo"$last_name";?>' />
		      </label>
		    </td>
		  </tr>
		</table>
	      </fieldset>

	<input type='hidden' name='userId' value='' />
	<input type='hidden' name='sourceId' value='' />
  <br/>
	<input type='submit' value='Register' />
  </form>
  <h3>Login to Existing Account:</h3>
  <form name="loginForm" method="post" action="register.php">
    <fieldset>
      <label>Account ID:</label> <input type="text" value="" size="20" name="mcid"/>
      <input type="submit" value="Login"/>
    </fieldset>
  </form>
  </body>
</html>
  
