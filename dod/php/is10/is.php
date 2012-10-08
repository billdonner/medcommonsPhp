<?php

require_once 'is.inc.php';
require_once 'common.php';
require_once './OAuth.php';
function isheader($title,$priv){
	{
		$private =$priv?"<p>You should only be on this page if  you work for InformedSports.com</p>":"";
		//$league = getleague('Friends of Informed Sports');
		//$leagueimg = league_logo($league,'is');
		$page_header = file_get_contents("_oldheader.html");
		$page_header = str_replace('Generic Wrapper',$title,$page_header);
		$mimg = main_logo('is');
		$header = <<<XXX
	            <div id='is_header'>
		$page_header
	            $mimg  Informed Sports Admin
	            <a href='index.php?logout=ispage'>logout</a> 
	              <a href='showlog.php' target='_new' >show log</a> 
	            <a href='l.php?leagueind=1'>nba</a> 
	             <a href='l.php?leagueind=5'>frontier</a> 
	             <a href='l.php?leagueind=2'>biking</a> 
	            $private
		</div>
XXX;
		return $header;
}
}
function makeplayer ($healthurl, $ln,$fn,$dob,$sex,$img, $teamind, $status)
{
	dbg("making player with healthurl $healthurl");
	if (!$healthurl)
	{
		// if none supplied, then make one
		$remoteurl = $GLOBALS['appliance']."/router/NewPatient.action?familyName=$ln&givenName=$fn&dateOfBirth=$dob".
		"&sex=$sex&auth=".$GLOBALS['appliance_access_token']; // ssadedin 2/1/08: need to pass
		// consumer token when creating patient
		$file = file_get_contents($remoteurl);
		//parse the return looking for mcid
		//echo $file;
		$m = "{status:'ok',patientMedCommonsId:'"; $ml = strlen($m);
		$pos1 = strpos($file,$m);
		$pos2 = strpos ($file,"',",$pos1);
		if ($pos2<=$pos1) return false;
		$healthurl = $GLOBALS['appliance'].substr($file,$pos1+$ml,16);
		$m = "auth:'"; $ml = strlen($m);
		$pos1 = strpos($file,$m);
		$pos2 = strpos ($file,"',",$pos1);
		if ($pos2<=$pos1) return false;
		$auth = substr($file,$pos1+$ml,$pos2-$pos1-$ml);
		$m = "secret:'"; $ml = strlen($m);
		$pos1 = strpos($file,$m);
		$pos2 = strpos ($file,"'}",$pos1);
		if ($pos2<=$pos1) return false;
		$secret = substr($file,$pos1+$ml,$pos2-$pos1-$ml);
		dbg("created healthurl $healthurl auth $auth secret $secret");
	}
	else {
		$auth = "";
		$secret = "";
	}
	$team = teamnamefromind($teamind);
	// lets be careful and make sure we always make new records
	$sql = "Insert into players set name='$fn $ln', team='$team', imageurl='$img', oauthtoken='$auth,$secret', born='$dob', status='$status',healthurl='$healthurl' ";
	$status =mysql_query($sql );
	if ($status == false ) return false;

	$playerind = isdb_insert_id(); // get last
	dosql("Insert into teamplayers set teamind='$teamind', playerind='$playerind' ");
	return $playerind;
}

function teamsetupform($league,$team,$teamerr, $hp,$hperr,$sc,$scerr,$news,$newserr,$logo,$logoerr)
{
	$form =<<<XXX
<table>
<tr><td class=prompt>league</td><td>$league</td><td></td></tr>
<tr><td class=prompt>team name</td><td><input class=infield type=text name=team value='$team' /></td><td class=errfield>$teamerr</td></tr>
<tr><td class=prompt>home page url</td><td><input class=infield type=text name=homepageurl value='$hp' /></td><td class=errfield>$hperr</td></tr>
<tr><td class=prompt>schedule url</td><td><input class=infield type=text name=schedurl value='$sc' /></td><td class=errfield>$scerr</td></tr>
<tr><td class=prompt>rss news url</td><td><input class=infield type=text name=newsurl value='$news' /></td><td class=errfield>$newserr</td></tr>
<tr><td class=prompt>logo url</td><td><input class=infield type=text name=logourl value='$logo' /></td><td class=errfield>$logoerr</td></tr>

<tr><td></td><td></td><td></td></tr>
</table>
<input type=submit name=submit value=submit />
XXX;
	return $form;
}

function playersetupform($team,$fn,$fnerr,$gn,$gnerr,$dob,$doberr,$sex,$sexerr,$img,$imgerr,$hurl,$hurlerr,$oauth,$oautherr)
{
	//<tr><td class=prompt>oauth</td><td><input class=infield type=text name=oauth value='$oauth' /></td><td class=errfield>$oautherr</td></tr>
	$maleselected= ($sex=='M')?'selected':'';
	$femaleselected= ($sex=='F')?'selected':'';
	if ($doberr=='') $doberr="<small>e.g. 11/23/87</small>";
	if ($oautherr=='') $oautherr="<small>token,secret pair, leave blank to authorize after submission</small>";
	$form =<<<XXX
<input type='hidden' name='oauth' value='$oauth'/>
<fieldset>
<legend>Create New Player</legend>
<table>
<tr><td class=prompt>family name</td><td><input class=infield type=text name=familyName value='$fn' /></td><td class=errfield>$fnerr</td></tr>
<tr><td class=prompt>given name</td><td><input class=infield type=text name=givenName value='$gn' /></td><td class=errfield>$gnerr</td></tr>
<tr><td class=prompt>date of birth</td><td><input class=infield type=text name=dateOfBirth value='$dob' /></td><td class=errfield>$doberr</td></tr>
<tr><td class=prompt>image url</td><td><input class=infield type=text name=image value='$img' /></td><td class=errfield>$imgerr</td></tr>

<tr><td class=prompt>sex</td><td><select  class=infield name=sex>
<option value='M' $maleselected >male</option>
<option value='F' $femaleselected >female</option>
</td><td>$sexerr</td></tr>
<tr><td></td><td><input type=submit name=addplayerpost value='Create Player'/></td><td></td></tr>
</table>
</fieldset>
<p></p>

<fieldset>
<legend>Import Existing HealthURL</legend>
<table>
<tr><td class=prompt>HealthURL</td><td><input class=infield type=text name=hurl size='50' value='$hurl' onchange='document.isform.oauth.value=""' /></td>
    <td class=errfield>$hurlerr</td></tr>
<tr><td>&nbsp;</td><td><input type='submit' name='importplayer' value='Import Player'/></td><td></td></tr>
</table>
</fieldset>

XXX;
	return $form;
}

function trainersetupform($team,$email,$emailerr,$openid,$openiderr,$sms,$smserr)
{
	return usersetupform('team',$team,$email,$emailerr,$openid,$openiderr,$sms,$smserr);
}
function usersetupform($role,$team,$email,$emailerr,$openid,$openiderr,$sms,$smserr)
{

	$form =<<<XXX
<table>
<tr><td class=prompt>role</td><td>$role</td><td></td></tr>
<tr><td class=prompt>team</td><td>$team</td><td></td></tr>
<tr><td class=prompt>email</td><td><input class=infield type=text name=email value='$email' /></td><td class=errfield>$emailerr</td></tr>
<tr><td class=prompt>openid</td><td><input class=infield type=text name=openid value='$openid' /></td><td class=errfield>$openiderr</td></tr>
<tr><td class=prompt>sms</td><td><input class=infield type=text name=sms value='$sms' /></td><td class=errfield>$smserr</td></tr>
<tr><td></td><td></td><td></td></tr>
</table>
<input type=submit name=submit value='Setup Trainer' />
XXX;
	return $form;
}

function fullteamchooser($id)
{
	// returns a big select statement
	$outstr = "<select $id name='teamind'>";
	$result = dosql ("SELECT t.name,t.teamind,l.name from teams t, leagueteams lt, leagues l
	                                         where  lt.teamind = t.teamind and lt.leagueind=l.ind
	                                                               order by l.name, t.name");
	$first = true;
	while ($r2 = isdb_fetch_array($result))
	{
		$team = $r2[0]; $teamind = $r2[1]; $league = $r2[2];
		//$ename = urlencode($name);
		$selected = ($first)?' selected ':'';
		$outstr .="<option value='$teamind' $selected >$league:$team</option>";
		$first = false;
	}
	$outstr.="</select>";
	return $outstr;
}
$require_check=true;
// main starts here

if (!isset ($_COOKIE['u']))
{
	// only try this if not logged on
	if (isset($GLOBALS['openid_hack'])){
		$openid = $GLOBALS['openid_hack'];
		islog('loginhack',$openid,"bypassing openid login");
	}
	else {
		$tag ='';
		if (isset($_GET['openid_ns']))
		$tag.= ("openid_ns: ".$_GET['openid_ns']);
		if (isset($_GET['openid_mode']))
		$tag.= (" openid_mode: ".$_GET['openid_mode']);
		islog('loginopenid','--unclear--',$tag);
		if (isset($_GET['openid_ns']) || isset($_GET['openid_mode'])) {

			/*******************************
			* TTW 31-Jan-2008 Add OpenID...
			*/
			require_once 'common.php';

			session_start();

			$response = $consumer->complete(get_trust_root() . 'is.php');

			$tag.=" response-status: $response->status ";


			if (isset($_GET['openid_ns']) || isset($_GET['openid_mode']))
			islog('oidcomplete',$response->identity_url,$tag);

			if ($response->status != Auth_OpenID_SUCCESS) {
				if ($response->status == Auth_OpenID_CANCEL)
				$url = 'index.php?err=Verification+Cancelled';
				else if ($response->status == Auth_OpenID_FAILURE)
				$url = 'index.php?err=OpenID+Authentication+Failed';
				else
				$url = 'index.php?err=Unknown+OpenID+Error';

				header("$url");  // no need to go thru redire(), already well logged
				exit;
			}

			$openid = $response->identity_url; // this is occasionally null, bring to terry's attention and is thus not found below

			/* ... end of OpenID... use $openid instead of $email */

		}
		// at this point we will try to match the user regardless of whether we hacked this in or not
		if (isset($GLOBALS['openid_hack'])) $hack=$GLOBALS['openid_hack']; else $hack='--';
		islog('trylogin',$openid,$hack);
		$select="Select * from users u  where u.openid='$openid' ";
		$result=dosql($select);
		$r=isdb_fetch_object($result);
		if ($r===false)
		{
			islog('usernotfound',$openid,mysql_escape_string($select));
			$url ="index.php?err=OpenID+Not+Found+on+IS+" . urlencode($openid);
		}
		else
		{
			setcookie('u',urlencode($openid)); // setup a simple cookie to remember where we are, expire in 30 days
			// pick a starting point based on role

			islog('userfound',$openid, "role::$r->role");
			switch ($r->role)
			{
				case 'is': {$url = "";break;}
				case 'team': {$url = "t.php?teamind=$r->teamind";break;}
				case 'league':{$url = "l.php?leagueind=$r->leagueind";break;}
				default :{$url = "index.php?err=bad+role"; break;}
			}
		}
		if ($url!='') {
			redirect ($url);
			//header("Location: $url");
			//echo ("Redirecting to $url");

			exit;
		} else $require_check=false;
	}
}
if ($require_check) // this can only be true if we are coming in from above as role=is, the cookie will not yet be set
// all the functions below this line require login
check_login('is',"is page");// only returns if logged in


if (isset($_REQUEST['addtrainerpost']))
{
	$any=false;
	$teamind = $_REQUEST['teamind'];
	$role = $_REQUEST['role'];
	$openid = $_REQUEST['openid']; $fnerr='';
	$email = $_REQUEST['email']; $gnerr='';
	$sms = $_REQUEST['sms']; $smserr='';
	$team = teamnamefromind($teamind);
	// edit check all the fields
	if (substr($openid,0,7)!='http://')  {$fnerr = "real openid with http: must be specified"; $any=true;} else
	if (strpos($openid,"'")) {$fnerr = "no quotes allowed in openid"; $any=true;} else
	if (substr($openid,strlen($openid)-1,1)!='/') {$fnerr = "last char in openid must be /"; $any=true;}


	if (strlen($email)<10) {$gnerr = "real email must be specified";$any=true;}

	if ($any) {
		//addplayerpost
		$header = isheader('Error adding new trainer',true);
		$formbody = usersetupform($role,$team, $email,$gnerr,$openid,$fnerr,$sms,$smserr);
		$markup = <<<XXX
$header
		<div id='is_body'><h5>Please correct these errors to add a trainer (role: $role)</h5>
<form action="?" method=post>
<input type=hidden name=addtrainerpost value=addtrainerrpost />
<input type=hidden name=teamind value=$teamind />
<input type=hidden  name=role value=$role />
$formbody
</form>
</div>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
		echo $markup;
		exit;
	}

	$result =mysql_query("Insert into users set email='$email',sms='$sms', openid='$openid',teamind='$teamind',role='$role' ");
	if ($result == false) $loc = "is.php?err=duplicateUser"; else $loc ="is.php?err=completedok";
	header ("Location: $loc");
	echo "Redirecting to $loc";
	exit;
} else
if (isset($_REQUEST['deltrainerpost']))
{
	$name = $_REQUEST['name'];
	$teamind = $_REQUEST['teamind'];
	$team=teamnamefromind($teamind);
	dosql("DELETE from users where email='$name' and teamind='$teamind' and role='team' ");
	$header = isheader("Removed trainer $name ",true);
	$markup = <<<XXX
$header
	<div id='is_body'>
	<h5>Trainer $name was removed from  Team $team</h5>
</div>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
	echo $markup;
	exit;
} else
if (isset($_REQUEST['delplayerpost']))
{
	$name = $_REQUEST['name'];
	$team = $_REQUEST['team'];
	// remove links into here
	$playerind =get_playerind($name);
	$teamind = get_teamind($team);
	dosql("DELETE FROM teamplayers where playerind = '$playerind' and teamind = '$teamind'");
	$result = dosql("Select healthurl from players where playerind='$playerind' ");
	$r=isdb_fetch_object($result);
	$healthurl ="<a target='_new' href='$r->healthurl' title='this healthurl is in MedCommons and is always accessible to qualified users'>$r->healthurl</a>";
	dosql("DELETE from players where playerind='$playerind' and team='$team' ");

	$header = isheader("Removed player $name (was on $team)",true);
	$markup = <<<XXX
$header	
<div id='is_body'><h5>Player $name was removed from Informed Sports</h5>
<p>The associated healthurl $healthurl is still viable and can be utilized again if you choose to add the player at a later date</p>
</div>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
	echo $markup;
	exit;
} else

if (isset($_REQUEST['moveplayerpost']))
{
	$name = $_REQUEST['name'];
	$fromteam = $_REQUEST['fromteam'];
	$toteamind = $_REQUEST['teamind'];
	$toteam = teamnamefromind($toteamind);
	// remove links into here
	$playerind =get_playerind($name);
	$fromteamind = get_teamind($fromteam);
	dosql("Update  teamplayers set teamind='$toteamind'  where playerind = '$playerind' and teamind = '$fromteamind'");
	$result = dosql("Select healthurl from players where playerind='$playerind' ");
	$r=isdb_fetch_object($result);
	$healthurl ="<a target='_new' href='$r->healthurl' title='this healthurl is in MedCommons and is always accessible to qualified users'>$r->healthurl</a>";
	dosql("Update players set team='$toteam'  where playerind='$playerind' and team='$fromteam' ");
	$header = isheader("Moved player $name  from $fromteam to $toteam",true);
	$markup = <<<XXX
$header
		<div id='is_body'>
		<h5>Player $name was Moved from $fromteam to $toteam </h5>
<p>The associated healthurl $healthurl is still viable and associated with $name</p>
</div>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
	echo $markup;
	exit;
} else
if (isset($_REQUEST['importplayer']))
{
	try {
		$hurl = $_REQUEST['hurl']; $hurlerr='';
		$teamind = $_REQUEST['teamind'];
		$team = teamnamefromind($teamind);
		if($team === false)
		throw new Exception("Unable to determine team name for team $teamind");

		$callback = get_trust_root()."is.php?authorize_player";
		list($req_token,$url)= ApplianceApi::authorize($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$hurl,$callback);

		// set cookie with token and secret
		setcookie('oauth', $req_token->key.",".$req_token->secret.",".$hurl.",".$teamind, time()+300); // expire after 300 seconds


		// Add on team name as realm
		// TODO: what is the real realm???
		$url.="&realm=".urlencode($team);

		header("Location: $url");
		exit;
	}
	catch(Exception $e) {
		die(isheader('Error adding new player',true)."<p>An error occurred while attempting to authorize the HealthURL you entered.</p><pre>{$e->getMessage()}</pre>");
	}

	exit;

} else
if (isset($_REQUEST['addplayerpost']))
{
	$any=false;
	$teamind = $_REQUEST['teamind'];
	$fn = $_REQUEST['familyName']; $fnerr='';
	$gn = $_REQUEST['givenName']; $gnerr='';
	$dob = $_REQUEST['dateOfBirth']; $doberr='';

	$sex = $_REQUEST['sex']; $sexerr='';
	$img = $_REQUEST['image']; $imgerr='';
	$hurl = $_REQUEST['hurl']; $hurlerr='';
	$oauth = $_REQUEST['oauth']; $oautherr='';
	$team = teamnamefromind($teamind);
	// edit check all the fields
	if (strpos($fn,"'")) {$fnerr = "no quotes allowed in family name"; $any=true;}
	if (strpos($gn,"'")) {$gnerr = "no quotes allowed in given name";$any=true;}
	if ($hurl!='') if ($oauth=='') {$oautherr="Please authorize this HealthURL"; $any=true;}

	if ($any) {
		//addplayerpost
		$header = isheader('Error adding new player',true);
		$formbody = playersetupform($team, $fn,$fnerr,$gn,$gnerr,$dob,$doberr,$sex,$sexerr,$img,$imgerr,$hurl,$hurlerr,$oauth,$oautherr);
		$markup = <<<XXX
$header
			<div id='is_body'>
			<h5>please correct these errors to add a player</h5>
<form name='isform' action="?" method=post>
<input type=hidden name=teamind value=$teamind />
$formbody
</form>
</div>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
		echo $markup;
		exit;
	}
	// otherwise create the healthurl and then create the player in our tables
	//echo "making healthurl and then player $fn $gn";
	$playerind = makeplayer ($hurl, $fn,$gn,$dob,$sex,$img,$teamind,'test');
	if ($playerind == false) {
		dbg("dupe player");
		$loc = "is.php?addplayer=add&teamind=$teamind&err=".urlencode("Duplicate Player");
	}
	else  {  // success
		$loc ="p.php?playerind=$playerind";
	}

	dbg("redirecting to $loc");
	header ("Location: $loc");
	echo "Redirecting to $loc";
	exit;

} else
if (isset($_REQUEST['authorize_player'])) { // Appliance callback for successful authorization

	dbg("successful return from authorization call");

	if(!isset($_COOKIE['oauth']))
	die(isheader('Error adding new player',true)."<p>An error occurred while attempting to authorize the HealthURL you entered - missing cookie</p>");

	$oauth = explode(",",$_COOKIE['oauth']);
	$hurl = $oauth[2];
	$teamind = $oauth[3];

	dbg("access token from cookie ".$oauth[0]." / ".$oauth[1]);

	try {
		$api = ApplianceApi::confirm_authorization($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$oauth[0], $oauth[1],$hurl);

		$access_token = "{$api->access_token->key},{$api->access_token->secret}";

		dbg("access token: $access_token");

		list($base_url,$accid) = $api->parse_health_url($hurl);

		// Now we have the gateway, get the CCR
		$ccr = $api->get_ccr($accid);

		// Got the CCR
		// Get the important details of this patient
		// We have to iterate all the actors looking for the patient
		$patientActorID = $ccr->patient->actorID;
		foreach($ccr->actors->actor as $a) {
			if($a->actorObjectID == $patientActorID) {
				$given = $a->person->name->currentName->given;
				$family = $a->person->name->currentName->family;
				$dob = $a->person->dateOfBirth;

				if(isset($dob->exactDateTime)) {
					$age = (int)((time() - strtotime($dob->exactDateTime)) /  ( 365 * 24 * 60 * 60 ));
				}
				else
				if(isset($dob->age))
				$age = (int)$dob->age->value;

				if(isset($a->person->gender)) {
					$gender = $a->person->gender->text;
				}

				// Found patient, we're done
				break;
			}
		}

		$fmtDob = $dob->exactDateTime ? date("m/d/Y",strtotime($dob->exactDateTime)) : "";
		if($gender == "Female")
		$genderIndex = 1;
		else
		if($gender == "Male")
		$genderIndex = 0;
		else
		$genderIndex = -1;
	}
	catch(Exception $ex) {
		error_log("failed to initialize player from health url: ".$ex->getMessage());
		die(isheader('Error adding new player',true)."<p>An error occurred while attempting to access the HealthURL you entered.</p>");
	}

	// create the player in our tables
	//echo "making healthurl and then player $fn $gn";
	$playerind = makeplayer ($base_url.$accid, $family,$given,$fmtDob,"",null,$teamind,'test');
	if ($playerind == false) {
		dbg("dupe player");
		$loc = "is.php?addplayer=add&teamind=$teamind&err=".urlencode("Duplicate Player");
	}
	else  {  // success
		// Lazy but easier than refactoring the makeplayer code
		$result = dosql("update players set oauthtoken = '$access_token' where playerind = $playerind");
		if(!$result) {
			$loc = "is.php?addplayer=add&teamind=$teamind&err=".urlencode("Failed to set authentication token");
		}
		else
		$loc ="p.php?playerind=$playerind";
	}

	dbg("redirecting to $loc");
	header ("Location: $loc");
	echo "Redirecting to $loc";
	exit;
} else
if (isset($_REQUEST['delteampost']))
{

	$team = $_REQUEST['team'];
	// might leave inaccessible players

	$teamind = get_teamind($team);
	dosql("DELETE FROM teamplayers where  teamind = '$teamind'");
	dosql("DELETE FROM leagueteams where  teamind = '$teamind'");
	dosql("DELETE from teams where teamind='$teamind' ");

	$header = isheader("Removed team $team",true);
	$markup = <<<XXX
$header
<div id='is_body'>
<h5>Team $team  was removed from Informed Sports</h5>
<p>The associated healthurls of any players are  still viable and can be utilized again if you choose to add the player to another team at a later date</p>
</div>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
	echo $markup;
	exit;
} else

if (isset($_REQUEST['addteampost']))
{
	$any=false;
	$team = $_REQUEST['team'];$teamerr='';
	$league = $_REQUEST['league'];
	$leagueind= getleagueind($league);
	$hp = $_REQUEST['homepageurl']; $hperr='';
	$sc = $_REQUEST['schedurl']; $scerr='';
	$news = $_REQUEST['newsurl']; $newserr='';
	$logo = $_REQUEST['logourl']; $logoerr='';
	// edit check all the fields
	if (strpos($team,"'")) {$teamerr = "no quotes allowed in team name"; $any=true;}
	if ($any) {

		$header = isheader('Error adding new team',true);
		$formbody = teamsetupform($league,$team, $teamerr,$hp,$hperr,$sc,$scerr,$news,$newserr,$logo,$logoerr);
		$markup = <<<XXX
$header
			<div id='is_body'>
			<h5>please correct these errors to add a team</h5>
<form action="?" method=post>
<input type=hidden name=addteampost value=addteampost />
<input type=hidden name=team value=$team />
<input type=hidden name=league value=$league />
$formbody
</form>
<div id='is_footer'>
</div>
</div>
</body>
XXX;
		echo $markup;
		exit;
	}
	dosql("Insert into teams set name='$team',homepageurl='$hp',schedurl='$sc',newsurl='$news',logourl='$logo' ");
	$teamind = isdb_insert_id(); // get it
	dosql("Insert into leagueteams set teamind='$teamind', leagueind='$leagueind' ");

	// no errors, add new team and go to the new page
	$loc ="t.php?teamind=$teamind";
	header ("Location: $loc");
	echo "Redirecting to $loc";
	exit;
} else if (isset($_REQUEST['addplayer']))
{
	$footer=userpagefooter();
	$teamind = $_REQUEST['teamind'];
	$teamname=teamnamefromind($teamind);
	$formbody = playersetupform($teamname,'','','','','','','','','','','','','',''); // put up a blank form
	$header = isheader("Add Player to $teamname",true);
	$err = @$_REQUEST['err'];
	if($err) {
		$err="<p style='color: red;'>".htmlspecialchars($err)."</p>";
	}
	$markup = <<<XXX
$header
		<div id='is_body'>
<h5>Add a Player to $teamname</h5>
$err
<form name='isform' action=is.php method=post>
<input type=hidden name=addplayerpost value=addplayerpost />
<input type=hidden name=teamind value=$teamind />
$formbody
</form>
</div>
<div id='is_footer'>
$footer
</div>
</div>
</body>
XXX;
	echo $markup;
	exit;
}
else if (isset($_REQUEST['moveplayer']))
{
	$footer=userpagefooter();
	$teamind = $_REQUEST['teamind'];
	$teamname=teamnamefromind($teamind);
	$league = getLeague($teamname);
	$playerchooser =playerchooserquiet($teamname, ''); // get all players on team none is special

	$teamchooser =teamchooserindquiet($league->ind,'') ;// get all players on team none is special
	$header = isheader("Move Player from $teamname to another Team",true);
	$markup = <<<XXX
$header
		<div id='is_body'>
<h5>Move a Player from $teamname to Another Team</h5>
<form action=is.php method=post>
<input type=hidden name=moveplayerpost value=moveplayerpost />
<input type=hidden name=fromteam value=$teamname />
<p>Choose a player to move from this team and a team to move to. The player's healthurl will not be affected</p>
<table>
<tr><td class=prompt>Move Player</td><td class=infield>$playerchooser </td><td></td></tr>
<tr><td class=prompt>To Team</td><td class=infield>$teamchooser </td><td></td></tr>
</table>
<input type=submit name=submit value='Move Player' />
</form>
</div>
<div id='is_footer'>
$footer
	</div>
	</div>
</body>
XXX;
	echo $markup;
	exit;
}
else if (isset($_REQUEST['delplayer']))
{
	$footer=userpagefooter();
	$teamind = $_REQUEST['teamind'];
	$teamname=teamnamefromind($teamind);
	$playerchooser =playerchooserquiet($teamname, ''); // get all players on team none is special
	$header = isheader("Remove Player from $teamname",true);
	$markup = <<<XXX
$header
		<div id='is_body'>
<h5>Remove a Player from $teamname</h5>
<form action=is.php method=post>
<input type=hidden name=delplayerpost value=delplayerpost />
<input type=hidden name=team value=$teamname />
<p>Choose a player to remove from this team. The player's healthurl will not be affected</p>
<table>
<tr><td class=prompt>Remove Player</td><td class=infield>$playerchooser </td><td></td></tr>
</table>
<input type=submit name=submit value='Remove Player' />
</form>
</div>
<div id='is_footer'>
$footer
	</div>
	</div>
</body>
XXX;
	echo $markup;
	exit;
}
else if (isset($_REQUEST['addtrainer']))
{
	if (isset($_REQUEST['role'])) $role = $_REQUEST['role']; else $role='team';
	$footer=userpagefooter();
	$teamind = $_REQUEST['teamind'];
	$teamname=teamnamefromind($teamind);
	$formbody =trainersetupform($teamname, '','','','','','');
	$header = isheader("Add Trainer (Role: $role) to $teamname",true);
	$markup = <<<XXX
$header
		<div id='is_body'>
<h5>Add a Trainer to $teamname</h5>
<form action=is.php method=post>
<input type=hidden name=addtrainerpost value=addtrainerpost />
<input type=hidden name=teamind value=$teamind />
<input type=hidden name=role value=$role />
$formbody
</form>
</div>
<div id='is_footer'>
$footer
</div>
</body>
XXX;
	echo $markup;
	exit;
}
else if (isset($_REQUEST['deltrainer']))
{
	$footer=userpagefooter();
	$teamind = $_REQUEST['teamind'];
	$teamname=teamnamefromind($teamind);
	$trainerchooser =trainerchooser($teamname, ''); // get all players on team none is special
	$header = isheader("Remove Trainer from $teamname",true);
	$markup = <<<XXX
$header
		<div id='is_body'>
<h5>Remove Trainer from $teamname</h5>
<form action=is.php method=post>
<input type=hidden name=deltrainerpost value=deltrainerpost />
<input type=hidden name=teamind value=$teamind />
<p>Choose a trainer to remove from this team. </p>
<table>
<tr><td class=prompt>Remove Trainer</td><td class=infield>$trainerchooser </td><td></td></tr>
</table>
<input type=submit name=submit value='Remove Trainer' />
</form>
</div>
<div id='is_footer'>
$footer
	</div>
	</div>
</body>
XXX;
	echo $markup;
	exit;
}
else if (isset($_REQUEST['addteam']))
{
	$footer=userpagefooter();
	$league = $_REQUEST['league'];
	$leagueind = getleagueind($league);
	$formbody =teamsetupform($league,'','', '','','','','','','','');
	$header = isheader("Add Team to  $league",true);
	$markup = <<<XXX
$header
	<div id='is_body'>
<h5>Add a Team to $league</h5>
<form action=is.php method=post>
<input type=hidden name=addteampost value=addteampost />
<input type=hidden name=league value=$league />
$formbody
</form>
</div>
<div id='is_footer'>
$footer
	</div></div>
</body>
XXX;
	echo $markup;
	exit;
}
else if (isset($_REQUEST['delteam']))
{
	$footer=userpagefooter();
	$league = $_REQUEST['league'];
	$leagueind = getleagueind($league);
	$teamchooser=teamchooser($leagueind,'','');
	$header = isheader("Remove Team from $league",true);
	$markup = <<<XXX
$header
		<div id='is_body'>
<h5>Remove a Team from $league</h5>
<form action=is.php method=post>
<input type=hidden name=delteampost value=delteampost />
<p>Choose a team to remove from this league. The various player's healthurl will not be affected</p>
<table>
<tr><td class=prompt>Remove Team</td><td class=infield>$teamchooser </td><td></td></tr>
</table>
<input type=submit name=submit value='Remove Team' />
</form>
</div>
<div id='is_footer'>
$footer
	</div></div>

XXX;
	echo $markup;
	exit;
}
// is addministrationpage
list($lc,$tc,$pc,$hu,$tr,$lm,$us,$al) = getstats();
$userpagefooter = userpagefooter();
$teamchooser = allteamchooser('');
$fullteamchooser = fullteamchooser('');
$leaguechooser=leaguechooser();
if (Isset($_REQUEST['err'])) $err=$_REQUEST['err']; else $err='';
$header = isheader('Informed Sports Administration',true);
$appl = $GLOBALS['appliance'];
$server = $_SERVER['HTTP_HOST'];
$db = $GLOBALS['DB_Database'] ;
$markup = <<<XXX
$header
<div id='is_body'>
$err
<p>This service is supporting $lc leagues, $tc teams, $pc players, $tr trainers, and $lm league managers.</p>
<p>This service is run by $us Informed Sports employees. A total of $al alerts have been generated. </p>
<p>The actual hardware server is running on $server and the database is "$db" </p>
<p>We are currently creating new healthURLs on $appl; $hu have been created for these players.</p>
<br/>
<fieldset>
<legend>Customer Pages</legend>
<p>Please be aware you also have extra menu items on the league, team, and player pages because you work for Informed Sports.</p>
<table>
<tr><td class=prompt><span>Manage League </span></td><td class=infield>
<form method=post action='l.php'>
<input type=hidden name=chooseleague value=chooseleague />
$leaguechooser <input type=submit value=go name=go/>
</form>
</td><td>-- where league executive management and docs start</td></tr>
<tr><td class=prompt><span>Manage Team </span> </td><td class=infield>
<form method=post action='t.php'>
<input type=hidden name=chooseteam value=chooseteam />
$teamchooser <input type=submit value=go name=go  />
</form>
</td><td>-- where team trainers and team execs start</td></tr>
</table>
</fieldset>
<br/>
<fieldset>
<legend>Operate on Leagues</legend>

<p>You can add and remove league administrators for any league. Every administrator must have a valid OpenId to access the Informed Sports League, Team and Player screens. League administrators have read-only access to a player's healthURL on MedCommons</p>
<table>
<tr><td class=prompt><span>Add League Administrator  to</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=addleagueadmin value=add />
$leaguechooser <input type=submit value=go name=go  />
</form>
</td></tr>
<tr><td class=prompt><span>Remove League Administrator from</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=delleagueadmin value=del />
 $leaguechooser <input type=submit value=go name=go  />
</form>
</td></tr>
</table>
</fieldset>
<br/>
<fieldset>
<legend>Operate on Teams</legend>
<table>
<tr><td class=prompt><span>Add Team to</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=addteam value=add />
$leaguechooser <input type=submit value=go name=go  />
</form>
</td></tr>
<tr><td class=prompt><span>Remove Team  from</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=delteam value=del />
 $leaguechooser <input type=submit value=go name=go  />
</form>
</td></tr>
</table>
<p>You can add and remove trainers from any team. Every trainer must have a valid OpenId to access the Informed Sports Team and Player screens</p>
<table>
<tr><td class=prompt><span>Add Trainer to</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=addtrainer value=add />
$teamchooser <input type=submit value=go name=go  />
</form>
</td></tr>
<tr><td class=prompt><span>Remove Trainer from</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=deltrainer value=del />
 $fullteamchooser <input type=submit value=go name=go  />
</form>
</td></tr>
</table>
</fieldset>
<br/>
<fieldset>
<legend>Operate on Players</legend>
<p>Players do not have direct access to their records via Informed Sports. We encourage players and their Care Teams  to utilize Healthbook and Healthframe to access their own records</p>
<table>
<tr><td class=prompt><span>Add Player to</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=addplayer value=add />
$teamchooser <input type=submit value=go name=go  />
</form>
</td><td>also on team dropdown for is employees</td></tr>
<tr><td class=prompt><span>Remove Player from</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=delplayer value=del />
 $fullteamchooser <input type=submit value=go name=go  />
</form>
</td><td>also on player dropdown for is employees</td></tr>

<tr><td class=prompt><span>Move Player from</span> </td><td class=infield>
<form method=post action='is.php'>
<input type=hidden name=moveplayer value=del />
 $fullteamchooser <input type=submit value=go name=go  />
</form>
</td><td>also on player dropdown for is employees</td></tr>
</table>
</fieldset>
<br/>


<p>As a courtesy to our friends, we can add and care for anyone on the roster of the team  'Friends of Informed Sports'</p>
</div>
<div id='is_footer'>
$userpagefooter
</div>
</div>
</body>
XXX;
echo $markup;
?>
