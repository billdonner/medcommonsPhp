<?php
require_once "healthbook.inc.php";
require_once "topics.inc.php";
require_once "searchbox.inc.php";
function getNewsLinksByTopic ($ord)
{	$ret=array();
$q = "select pageurl, rssurl, comment  from topicnewslinks where topicord='$ord' order by ind desc limit 3";
$result = mysql_query($q) or die("Cant $q ".mysql_error());
while ($r=mysql_fetch_array($result)) $ret[]=$r;
mysql_free_result($result);
return $ret;
}
function getAppLinksByTopic ($ord)
{	$buf='';

$me = $_SERVER['PHP_SELF'];
$key = substr($me,0,strrpos($me,'/')+1);
$q = "select *  from `topicapplinks` where `ord`='$ord'  and `key`= '$key' limit 3";
$result = mysql_query($q) or die("Cant $q ".mysql_error());
while ($r=mysql_fetch_object($result)) $buf.=$r->links."<br/>";
mysql_free_result($result);
return $buf;
}
function getModeratorLinksByTopic ($ord)
{	$buf = '';
$q = "select groupuid  from topicgroups where nlmord='$ord' order by created desc limit 7";
$result = mysql_query($q) or die("Cant $q ".mysql_error());
while ($r=mysql_fetch_object($result))
		$buf.="<br/><fb:grouplink gid='$r->groupuid' /> ";
mysql_free_result($result);
return $buf;
}
function getRssLinks ($a)
{
	$buf='';
	foreach ($a as $vec) {
		$rssfeed=$vec[1];$comment=$vec[2];
		$buf.="<a class=tinylink target='_new' title='$comment' href='$rssfeed'>$comment rss</a> ";
	}
	return $buf;
}
function isPublicAppliance($h)
{
	$h = substr($h,0,strlen($h)-16);
	$q="Select * from publicappliances where applianceurl = '$h' ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$count = mysql_num_rows($result);
	//echo "$h is public appliance $count ";
	return ($count>0);
	
}
function checkHealthURL($h)
{
	function isdig16($s)
	{
		for ($i=0;$i<16;$i++)
		{
			$c = substr($s,$i,1);
			if (('0'>$c)|| ($c>'9')) return false;
		}
		return true;
	}
	// right now just check to make sure it ends with all digits, 16 of them
	if  (isPublicAppliance($h)) //((substr($h,0,7)=='http://')||(substr($h,0,8)=='https://'))
	{
		$len = strlen($h);
		if (isdig16(substr($h,$len-16))) return true;
	}
	return false;
}
connect_db();
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->get_loggedin_user(); //require_login();
$gcount = nlmGetGroupCount();
$ntopics = nlmGetTopicsCount();
$dash = dashboard($user);
$app = $GLOBALS['healthbook_application_name'];
$apikey =$GLOBALS['appapikey']; 
$standardsearch =  standard_searchbox(); // standard header in all cases
$header = "<fb:fbml version='1.1'>$dash
    <fb:title>Topics</fb:title> 
 <fb:explanation>
    <fb:message>Find Topics</fb:message> 
    <p>There are $ntopics Topics associated with $gcount  Facebook  Groups. Pick a subject, topic , group name or keyword to see a list of topics and groups that may be of use to you.</p>
 $standardsearch </fb:explanation>";
if (isset($_REQUEST['posthurl']))
{ // healthurl post form was set, write to database
	$comment ="<span class=purl>" .$_REQUEST['comment']."</span>";
	$healthurl = $_REQUEST['healthurl'];
	$ord = $_REQUEST['ord'];
	$gid = $_REQUEST['gid'];
	$b=getTopicInfo($ord);
	// check whether these healthurls look any good at all
	if (!checkHealthURL($healthurl))
	{	$markup = <<<XXX
<fb:fbml version='1.1'>$dash
    <fb:title>Bad Public HealthURL</fb:title> 
  <fb:error>
    <fb:message>Bad Public HealthURL: $healthurl </fb:message> 
    <p>You can only specify HealthURLs created on a special Public Health URL Appliance. Please note the format of a HealthURL is {domain}{16 digits} as in http://public.medcommons.net/0123456789012345. You can <a class=tinylink title='go to this topic page on healthbook' href='topics.php?hurl&ord=$ord' >try again </a>
  </fb:error>
  </fb:fbml>
XXX;
}
else {
	$hcp1 = $b->hurlcount+1;
	$time = time();
	$q="replace into  topichurls set time='$time', topic='$ord', hurl='$healthurl',comment='$comment',groupid='$gid',authorfbid='$user'";
	mysql_query($q) or die("Cant $q ".mysql_error()); // should really check to see
	$q="update topics set hurlcount='$hcp1' where ord='$ord'";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());

	$alink = $GLOBALS['facebook_application_url']."groups.php?gid=$gid";
	$blink = $GLOBALS['facebook_application_url']."topics.php?ord=$ord";
	$feed_title = "<fb:userlink uid=$user shownetwork='false' /> posted a HealthURL and said $comment";
	if ($gid!=0) $feed_title = "<fb:grouplink gid=$gid /> member  $feed_title";
	if ($gid!=0) $feed_body = "Check out the group on <a href='$alink' >$app</a>,
	or explore the Topic <a href=$blink >$b->nlmtopic</a>";
	else  $feed_body = "Explore the Topic <a href=$blink >$b->nlmtopic</a>";
	$feed_body .= " or, go to view the HealthURL in MedCommons via <a href='$healthurl' target='_new'><img src='http://www.medcommons.net/images/icon_healthURL.gif' />$healthurl</a>";
	logMiniHBEvent($user,'HealthURL',$feed_title,$feed_body);

	//republish_user_profile($user);
	$markup = <<<XXX
<fb:fbml version='1.1'>$dash
    <fb:title>HealthURL Posted</fb:title> 
  <fb:success>
    <fb:message>Public Health URL  <img src="http://www.medcommons.net/images/icon_healthURL.gif" />$healthurl Posted to  NLM Topic $ord: <a title='go to this topic page on healthbook' href='topics.php?ord=$ord' >$b->nlmtopic
    </a></fb:message> 
  </fb:success>
  </fb:fbml>
XXX;
}
echo $markup;
exit;
} else
if (isset($_REQUEST['hurl']))
{ // put up the form
	if (!isset($_REQUEST['ord']))
	{
		$ml = fb_must_add_app("You need to add $app  before you can Post a HealthURL",'posturl0');
	
		// display instructions for generating healthurls
		$markup = <<<XXX
<fb:fbml version='1.1'>$dash
    <fb:title>How To Post a Public Health URL</fb:title> 
    <fb:if-is-app-user>
      <fb:else >
   $ml</fb:else>
    </fb:if-is-app-user>
 <fb:explanation>
    <fb:message>Find Topics</fb:message> 
    <p>There are $ntopics Topics associated with $gcount  Facebook  Groups. Pick a subject, topic , group name or keyword to see a list of topics and groups that may be of use to you.</p>
 $standardsearch </fb:explanation>
  <fb:success>
    <fb:message>How To Post a Public Health URL</fb:message> 
      <p>Here's the general plan:
      <ul>
      <li>Step 1 : Choose A Topic - every Public Health URL must be associated with at least one topic. You select a topic or a group in the searchbox above.</li>
      <li>Step 2 : If you are posting as a private citizen, select the 'add healthURL' link on every Topic's home page<br/>If posting as a representative of your facebook group, select the 'add healthURL' link on the Topic within the group's display</li> 
      <li>Step 3: Enter the Public Health URL into the form and hit Post</li></ul></p>    
  </fb:success>
</fb:fbml>
XXX;
		echo $markup;
		exit;
	}
	$ord = $_REQUEST['ord'];
	if (isset($_REQUEST['gid'])) $gid=$_REQUEST['gid']; else $gid=0;
	$b=getTopicInfo($ord);
	if ($gid!=0){
		$qqq="SELECT gid,pic_small,name,description FROM group   WHERE  gid IN ($gid)";
		$ret = $facebook->api_client->fql_query($qqq);
		if ($ret){
			$gid = $ret[0]['gid'];
			$pic = $ret[0]['pic_small'];
			$name = $ret[0]['name'];
			$desc = $ret[0]['description'];
			$img = "<img src='$pic' alt='$pic' /><p>Your posting will be associated with the group $name.</p> <p>$desc</p>";
		} else $img ="no pic for gid $gid";
	} else $img ="<p>This is a personal posting. You are not posting on behalf of any group. If you'd like to post on behalf of a group, select that group's add healthurl link</p>";
	$ml = fb_must_add_app("You need to add $app  before you can Post a HealthURL to $b->nlmtopic ",'posturl');
	$markup = <<<XXX
<fb:fbml version='1.1'>$dash
    <fb:title>Post HealthURL</fb:title> 
        <fb:if-is-app-user>

  <fb:explanation>
    <fb:message>Post a Public Health URL to  Topic <a title='view this page HealthBook' href='topics.php?ord=$ord' >$b->nlmtopic</a></fb:message> 
      <p>Enter the healthURL you want to post, and a <a href='' title='how to format a tagline for effective display of the public  Health URL' >tagline</a>. </p>    
      $img
    <fb:editor action="topics.php" labelwidth="100">
    <input type=hidden name=ord value='$ord' />
        <input type=hidden name=gid value='$gid' />
    <input type=hidden name=posthurl value=posthurl/>
     <fb:editor-text name="healthurl" label="MedCommons Health URL" value=""/>
          <fb:editor-text name="comment" label="Tagline " value=""/>
     <fb:editor-buttonset>
          <fb:editor-button value="Post HealthURL to Topic"  />
     </fb:editor-buttonset>
 </fb:editor>
  </fb:explanation>
        <fb:else>
 $ml
       </fb:else>
    </fb:if-is-app-user>
</fb:fbml>
XXX;
	echo $markup;
	exit;
}
else
if (isset($_REQUEST['apply']))
{
	$ord = $_REQUEST['ord'];
	$b=getTopicInfo($ord);
	/* uses overloaded url */
	if (isset($_REQUEST['explanation']))
	{
		$appname = $GLOBALS['healthbook_application_name'];
		$ret= ($facebook->api_client->users_getInfo($user,array('first_name','last_name','pic_small','sex','current_location')));
		$fn = $ret[0]['first_name'];
		$ln = $ret[0]['last_name'];
		$explanation = $_REQUEST['explanation'];
		$gid = $_REQUEST['gid'];
		$uid = $user;
		$explain = urlencode($_REQUEST['explanation']);
		$b=getTopicInfo($ord);
		$bindpage = $GLOBALS['facebook_application_url']."groups.php?uid=$uid&gid=$gid&ord=$ord&explain=$explain";
		$body = $explanation."<br/><br/>THIS MESSAGE IS FOR THE MedCommons EDITOR IN CHIEF <br/>
<br/>visit http://www.facebook.com/group.php?gid=$gid to examine the group
<br/>visit http://www.facebook.com/profile.php?pid=$uid to see the  submitter's profile ($fn $ln)
<p><i>
$fn $ln says $explain 
</i></p>
 <br/>visit $b->nlmurl  to see the page on the NLM
 <br/>visit $bindpage  to bind";

		$subject = "$appname says $fn $ln says pls connect  gid=$gid  to nlm topic $b->nlmtopic ";

		if (!isset($GLOBALS['autoapprovemoderators']))
		{
			$page = $GLOBALS['facebook_application_url'];

			$subject = "$appname says $fn $ln says pls connect  gid=$gid  to nlm topic $b->nlmtopic ";
		}
		else
		{
			$page = $bindpage;

			$subject = "$appname says $fn $ln was auto-connected  gid=$gid  to nlm topic $b->nlmtopic ";
		}

		opsMailBody($subject,$body);

		echo "<fb:fbml version='1.1'>redirecting via facebook to $page";
		echo "<fb:redirect url='$page' /><fb:fbml version='1.1'>";
		exit;
	}
	else
	if (isset($_REQUEST['gid']))
	{
		// gid and ord
		$gid = $_REQUEST['gid'];
		//role="officer removed
		$markup = <<<XXX
$header
<fb:if-is-group-member gid="$gid" uid="$user">
  <fb:explanation>
    <fb:message>Apply for Group Moderation of Topic  <a title='view this page on NLM' target='_new' href='$b->nlmurl' >$b->nlmtopic</a></fb:message>     	<fb:grouplink gid='$gid' /> 
    	<p>Step 2: Please tell us why <fb:grouplink gid='$gid' /> should moderate $b->nlmtopic. Your tagline will appear in the HealthBook directory alongside your group's logo.
     
    <fb:editor action="topics.php" labelwidth="100">
    <input type=hidden name=ord value='$ord' />
    <input type=hidden name=gid value='$gid' />
        <input type=hidden name=apply value=apply />'
     <fb:editor-text name="explanation" label="explanation" value=''/>
     <fb:editor-buttonset>
          <fb:editor-button value="done"  />
     </fb:editor-buttonset>
 </fb:editor>
  </fb:explanation>
  <fb:else>
  <fb:error>
      <fb:message>Officers only</fb:message>
      You must be a facebook group officer to associate your group with  Topic  $b->nlmtopic<br/>
      Your facebook id is $user, the gid is $gid
 </fb:error>
 </fb:else>
</fb:if-is-group-member>
</fb:fbml>
XXX;
} else
{
	// only ord specified
	$markup = <<<XXX
$header
  <fb:explanation>
    <fb:message>Apply for Group Moderation of Topic  <a title='view this page on NLM' target='_new' href='$b->nlmurl' >$b->nlmtopic</a></fb:message> 
      <p>Step 1: Specify the Facebook Group ID. <small>You must be an officer</small>
      </p>    
    <fb:editor action="topics.php" labelwidth="100">
    <input type=hidden name=ord value='$ord' />
    <input type=hidden name=apply value=apply />
     <fb:editor-text name="gid" label="facebook group id" value=""/>
     <fb:editor-buttonset>
          <fb:editor-button value="to Step 2>>"  />
     </fb:editor-buttonset>
 </fb:editor>

  </fb:explanation>
</fb:fbml>
XXX;

}
echo $markup;
exit;
}// end if aookt
else
if (isset($_REQUEST['all'])){
	$a=$_REQUEST['all'];
	$q="SELECT DISTINCT nlmtopic, nlmurl,nlmxtra,ord,hurlcount from topics ";
	$message = "Your search results for all topics";
	$searchresults = getTopicSearchResults($q,$message);

	$markup =  $header.$searchresults."</fb:fbml>"; echo $markup; exit;
}
else
if (isset($_REQUEST['a'])){
	$a=$_REQUEST['a'];
	$q="SELECT DISTINCT nlmtopic, nlmurl,nlmxtra,ord,hurlcount from topics where (nlmtopic like '$a%')";
	$message = "Your search results for topics starting with $a";
	$searchresults = getTopicSearchResults($q,$message);
	$markup =  $header.$searchresults."</fb:fbml>"; echo $markup; exit;
}
else
if (isset($_REQUEST['search'])){
	$filter = $_REQUEST['filter'];
	if ($filter=='') $markup = gototopics($facebook,$user); // if nothing specified dont retrieve everything, do nothing instead
	else
	{
		$rfilter = "where ((nlmtopic like '%$filter%') or (nlmxtra like '%$filter%'))";
		$q="SELECT DISTINCT nlmtopic, nlmurl,nlmxtra,ord,hurlcount from topics $rfilter";
		$message ="Topics suggested by query with filter $filter";
		$searchresults = getTopicSearchResults($q,$message);
		$groupresults = getFilteredHealthBookGroups($facebook,$filter);
		$markup =  $header.$searchresults.$groupresults."</fb:fbml>"; echo $markup; exit;
	}
}
else
if (isset($_REQUEST['ord']))
{
	$ord = $_REQUEST['ord'];
	$b = getTopicInfo($ord);

	$blink = $GLOBALS['facebook_application_url']."topics.php?ord=$b->ord";
	// these little subcases just update the database and then redisplay this page
	if (isset($_REQUEST['addfav']))
	{
		$time=time();
		$q = "replace into favorites set fbid='$user', time='$time',topicord='$ord'";
		mysql_query($q) or die("Cant $q ".mysql_error());


		$feed_title = "<fb:userlink uid=$user shownetwork='false' /> Added Topic $b->nlmtopic to $app Favorites";

		$feed_body = "Explore <a href=$blink >$b->nlmtopic</a> on $app.";
		logMiniHBEvent($user,'HealthURL',$feed_title,$feed_body);

		//republish_user_profile($user);


	}
	else
	if (isset($_REQUEST['remfav']))
	{
		$q = "delete from favorites where  fbid='$user' and  topicord='$ord'";
		mysql_query($q) or die("Cant $q ".mysql_error());
		$feed_title = "<fb:userlink uid=$user shownetwork='false' /> Removed Topic $b->nlmtopic from $app Favorites";

		$feed_body = "Explore <a href=$blink  >$b->nlmtopic</a> on $app.";
		logMiniHBEvent($user,'HealthURL',$feed_title,$feed_body);
		//republish_user_profile($user);
	}

	$favmsg=getFavLink($user,$ord,$b->nlmurl);
	$hurlstuff = getPostedHealthUrlsByTopic($facebook,$ord,$b->nlmtopic);
	$stuff = getOrdHealthBookGroups($facebook,$ord,'called via ord');
	$newsinfo = getNewsLinksByTopic($ord);
	$newslinks = getRssLinks($newsinfo);
	$moderatorlinks = getModeratorLinksbyTopic($ord);
	$applinks = getAppLinksbyTopic($ord);
	if ($newslinks!='') $newslinks = "<br/><div class=newslinks>$newslinks</div>";
	$markup = "<fb:fbml version='1.1'>$dash<fb:title>$b->nlmtopic topic</fb:title>
<fb:explanation><fb:message>$b->nlmtopic $favmsg   </fb:message>
	<table><tr><td>
    <a target='_new' href='http://medlineplus.gov/'><IMG SRC='http://www.nlm.nih.gov/medlineplus/images/mpluslinksm.gif' alt='MedlinePlus Trusted Health Information for You' width='179' height='35'></a> 
    $newslinks  $moderatorlinks $applinks</td><td>
    <p>MedLine is a  service of the National Library of Medicine. You can <a title='your group will be supercharged with collaborative yet anonymous health records' 
    href=topics.php?apply&ord=$ord>apply</a> to HealthBook Administration to add your facebook group as a moderator of this  topic.</p>
    <p>Anyone can create and then <a href=topics.php?hurl&ord=$ord > post a public Health URL  to this topic page </a> that everyone will see. You can also post a <a href=topics.php?hurl&ord=$ord > post a public Health URL  to this topic page as a member of a Facebook Group </a> that is a moderator of that topic</p>
    </td></tr></table>
  </fb:explanation>
    $hurlstuff
 $stuff
</fb:fbml>";
	echo $markup;
	exit;
}
// nothing was done, do just put out the  groups

$markup = gototopics($facebook,$user);
echo $markup; 

?>