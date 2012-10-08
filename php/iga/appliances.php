<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en'>
  <head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title></title>
    <link rel='Stylesheet' type='text/css' href='style.css' />
  </head>
  <body>

    <table>
      <thead>
	<tr>
	  <th>Name</th>
	  <th>Home Page</th>
	  <th>Console</th>
	  <th>Administrator</th>
	</tr>
      </thead>
      <tbody>

<?php 

require 'settings.php';

$db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS,
	      $DB_SETTINGS);

$stmt = $db->prepare("SELECT name, url, email FROM appliances");
$result = $stmt->execute();
while ($row = $stmt->fetch()) {

  echo "\t<tr>\n";
  echo "\t  <td>" . $row['name'] . "</td>\n";
  echo "\t  <td><a href='" . $row['url'] . "'>" . $row['url'] . "</a>\n";
  echo "\t  <td><a href='" . $row['url'] . "/console/'>" . $row['url'] . "/console/</a>\n";
  echo "\t  <td><a href='mailto:" . $row['email'] . "'>" . $row['email'] . "</a>\n";

  echo "\t</tr>\n";
}
 
?>

      </tbody>
    </table>
  </body>
</html>
