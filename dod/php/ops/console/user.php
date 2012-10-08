<?
  require_once "urls.inc.php";
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <link rel="stylesheet" href="admin.css" type="text/css"/>
    <title><?php echo $Secure_Url; ?> User Administration</title>
  </head>
  <body>
  <div id='header'>
    <h1><?php echo $Secure_Url; ?> User Administration</h1>
    <div id='user_tools'>
      Logged in as <b><?php echo $_SERVER['PHP_AUTH_USER']; ?></b>
    </div>
  </div>

  <div id="contents">

   <form method='get' action='user.php'>
 
   <label>User query:
      <input type='text' name='q' value='<?
 if (isset($_GET['q']))
   echo htmlentities($_GET['q']);
?>' /></label>

      <input type='submit' value='Search' />
    </form>

<p>
You may query for users by entering either:
</p>
<ol>
<li>A 16-digit MCID (for example 0123-4567-8901-2345);</li>
<li>An email address (for example terry@wayforward.net)</li>
<li>any combination of first name, last name</li>
<li>Case ID (coming soon)</li>
</ol>

<?php

require 'settings.php';

function nice_mcid($mcid) {
  return substr($mcid, 0, 4) . '-' .
    substr($mcid, 4, 4) . '-' .
    substr($mcid, 8, 4) . '-' .
    substr($mcid, 12);
}

function normalize_mcid($mcid) {
  $x = split("[ \t-.]", $mcid);
  return implode('', $x);
}

function is_mcid($mcid) {
  if (strlen($mcid) != 16)
      return False;
    else
      return preg_match('/^[0-9]*$/', $mcid);
}

if (isset($_GET['q'])) {
  /*
   * Query for users based on the 'q' parameter
   */
  $q = trim($_GET['q']);

  try {
    $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS, $DB_SETTINGS);

    /*
     * what kind of query?
     * email - if $q has a '@'
     * mcid - mostly numbers
     * name otherwise
     */
    if (strstr($q, '@')) {
      $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
      $args = array("email" => $q);
    }
    else {
      $mcid = normalize_mcid($q);

      if (is_mcid($mcid)) {
	$args = array($mcid);
	$stmt = $db->prepare("SELECT * FROM users WHERE mcid = ?");
      }
      else {
	$x = split("[ \t,]", $q);
	$i = 0;

	$query = "SELECT * FROM users WHERE ";
	$query .= "(first_name = :v0 OR last_name = :w0)";
	$args = array("v0" => $x[0], "w0" => $x[0]);

	for ($i = 1; $i < count($x); $i++) {
	  $v = "v" . $i;
	  $w = "w" . $i;

	  $query .= " AND (first_name = :" . $v . " OR last_name = :" . $w . ") ";
	  $args[$v] = $x[$i];
	  $args[$w] = $x[$i];
	}

	$stmt = $db->prepare($query);
      }
    }

    if (!$stmt) {
      print_r($db->errorInfo());
      die();
    }

    if (!$stmt->execute($args)) {
      print_r($db->errorInfo());
      die();
    }
    else {

?>
<table id="users">
  <thead>
    <tr>
      <th>MCID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Account Created</th>
      <th>CCR Log Updated</th>
    </tr>
  </thead>

  <tbody>
<?php

    while ($row = $stmt->fetch()) {
      print "<tr>\n";
      print "  <td>\n";
      print "<a href='edit.php?mcid=";
      print $row['mcid'];
      print "'>";
      print nice_mcid($row['mcid']);
      print "</a>";
      print "  </td>\n";

      print "  <td>\n";
      print $row['first_name'];
      print ' ';
      print $row['last_name'];
      print "  </td>\n";

      print "  <td>\n";
      print "<a href='user.php?q=";
      print $row['email'];
      print "'>";
      print $row['email'];
      print "</a>\n";
      print "  </td>\n";

      print "  <td>\n";
      print $row['since'];
      print "  </td>\n";

      print "  <td>\n";
      print date('Y-m-d H:i:s', $row['ccrlogupdatetime']);
      print "  </td>\n";

      print "</tr>\n";
    }
?>
  </tbody>
</table>

<?php
      }

  } catch (PDOException $e) {
    print "Error! ";
    print $e->getMessage();
    die();
  }
 }

?>


    </div>
  </body>
</html>
