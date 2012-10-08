<?php
// this is required of all facebook apps
require_once "healthbook.inc.php";
require_once "topics.inc.php";


//**start here
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
connect_db();
$dash = dashboard($user,'overview');

$app = strtoupper($GLOBALS['healthbook_application_name']);

if ( isset($_REQUEST['bind']))
{
	$ord = $_REQUEST['ord'];$b=getTopicInfo($ord);
	$gid = $_REQUEST['gid'];
	$uid = $_REQUEST['uid'];
	/* absorb some metadata about the group so we can do searches */
	$qqq="SELECT name, description,recent_news FROM group   WHERE  gid IN ($gid)";
	$ret = $facebook->api_client->fql_query($qqq);
	if ($ret){

		$meta = $ret[0]['name'].' '.$ret[0]['description'].' '.$ret[0]['recent_news'];

	}
	else $meta = "Group $gid has no metadata";

	$explain = mysql_escape_string($_REQUEST['explain']);
	$q = "REPLACE INTO topicgroups set tagline='$explain',creatorfbid = '$uid', groupuid='$gid',nlmord='$ord',groupmeta='$meta'";
	mysql_query($q) or die ("Cant $q ".mysql_error());

	$blink = $GLOBALS['facebook_application_url']."topics.php?ord=$b->nlmord";
	$feed_title = "<fb:userlink uid=$user shownetwork='false' />Added group <fb:grouplink gid=$gid />
	                               to topic $b->nlmtopic ";

	$feed_body = "Check out <a href=$blink >$b->nlmtopic</a> on ".$GLOBALS['healthbook_application_name']."<p>$explain</p>";
	logMiniHBEvent($user,'HealthURL',$feed_title,$feed_body);
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
  <fb:success>
  <fb:message>$app ADMINISTRATION ONLY - JOINED</fb:message>
 <p>You have joined the group $gid with nlm topic $ord</p>
 <p>If you'd like to send the group officer a notification click <a href="http://www.facebook.com/inbox/?compose&id=$uid>here</a></p>
  </fb:success>
</fb:fbml>
XXX;

} else  if (isset($_REQUEST['explain']))

{
	$explain = $_REQUEST['explain'];
	$markup=<<<XXX
<fb:fbml version='1.1'><br/>$dash
<fb:if-is-group-member gid="5946983684" uid="$user" >
 <fb:explanation>
    <fb:message>$app ADMINISTRATION ONLY - PLEASE CONFIRM THIS OPERATION</fb:message>
    Do you really want to approve the facebook group <img src='http://static.ak.facebook.com/images/icons/group.gif?48:25796' /><fb:grouplink gid='$gid' />  as moderator of Topic $ord: 
    <a title='view this page on NLM' target='_new' href='$b->nlmurl' >$b->nlmtopic</a>  as suggested by <fb:name uid=$uid /> with this explaination $explain?
    
  <fb:editor action="groups.php" labelwidth="100">
  <input type=hidden name=bind value='bind' />
    <input type=hidden name=ord value='$ord' />
      <input type=hidden name=gid value='$gid' />
        <input type=hidden name=uid value='$uid' />
        <input type=hidden name=explain value='$explain' />
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
else
{
	if (isset ($_REQUEST['gid'])) $gid = $_REQUEST['gid']; else $gid='';

	if ($gid=='') {
		// if no gid then must have opcode
		if(!isset($_REQUEST['o'])) $op=''; else $op = $_REQUEST['o'];
		switch ($op) {
			case 'f':{
				$dash = dashboard($user,'my friends groups');

				$groups = getManyHealthBookGroups($facebook);
				$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>My Friends HealthBook Groups</fb:title>
$dash $groups
</fb:fbml>
XXX;
				break;
}
case 'r':{
	$dash = dashboard($user,'recently added');
	$groups = getManyHealthBookGroups($facebook);
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Recently Added HealthBook Groups</fb:title>
$dash
			$groups
</fb:fbml>
XXX;

	break;
}
case 'm':{
	$dash = dashboard($user,'my groups');


	$groups  = getHealthBookGroups($facebook,$user,$user); //3rd arg might be targtfbid we should discuss


	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>My HealthBook Groups</fb:title>
$dash $groups
</fb:fbml>
XXX;
	break;
}

default: {
	$dash = dashboard($user,'overview');
	$purl = getPostedHealthUrlsRecent($facebook);
	$groups  = getHealthBookGroups($facebook,$user,$user); //3rd arg might be targtfbid we should discuss

	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>Groups </fb:title>
$dash $groups $purl
</fb:fbml>
XXX;
}
}
}


else {
	$name = getGroupName($facebook,$gid);
	$grup = getOneHealthBookGroup($facebook,$gid);
	$purl =  getPostedHealthUrlsByGroup($facebook,$gid);
	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>$name Group Health URLs</fb:title>
$dash
	  <fb:explanation>
$grup

    <fb:message>Facebook Group <img src='http://static.ak.facebook.com/images/icons/group.gif?48:25796' /><fb:grouplink gid=$gid /> -- member posted HealthURLs</fb:message>
    $purl
</fb:explanation>
</fb:fbml>
XXX;
}
}
echo $markup;
?>