<?php
/**
* Returns events from the CCR Event table using any of the following parameters
**/
require_once "dbparamsidentity.inc.php";

mysql_connect($GLOBALS['DB_Connection'],
    $GLOBALS['DB_User'],
    $GLOBALS['DB_Password']
    ) or die ("can not connect to mysql");
    $db = $GLOBALS['DB_Database'];
    mysql_select_db($db) or die ("can not connect to database $db");

// normal case taken from post args (int=0 for oneshot else int=inteverval)

require_once "args.inc.php";

if ($limit=='') $limit=20; else if ($limit>20) $limit=20;

$lasttime = cleanreq('lt');
if ($lasttime=='') $lasttime=0;

require_once "where.inc.php";

$wherestring = "wc:$wc limit:$limit int:$int query:$whereclause"; 

require_once "content.inc.php";
$synch = time();
// make a html header and insert the first round of content in the body
$body = <<<XXX
<ajblock><timesynch>$synch</timesynch><content>$content</content></ajblock>
XXX;


echo $body;

?>