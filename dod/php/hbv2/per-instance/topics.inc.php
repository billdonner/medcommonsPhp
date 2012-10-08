<?php

function getFavLink($user,$ord,$nlmurl)
{
	if (!$user) return ''; // if not logged in then no favorites
	$q="select * from favorites where fbid='$user' and topicord='$ord'";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	$first="
	<a class='tinylink' href='$nlmurl' >view on medline</a>
	<a class='tinylink' href='http://apps.medcommons.net/public/?ord=$ord' >view on medcommons</a>";

	$b=mysql_fetch_object($result);
	if ($b===false) return " $first <a class='tinylink' href='topics.php?addfav&ord=$ord' >add to your favorites</a>";
	
	
	else return "$first <a class='tinylink' href='topics.php?remfav&ord=$ord' >remove from your favorites</a>";
}
function getFavoriteTopics($user,$facebook)
{
	$apikey = $GLOBALS['appapikey'];
	if (!$facebook->user) return (fb_must_login("Favorite Topics",'hbgpics'));
	
	$q="SELECT nlmtopic, nlmurl,nlmxtra,ord,hurlcount from topics , favorites where  fbid='$user' and ord=topicord ";
	$message = "Your Favorite  Topics ";
	$ret = getTopicSearchResults($q,$message);
	if ($ret) 	$msg = $ret;
	else
	$msg = "<fb:explanation><fb:message>You Have No Favorite Topics <a class=tinylink href=topics.php >add favorites</a></fb:message><p>your favorite topics will be added to your mini-feed on your profile page</p></fb:message></fb:explanation>";
	return $msg;
}
function getRecentTopics($facebook)
{
	$apikey = $GLOBALS['appapikey'];
	if (!$facebook->user) return (fb_must_login("Recent Topics",'hbgpics'));
	$q="SELECT DISTINCT nlmtopic, nlmurl,nlmxtra,ord,hurlcount from topics order by hurlcount_modified_time desc  limit 3";
	$message = "Recently Active Topics";
	$ret = getTopicSearchResults($q,$message);
	if ($ret) 	$msg = $ret;
	else
	$msg = "<fb:explanation><fb:message>There Are No Topics With Recent Activity</fb:message><p>your favorite topics will be added to your mini-feed on your profile page</p></fb:message></fb:explanation>";
	return $msg;
}
function isGroupHealthBookEnabled($gid)
{
	$q = "select count(*) from topicgroups where groupuid='$gid'";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r  = mysql_fetch_array($result);
	$count = $r[0];
	mysql_free_result($result);
	return ($count>0);
}


function getGroupOrdTagline($gid,$ord)
{
	$q = "select tagline from topicgroups where groupuid='$gid' and nlmord='$ord' ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r  = mysql_fetch_array($result);
	$tagline = $r[0];
	mysql_free_result($result);
	return $tagline;
}
function getGroupName($facebook, $gid)
{
	$qqq="SELECT name FROM group   WHERE  gid IN ($gid)";
	$ret = $facebook->api_client->fql_query($qqq);
	if ($ret){
		return $ret[0]['name'];
	}
	return false;
}
function getGroupUrlCount($facebook,$gid)
{
	$q="SELECT gurlcount FROM groupcounts   WHERE  gid ='$gid'";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r  = mysql_fetch_array($result);
	$ret = $r[0];
	mysql_free_result($result);
	return $ret;
}
function getGroupBadge($facebook,$gid,$pic,$linked,$exclude,$reford,$subhead)
{
	$gurlcount=0;

	$q = "select nlmord, nlmtopic,nlmurl,tagline,created,creatorfbid,hurlcount from topicgroups g ,topics m
	 where g.groupuid='$gid' and  m.ord = g.nlmord";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$links = array();
	while ($r  = mysql_fetch_object($result))
	{
		if ($r->nlmord!=$exclude) // exclude one if needed
		{
			$span= <<<XXX
		<span><a href='topics.php?ord=$r->nlmord' title="gid:$gid topic:$r->nlmord by:$r->creatorfbid $r->created"  >$r->nlmtopic</a>  
		                                                                          <span style="font-size:1.0em"> $r->tagline </span></span>
XXX;
			$links[]=array($span,$r->nlmord);
}
}
mysql_free_result($result);
$name = getGroupName($facebook,$gid);
$linkdisplay=''; $badge='';
$linkcount = count($links);
for ($ii=0; $ii<$linkcount; $ii++)
{
	$ord = $links[$ii][1];
	$linkdisplay.="<li>".$links[$ii][0]."<fb:if-is-group-member gid=$gid ><a class=tinylink href=topics.php?hurl&ord=$ord&gid=$gid>addHealthURL</a>
		</fb:if-is-group-member></li>";
}
if (strlen($pic)>10) $img = "<img src='$pic' alt='image for facebook group $gid' />";
else $img = "There is no small picture for this group (yet)";
$also = ($exclude!=0)?'also':'';
$rhs = <<<XXX
<ul style=" list-style-type: none;padding: 0;margin-left: 1em;">
<li><span style="font-size:.8em">$subhead</span></li>
			<li><span style="font-size:1.0em"><i>is $also  topic moderator for:</i></span></li>$linkdisplay</ul>	
XXX;
//	if (($linkcount==0) || (($exclude!=0) &&( $linkcount<=1)))
//	$rhs="<span style='font-size:.8em'>$subhead</span>";
if ($linked) $alink = "<img src='http://static.ak.facebook.com/images/icons/group.gif?48:25796' /><fb:grouplink gid=$gid />";
else $alink="$name";
$badge .= <<<XXX
  
			<fb:message>$alink<fb:if-is-group-member gid=$gid >
			<fb:else > <a class=tinylink href='http://apps.facebook.com/group.php?gid=$gid#' >join</a>
					</fb:else ></fb:if-is-group-member> </fb:message> 
			<table><tr>
			<td width="130px">$img</td>
			<td>$rhs</td>
			</tr></table>
    
XXX;
return $badge;
}
function getHealthBookGroups($facebook, $user,$targetfbid)
{
	$apikey = $GLOBALS['appapikey'];
	if (!$facebook->user) return (fb_must_login("Facebook Groups with Public HealthURLs ",
	"hbg"));
	if ($targetfbid==0)  $targetfbid = $user; // if not viewing anyones records, we can still see our own stuff, why not?
	$arGroup = '';
	$ret = $facebook->api_client->fql_query("SELECT gid,pic_small FROM group WHERE gid IN (SELECT gid FROM group_member WHERE uid='$targetfbid') ");
	//  Build an delimited list of users...
	if ($ret)
	{
		$count = count($ret);
		for ( $i = 0; $i < $count; $i++ )
		{
			$gid = $ret[$i]["gid"];
			$pic = $ret[$i]["pic_small"];
			if (isGroupHealthBookEnabled($gid))
			{
				$linkdisplay='';

				$arGroup .= getGroupBadge($facebook,$gid,$pic,true,0,0,'');

			}
		}
	}
	$my = ($user==$targetfbid )?'My':"<fb:name linked=false possessive='true'  uid=$targetfbid></fb:name>";
	$markup = <<<XXX
  <fb:explanation>
    <fb:message>$my Connected Facebook Groups <a class=tinylink href='topics.php'>more...</a></fb:message> 
   $arGroup
  </fb:explanation>
</fb:fbml>
XXX;
	return  $markup;
}
function getHealthBookGroupPics($facebook, $user,$targetfbid)
{
	$apikey = $GLOBALS['appapikey'];
if (!$facebook->user) return (fb_must_login("Facebook Groups with Public HealthURLs","hbg"));
if ($targetfbid==0)  $targetfbid = $user; // if not viewing anyones records, we can still see our own stuff, why not?
$my = ($user==$targetfbid )?'My':"<fb:name linked=false possessive='true'  uid=$targetfbid></fb:name>";
$outstr =" <div class='mugshots'><fb:explanation>
          <fb:message>$my Connected Facebook Groups <a class=tinylink href='home.php?o=x'>more...</a></fb:message><table><tr>";
$counter = 0;
$ret = $facebook->api_client->fql_query("SELECT gid,pic_small,name FROM group WHERE gid IN (SELECT gid FROM group_member WHERE uid='$targetfbid') ");
//  Build an delimited list of users...
if ($ret)
{
	$count = count($ret);
	for ( $i = 0; $i < $count; $i++ )
	{
		$gid = $ret[$i]["gid"];
		$pic = $ret[$i]["pic_small"];
		$name = $ret[$i]["name"];
		if ($pic) {
			if (isGroupHealthBookEnabled($gid))
			{
				//    <a href='http://www.facebook.com/groups.php?gid=$gid' />
				$linkdisplay='';
				$mod = $counter -  floor($counter/11)*11;
				if ($mod==0 && $counter!=0)$outstr.="</tr><tr>";
				$outstr.="<td class='mugshotgiver' width=55px style='color: #3b5998' >
		                                           <a href=groups.php?gid=$gid ><img src='$pic' /></a><br/>$name</td>";
				$counter++;
			}
		}
	}
}
if ($counter==0) $outstr.="<td>You are not a member of any HealthBook enabled Groups</td>";
$outstr .='</tr></table></fb:explanation></div> '; //
return  $outstr;
}

function getHealthBookGroupsInt($facebook,$q,$message,$link)
{$apikey = $GLOBALS['appapikey'];
if (!$facebook->user) return (fb_must_login("Facebook Groups","hbgint"));
// needs to be made more efficient
$arGroup = '';
$gids= '';$kk=0;
$result = mysql_query($q) or die("Cant $q ".mysql_error());
while ($r  = mysql_fetch_array($result))	{
	if ($kk==0) $gids ="'".$r[0]."'"; else $gids.=' , '."'".$r[0]."'";$kk++;
}
$qqq="SELECT gid,pic_small FROM group   WHERE  gid IN ($gids)";
$ret = $facebook->api_client->fql_query($qqq);
if ($ret){
	$count = count($ret);
	for ($j=0; $j<$count; $j++)
	{
		$gid = $ret[$j]['gid'];
		$pic = $ret[$j]['pic_small'];
		$arGroup .= getGroupBadge($facebook,$gid,$pic,true,0,0,'');
	}
}
$markup = <<<XXX
  <fb:explanation>
    <fb:message>$message $link</fb:message> 
   $arGroup
  </fb:explanation>
</fb:fbml>
XXX;
return  $markup;
}
function getFilteredHealthBookGroups($facebook,$filter)
{
	$morelink = "<a class=tinylink href=groups.php?recent >more</a>";
	$q = "SELECT DISTINCT groupuid from topicgroups where (groupmeta like '%$filter%') or (tagline like '%$filter') order by created desc limit 5";
	$message = "Facebook Groups Suggested By Query with Filter $filter";
	return getHealthBookGroupsInt($facebook,$q,$message,$morelink);

}
function getRecentHealthBookGroups($facebook)
{
	$morelink = "<a class=tinylink href=groups.php?recent >more</a>";
	$q = "SELECT DISTINCT groupuid from topicgroups order by created desc limit 5";
	$message = "Recently Active Facebook Groups";
	return getHealthBookGroupsInt($facebook,$q,$message,$morelink);
}
function getManyHealthBookGroups($facebook)
{
	$lesslink = "<a class=tinylink href=topics.php >less</a>";
	$q = "SELECT DISTINCT groupuid from topicgroups order by created desc limit 30";
	$message = "Recently Active Facebook Groups";
	return getHealthBookGroupsInt($facebook,$q,$message,$lesslink);
}
function getOneHealthBookGroup($facebook,$gid)
{
	$apikey = $GLOBALS['appapikey'];
	if (!$facebook->user) return (fb_must_login("Facebook Groups","hbgone"));
	// needs to be made more efficient
	$arGroup = '';
	$gids= '';$kk=0;
	$qqq="SELECT gid,pic_small,name FROM group   WHERE  gid IN ($gid)";
	$ret = $facebook->api_client->fql_query($qqq);
	if ($ret){

		$gid = $ret[0]['gid'];
		$pic = $ret[0]['pic_small'];
		$name = $ret[0]['name'];
		$arGroup .= getGroupBadge($facebook,$gid,$pic,true,0,0,'');
	}
	$markup =$arGroup;
	return  $markup;
}
function getOrdHealthBookGroups($facebook,$ord,$tagline)
{
	$apikey = $GLOBALS['appapikey'];
	if (!$facebook->user) return (fb_must_login("Facebook Groups","hbgint"));
	// needs to be made more efficient
	$arGroup = '';
	$gids= '';$kk=0;
	$topic = getTopicInfo($ord)->nlmtopic;
	$q =  "SELECT DISTINCT groupuid from topicgroups where nlmord='$ord'";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	while ($r  = mysql_fetch_array($result))	{
		if ($kk==0) $gids ="'".$r[0]."'"; else $gids.=' , '."'".$r[0]."'";$kk++;
	}
	$qqq="SELECT gid,pic_small FROM group   WHERE  gid IN ($gids)";
	$ret = $facebook->api_client->fql_query($qqq);
	if ($ret){
		$count = count($ret);
		for ($j=0; $j<$count; $j++)
		{
			$gid = $ret[$j]['gid'];
			$pic = $ret[$j]['pic_small'];
			$tagline = getGroupOrdTagline($gid,$ord);
			$arGroup .= getGroupBadge($facebook,$gid,$pic,true,0,$ord,$tagline); // was ord instead of zero
		}
	}
	if ($arGroup=='') $arGroup = <<<XXX
There are no Facebook Groups associated with this topic. 
 <a title='your group will be supercharged with collaborative yet anonymous health records' href=topics.php?apply&ord=$ord>Start a group</a>  in your community,friends, or with your careteam.
XXX;
	$markup = <<<XXX
  <fb:explanation>
    <fb:message>Recently Active Facebook Groups for $topic</fb:message> 
   $arGroup
  </fb:explanation>
</fb:fbml>
XXX;
	return  $markup;
}
function nlmGetGroupCount()
{
	$q =  "SELECT COUNT( DISTINCT groupuid ) from topicgroups";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r  = mysql_fetch_array($result);
	return $r[0];
}
function nlmGetTopicsCount()
{
	$q =  "SELECT COUNT( DISTINCT nlmord ) from topicgroups";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r  = mysql_fetch_array($result);
	return $r[0];
}


function getPostedHealthUrlsInt($facebook,$q,$message)
{          $apikey = $GLOBALS['appapikey'];
if (!$facebook->user) return (fb_must_login("Posted Public HealthURLs","addhurl"));

// first get all of the group pics, what a hassle
$gids = '';$store=array();$buf='';
$gid=0; $pic=''; $name='';
$result = mysql_query($q) or die("Cant $q ".mysql_error());
while ($r = mysql_fetch_object($result))
{
	if ($gids!='') $gids.=',';
	$gids.=$r->groupid; // all the gids we need
}
mysql_free_result($result);
$qqq="SELECT gid,pic_small,name FROM group   WHERE  gid IN ($gids)";
$ret = $facebook->api_client->fql_query($qqq);
if ($ret){
	$count = count($ret);
	for ($j=0; $j<$count; $j++)
	{
		$gid = $ret[$j]['gid'];
		$pic = $ret[$j]['pic_small'];
		$name = $ret[$j]['name'];
		$store[$gid]=$pic;
		$storen[$gid]=$name;
	}
}
// re - execute top query
$result2 = mysql_query($q) or die("Cant $q ".mysql_error());
while ($rr = mysql_fetch_object($result2))
{
	if (isset( $storen[$rr->groupid]))
	{
		$gid = $rr->groupid;
		$pic = $store[$rr->groupid];
		$name = $storen[$rr->groupid];
	}
	else
	{
		$gid=0; $pic='';$name='';
	}
	if (strlen($pic)>10) $img = "<img src='$pic' alt='image for facebook group $gid' />";
	else $img ='';// "There is no small picture for this group (yet)";
	if (strlen($name)>1) $name = " as a member of group <a href='groups.php?gid=$gid' >$name</a> ";
	else $name ='';// "There is no small picture for this group (yet)";
	if ($facebook->user==$rr->authorfbid)$remove="<a class=tinylink href=''>remove this post</a>"; else $remove='';
	$buf.=<<<XXX
<fb:wallpost linked=false uid="$rr->authorfbid" t="$rr->time" ><table><tr><td width=400px>
posted Health URL <span><a target='_new'  href='$rr->hurl' ><img src="http://www.medcommons.net/images/icon_healthURL.gif" />$rr->hurl</a></span> to topic <a href='topics.php?ord=$rr->topic' >$rr->nlmtopic</a>$name 
<br>$rr->comment</td><td align='right'>$img</td></tr></table>$remove</fb:wallpost>
XXX;
}
if ($buf!='') $buf = "<div style='font-size:10px;' ><fb:explanation><fb:message>$message</fb:message>
   <fb:wall>$buf</fb:wall>
</fb:explanation></div>";
return $buf;
}
function getPostedHealthUrlsByTopic($facebook,$ord,$name)
{
	$message ="Recently Published Public HealthURLs For Topic $name";
	$q="Select * from topichurls h,topics t where h.topic='$ord' and t.ord='$ord' order by h.ind desc limit 3 ";
	$ret = getPostedHealthUrlsInt($facebook,$q,$message);
	if ($ret) return $ret;
	return  "<fb:explanation><fb:message>$message</fb:message><p>There are no Public HealthURLs associated with this topic.
	<a class=tinylink href='topics.php?hurl&ord=ord'  >add HealthURL</a></p></fb:explanation>";
}
function getPostedHealthUrlsByUser($facebook,$user)
{
	$message ="Recently Published Public HealthURLs By
   <fb:name useyou=false linked=false uid=$user /></span>";
	$q="Select * from topichurls h,topics t where authorfbid='$user' and t.ord=h.topic order by h.ind desc limit 3 ";
	$ret = getPostedHealthUrlsInt($facebook,$q,$message);
	if ($ret) return $ret;
	return  "<fb:explanation><fb:message>$message</fb:message><p><fb:name uid=$user />has not published any Public HealthURLs.
	<a class=tinylink href='topics.php?hurl'  >add HealthURLs</a></p></fb:explanation>";
}

function getPostedHealthUrlsByGroup($facebook,$gid)
{
	$message ="Recently Published Public HealthURLs ";;
	$q="Select * from topichurls  h,topics t where h.groupid='$gid' and t.ord=h.topic order by h.ind desc limit 3 ";
	$ret = getPostedHealthUrlsInt($facebook,$q,$message);
	if ($ret) return $ret;
	return  "<fb:explanation><fb:message>$message</fb:message><p>There are no Public HealthURLs published by <img src='http://static.ak.facebook.com/images/icons/group.gif?48:25796' /><fb:grouplink gid='$gid' /></p>
	<p><a class=tinylink href='topics.php?hurl&gid=$gid'  >add HealthURL</a></p></fb:explanation>";
}

function getPostedHealthUrlsRecent($facebook)
{
	$message ="Recently Published Public HealthURLs ";
	$q="Select * from topichurls  h,topics t where t.ord=h.topic order by h.ind desc limit 3 ";
	$ret = getPostedHealthUrlsInt($facebook,$q,$message);
	if ($ret) return $ret;
	return  "<fb:explanation><fb:message>$message</fb:message><p>There are no Recently Posted Public HealthURLs
	<a class=tinylink href='topics.php?hurl'  >add HealthURL</a></p></fb:explanation>";
}
?>