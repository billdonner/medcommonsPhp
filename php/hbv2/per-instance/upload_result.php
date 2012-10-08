<?
require_once 'healthbook.inc.php';
require_once "topics.inc.php";

$dash = hurl_dashboard($u->fbid,'HealthURL');
?>
<fb:fbml version='1.1'><fb:title>Health URL</fb:title>
<?=$dash?>
  <fb:explanation>
    <fb:message><p>Your document was uploaded!</p></fb:message>
  </fb:explanation>
</fb:fbml>
