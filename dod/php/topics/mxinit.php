<?php
//quick parser to harvest links
/*


&url - page to start the harvest on


*/
//
if (!isset($_REQUEST['url'])||!isset($_REQUEST['title'])) die("usage:  ?url=starting url &title=title");


$db = $_REQUEST['db'];
$f = $_REQUEST['url']; // file
$title = $_REQUEST['title'];

mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");
// empty existin
$empty1 = "DELETE from mcdirlinks";
mysql_query($empty1) or die("Cant $empty1 ".mysql_error());
$empty2 = "DELETE from mcdirpages";
mysql_query($empty2) or die("Cant $empty2 ".mysql_error());


// write an initial record
$insert = "REPLACE INTO mcdirlinks SET label='$title',link='$f',level='0',parentlink='http://www.medcommons.net'";
mysql_query($insert) or die("Cant $insert ".mysql_error());
$parentid = mysql_insert_id();
echo "Inserted initial record $parentid title $title url $f\r\n";

?>