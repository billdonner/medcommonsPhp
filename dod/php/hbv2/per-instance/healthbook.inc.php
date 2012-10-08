<?php
require_once "session.inc.php";
require_once "./JSON.php";
//require_once "globals.inc.php"; obsolete
require_once "hbuser2.inc.php";
require_once "appinclude.php";  // required of all facebook apps put this last
require_once "utils.inc.php";
function smallwall ($user,$limit)
{
	$wallstuff='';
	$q = "select * from  carewalls where wallfbid = '$user' and severity>0 order by time desc limit $limit ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		$time = strftime ('%D',$u->time); $wallstuff.="$time: $u->msg\r\n";
	}
  if($wallstuff != '')
    $wallstuff .= "\r\n$wallstuff";

	mysql_free_result($result);

	return  $wallstuff;
}
function smallwallbr ($user,$limit)
{
	//$wallstuff='<br/>'; 
  dbg("creating smallwall for $user (lmit = $limit)");
	$stuff=array();
	$q = "select * from  carewalls where wallfbid = '$user' order by time desc limit $limit ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		$stuff  [] =  array($u->time, $u->authorfbid, $u->msg );
    dbg("found carewall entry ".$u->msg);
		//$wallstuff.="$time: $u->msg<br/>";
	}
	mysql_free_result($result);
	//return  $wallstuff;
	return  $stuff;
}
function publish_info($user)
{
	$appname = $GLOBALS['healthbook_application_name'];
	$q = "select applianceurl, mcid from  fbtab  where fbid = '$user' ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	$u=mysql_fetch_array($result);
	if (!$u) return false;
	$hurl = $u[0].$u[1];


	$wallstuff = smallwall($user,5);
	$carewall = array(      'field' => 'Carewall ',
	'items' =>array(array('label'=>$wallstuff,
	'description' => 'Recent lines ',
	'link'=>$hurl)));
	$info_fields = array(
	array('field' => 'In Case of Emergency Contact',
	'items' => array(array('label'=> '[replace with a friendly name and number]',
	'description'=>'The Mountain Goats is an urban folk band led by American singer-songwriter John Darnielle.'
	))),
	array(      'field' => 'Online Access URL ',
	'items' =>array(array('label'=>$hurl,
	'description' => 'My Health Record as stored in MedCommons.',
	'link'=>$hurl))),
	$carewall
	);

	$team=array();
	$gids = '';
	$q = "select * from  careteams where fbid = '$user' ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($gids!='') $gids.=',';
		$gids.= $u->giverfbid;
	}
	$qqq="SELECT uid,name FROM user   WHERE  uid IN ($gids)";
	$ret = $GLOBALS['facebook']->api_client->fql_query($qqq);
	if ($ret){
		$count = count($ret);
		for ($j=0; $j<$count; $j++)
		{
			$id = $ret[$j]['uid'];
			$name = $ret[$j]['name'];
			$team[]=array('label'=> $name,
			'link'=>"http://www.facebook.com/profile.php?id=$id" );
		}
	}

	mysql_free_result($result);
	if (count($team)>0)	$info_fields [] = array('field' => 'I Receive Care From',	'items' => $team);

	$team=array();
	$gids = '';
	$q = "select * from  careteams c  left join fbtab f on c.fbid=f.fbid where c.giverfbid ='$user' and c.giverrole='4' and f.mcid!='0'";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($gids!='') $gids.=',';
		$gids.= $u->fbid;
	}
	$profile='';
	$qqq="SELECT uid,name FROM user   WHERE  uid IN ($gids)";
	$ret = $GLOBALS['facebook']->api_client->fql_query($qqq);
	if ($ret){
		$count = count($ret);
		for ($j=0; $j<$count; $j++)
		{
			$id = $ret[$j]['uid'];
			$name = $ret[$j]['name'];
			$team[]=array('label'=> $name,'link'=>"http://www.facebook.com/profile.php?id=$id" );
			$q = "select * from  carewalls where wallfbid = '$id' and severity>0 order by time desc limit 1 ";
			$result = mysql_query($q) or die("cant  $q ".mysql_error());$wallstuff='';
			$u=mysql_fetch_object($result);
			if ($u)
			{
				$time = strftime ('%D',$u->time);
				$profile.="$name $time: $u->msg<br/>";
			}
		}
	}
	$profile .= "<small><a href=".$GLOBALS['facebook_application_url']." >".$GLOBALS['healthbook_application_name']." - see records  give care"."</a></small>";
	if (count($team)>0)	{ $info_fields [] = array('field' => 'I Give Care To','items' => $team); }
	try {
		$GLOBALS['facebook']->api_client->profile_setInfo("$appname Emergency Info", 1, $info_fields, $user);

	}
	catch(Exception $e) {
		$mess = $e->getMessage();
		logHBEvent ($user, 'setInfo' , "Cant publish setinfo $mess");
	}
	try {
		$GLOBALS['facebook']->api_client->profile_setFBML( NULL, $user, 'boxes:'.$profile, '', 'mobile support coming soon',$profile); // changed by facebook
	}
	catch(Exception $e) {
		$mess = $e->getMessage();
		logHBEvent ($user, 'setFBML' , "Cant publish setFBML $mess");
	}

}


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
{            return;

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

	$splash = <<<XXX
	
	<table class=splash><tr><td><img src='http://www.medcommons.net/images/splashscreens/Facebook%20Home.png' alt='splashpic1' ><br/>
	<span class=caption>Manage your health and your loved one's too!</span></td>
	<td><img src='http://www.medcommons.net/images/splashscreens/Facebook%20Settings.png' alt='splashpic2' ><br/>
	<span class=caption>Your Records are safely stored at Amazon S3</span></td></tr>
	<tr><td><img src='http://www.medcommons.net/images/splashscreens/HealthURL%20Privacy.png' alt='splashpic3' ><br/>
	<span class=caption>You control who can access your records and how</span></td>
	<td><img src='http://www.medcommons.net/images/splashscreens/HealthURL%20Viewer.png' alt='splashpic4' ><br/>
	<span class=caption>Get radiology, labs, fax into your account</span></td></tr></table>
XXX;

	if  ($GLOBALS['bigapp'])
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
	}
	else
	{
		$appname = $GLOBALS['healthbook_application_name'];
		$apikey = $GLOBALS['appapikey'];

		$dash = dashboard($user);
		$markup = "<fb:fbml version='1.1'>$dash
<fb:is-logged-out>
   <fb:title>You are Not Logged On - MedCommons Facebook Home</fb:title> 
 <fb:explanation>
    <fb:message>Please <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=nlgisplash' >sign up</a> to use $appname </fb:message>  
    $splash
</fb:explanation>
<fb:else>
 
 <fb:if-is-app-user>
 <fb:title>App Loaded - MedCommons Facebook Home</fb:title> 
 <fb:explanation>
       <fb:message>If you want to keep your own records on MedCommons Facebook, go to <a class=applink href='settings.php' >settings</a>, or if you are just a Care Giver you can go <a class=applink href='home.php'>home</a></fb:message>  
       $splash
       </fb:explanation>
       <fb:else>
   <fb:title>App Not Loaded - MedCommons Facebook Home</fb:title> 
 <fb:explanation>
       <fb:message>Please <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=lgisplash' >sign up</a> to use $appname </fb:message>  
           $splash
</fb:explanation>
</fb:if-is-app-user>  
</fb:is-logged-out></fb:fbml>";

	}
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
	// no longer needed now that appinclude.php must connect
	/*	mysql_connect($GLOBALS['DB_Connection'], $GLOBALS['DB_User']) or err("Error connecting to database.");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die("can not connect to database $db");
	*/
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
function css ()
{//border:1px solid #3B5998;vertical-align:middle;
	$css = <<<CSS
<style type="text/css">
a { color:  #3B5998;}
a.tinylink  {font-size:xx-small; text-decoration:underline; color: gray;}
a.tinylink.embedded  {font-size:11px;}
a.tinylink img { position: relative; top: 0px; }
table #mugshots {border-width: 0px;	border-spacing: 5px;	border-style:solid; border-color: gray;	border-collapse: separate;	background-color: white;}
#mugshots tr.invisible {display:none;}
#mugshots td {	font-size: x-small;	width:110px;	border-width: 1px ;	padding: 3px;	border-style: solid;	border-color: gray;	background-color: rgb(255, 245, 238);}
#mugshots td.mugshotgiver {	font-size: x-small; width:55px;border-width: 1px;padding:  2px;	border-style: solid; border-color: gray;background-color:green;}
#mugshots td.mugshotrole{border-color: gray;background-color: rgb(220, 220 ,200);}
.topline {margin-top: 0px; height: 20px; padding-top:4px; padding-bottom:4px; font-size:1.0em}
.floatleft {float: left;display:inline; margin-left:13px; padding-top:3px;}
.floatright {float:right; display:inline;margin-right:14px; }
.viewas { font-size:1.0em; color:#3B5998;}
.viewasgo {font-size:.8em;}
.miniform, .miniform form {display:inline; padding:0; margin:0; }
.miniput {height:1.1em;padding:0;font-size:.9em;}
td.logocaption {  padding-left: 10px; color: #444; }
.appnamebanner { font-size:11px; font-weight: bold; font-family: verdana;}
.hurllinks {display:inline; border: 1px solid blue; padding: 0px;}
.bodypart {clear:both;}
.splash {background-color:#EEE}
.splash td {width:350px;}
.splash td img { padding: 20px; border: 1px solid; width: 260px;}
.splash td .caption { padding-left: 20px; font-size:.9em;}
.confirmbuttonstyle { font-weight: normal; margin-left: auto; margin-right:auto; width: 100px; padding: 3px; text-decoration:none; font-size: .8em;  color:#eee;  background-color:#3b5998;  border:1px solid #3b5998;}
#disconbutton {padding: 20px 0px 20px  260px;}
.caregivee { 
  background-color: #f6f6f6;
  border: 1px solid #bedada;
  margin-bottom: 8px;
  padding: 5px 2px 0px 5px;
}
.smallwallcontainer { margin: 5px 0px; }
.caregivee .pic { width: 70px; padding: 0px 10px; }
.caregivee .txt { font-size: 0.9em; margin: 10px 0px; vertical-align: middle; }
td.wall { vertical-align: top; font-weight: normal; }
td.wall img { position: relative;  top: 3px;}
#mcheader {
  margin-bottom: 20px;
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
function topright_menu($user,$viewinguser, $domyself,$donone,$xtra)
{
	//onchange="location = 'ctviewas.php?xfbid='+this.options[this.selectedIndex].value;">
	//		onchange="location = 'ctviewas.php?xfbid='+ this.getValue();  return false;"
	$counter = 0;
	$healthurl = ($viewinguser!=0)?"$xtra&nbsp;<a href=healthurl.php >HealthURL</a>&nbsp;&nbsp;":'';
	$outstr = <<<XXX
		
	<div class=miniform><form    name=mform action='ctviewas.php' method='get'>
		$healthurl<select name='xfbid' id='xfbid'
		onchange="mform.submit();" 
		title='view another friends records (you must be a care giver)' class=viewas >
XXX;
	$q = "select * from  careteams c  left join fbtab f on c.fbid=f.fbid where c.giverfbid ='$user' and c.giverrole='4' and f.mcid!='0'";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($counter !=0) $outstr.=' ';
		//$outstr.="<a href='ctviewas.php?fbid=$u->fbid' ><fb:name linked='false' uid=$u->fbid></fb:name></a>";
		$selected = ($u->fbid==$viewinguser) ? 'selected':'';
		$outstr .="<option $selected value='$u->fbid' ><fb:name linked='false' uid=$u->fbid></fb:name></option>";
		$counter++;
	}
	if ($domyself) {
		$selected = ($user==$viewinguser) ? 'selected':'';
		$outstr .= "<option  $selected value='$user' >myself</option>";
	}
	if ($donone) {
		$selected = ($viewinguser==0) ? 'selected':'';
		$outstr.= "<option $selected value='0' >none</option>";
	}
	$outstr.='</select>'.
	//<BUTTON name="submit" value="submit" type="submit">'.
	//'''   <IMG src="http://www.medcommons.net/images/bluecycle8x8.gif" alt="go"></BUTTON></span></form></div>';;
	"<input type=submit value='go' class=viewasgo /></span></form></div>";;
	mysql_free_result($result);
	//always return something for the upper right
	if ($counter==0) return "<div class=miniform>$healthurl</div>"; else
	return $outstr;
}



function dashboard ($user)
{
	// might have $user==0
	$marqueefbml = '';//$GLOBALS['marqueefbml'];
	$appname = $GLOBALS['healthbook_application_name'];
	$hbappuser = $GLOBALS['healthbook_application_image'];
	$version = $GLOBALS['healthbook_application_version'];
	$publisher = @$GLOBALS['healthbook_application_publisher'];
	if (isset( $GLOBALS['healthbook_application_font_family']))
	$ffamily = "font-family: ".$GLOBALS['healthbook_application_font_family'].';'; else $ffamily='';
	$apikey = $GLOBALS['appapikey'];
	$css = css();
	$tfbid = 0; // needs to work

	if (!$user)	{		$my_viewing_friends=''; $melink='';  $viewing=''; $color='#DDDDDD';	}
	else {
		$q="SELECT targetfbid,targetmcid,applianceurl,mcid from fbtab where fbid='$user' ";
		$result = mysql_query($q) or die ("$q ".mysql_error());
		$r = mysql_fetch_array($result);
		if(!$r)
		{$tfbid=0;$mcid=0; $xtra ='';}
		else {
			$u = HealthBookUser::load($user);
			$tfbid = $r[0]; $mcid = $r[3];

			// ssadedin: note: must get hurl for target user, not current user
			$hurl = $u->t_hurl();
			$hurlimage = "http://www.medcommons.net/images"."/tx_hurl.gif";
			$xtra = "<a target='_new' title='open healthURL on MedCommons' href='$hurl'><img src=$hurlimage alt=hurl /></a>";
		}
		$domyself =($r[3]!=0);//&&($user!=$tfbid) );
		$donone = false; //($tfbid!=0);

		$my_viewing_friends = topright_menu($user,$tfbid, $domyself,$donone,$xtra);
		if ($tfbid==0)$viewing = "<span>not viewing anyone's records</span>";
		else $viewing = "<table class=pichurl><tr><td width=80px>now viewing <fb:name possessive=false uid='$tfbid' useyou='false'/>
			 </td>
			<td  width='60px' ><fb:profile-pic uid=$tfbid /></td></tr>
			</table>";
	}

	// a litle splash of color
	if ($tfbid==0) $color ='white';
  else 	if ($user!=$tfbid) $color="#EED8C4"; 
  else
   $color="#BFD7F4";

	if ($GLOBALS['bigapp'])	$searchform = <<<XXX
<div class=miniform><form action='topics.php' method='POST'>
<input type=hidden value='search' name='search'>	<input class=miniput size=12  type=text value='' name=filter ><input type=submit value='search' size=20  name=submit>
</form></div>
XXX;
	else $searchform = '';
	//if (!$user) $ulink='not logged on'; else
	//$ulink = " <img src='http://static.ak.facebook.com/images/icons/friend.gif' /><fb:name uid=$user useyou=false/>";
	$xlink = "<img src='$hbappuser' alt='missing $hbappuser' />";
	if ($GLOBALS['extgroupurl']!='')
	$xlink = "<a href='".$GLOBALS['extgroupurl']."' >$xlink</a>";
	if ($GLOBALS['bigapp'] ) $topicslink = "<a href='topics.php'>topics</a> | "; else $topicslink = '';
	$markup = <<<XXX
$css<div id=mcheader style="$ffamily  background-color: $color "  >
<div class=topline>
<span class=floatleft >
   <fb:if-is-app-user>
      <a href="index.php">home</a> | <a href="ct.php?o=i">invite</a> | 
      <a href="settings.php">settings</a> | 
    </fb:if-is-app-user>
      <a href="http://www.facebook.com/apps/application.php?api_key=$apikey &app_ref=about">about</a> | 
     <a href="http://www.facebook.com/group.php?gid=10318079541">forum</a> | 
     <a href="help.php">help</a>
$searchform
$topicslink
</span>
<span class=floatright>
   <fb:if-is-app-user>$my_viewing_friends 
   <fb:else>
       <a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=dash' >add $appname</a>
    </fb:else>
  </fb:if-is-app-user>  
</span>
</div>
<div class=tablebanner>
    <table><tr><td align=left width='60px' >$xlink</td><td width='430px' class='logocaption'>
    <span class='appnamebanner'>$appname</span><br/><span class='appversion'>$version by $publisher</span>
    </td><td>$viewing</td></tr></table>
</div>
$marqueefbml
</div>
<div class=bodypart>
XXX;
	return $markup; //</div> was deliberaely removed, yes the html will be unbalanced, lets see
}
//    <fb:tab_item href='hbmlexec.php' title='plug-ins' />
//      <fb:tab_item href='healthurl.php?o=i' title='info' />
//     <fb:tab_item href='healthurl.php?o=f' title='forms' />

function hurl_dashboard ($user, $kind)
{
	$top = dashboard($user);
	if ($GLOBALS['bigapp']) $phurl = "<fb:tab_item href='home.php?o=u' title='Public HealthURLs' />"; else $phurl = '';

	$bottom = <<<XXX
<fb:tabs>
      <fb:tab_item href='healthurl.php' title='HealthURL' />
      <fb:tab_item href='healthurl.php?o=a' title='Activity Log' />
          <fb:tab_item href='documents.php' title='Documents' />
          $phurl
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
	return "";
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
