<?php
// vim: tabstop=8 shiftwidth=8 noexpandtab

//require_once "topics.inc.php";

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

	}

	$targetmcid = $u->targetmcid;
	$mymcid = $u->mcid;
	$t= $u->getTargetUser();
	$familyfbid = $u->familyfbid;
	$appurl = $t? $t->appliance : "";
	$tm = $menuitem;
	//echo "home user $user mcid $mcid";
	if ($tm=='fcollaborate')  $tm = 'collaborate';
	if ($tm=='collaborate')  $dash = hurl_dashboard($user,'collaborate'); else
	if ($menuitem=='care team') $dash = dashboard($user,false);
	else
	if ($menuitem=='Public HealthURLs') $dash = hurl_dashboard($user,$menuitem);
	else
	$dash = home_dashboard($user,$tm,true,($mymcid==0) || ($targetmcid==$mymcid)); // PASS IN FLAG TO SHOW CARETEAM AND WALL
	//$dash = home_dashboard($targetfbid,$menuitem);

	switch ($menuitem)
	{
		case 'collaborate':{
			$skey_warning = "";
			//if(($u->fbid == $u->targetfbid)||($u->mcid==0))	{
			// if I'm Giving Care, Tell Everyone
			$title = "Friends and Family In Our Care "; $markup = elders_in_our_care($user,$facebook,$familyfbid,$mymcid);
			if ($markup!='')  { $dash = dashboard($user,false); // back to plain dashboard
			if //(($u->fbid == $u->targetfbid) &&
			(($u->mcid)
			&& (!$u->storage_account_claimed))  {
				ob_start();
				include "confirm_account_warning.php";
				$skey_warning = ob_get_contents();
				ob_end_clean();

			}
			$markup = $skey_warning.$markup;
			break;
			}
		}
		//}
		case 'fcollaborate' :  // fall in from above if not viewing self
			{
				$title="Collaboration";
				$wall =     care_wall($user, $facebook, $u->targetmcid,$u,$u->getTargetUser());
				$app = $GLOBALS['facebook_application_url'];
				$my = "{$t->getFirstName()} {$t->getLastName()}'s";
				$markup = <<<XXX
				$wall
				<div style='padding:5px;'><fb:board xid="$u->targetmcid"
				canpost="true"          candelete="false"          canmark="false"          cancreatetopic="true"          numtopics="2"          returnurl="$app">
				<fb:title>$my Discussion Board</fb:title>
</fb:board>
</div>
XXX;
				break;
			}

			/*case 'favorites': {
			 $title="Favorite Topics"; $markup = getFavoriteTopics($user,$facebook); break;
			 }
			 */
		case 'care team': { // show the care team of the sponsor of this mcid
			$s = HealthBookUser::load($familyfbid);
			$title="Family Care Team";
			$markup = family_careteam($user,$facebook,$familyfbid);
			if ($markup=='') $markup = nofamilycareteam($user); break;
		}

		case 'detailed care team': {
			$title="Care Team"; $markup = display_careteam($user,$facebook,$targetmcid,$u,$t);
			if ($markup=='') $markup = nocareteam($user); break;
		}
		case 'care giving': {
			//if ($targetfbid==0)
			//$targetfbid = $user; // show something
			$title = "Friends and Family In My Care"; $markup = elders_in_our_care($user,$facebook,$user,$mcid);
			if ($markup=='') $markup = nocaregiving(0); $dash = dashboard($user,false); // back to plain dashboard
			break;
		}
		case 'care wall': {$title = "Care Wall"; $markup = care_wall($user, $facebook, $u->targetmcid,$u,$u->getTargetUser());break;}

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


function build_wallpost_entry($user,$authorfbid,$time,$msg,$dbrecid)
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
	if ($authorfbid!=0&&$authorfbid!=11)
	{
		return <<<XXX
		<fb:wallpost linked=false uid="$authorfbid" t="$time" >
		$msg
		$remove
  		</fb:wallpost>
XXX;
	}
	else // these are system messages from medcommons
	return <<<XXX
	<fb:wallpost linked=false uid="1107682260" t="$time" >

	$msg
</fb:wallpost>
XXX;
}
function  care_wall ($user,$facebook,$mcid,$u,$t)
{ // return fbml wall

$appname = $GLOBALS['healthbook_application_name'];
//if ($targetfbid ==0) return '';
$my = "{$t->getFirstName()} {$t->getLastName()}'s";
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
$q = "select * from  carewalls where wallmcid  = '$mcid' order by time desc limit 5 ";
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
	if (isset($GLOBALS['facebook_userid'])) $appliance_userid = $GLOBALS['facebook_userid']; else $appliance_userid=6471872199;
	if ($any) $outstr.=build_wallpost_entry ($user,0,$firsttime,$up,$u->id);
	$outstr .= build_wallpost_entry($user, $u->authorfbid,$u->time,$u->msg,$u->id);

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
if ($any) $outstr.=build_wallpost_entry ($user,11,$s->beginTimeMs/1000,$up,0);
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

	$appUrl = $GLOBALS['app_url'];
	$remove1= <<<DIALOG

	<fb:dialog id="removemember_dialog{$r->giverfbid}" cancel_button=1>
	<fb:dialog-title>Remove A Friend from Your Care Team</fb:dialog-title>
	<fb:dialog-content><form >Do you really want to remove this member of your Care Team?</form></fb:dialog-content>
	<fb:dialog-button type="button" value="Yes" href="ct.php?o=r&id=$r->familyfbid&gid=$r->giverfbid" />
	</fb:dialog>
DIALOG;

	$remove2= <<<DIALOG
	<a class=tinylink href="#"  clicktoshowdialog="removemember_dialog{$r->giverfbid}" ><img src='http://fb01.medcommons.net/facebook/000/images/icon_deletelink.gif' alt=missingimg /></a>
DIALOG;

	$appname = $GLOBALS['healthbook_application_name'];
	$remlink ='';// $power?" $remove2 ":' ';

	switch ($r->giverrole)
	{
		case '0': {$blurb = " invited to $appname at  $r->lastinvite $remlink "; break;}
		case '1': {$blurb = " was invited to join at $r->lastinvite $remlink "; break;}
		case '2': {$blurb = " case 2 "; break;}
		case '3': {$blurb = " case 3 "; break;}
		case '4': {$blurb = "



		<div class='smallwallcontainer'>
		<img src='${appUrl}images/speech.png'/>&nbsp;  <fb:name uid={$r->giverfbid} capitalize='true' />	joined the team at $r->lastinvite
		</div>

		"; break;}
		default: {$blurb = " case error  "; break;}
}
$wall = '';
$walls = authorwallbr ($r->fbid,4)	;
foreach($walls as $u) {
	$wall .=
			"<div class='smallwallcontainer'>
	<img src='${appUrl}images/speech.png'/>&nbsp;to $u[1] wall:".
			"\"{$u[2]}\" - ".strftime('%D',$u[0]).
			"</div>";
	}
	$outstr = "<div class=caregivee><table><tr><td  >$remove1 <div class=pic ><fb:profile-pic uid={$r->giverfbid} /></div>
	<div class='txt' ><fb:name uid={$r->giverfbid} capitalize='true'/> $remlink</div> </td><td class='wall'>$blurb $wall</td></table></div>";



	return $outstr;
}

function blurb_asself ($user,$fbid)
{// only shown if no family members under care

$appUrl = $GLOBALS['app_url'];
if ($user==$fbid)
$smallwall =<<<XXX
<div class='smallwallcontainer'><img src='${appUrl}images/speech.png'/>&nbsp; You have no family members under care.</div>

<div class='smallwallcontainer'><img src='${appUrl}images/speech.png'/>&nbsp; Add members using the link above</div>
XXX;
else
$smallwall =<<<XXX
<div class='smallwallcontainer'><img src='${appUrl}images/speech.png'/>&nbsp; You are not the administrator of this family care team</div>

<div class='smallwallcontainer'><img src='${appUrl}images/speech.png'/>&nbsp; Contact <fb:name uid=$fbid /> to make changes to the family structure</div>
XXX;

$outstr = "<table><tr><td><div class=pic ><fb:profile-pic uid=$user></fb:profile-pic></div>
<div class='txt' ><fb:name useyou=false uid=$user></fb:name> &nbsp;&nbsp;</div> </td><td class='wall'>
$smallwall
</td></table>";
return $outstr;

}
function family_discon($fbid,$name)
{
	return "
	<a  class = familydiscon href='ct.php?o=b'>Leave $name</a>
";
}
function  family_careteam($user,$facebook,$familyfbid)
{
	$familyname = familyname($familyfbid);
	if ($familyname=='') return '';

	$discon = family_discon($familyfbid,$familyname);
	if ($user==$familyfbid)
	$addlink = "<a class='tinylink' href='ct.php?o=h' >add friends</a>";
	else $addlink='';


	$app = $GLOBALS['facebook_application_url'];

	$outstr ="  <fb:explanation>
	<fb:message>Facebook Friends Who Care For '$familyname' $discon
	<a class='tinylink' href='ct.php?o=n' >notify friends</a>&nbsp; $addlink </fb:message>
	"; $counter = 0;
	$q = "select * from  careteams c,  fbtab f  where  c.giverfbid = f.fbid and  f.fbid!=0 and f.familyfbid= '$familyfbid' ";

	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		$mod = $counter -  floor($counter/1)*1;
		if ($mod==0 && $counter!=0)$outstr.="<br/>";
		$outstr.= blurb_myteam($u,(
		// conditions for putting up the removelink
		(($user==$u->familyfbid) && ($u->giverfbid!=$user))  // case 1, i am the sponsor, but its not me

		||

		(($user!=$u->familyfbid)&&($u->giverfbid==$user)) // case 2, i am not the sponsor, it is me

		))


		;//($user==$targetfbid ));
		$counter++;
	}
	mysql_free_result($result);
	if ($counter==0) return '';
	else
	$outstr .='</fb:explanation>';

	return $outstr;
}


function blurb_asgiver ($user, $r,$candelete)
{
	$appname = $GLOBALS['healthbook_application_name'];
	$appUrl = $GLOBALS['app_url'];


	if  (!$candelete) $joinedlinks =''; else
	$joinedlinks ='';//]] "<a class='tinylink' href='#' clicktoshowdialog='remove_{$r->mcid}_dlg' title='Remove yourself from this Care Team'><img src='${appUrl}images/icon_deletelink.gif'/></a>";
	$smp = false;

	$smp= smallwallbr($r->mcid,3);
	$blurb = "&nbsp;  $joinedlinks";//.				"<a class='tinylink' href='ctviewas.php?xmcid=$r->mcid'>
	//<img src='{$appUrl}images/magnifier.png' title='Switch to view this person'/></a> ";


	$smallwall = '';
	if(($smp === false) || (count($smp)==0)) {

		$smallwall =
"<div class='smallwallcontainer'>
		No messages on  $r->firstname $r->lastname's care wall.
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


$outstr = "<table><tr><td  ><div class=pic ><img src=$r->photoUrl alt='missing photo' /></div>
<div class='txt' >$r->firstname $r->lastname $blurb</div> </td><td class='wall'>$smallwall</td></table>";


// Each care recipient gets their own disconnect confirmation dialog
// I'm sure there is a better way, but this seems to be the default
// way to do it in fb examples
$outstr .= "
<fb:dialog id='remove_{$r->mcid}_dlg' cancel_button=1>
<fb:dialog-title>Remove Yourself from  <fb:name uid={$r->familyfbid} possessive='true' capitalize='true'/> Care Team</fb:dialog-title>
<fb:dialog-content><form id='my_form'>Do you really want to remove yourself from this person's Care Team?</form></fb:dialog-content>
<fb:dialog-button type='button' value='Yes' href='ct.php?o=r&gid={$r->familyfbid}&id={$r->mcid}' />";

return $outstr;
}

function  elders_in_our_care($user,$facebook,$familyfbid,$mcid)
{
	$familyname = familyname($familyfbid);
	if ($familyname=='') return <<<XXX
	 <fb:explanation><fb:message>No Family Members are Under Your Care</fb:message>
	  <p>instructions go here
	</p>
	   </fb:explanation>
XXX;
	$discon = family_discon($user,$familyname);
	$my = "'$familyname' Members in Our Care";
	;
	$outstr = '';
	$outstr1 =<<<XXX
	<fb:explanation><fb:message>$my&nbsp;$discon</fb:message>
      <div class=caregivee >
XXX;
	$outstr2 =<<<XXX
	<fb:explanation><fb:message>$my&nbsp;$discon</fb:message>
	<div class=caregivee >
XXX;

	if ($mcid==0) return <<<XXX
	 <fb:explanation><fb:message>Please Setup A MedCommons Account or Wait for An Invite</fb:message>
	  <p>You need to setup a MedCommons Account via the 'settings' menu item before you can care for Family Members, Of you can await an invitation from a Facebook Friend.
	<P>You can go <a href='http://www.facebook.com/home.php' >http://www.facebook.com/home.php</a> to await the invite</p>
	</p>
	   </fb:explanation>
XXX;



	$counter = 0;
	$q = "select * from  mcaccounts f where f.familyfbid = '$familyfbid' ";
	$result = mysql_query($q) or die("cant  $q ".mysql_error());
	while($r=mysql_fetch_object($result))
	{
		$mod = $counter -  floor($counter/1)*1; //change to run across
		if ($mod==0 && $counter!=0)$outstr.="</div>
<div class=caregivee >";
		$outstr.= blurb_asgiver($user,$r,($user==$r->familyfbid));
		$counter++;
	}
	mysql_free_result($result);
	$outstr = (($familyfbid==$user)?$outstr1:$outstr2).$outstr.blurb_asself($user,$familyfbid);

	$outstr .= <<<XXX
	</div></fb:explanation>
XXX;
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
function nofamilycareteam ($user)
{
	$appname = $GLOBALS['healthbook_application_name'];
	//if ($user==0)
	//$markup = '';

	$markup = <<<XXX
	<fb:explanation>
	<fb:message>You Have No Family Care Team </fb:message>
	<p>You can either start your own Family Care Team, or wait for another family member to invite you.</p><p>Health records are always kept separately from Facebook on <a href="http://www.medcommons.net/" >secure MedCommons appliances running at Amazon</a>.</p>
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
function nocareteamorgiving ($user)
{
	$appname = $GLOBALS['healthbook_application_name'];
	//if ($user==0)
	//$markup = '';

	$markup = <<<XXX
	<fb:explanation>
	<fb:message>No Friends or Family are in Your Care </fb:message>
	<p>You are not keeping your own records <i>$appname</i>. <a class=tinylink href=settings.php>start keeping my records</a></p>
	<p>To become a Care Giver, a friend of yours must invite you to their Care Team. You can kick off the process by <a href=ct.php?o=i >inviting them to join HealthBook</a>.</p></fb:explanation>
XXX;

	return $markup;
}

// end of careteams
?>
