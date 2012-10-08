<?php	
require 'settings.php';

try {
  $db = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);

  $stmt = $db->prepare("SELECT property, value FROM mcproperties");

  if (!$stmt) {
    print_r($db->errorInfo());
    die();
  }

  $stmt->execute();

  while ($row = $stmt->fetch()) {
    ${$row['property']} = $row['value'];
  }
} catch (PDOException $e) {
  print "Error! ";
  print $e->getMessage();
  die();
}
?>
