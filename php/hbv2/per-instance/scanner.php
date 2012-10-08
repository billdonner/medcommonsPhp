<?php
require_once "healthbook.inc.php";
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
connect_db();


$dash = dashboard('scanner');
$app = $GLOBALS['healthbook_application_name'];

if ( isset($_REQUEST['bind']))
{
		$totq = 0;

	$q = "SELECT groupid, Count(hurl) counter FROM topichurls group by groupid";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	while ($r=mysql_fetch_object($result))
	{
		$totq+=$r->counter;
		$qq = "replace into  groupcounts set gurlcount='$r->counter',gurlcount_modified_time=NOW(),gid='$r->groupid' ";
		mysql_query($qq) or die ("Cant $qq ".mysql_error());
	}

	$tot = 0;

	$q = "SELECT topic, Count(hurl) counter FROM topichurls group by topic";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	while ($r=mysql_fetch_object($result))
	{
		$tot+=$r->counter;
		$qq = "update topics set hurlcount='$r->counter',hurlcount_modified_time=NOW() where ord='$r->topic' ";
		mysql_query($qq) or die ("Cant $qq ".mysql_error());
	}

	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:success>
  <fb:message>$app ADMINISTRATION ONLY - SCANNED AND UPDATED</fb:message>
<p>A total of $tot public healthURLs were encountered</p>
  </fb:success>
</fb:fbml>
XXX;

} else
{
	$markup=<<<XXX
<fb:fbml version='1.1'><br/>$dash
<fb:if-is-group-member gid="5946983684" uid="$user" >
 <fb:explanation>
    <fb:message>$app ADMINISTRATION ONLY - PLEASE CONFIRM THIS OPERATION</fb:message>
    Do you really want to scan the healthbook tables? 
 
  <fb:editor action="scanner.php" labelwidth="100">
  <input type=hidden name=bind value='bind' />
      <fb:editor-buttonset>
          <fb:editor-button value="Do it"  />
     </fb:editor-buttonset>
 </fb:editor>
</fb:explanation>
  <fb:else>
  <fb:error>
      <fb:message>$app ADMINISTRATION ONLY</fb:message>
     This operation is restricted to the Healthbook Editor in Chief
 </fb:error>
 </fb:else>
</fb:if-is-group-member>
</fb:fbml>
XXX;
}
echo $markup;
?>