<?php
/*

<i>name <fb:name uid='$user' useyou='false'/></i>
<form style="display:inline; font-size:.8em;"  action='topics.php' method='POST'>
<input type=hidden value='search' name='search'>
<input size=12  type=text value='' name=filter >
<input type=submit value='go' size=12  name=submit>
</form>
*/
require_once "session.inc.php";
require_once "./JSON.php";
require_once "globals.inc.php";
require_once "hbuser2.inc.php";
require_once "appinclude.php";  // required of all facebook apps put this last
require_once "utils.inc.php";

function fb_must_login($s,$t)
{
	$appname = $GLOBALS['healthbook_application_name'];
	$apikey = $GLOBALS['appapikey'];
	return <<<XXX
	<fb:explanation><fb:message>$s</fb:message><p>You must login to Facebook or even better, <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=$t' >add $appname</a>
	</p></fb:explanation>
XXX;

}
function fb_must_add_app($s,$t)
{
	$appname = $GLOBALS['healthbook_application_name'];
	$apikey = $GLOBALS['appapikey'];
	return <<<XXX
	<fb:explanation><fb:message>$s</fb:message><p>You must <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=$t' >add $appname</a> before you can perform this function.
	</p></fb:explanation>
XXX;

}
function republish_user_profile($user)
{
	$clickthru1 = $GLOBALS['facebook_application_url']."/topics.php?z=$user";
	$clickthru2 = $GLOBALS['facebook_application_url']."/topics.php?zz=$user";
	$profile_action = <<<XXX
<fb:profile-action url="$clickthru2" >  Give Health Care to  -   <fb:name uid=$user firstnameonly=true possessive=false useyou=false />  </fb:profile-action> 
XXX;

	$alink = $GLOBALS['facebook_application_url'];
	$profile = '';
	$appname = $GLOBALS['healthbook_application_name'];
	$q="Select * from fbtab where fbid='$user' ";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	$r = mysql_fetch_object($result);
	if ($r) {

		$profile .="<fb:name useyou=false uid=$r->fbid /> has been a member of <a href=$alink > $appname</a> since $r->gw_modified_date_time and is ";

		if ($r->targetfbid==0)
		$profile.= "not viewing medical records<br/>";
		else if ($r->targetfbid!=$r->fbid) $profile.="viewing medical records as  CareGiver to <fb:name uid=$r->targetfbid /><br/>";
		else $profile.="viewing own medical records<br/>";
	}
	$q = "select * from  carewalls where wallfbid = '$user' order by time desc limit 5 ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{           $profile.=" &nbsp;&nbsp;<i><fb:name useyou=false capitalize=true uid=$u->authorfbid /> wrote:</i> $u->msg<br/>";
	}

	$q="SELECT nlmtopic, nlmurl,nlmxtra,ord,hurlcount from topics , favorites where  fbid='$user' and ord=topicord
				order by time desc limit 3";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	$ret='';
	while ($r=mysql_fetch_object($result))
	{
		if ($ret!='') $ret.= " and ";
		$ret.= "<a  title='topic #$r->ord $r->nlmxtra' href='topics.php?ord=$r->ord' >$r->nlmtopic</a>&nbsp; ";
	}
	if ($ret) $profile.= "&nbsp;&nbsp;<fb:name uid=$user  useyou=false capitalize=true firstnameonly=true possessive=true /> favorite topics:  $ret <br/>";

	$q="Select * from topichurls h,topics t where authorfbid='$user' and t.ord=h.topic order by h.ind desc limit 3 ";
	$result2 = mysql_query($q) or die("Cant $q ".mysql_error());
	$ret = '';
	while ($rr = mysql_fetch_object($result2))
	{

		if ($ret!='') $ret.= " and ";
		$ret .="<a target='_new'  href='$rr->hurl' >$rr->hurl</a> to topic <a href='topics.php?ord=$rr->topic' >$rr->nlmtopic</a>&nbsp;";
	}
	if ($ret)            $profile.=" &nbsp;&nbsp;<fb:name uid=$user firstnameonly=true useyou=false capitalize=true possessive=false /> published $ret <br/>";
	return $GLOBALS['facebook']->api_client->profile_setFBML('', $user, $profile, $profile_action, 'mobile support coming soon');
}
function logHBEvent($user,$cat,$message)
{
	$time=time();
	$message = mysql_escape_string($message);
	$q = "insert into hblog set fbid='$user',category='$cat',title='$message',body='$message',time='$time'";
	mysql_query($q) or die ("Cant $q ".mysql_error());
}
function logMiniHBEvent($user,$filter,$feed_title,$feed_body)
{
	$time=time();
	$message = mysql_escape_string($feed_body);
	$title = mysql_escape_string($feed_title);
	$q = "insert into hblog set fbid='$user',category='$filter',title='$title',body='$message',time='$time'";
	mysql_query($q) or die ("Cant $q ".mysql_error());
	try {
		$GLOBALS['facebook']->api_client->feed_publishActionOfUser($feed_title, $feed_body);
	}
	catch ( Exception $e ) { echo "Cant publish have exceed facebook daily limit for this user  $user"; }
}


function gototopics($facebook, $user)
{
	//echo "Gototopics: $facebook $user <br/>";
	require_once "topics.inc.php";
	require_once "searchbox.inc.php";
	// coming in fresh, no account,  go redirect to topics
	$gcount = nlmGetGroupCount();
	$ntopics = nlmGetTopicsCount();
	
	$dash = dashboard($user);
	$app = $GLOBALS['healthbook_application_name'];
	$standardsearch =  standard_searchbox(); // standard header in all cases
	$markup = "<fb:fbml version='1.1'>$dash
 <fb:title>Topics</fb:title> 
 <fb:explanation>
    <fb:message>Find Topics</fb:message> 
    <p>There are $ntopics Topics associated with $gcount  Facebook  Groups. Pick a subject, topic , group name or keyword to see a list of topics and groups that may be of use to you.</p>
 $standardsearch 
</fb:explanation>"
	.getRecentTopics($facebook)
	.getPostedHealthUrlsRecent($facebook)
	.getRecentHealthBookGroups($facebook)

	."</fb:fbml>";
	return $markup;
}
function getTopicInfo($ord)
{
	$q = "SELECT * from topics where ord='$ord'";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	$b=mysql_fetch_object($result);
	return $b;
}
function fmcid ($fbid)
{
	$u = HealthBookUser::load($fbid);
	if ($u===false) {
		//echo "fmcid $fbid returns false";
		return false; }
		else  //bill dec 5
		return array($u->mcid,$u->appliance,$u->gw,$u->targetfbid,$u->targetmcid);
}
function connect_db()
{
	mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User']) or err("Error connecting to database.");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");
}
function opsMailBody($subject,$message)
{
	// $to= 'ssadedin@gmail.com';
	$to= 'billdonner@gmail.com,agropper@gmail.com';
	$appname = $GLOBALS['healthbook_application_name'];
	$headers = 'From: '.$appname.'@medcommons.net' . "\n" .
	'Reply-To:noreply-'.$appname.'@medcommons.net' . "\n" .
  'Content-Type: text/html; charset="iso-8859-1"';

	mail($to, $subject, $message, $headers);
  dbg("sent mail to $to with headers $headers");
}
function opsMail ($subject) { return opsMailBody($subject,$subject);}
function new_account_factory_appliance ()
{
	return $GLOBALS['new_account_appliance']; // someday this will be a fancy allocation policy machine
}
function mugshot_css ()
{
	$css = <<<CSS
	<style type="text/css">
	a { color:  #3B5998;}
	a.tinylink  {font-size:xx-small; text-decoration:underline; color: gray;}
table #mugshots {
	border-width: 0px 0px 0px 0px;
	border-spacing: 5px;
	border-style: solid solid solid solid;
	border-color: gray gray gray gray;
	border-collapse: separate;
	background-color: white;
}
#mugshots tr.invisible {display:none;}
#mugshots td {
		font-size: x-small;
		width:110px;
	border-width: 1px 1px 1px 1px;
	padding: 3px 3px 3px 3px;
	border-style: solid solid solid solid;
	border-color: gray;
	background-color: rgb(255, 245, 238);
	-moz-border-radius: 0px 0px 0px 0px;
}
#mugshots td.mugshotgiver {
		font-size: x-small;
		width:55px;
	border-width: 1px 1px 1px 1px;
	padding: 2px 2px 2px 2px;
	border-style: solid solid solid solid;
	border-color: gray;
	background-color:green;
	-moz-border-radius: 0px 0px 0px 0px;
}
#mugshots td.mugshotrole0 
{
border-color: gray;
background-color: rgb(220, 220 ,200);
}
#mugshots td.mugshotrole1 
{
border-color: gray;
background-color: rgb(220, 220 ,200);
}
 #mugshots td.mugshotrole2
{
border-color: gray;
background-color: rgb(220, 220 ,200);
}
#mugshots td.mugshotrole3
{
border-color: gray;
background-color: rgb(220, 220 ,200);
}
</style>
CSS;
	return $css;
}
function getModerators ($ord)
{
	$buf='';
	$q="Select groupuid from topicgroups where nlmord='$ord'";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	while ($r=mysql_fetch_object($result))
	{
		if ($buf!='') $buf.=', ';
		$buf .= "<a class=tinylink href='groups.php?gid=$r->groupuid' ><fb:grouplink linked=false  gid=$r->groupuid /></a> ";
	}
	if ($buf!='') $buf="  <small> moderators: ".$buf."</small>";
	mysql_free_result($result);
	return $buf;
}

function getTopicSearchResults($q,$message)
{
	//echo "getTopicSearchResults q is $q <br/>";
	$counter=0;
	$buf = "<fb:explanation><fb:message>$message</fb:message>
<p>click topic to see  Facebook Groups and MedLine info</p><table>";
	$result = mysql_query($q) or die ("Cant $q ".mysql_error());
	while ($r=mysql_fetch_object($result))
	{
		$moderators = getModerators($r->ord);
		$buf.= "<tr><td><a  title='topic #$r->ord $r->nlmxtra' href='topics.php?ord=$r->ord' >$r->nlmtopic
		<span class=hbcounter>($r->hurlcount)</span></a>
		&nbsp; </td><td>$moderators</td></tr>";
		$counter++;
	}
	$buf.= "</table></fb:explanation>";
	if ($counter==0) $buf=''; // might all disappear
	return $buf;
}


/*


//<td class='mugshotrole6'><fb:profile-pic uid=$user ></fb:profile-pic><td class='mugshotrole5'><fb:profile-pic uid=$targetfbid ></fb:profile-pic>
*/
function  caregiving_list($user)
{
	$outstr =""; $counter = 0;
	$q = "select * from  careteams c  left join fbtab f on c.fbid=f.fbid where c.giverfbid ='$user' and c.giverrole='4' and f.mcid!='0'";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($counter !=0) $outstr.=' ';
		$outstr.="<a href='ctviewas.php?fbid=$u->fbid' ><fb:name linked='false' uid=$u->fbid></fb:name></a>";
		$counter++;
	}
	mysql_free_result($result);
	if ($counter==0) return ''; else
	return $outstr;
}



function dashboard ($user)
{
	// might have $user==0
	$appname = $GLOBALS['healthbook_application_name'];
	$hbappuser = $GLOBALS['healthbook_application_image'];
	$version = $GLOBALS['healthbook_application_version'];
	$publisher = @$GLOBALS['healthbook_application_publisher'];
	if (isset( $GLOBALS['healthbook_application_font_family']))
	$ffamily = "font-family: ".$GLOBALS['healthbook_application_font_family'].';'; else $ffamily='';
	$apikey = $GLOBALS['appapikey'];
	$css = mugshot_css();
	if (!$user)
	{
		$my_viewing_friends=''; $melink=''; $vlinks=''; $viewing=''; $color='#DDDDDD';
	}
	else {
		$q="SELECT targetfbid,targetmcid,applianceurl,mcid from fbtab where fbid='$user' ";
		$result = mysql_query($q) or die ("$q ".mysql_error());
		$r = mysql_fetch_array($result);
    if(!$r) 
      $tfbid=0; 
    else {
      $u = HealthBookUser::load($user);
      $tfbid = $r[0];
      $hurl=$u->authorize($r[2].$r[1]);
    }
		$my_viewing_friends = caregiving_list($user); if ($my_viewing_friends=='') $my_viewing_friends = "";

		$melink =($r[3]!=0&&($user!=$tfbid) )?"<a  href='ctviewas.php?fbid=$user' >myself</a> ":'';
		if ($tfbid!=0) $melink = "<a  href='ctviewas.php' >none</a> $melink";
		if ($tfbid==0) {
			$vlinks = '';
			$viewing = "<td width=80px>not viewing anyone's records</td><td  width='60px' class='mugshotrole5'>&nbsp;</td>";
		}
		else {
			$hurlimage = $GLOBALS['images']."/hurl.png";
			$viewing = " now viewing <fb:name possessive=false uid='$tfbid' useyou='false'/> <a target='_new' title='open healthURL on MedCommons' href='$hurl'>
		<img src=$hurlimage alt=hurl /></a>";
			$viewing = "<td width=80px>$viewing</td><td  width='60px' class='mugshotrole5'>&nbsp;<fb:profile-pic uid=$tfbid ></td>";
			$vlinks = <<<XXX
      | <a href="healthurl.php">HealthURL</a>
      | <a href="documents.php">documents</a>
XXX;
	}
if ($tfbid==0) $color ='white'; else
{
	if ($user!=$tfbid) $color="#EED8C4"; else
	$color="#B8D5F3";
}
}

	if ($melink!='')$changeview = "<br/>change view to: $melink $my_viewing_friends"; else
	{
		if ($my_viewing_friends)
		$changeview="<br/>change view to: $my_viewing_friends";
		else $changeview='';
	}
	if (!$user) $ulink='not logged on'; else
	$ulink = " <img src='http://static.ak.facebook.com/images/icons/friend.gif' /><fb:name uid=$user useyou=false/>";

	$markup = <<<XXX
$css<div style="$ffamily  background-color: $color "  >
<span style='float: left; display:inline;margin-top:10px;margin-left:7px;font-size:1.0em '>
   &nbsp;
   <fb:if-user-has-added-app><a href="home.php">home</a> $vlinks
 <fb:else>
       <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=dash' >add $appname</a> </fb:else>
</fb:if-user-has-added-app>  </span>
   <span style='float: right;display:inline; margin-top:5px;margin-right:13px;'>
	<span style='font-size:1.0em '>
<form style="display:inline;height:10px;margin-right:25px; "  action='topics.php' method='POST'>
<input type=hidden value='search' name='search'>
	<input style = "height:1.2em;padding:0;border:1px solid #000000;vertical-align:middle;font-size:1em;" size=12  type=text value='' name=filter >
	<input type=submit value='search' size=20  name=submit>
</form>
<a href="topics.php">topics</a> | 
   <fb:if-user-has-added-app><a href="ct.php?o=i">invite</a> | 
      <a href="index.php?privacy">privacy</a> | </fb:if-user-has-added-app>
      <a href="http://www.facebook.com/apps/application.php?api_key=$apikey &app_ref=about">about</a> | 
     <a href="help.php">help</a></span></span><br/><br/>
<table style="margin-left:7px; margin-right:7px; width:635px" ><tr><td align=left width='60px' class='mugshotrole6'><img src=$hbappuser /></td><td width='430px'>
    <span style="font-size:14px;color: black;" >$appname</span><br/><i>$version  by: $publisher
   $ulink </i>    
     $changeview</td><td>$viewing</td></tr></table>
XXX;
	return $markup; //</div> was deliberaely removed, yes the html will be unbalanced, lets see
}
//    <fb:tab_item href='hbmlexec.php' title='plug-ins' />
//      <fb:tab_item href='healthurl.php?o=i' title='info' />
 //     <fb:tab_item href='healthurl.php?o=f' title='forms' />
function hurl_dashboard ($user, $kind)
{
	$top = dashboard($user);
	$bottom = <<<XXX
<fb:tabs>

      <fb:tab_item href='healthurl.php' title='HealthURL' />
      <fb:tab_item href='healthurl.php?o=a' title='activity log' />

     
 </fb:tabs>
XXX;
	$needle = "title='$kind'";
	$ln = strlen($needle);
	$pos = strpos ($bottom,$needle);
	if ($pos!==false)
	{  // add selected item if we have a match
		$bottom = substr($bottom,0,$pos)." selected='true' ".
		substr ($bottom, $pos);
	}
	return $top.$bottom;
}
function hidden_login_frame($user,$mcid)
{
	// NOTE: we log in even if the user does not have an mcid.  This allows
	// users without medcommons accounts to still view records.
	//if ($mcid=='0' ) return false;
  
  return "";

	// We authenticate into the gateway as member of the MC group of the target mcid
	$result = mysql_query("select fb2.groupid from fbtab fb1,fbtab fb2 where fb1.fbid=$user and fb2.fbid=fb1.targetfbid")
	or die ("bad facebook id");


	$fbinfo = mysql_fetch_object($result);
	if(!$fbinfo) {
		return "";
	}
	$u = HealthBookUser::load($user);

	mysql_free_result($result);
	$appname = $GLOBALS['healthbook_application_name'];
  // TODO: remove checkidp parameter after hblogin is updated to always check idp
	$iframesrc = $GLOBALS['login_iframe']."?fbid=$user&mcid=$mcid&gid=".$fbinfo->groupid."&fn=".urlencode($u->getFirstName())."&ln=".urlencode($u->getLastName())."&checkidp=true";
	$appliance_key = $GLOBALS['appliance_key'];
	$appliance_app_code = $GLOBALS['appliance_app_code'];
	$iframesrc = sign_application_url($appliance_app_code,$appliance_key,$iframesrc);
	$msg = " <fb:iframe src='$iframesrc' smartsize='false' frameborder='false' style='border: 0; width: 100%; ' scrolling='no'/>";
	return $msg;
}

//END OF DASHBOARD SECTION



// For debugging
function backtrace()
{
	$bt = debug_backtrace();
	ob_start();

	echo("<br /><br />Backtrace (most recent call last):<br /><br />\n");
	for($i = 0; $i <= count($bt) - 1; $i++)
	{
		if(!isset($bt[$i]["file"]))
		echo("[PHP core called function]<br />");
		else
		echo("File: ".$bt[$i]["file"]."<br />");

		if(isset($bt[$i]["line"]))
		echo("&nbsp;&nbsp;&nbsp;&nbsp;line ".$bt[$i]["line"]."<br />");
		echo("&nbsp;&nbsp;&nbsp;&nbsp;function called: ".$bt[$i]["function"]);

		if($bt[$i]["args"])
		{
			echo("<br />&nbsp;&nbsp;&nbsp;&nbsp;args: ");
			for($j = 0; $j <= count($bt[$i]["args"]) - 1; $j++)
			{
				if(is_array($bt[$i]["args"][$j]))
				{
					print_r($bt[$i]["args"][$j]);
				}
				else
				echo($bt[$i]["args"][$j]);

				if($j != count($bt[$i]["args"]) - 1)
				echo(", ");
			}
		}
		echo("<br /><br />");
	}
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}


?>
