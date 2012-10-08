<?php
// vim: tabstop=8 shiftwidth=8 noexpandtab

require_once "topics.inc.php";
function home_dashboard ($user, $kind,$showcareteam,$showcaregiving)
{
	$top = dashboard($user);
	$ifrecords='';
	if ($showcareteam) $ifrecords = " <fb:tab_item href='home.php?o=t' title='care team' /> <fb:tab_item href='home.php?o=w' title='care wall' />";

	$showrecords='';
	if ($showcaregiving) $showrecords = " <fb:tab_item href='home.php?o=g' title='care giving' />";

	$bottom = <<<XXX
<fb:tabs>
<fb:tab_item href='home.php?o=o' title='collaborate' />       $showrecords           $ifrecords      
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
function home($user,$mcid,$full,$facebook,$appliance,$menuitem)
{
	$appname = $GLOBALS['healthbook_application_name'];


	// ssadedin: note we *do* allow mcid=0 to be passed in here because
	// the user may be a caregiver to another user who does have mcid.  In
	// such case they will be authenticated onto the appliance under authority
	// of that person's healthbook group via the hidden frame
	//$hlf  = $full?hidden_login_frame($user,$mcid):''; //dont do bottoms
	//	$careteaminfo = careteam_info($user,$facebook,$appliance,$mcid);

	$u = HealthBookUser::load($user);

	if ($u===false){  
		
			logHBEvent($user,'home',"load healthbook user failed, going to topics");
		echo gototopics($facebook,$user); exit;
	/*$page='topics.php';
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	echo $markup;
	exit;
	*/
	}

	$targetfbid = $u->targetfbid;
	$targetmcid = $u->targetmcid;
	$mymcid = $u->mcid;
	$appurl = $u->getTargetUser() ? $u->getTargetUser()->appliance : "";
	if ($menuitem=='Public HealthURLs') $dash = hurl_dashboard($user,$menuitem);
	else
	$dash = home_dashboard($user,$menuitem,($targetfbid!=0),($mymcid==0) || ($targetmcid==$mymcid)); // PASS IN FLAG TO SHOW CARETEAM AND WALL
	//$dash = home_dashboard($targetfbid,$menuitem);
	if ($mymcid=='0')  $welcome_blurb=<<<XXX
 <fb:explanation>
<fb:message>Welcome to MedCommons on Facebook</fb:message>
<p>A private HealthURL account is separate from Facebook. It can be shared with to share with your Facebook Care Team and with your doctors independent of Facebook. </p>
<p><a href=settings.php >Subscribe</a> to your own MedCommons account. Invite friends to your Care Team using the links below.</p>
</fb:explanation>
XXX;

	else

	$welcome_blurb=<<<XXX
 <fb:explanation>
<fb:message>Welcome to MedCommons on Facebook</fb:message>
<p>View, edit and share the <a href=healthurl.php >HealthURL</a> of <fb:name uid=$user useyou=false></fb:name></p>
<p>Manage your own MedCommons account using <a href=settings.php >Settings</a></p>
<p>Invite friends to your Care Team using the links below.</p>
</fb:explanation>
XXX;

	switch ($menuitem)
	{
		case 'collaborate':{
			$title="Collaboration";
			$skey_warning = "";
			if(($u->fbid == $u->targetfbid) && ($u->mcid) && (!$u->storage_account_claimed))  {
				ob_start();
				include "confirm_account_warning.php";
				$skey_warning = ob_get_contents();
				ob_end_clean();
			}
			//if ($targetfbid!=0) {  // only show the targets careteam stuff if there is a target
				$markup0 = ''; //careteam_wall($user, $facebook, $targetfbid);
				$markup1 =  careteam_mugshots_quick($user,$facebook,$targetfbid);
				if ($markup1=='') $markup1 = nocareteam($targetfbid);
			// always show something about caregiving if viewing self
			if (($targetfbid==$user) || ($targetfbid == 0))
			{
				$markup2 = caregiving_mugshots($user,$facebook,$user,$mcid); // always look relative to self
				if ($markup2=='') $markup2 = nocaregiving($user); //must be non-nill to show something
				$markup4 = '';//getFavoriteTopics($user,$facebook);
			} else $markup2=$markup4='';
			//$targetfbid = $user; // show something
			$markup5 ='';//  getPostedHealthUrlsByUser($facebook,$targetfbid);
			if ($GLOBALS['bigapp'] )$markup3  = getHealthBookGroupPics($facebook,$user,$targetfbid); else $markup3='';
			$markup = $welcome_blurb.$skey_warning.$markup0.$markup1.$markup3.$markup5.$markup2.$markup4; break;
		}
		case 'Public HealthURLs':
			{
				if ($targetfbid==0) $targetfbid = $user; // show something
				$title='Public HealthURLs';
				$markup =  getPostedHealthUrlsByUser($facebook,$targetfbid);
				if (!$markup)
				$markup =<<<XXX
  <fb:explanation>
    <fb:message><fb:name uid='$targetfbid' possessive='false' linked=false useyou='false'/> has no  posted Public HealthURLs </fb:message>
    $purl
</fb:explanation>
XXX;
				break;
}
/*case 'favorites': {
$title="Favorite Topics"; $markup = getFavoriteTopics($user,$facebook); break;
}
*/
case 'care team': {
	$title="Care Team"; $markup = careteam_mugshots($user,$facebook,$targetfbid);
	if ($markup=='') $markup = nocareteam($targetfbid); break;
}
case 'care giving': {
	//if ($targetfbid==0)
	$targetfbid = $user; // show something
	$title = "Care Giving"; $markup = caregiving_mugshots($user,$facebook,$targetfbid,$mcid);
	if ($markup=='') $markup = nocaregiving($targetfbid); break;
}
case 'care wall': {$title = "Care Wall"; $markup = careteam_wall($user, $facebook, $targetfbid);break;}
case 'facebook groups': { $title = "Facebook Groups with Public HealthURLs";

require_once "topics.inc.php";$markup  = getHealthBookGroups($facebook,$user,$targetfbid);
break; // note may fall into next

}
default: $markup = "bad submenu on homemenu";
	}





	$markup = <<<XXX
<fb:fbml version='1.1'><fb:title>$title</fb:title>
$dash
$markup

</fb:fbml>
XXX;
	return $markup;
}


/*
Code for careteams
*/
function  careteam_notify_list ($user,$facebook)
{ // return a string which is an array delimited list of facebook ids
	$counter = 0; $outstr=array();
	$q = "select * from  careteams where fbid = '$user' ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{


		$outstr[] = $u->giverfbid;

		$counter++;
	}

	mysql_free_result($result);
	return $outstr;
}

function get_alog()
{
	$xxx=<<<XXX
	<p>Activity for user <?=$u->mcid?></p>
<ul>
<?foreach($sessions as $s):?>
  <li><?=strftime("%m-%d-%Y %H:%M:%S",$s->beginTimeMs/1000)?> - <?=$s->summary->description?> - <?=$s->summary->sourceAccount->id ?> ( <?=$s->summary->sourceAccount->idType?> )</li>
<?endforeach;?>
XXX;


}


function wallpost($user,$authorfbid,$time,$msg,$dbrecid)
{
if ($user==$authorfbid)
	{
	$remove= <<<DIALOG
<fb:dialog id="removewallpost_dialog{$time}{$user}{$authorfbid}" cancel_button=1>
  <fb:dialog-title>Remove Post from Care Wall</fb:dialog-title>	
  <fb:dialog-content><form >Do you really want to remove this post from the Care Wall?</form></fb:dialog-content>
  <fb:dialog-button type="button" value="Yes" href="ct.php?removepost=$time&id=$dbrecid" /> 
</fb:dialog>
<a class=tinylink href='#' clicktoshowdialog="removewallpost_dialog{$time}{$user}{$authorfbid}" >remove this post</a>
DIALOG;
	}
	else $remove='';
	return <<<XXX
  		<fb:wallpost linked=false uid="$authorfbid" t="$time" >
		$msg
		$remove
  		</fb:wallpost>
XXX;
}
function  careteam_wall ($user,$facebook,$targetfbid)
{ // return fbml wall

	$appname = $GLOBALS['healthbook_application_name'];
	if ($targetfbid ==0) return '';
	$my = ($user==$targetfbid )?'My':"<fb:name linked=false possessive='true' uid=$targetfbid></fb:name>";
	$counter = 0;
	$cursor = 0; // will move thru merge with activitylog
	$outstr="<fb:explanation><fb:message>$my CareWall</fb:message>             
	<p>Access to this  wall is restricted to CareTeam members. &nbsp;<a class=tinylink href=ct.php?o=w>write</a> to $my CareWall</p>
                <div style='font-size:10px; '> <fb:wall>";

	try {      $maxsolellolines = 5; // bill - inefficient but effective way of keeping solleo to
	$u = HealthBookUser::load($user);
	$t = $u->getTargetUser();
	$sess = array();
	if(is_object($t->getOAuthAPI())) {
		$sessions  = $t->getOAuthAPI()->get_activity($t->mcid);
		foreach($sessions as $s) // turn into a normal array
		if ($maxsolellolines>0) {$sess[]=$s; $maxsolellolines-=1;}
	}
	$logentrycount = count($sess);
	$q = "select * from  carewalls where wallfbid = '$targetfbid' order by time desc limit 5 ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		$up = "<h4>These activities occured in your MedCommons Account</h4>";
		if ($maxsolellolines==0) {
			$up.="<span class=moresolello><a href='healthurl.php?o=a'> view more MedCommons Activities</a>";
		}

		$up.=" <ul>";
		$any=false; $firsttime='****';
		// get any activity log entries that are olderr than this entry
		while (($cursor<$logentrycount)&&
		($sess[$cursor]->beginTimeMs/1000 >=$u->time))
		{
			if (!$any) $firsttime = $sess[$cursor]->beginTimeMs/1000;
			$s = $sess[$cursor++];
			$any=true;
			$time = strftime("%m-%d-%Y %H:%M:%S",$s->beginTimeMs/1000);
			$up.="<li>".
			$time." ".$s->summary->description.'-'.$s->summary->sourceAccount->id.'-'.$s->summary->sourceAccount->idType."</li>
                                        ";
		}
		$up.="
                                </ul>
                                ";
		if (isset($GLOBALS['facebook_userid'])) $appliance_userid = $GLOBALS['facebook_userid']; else $appliance_userid=11;
		if ($any) $outstr.=wallpost ($user,$appliance_userid,$firsttime,$up,$u->id);
		$outstr .= wallpost($user, $u->authorfbid,$u->time,$u->msg,$u->id);
		$counter++;
	}

	// get any aremaining y log entries that are olderr than this entry
	$up = "<ul>";
	$any=false;

	while (($cursor<$logentrycount))
	{
		$s = $sess[$cursor++];
		$any=true;
		$time = $s->beginTimeMs/1000;
		$up.="<li>".
		$time." ".$s->summary->description.'-'.$s->summary->sourceAccount->id.'-'.$s->summary->sourceAccount->idType."</li>
                                ";
	}
	$up.="
                        </ul>
                        ";
	if ($any) $outstr.=wallpost ($user,11,$s->beginTimeMs/1000,$up,0);
	}
	catch(Exception $e) {
		error_log("Error rendering care wall for user $user: ",$e->getMessage());
		$outstr.="<p>A problem was experienced loading your recent activity.  You may need
                        to <a href='settings.php'>reconnect</a> your $appname account to it's MedCommons Appliance.</p>";
	}

	if(isset($result) && $result)
	mysql_free_result($result);

	$outstr.="</fb:wall></div></fb:explanation>";
	return $outstr;
}

function myid($user,$blurb)
{
	$outstr = "<td class='mugshotrole'><fb:profile-pic uid=$user ></fb:profile-pic><p>
		<fb:name uid=$user useyou='false'></fb:name>
		$blurb </p></td>";
	$outstr .= "<table><tr>".$outstr."</tr></table>";
	return $outstr;
}
function patientid($user,$targetfbid,$mymcid, $blurb)
{
	if ($targetfbid==0)
	$outstr = "<td class='mugshotrole'>
		<p> You are not viewing anyone&quot;s records</p></td>";
	else
	$outstr = "<td class='mugshotrole'><fb:profile-pic uid=$targetfbid ></fb:profile-pic><p>
		<fb:name uid=$targetfbid useyou='false' possessive='true' ></fb:name>
		$blurb </p></td>";
	$outstr = "<table><tr>".$outstr."</tr></table>";
	return $outstr;
}
function platforminfo($user,$blurb)
{         $user = '5877746597'; // make it look like it is from corporate
$version = $GLOBALS['healthbook_application_version'];
$outstr = "<td class='mugshotrole'><fb:profile-pic uid=$user ></fb:profile-pic><p>
		<fb:name uid=$user useyou='false'></fb:name>
		$blurb </p><p>version $version</p></td>";
$outstr = "<table><tr>".$outstr."</tr></table>";
return $outstr;
}

//<td class='mugshotrole6'><fb:profile-pic uid=$user ></fb:profile-pic><td class='mugshotrole5'><fb:profile-pic uid=$targetfbid ></fb:profile-pic>

function blurb_myteam ($r,$power)
{

	$remove= <<<DIALOG

<fb:dialog id="removemember_dialog{$r->giverfbid}" cancel_button=1>
  <fb:dialog-title>Remove A Friend from Your Care Team</fb:dialog-title>	
  <fb:dialog-content><form >Do you really want to remove this member of your Care Team?</form></fb:dialog-content>
  <fb:dialog-button type="button" value="Yes" href="ct.php?o=r&id=$r->fbid&gid=$r->giverfbid" /> 
</fb:dialog>
<a class=tinylink href="#"  clicktoshowdialog="removemember_dialog{$r->giverfbid}" >remove</a>
DIALOG;

	$appname = $GLOBALS['healthbook_application_name'];
	$remlink = $power?"<br/>$remove ":'';

	switch ($r->giverrole)
	{
		case '0': {$blurb = " invited to $appname at  $r->lastinvite $remlink "; break;}
		case '1': {$blurb = " was invited to join at $r->lastinvite $remlink "; break;}
		case '2': {$blurb = " case 2 "; break;}
		case '3': {$blurb = " case 3 "; break;}
		case '4': {$blurb = " joined at $r->lastinvite  $remlink "; break;}
		default: {$blurb = " case error  "; break;}
	}
	$outstr = "<td class='mugshotrole'><fb:profile-pic uid=$r->giverfbid></fb:profile-pic><p>
		<fb:name uid=$r->giverfbid></fb:name>
		$blurb </p></td>";
	return $outstr;
}

function blurb_asself ($user)
{
	$outstr = "<td class='mugshotrole'><fb:profile-pic uid=$user></fb:profile-pic><p>
		<fb:name uid=$user></fb:name> are a caregiver for yourself<br/><a class='tinylink' href='ctviewas.php?fbid=$user'>view records</a> </p></td>";
	return $outstr;
}
function  careteam_mugshots($user,$facebook,$targetfbid)
{
	$my = ($user==$targetfbid )?'My':"<fb:name linked=false possessive='true'  uid=$targetfbid></fb:name>";
	$addlink = ($user==$targetfbid )?"<a class='tinylink' href='ct.php?o=h' >add members</a>":'';
	$app = $GLOBALS['facebook_application_url'];
	$fff = <<<XXX
<div style='padding:5px;'><fb:board xid="$targetfbid" 
          canpost="true"          candelete="false"          canmark="false"          cancreatetopic="true"          numtopics="5"          returnurl="$app">
   <fb:title>$my Discussion Board</fb:title>
</fb:board>
</div>
XXX;

	$outstr ="  <fb:explanation>
          <fb:message>$my Care Team <a class='tinylink' href='ct.php?o=n' >notify members</a>&nbsp; $addlink </fb:message>$fff
          <table id='mugshots'><tr class='invisible'><td></td><td></td><td></td><td></td><td></td></tr><tr>"; $counter = 0;
	$q = "select * from  careteams where fbid = '$targetfbid' ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		$mod = $counter -  floor($counter/5)*5;
		if ($mod==0 && $counter!=0)$outstr.="</tr><tr>";
		$outstr.= blurb_myteam($u,($user==$targetfbid ));
		$counter++;
	}
	mysql_free_result($result);
	if ($counter==0) return '';
	else $outstr ="$outstr</tr></table> </fb:explanation>";
	$outstr .='</div><br/>';

	return $outstr;
}

function  careteam_mugshots_quick($user,$facebook,$targetfbid)
{
	$wall =     careteam_wall($user, $facebook, $targetfbid);
	$app = $GLOBALS['facebook_application_url'];
	$my = ($user==$targetfbid )?'My':"<fb:name linked=false possessive='true'  uid=$targetfbid></fb:name>";
	$fff = <<<XXX
<div style='padding:5px;'><fb:board xid="$targetfbid" 
          canpost="true"          candelete="false"          canmark="false"          cancreatetopic="true"          numtopics="2"          returnurl="$app">
   <fb:title>$my Discussion Board</fb:title>
</fb:board>
</div>
XXX;
	$outstr =" <div class='mugshots'><fb:explanation>
          <fb:message>$my Care Team <a class=tinylink href='home.php?o=t'>more...</a></fb:message>

          <table><tr>";
	$counter = 0;
	$q = "select * from  careteams where fbid = '$targetfbid' ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($r=mysql_fetch_object($result))
	{
		$mod = $counter -  floor($counter/11)*11;
		if ($mod==0 && $counter!=0)$outstr.="</tr><tr>";
		$outstr.="<td class='mugshotgiver' width=55px style='color: #3b5998'><fb:profile-pic uid=$r->giverfbid /> <fb:name linked=false uid=$r->giverfbid /></td>";
		$counter++;
	}
	mysql_free_result($result);
	if ($counter==0) return '';
	else $outstr ="$outstr</tr></table>";
	$outstr .="         $wall
          $fff</div>";

	return $outstr;
}
function blurb_asgiver ($user, $r)
{
	$appname = $GLOBALS['healthbook_application_name'];
	$appUrl = $GLOBALS['app_url'];
	$pronoun = "<fb:pronoun useyou=false  possessive='true' uid='$r->fbid'/>";

	if (($r->targetfbid!=0)&&
	($user!=$r->giverfbid) ) $invitelinks =''; else
	$invitelinks = "<a class='tinylink' href='ct.php?o=a&gid=$r->giverfbid&id=$r->fbid''>accept</a>
	                                  <a class='tinylink' href='ct.php?o=r&gid=$r->giverfbid&id=$r->fbid''>decline</a>";
	if (($r->targetfbid!=0)&& ($user!=$r->giverfbid) )$joinedlinks =''; else
	$joinedlinks = "<a class='tinylink' href='#' clicktoshowdialog='remove_{$r->fbid}_dlg' title='Remove yourself from this Care Team'><img src='${appUrl}images/icon_deletelink.gif'/></a>";
	$smp = false;
	switch ($r->giverrole)
	{
		case '0': {$blurb = " invited to $appname at  $r->lastinvite "; break;}
		case '1': {$blurb = " invited <fb:name uid=$user useyou=true linked=false /> to join  at $r->lastinvite $invitelinks "; break;}
		case '2': {$blurb = "case 2 "; break;}
		case '3': {$blurb = "case 3 "; break;}
		case '4': {
			$smp= smallwallbr($r->fbid,3); 
			$blurb = "&nbsp;  $joinedlinks".
				"<a class='tinylink' href='ctviewas.php?xfbid=$r->fbid'>
				<img src='{$appUrl}images/magnifier.png' title='Switch to view this person\'s records'/></a> "; 
			break;
		}
		default: {$blurb = "case error  "; break;}
	}
	
	$smallwall = ''; 
	if(($smp === false) || (count($smp)==0)) {
		$smallwall = 
			"<div class='smallwallcontainer'>
				No messages on  <fb:name uid={$r->fbid} possessive='true' capitalize='true'/> care wall.
			</div>";
	}
	else
	foreach($smp as $u) {
		$smallwall .= 
			"<div class='smallwallcontainer'>
			<img src='${appUrl}images/speech.png'/>&nbsp;  <fb:name uid={$u[1]} capitalize='true'/> wrote ".
			"\"{$u[2]}\" - ".strftime('%D',$u[0]).
			"</div>";
	}
	$outstr = "<table><tr><td><div class=pic ><fb:profile-pic uid=$r->fbid></fb:profile-pic></div>
	<div class='txt' ><fb:name uid=$r->fbid></fb:name>$blurb</div> </td><td class='wall'>$smallwall</td></table>";

	// Each care recipient gets their own disconnect confirmation dialog
	// I'm sure there is a better way, but this seems to be the default
	// way to do it in fb examples
	$outstr .= "
		<fb:dialog id='remove_{$r->fbid}_dlg' cancel_button=1>
		  <fb:dialog-title>Remove Yourself from  <fb:name uid={$r->fbid} possessive='true' capitalize='true'/> Care Team</fb:dialog-title>	
		  <fb:dialog-content><form id='my_form'>Do you really want to remove yourself from this person's Care Team?</form></fb:dialog-content>
		  <fb:dialog-button type='button' value='Yes' href='ct.php?o=r&gid={$r->giverfbid}&id={$r->fbid}' />";

	return $outstr;
}
function  caregiving_mugshots($user,$facebook,$targetfbid,$mcid)
{
	$my = ($user==$targetfbid )?'My Care Giving':"<fb:name linked=false possessive='true'  uid=$targetfbid></fb:name>  Care Giving";
	$outstr =" <fb:explanation><fb:message>$my</fb:message>
	<div class=caregivee >"; $counter = 0;
	$q = "select * from  careteams c  left join fbtab f on c.fbid=f.fbid where c.giverfbid = '$targetfbid' and f.mcid!='0'";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		$mod = $counter -  floor($counter/1)*1; //change to run across
		if ($mod==0 && $counter!=0)$outstr.="</div><div class=caregivee >";
		$outstr.= blurb_asgiver($user,$u);
		$counter++;
	}
	mysql_free_result($result);
	if ($counter==0) return '';

	$outstr ="$outstr</div></fb:explanation>";
	//$outstr .=' <div class='mugshots'  > </div>   ';

	return $outstr;
}
function nocareteam ($user)
{
	$appname = $GLOBALS['healthbook_application_name'];
	//if ($user==0)
	//$markup = '';

		$markup = <<<XXX
	<fb:explanation>
<fb:message>You Have No Care Team <a class='tinylink' href='ct.php?o=h' >invite friends to your Care Team</a></fb:message>	
<p>If you keep your health records in <i>$appname</i>, it is easy to form a  Care Team - just invite your friends to MedCommons</p><p>Health records are always kept separately from Facebook on <a href="http://www.medcommons.net/" >secure MedCommons appliances running at Amazon</a>.</p>
    </fb:explanation>
XXX;

	return $markup;
}
function nocaregiving ($user)
{
	//if ($user==0)
	//$markup = ''; else
	$markup = <<<XXX
	<fb:explanation>
<fb:message>You  Are Not A Care Giver</fb:message>
<p>To become a Care Giver, a friend of yours must invite you to their Care Team. You can kick off the process by <a href=ct.php?o=i >inviting them to join HealthBook</a>.</p>
    </fb:explanation>
XXX;

	return $markup;
}

// end of careteams
?>
