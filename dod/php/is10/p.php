<?php
require_once "is.inc.php";
require_once "common.php";
require_once "./OAuth.php";
require_once "./JSON.php"; // php supports json natively but it is often not enabled

$MAX_RADIO_GROUP =0;

function set_consents($pid)
{
	function ur ($clause,$mode)
	{
		$o = array();
		$result = getallusers($clause);
		while ($r=isdb_fetch_object($result))
		{
			$o[]= array($r->openid,$mode);

		}
		return $o;
	}

	function wc ($search,$replace)
	{
		$o =array();
		$result = dosql("Select * from wildconsents");
		//echo "wc ".mysql_num_rows($result)."<br/>";
		while ($r=isdb_fetch_object($result))
		{	$openid = str_replace($search,$replace,$r->openid);
		$o[]=array($openid,$r->mode);
		//echo "adding $openid $r->mode";
		}
		return $o;
	}
	$r=getplayerbyind ($pid);
	if ($r->healthurl=='') {echo "There is no healthurl for $r->name";
	return false; // if no healthurl dont bother
	}
	$auth = explode(',',$r->oauthtoken);
	// Consumer - enter your application's token and secret here
	$consumer  = new OAuthConsumer($GLOBALS['appliance_access_token'], $GLOBALS['appliance_access_secret'], NULL);
	// Access Token - enter your the Access Token for the user you are calling here
	$acc_token = new OAuthToken($auth[0], $auth[1], 1);
	// walk thru the db structure and enumerate everyone who needs access
	$len = strlen($r->healthurl);
	$accid = substr($r->healthurl,$len-16,16);
	$appliance = substr($r->healthurl,0,$len-16);
	$teamname = $r->team;
	$r=getteambyname($r->team);
	$teamind = $r->teamind;
	$r = getleagueteambyteamind($teamind);
	$league = getLeague($teamname);
	$leagueind = $league->ind;
	$consents = array();
	// first all the users with role='is'
	$consents +=  ur (" role='is' ",'RW');
	// next all the league peole
	$consents += ur (" role='league' and leagueind='$leagueind' ",'R');
	// now the team people
	$consents += ur ("role='team' and teamind='$teamind' ",'RW');
	// now the new wild consents
	// was having trouble with += on wc, who knows?
	$results = dosql ("select * from wildidps");
	while ($idp = mysql_fetch_object($result)){
		$wc = wc (array('{idp}','{league}','{team}'), array ($idp->idp,$league->name, $teamname));
		for ($i=0; $i<count($wc); $i++) $consents[] = $wc[$i];
	}
	// ok, we have the consents in hand, make the remote oauth call
	// i am hoping we can turn this into one call
	//
	$json = new Services_JSON();

	foreach ($consents as $consent)
	{
		$openid = $consent[0]; $mode=$consent[1];

		$req = OAuthRequest::from_consumer_and_token($consumer,
		$acc_token, "GET", "{$appliance}api/set_consents.php",
		array("accid" => "$accid", "$openid" => "$mode"));

		$req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $acc_token);
		echo "<p>Fetching url ".$req->to_url()."</p>";
		$result = file_get_contents($req->to_url());
		echo "Raw result is ".$result;
		// Parse JSON
		$out = $json->decode($result);
		if($out->status == "ok") {
			echo "set_consents  succeeded";
		}
		else {
			echo "set_consents failed:  ".$out->message;
		}
	}
	$count = count($consents);
	echo "set_consents finished processing $count lines";
}
function show_consents($pid)
{
	function ur ($clause,$mode)
	{
		$o = '';
		$result = getallusers($clause);
		while ($r=isdb_fetch_object($result))
		{
			$o.="<span>email:$r->email openid:$r->openid role:$r->role access: $mode</span><br/>";
		}
		return $o;
	}
	function wc ($search,$replace)
	{
		$o = '';
		$result = dosql("Select * from wildconsents");
		while ($r=isdb_fetch_object($result))
		{	$openid = str_replace($search,$replace,$r->openid);
		$o.="<span>wildid:$openid access: $r->mode</span><br/>";
		}
		return $o;
	}
	// walk thru the db structure and enumerate everyone who needs access
	$ob='';
	$p=getplayerbyind($pid);
	$ob .=  "<h4>Consents for $p->name</h4><p>The individual identities will be removed as soon as the wildid features are enabled</p>";
	$r=getteambyname($p->team);

	$teamind = $r->teamind;
	$league = getLeague ($r->name);

	$leagueind = $league->ind;
	// first all the users with role='is'
	$ob .=  ur (" role='is' ",'RW');
	// next all the league peole
	$ob .= ur (" role='league' and leagueind='$leagueind' ",'R');
	// now the team people
	$ob .= ur ("role='team' and teamind='$teamind' ",'RW');
	// now display any wild consents
	$result= dosql ("select * from wildidps");
	while ($idp = mysql_fetch_object($result)){
		$ob .= wc (array('{idp}','{league}','{team}'), array ($idp->idp,$league->name, $r->name));
	}
	if ($p->healthurl!='')
	$ob.= "<p><a href='p.php?publish&playerind=$pid' title='refresh medcommons consents' >publish consents for $p->name to medcommons account</a></p>";
	return $ob;
}

function generate_db_insert($playerind, $parentind, $x,$formlib,$tablename,$vals)
{
	global $MAX_RADIO_GROUP;
	$out = ''; $values='';
	$count = count ($x);
	for ($i=0; $i<$count; $i++)
	{
		$region = $x[$i][0];
		$rest = $x[$i][1];
		$count2 = count($rest);
		for ($j=0; $j<$count2; $j++ )
		{
			$zone =  $rest[$j][0];
			$remains = $rest[$j][1];
			$count3 = count ($remains);

			// insert 'notes' values
			$name = makevarname($region.'-'.$zone.'-notes');
			if (isset($vals["f_$name"]))
			$value = $vals["f_$name"];
			else $value='';

			$out.="$name,
					"; 
			$values .=" '$value',
					"	;



			for ($k=0;$k<$count3; $k++)
			{
				$cond = $remains[$k][0];
				$code = $remains [$k][1];
				if (isset($vals["f_$code"]))
				$value = $vals["f_$code"];
				else $value='';
				if ($cond===false)
				{

					$name = makevarname($region.'-'.$zone);
					$out.="$name,
					"; 
					$values .=" '$value',
					"	;			
				}
				else
				{
					if ($value=='on') $value=1; else $value=0;
					$name = makevarname($region.'-'.$zone.'-'.$cond);
					$out.="$name,
					"; 	
					$values .=" '$value',
					";					
				}
			}
		}
	}
	$time = time();
	$openid = $_COOKIE['u'];
	$out ="Insert into  $tablename (  playerind,  parentind, useropenid,
		 $out
	`time` ) values ('$playerind', '$parentind', '$openid', $values '$time');
	";
	return $out;
}

function trainer_alert_form($team,$player)
{
	$playerind= get_playerind($player);
	$teamind = get_teamind($team);
	$iform = <<<XXX
   <div id='ajaxarea'>	
<i>use this form to put a note on $player's alert list</i>
<form action='p.php' method='post'>\r\n
<input type=hidden name='teamind' value='$teamind'>\r\n
<input type=hidden name='playerind' value='$playerind'>\r\n
Alert Type: <select name='atype'>
<option value='0'>General</option>
<option value='1'>Medical</option>
<option value='2'>Head</option>
<option value='3'>Cervical</option>
<option value='4'>Upper Extremity</option>
<option value='5'>Torso</option>
<option value='6'>Lower Extremity</option>
</select>&nbsp;&nbsp;
Alert Priority: <select name='aprio'>
<option value='0'>normal</option>
<option value='1'>high</option>
<option value='2'>critical</option>
</select><br/>
Enter optional alert text:<br/>
<textarea rows=5 cols=35 name='atext'></textarea><br/>\r\n
<input type=submit value='Add Alert' />&nbsp;&nbsp;

</div>
XXX;
	return $iform;
}
function reform($r,$x)
{
	global $MAX_RADIO_GROUP;
	$vals = array();

	$count = count ($x);
	for ($i=0; $i<$count; $i++)
	{
		$region = $x[$i][0];
		$rest = $x[$i][1];
		$count2 = count($rest);
		for ($j=0; $j<$count2; $j++ )
		{
			$zone =  $rest[$j][0];
			$remains = $rest[$j][1];
			$count3 = count ($remains);
			// insert 'notes' values
			$name = makevarname($region.'-'.$zone.'-notes');
			$vals["f_$name"] = $r->$name;

			for ($k=0;$k<$count3; $k++)
			{
				$cond = $remains[$k][0];
				$code = $remains[$k][1];

				if ($cond===false)

				$name = makevarname($region.'-'.$zone);

				else

				$name = makevarname($region.'-'.$zone.'-'.$cond);

				$vals["f_$code"] = $r->$name;
			}
		}
	}

	return $vals;
}

function rsummary($r,$x)
{
	$vals = array();
	$summ ='';
	$count = count ($x);
	for ($i=0; $i<$count; $i++)
	{
		$region = $x[$i][0];
		$rest = $x[$i][1];
		$count2 = count($rest);
		for ($j=0; $j<$count2; $j++ )
		{
			$zone =  $rest[$j][0];
			$remains = $rest[$j][1];
			$count3 = count ($remains);
			// insert 'notes' values
			//$name = makevarname($region.'-'.$zone.'-notes');
			// $vals["f_$name"] = $r->$name;
			for ($k=0;$k<$count3; $k++)
			{
				$cond = $remains[$k][0];
				$code = $remains[$k][1];

				if ($cond===false)
				{
					$name = makevarname($region.'-'.$zone);
					$var = $r->$name;
					if ($var)
					$summ .= "$var; ";
				}
				else
				{
					$name = makevarname($region.'-'.$zone.'-'.$cond);
					$var = $r->$name;
					if ($var)
					$summ.= "$zone:$cond; ";
				}

			}
		}
	}
	return $summ;
}

function render_form($flavor,$submitvalue, $out, $playerind,$parentind, $x,$formlib,$tablename,$next,$vals)
{
	global $MAX_RADIO_GROUP;

	if ($submitvalue=="Submit New Case") $hid = -1; else $hid=-2;
	$out .=<<<XXX
	<form action=p.php method=post>
	<input type=hidden value='$parentind' name=repost />
	<input type=hidden value='$formlib' name=formlib />
	<input type=hidden value='$tablename'  name=table />
	<input type=hidden value='$playerind'  name=playerind />
	<input type=hidden value='$next'  name=next />
	<input type=hidden value='$hid'  name=alerttype />
	<input type=hidden value='$flavor'  name=report />
XXX;
	$count = count ($x);
	$radiocounter = 0;
	for ($i=0; $i<$count; $i++)
	{
		$regn = $x[$i][0];
		$region = str_replace(array(' ','	'),array('_','__'),$regn);//javascript doesnt like spaces
		$rest = $x[$i][1];
		$count2 = count($rest);

		// pass 1
		$counter = 0;
		for ($j=0; $j<$count2; $j++ )
		{
			$zone =  $rest[$j][0];
			$remains = $rest[$j][1];
			$count3 = count ($remains);

			for ($k=0;$k<$count3; $k++)
			{
				$cond = $remains[$k][0];
				$code = $remains [$k][1];
				if (isset($vals["f_$code"]))
				$value = $vals["f_$code"];
				else $value='';
				if ($value=='1') $counter++;
			}
		}


		$region_content = $region."_content";
		if ($counter!=0)$oustr="$regn($counter)"; else $oustr=$regn;
		$out .=<<<XXX
		<div id='$region' class='amenu' onclick='toggle("$region")'>$oustr</div>
		<div id='$region_content' class='acontent'>	
XXX;
		// pass 2,
		for ($j=0; $j<$count2; $j++ )
		{
			$zone =  $rest[$j][0];
			$remains = $rest[$j][1];
			$count3 = count ($remains);
			if ($j==0) $showregion=$region; else $showregion='';
			$name = makevarname($region.'-'.$zone.'-notes');

			if (isset($vals["$name"])) $value = $vals["$name"]; else $value = '';
				
				$name = "<span style='font-size:.8em; color: blue;'>notes:".
				"<span class='in_form_var'   title='informed sports code #10101' >
				<input type=text name=f_$name value='$value' />".
				"</span></span>";
			
			$out .="<div class='in_form_row'>
			  <div class='subheader'>$zone $name</div>
			<div class='formitems'>";
			//if ($count3==0) echo"<br/>2: $region zone: $zone"; else
			// insert 'notes' values Background_Info_short_case_description_notes

			for ($k=0;$k<$count3; $k++)
			{
				$cond = $remains[$k][0];
				$code = $remains [$k][1];

				$out.="<span class='in_form_var'   title='informed sports code #$code' >
				";
				if (isset($vals["f_$code"]))
				$value = $vals["f_$code"];
				else $value='';
				if ($cond===false)
				{
					$out.="<input type=text name=f_$code value='$value' />
				 "; 					
				}
				else {
					if ($value=='1') $checked = "checked=checked " ; else $checked = '';
					if ($i<$MAX_RADIO_GROUP) {
						$out .="<input type=radio $checked name=f_r_$i value='f_$code'  /> ";

					}

					else

					$out.= "<input type=checkbox $checked name=f_$code /> ";
				}
				$out .= "$cond</span>&nbsp;
				";
			}
			$out .="</div>
			</div>
			";

		}
		$out .="</div>
		
		";
	}

	$out .="<input type=submit name=submit value='$submitvalue' />
	 </form></div>";
	return $out;
}
function player_report_chooser($my_role,$team,$player,$tis)
{
	$league = getLeague($team);
	$plugins = getAllPluginInfo($league->ind);
	$playerind = get_playerind($player);
	//$eplayer=urlencode($player);
	if ($my_role=='is'){
		$selm1 = ($tis==-1) ? " selected=selected ":"";
		$selm2 = ($tis==-2) ? " selected=selected ":"";
		$selm3 = ($tis==-3) ? " selected=selected ":"";
		$selm4 = ($tis==-4) ? " selected=selected ":"";

		$mstuff = <<<XXX
<option value = '-1' $selm1 >Poke HealthURL Params</option>
<option value = '-2' $selm2 >Create New HealthURL</option>
<option value = '-3' $selm3 >Remove Player from Team</option>
<option value = '-4' $selm4 >Move Player to Another Team</option>
XXX;
}
else $mstuff='';

$sel0 = ($tis==-10) ? " selected=selected ":"";
$sel1 = ($tis==-11) ? " selected=selected ":"";
$sel2 = ($tis==-12) ? " selected=selected ":"";
$sel3 = ($tis==-13) ? " selected=selected ":"";

// positive values

$istuff='';
while ($r=mysql_fetch_object($plugins))
{
	$up = 3*($r->ind-1); // turn plugin index into select
	$summ = $up++; $form=$up++; $query = $up;
	$istuff .=<<<XXX
 	
	<option value='$summ' >$r->label Summary</option>
 	<option value='$form' >$r->label Form</option>
 	<option value='$query' >$r->label Query Template</option>
 	
XXX;
}

$x = <<<XXX
<select id='actionselect' name='report' title='pick a report or function about $player'  onchange="location = 'p.php?playerind=$playerind&report='+this.options[this.selectedIndex].value;">
<option value='-99' >-choose a report or function-</option>
$mstuff
<option value = '-10' $sel0 >Pending Alerts</option>
<option value = '-11' $sel1 >Important Alerts</option>
<option value = '-12' $sel2 >Critical Alerts</option>
<option value = '-13' $sel3 >Send Alert to Trainers</option>
$istuff
</select>
XXX;
return $x;
}
function player_report_section ($my_role,$team, $player, $s,$flavor,$ggg,$formlib,$table,$next)
{
	//echo "midselction flavor $flavor ggg $ggg";
	$teamind = get_teamind($team);
	$playerind = get_playerind($player);
	if ($flavor >= 0)
	{
		// pertains to a plugin
		$plugid = intval($flavor/3);
		$action = $flavor - 3*$plugid;
		//echo "Flavor $flavor plugid $plugid action $action ";
		$league = getLeague($team);
		$plugin = getPluginInfo($plugid+1);
		$r = mysql_fetch_object($plugin);
		if ($r==false) $bo="No plugid $plugid team $team" ; else
		switch ($action)

		{
			case '0': {
				$bo=injurylistplayer("$r->label $player",$playerind,$r);
				break;
			}
			case '1': {
				$out = <<<XXX
	<p>This is a completely blank form  which will create a new case when you press the submit button.</p>
	<div style='display: block;' id='___$r->url' >
XXX;
				$iform = javascriptstuff().
				render_form($flavor,'Submit New Case',$out,$playerind,'0',$ggg,$r->url,$r->table,$next,array());
				$bo="
	<div id='ajaxarea'><h4 title='blank form:$r->url table:$r->table'>New $r->label Case</h4>".$iform."</div>"; break;

			}
			case '2': {

				$out = <<<XXX
	<p>This is a search form  which will generate a query request when you press the submit button.</p>
	<div style='display: block;' id='___$r->url' >
XXX;
				$iform = javascriptstuff().
				render_form($flavor,'Make Query Template',$out,$playerind,'0',$ggg,$r->url,$r->table,$next,array());
				$bo="
	<div id='ajaxarea'><h4 title='datawarehouse form:$r->url table:$r->table'>New $r->label Query Template</h4>".$iform."</div>"; break;

			}
			default : {die ("bad case action $action");}
		}

		return $bo;

	}
	else
	switch ($flavor)
	{
		//	<input type=text name=oauthtoken value='' />oauthtoken<br/>
		case '-99': { header("Location:p.php?playerind=$playerind"); exit;}
		case '-1': { if ($my_role=='is')$bo=<<<XXX
		<div id='ajaxarea'><h4>Poke HealthURL Form for $player</h4>
		<p>This is restricted to Informed Sports employees. If you want to patch an existing healthurl into an existing player, this is where you want to be :=)</p>
		<form action=p.php method="post">
		<input type=hidden name=playerind value='$playerind' />
    <table>
    <tr><th>HealthURL</th><td><input type=text name=healthURL size='30' value='' /></td></tr>
    <tr><td>&nbsp;</td><td><input type=submit name='updatehurl' value='Update HealthURL' /></td></tr>
    </table>
		</form></div>		
XXX;

		else $bo="Invalid role $my_role"; break;}

		case '-2': { if ($my_role=='is')$bo=<<<XXX
		<div id='ajaxarea'><h4>Create New HealthURL for $player</h4> 
			<p>This will overwrite the player's healthURL with a new one. The healthURL is still available from MedCommons</p>
		<form action=p.php method="post">
		<input type=hidden name=createhealthurl value=createhealthurl />
		<input type=hidden name=playerind value='$playerind' />
		<input type=submit name=submit value='Create New HealthURL' />
		</form>
		</div>
XXX;

		else $bo="Invalid role $my_role";break;}

		case '-4':{ if ($my_role=='is'){
			$teamchooser  = allteamchooser('');
			$bo=<<<XXX
<div id='ajaxarea'><h4>Move Player $player to Another Team</h4> 
		<p The player's healthURL is still available from MedCommons</p>
		<form action=is.php method="post">
		<input type=hidden name=moveplayerpost value=moveplayerpost />
		<input type=hidden name=name value='$player' />
		<input type=hidden name=fromteam value='$team' />
		$teamchooser
		<input type=submit name=submit value='Confirm Movement of $player' />
		</form>
		</div>
XXX;
}
else $bo="Invalid role $my_role";break;}
case '-3':{ if ($my_role=='is')$bo=<<<XXX
	<div id='ajaxarea'><h4>Remove Player $player from Informed Sports</h4> 
		<p The player's healthURL is still available from MedCommons</p>
		<form action=is.php method="post">
		<input type=hidden name=delplayerpost value=delplayerpost />
		<input type=hidden name=name value='$player' />
		<input type=hidden name=team value='$team' />
		<input type=submit name=submit value='Confirm Deletion of $player' />
		</form>
		</div>
XXX;
else $bo="<div id='ajaxarea'><h4>Invalid role $my_role</h4></div>";break;}

case '-10': {
	$bo=alertlist("Pending Alerts for $player", $team, $player, fetch_alerts(0,$playerind,$teamind),$s,$flavor); break;
}
case '-11': {
	$bo=alertlist("Important Alerts for $player",$team,$player,fetch_alerts(1,$playerind,$teamind),$s,$flavor); break;
}
case '-12': {
	$bo=alertlist("Critical Alerts for $player", $team,$player,fetch_alerts(2,$playerind,$teamind),$s,$flavor); break;
}
case '-13': {   $bo=trainer_alert_form($team,$player);  break;
}

default: {   $bo="Case default $flavor not implemented"; break;
}
	}
	// now pretty it up a bit
	return $bo; //return "<br/><fieldset>$bo</fieldset>";
}

function render_player($iform,$title,$playerind,$flavor,$my_role,$ggg,$formlib,$table,$next)
{
	$r = getplayerbyind($playerind);
	if ($r===false) return "couldnt find player $playerind";
	$player = $r->name;
	$team = $r->team;
	$league = getLeague($team);
	$reportchooser = player_report_chooser($my_role,$team,$player,$flavor);
	$page_header = file_get_contents("_header.html");
	$teamind = get_teamind($team);
	$league = getLeague($team);
	$leagueimg = league_logo($league, $my_role);
	//	$teamchooser = teamchooser($league->ind,$team);
	$playerchooser = playerchooserind($team,$player);
	$f = getplayerbyname($player,$team);
	if ($f->imageurl!='')
	$imgurl = $f->imageurl;	else $imgurl= $GLOBALS['missing_image'];
	$healthurl = $f->healthurl;
	$teamlink = "<span>Team: <a href='t.php?teamind=$teamind' title='visit $team team page on Informed Sports'>$team</a></span>";
	if ($healthurl!='') {
		// Sign the health url so that the appliance will accept it
		// without challenge
		$health_url_parts = ApplianceApi::parse_health_url($healthurl);
		$api = get_appliance_api($health_url_parts[0], $f->oauthtoken);
		$healthurl = $api->sign($healthurl);
		$hurl = "<a href='$healthurl' target='_new' title='view $player records on MedCommons Appliance'><img border='0' src='images/external.png' />HealthURL</a>";
	}
	else
	$hurl = "no healthURL ";
	$consenturl = "<a href='p.php?consents&playerind=$f->playerind' target='_new' title='who can access records of $player?' ><img border='0' src='images/external.png' />ACL</a>";
	if ( $f->homepageurl!='')
	$hpurl= "<a href='$f->homepageurl' target='_new' title='view homepage for $player'><img border='0' src='images/external.png' />Home Page</a>"; else $hpurl='';
	if ($f->born!='')$born="DOB: $f->born "; else $born='';

	$playerstuff = "<div id='playerimg'><img title='$player is on $league->name team $team' src='$imgurl' alt='missing player image for $player' border='0'></div>
	<div id='playernotes'>
	$teamlink<br/>
	$born 
	</div>
	 <div id='playerlinks'>
	$hurl<br/>$consenturl<br/>$hpurl
	</div>";
	$mimg = main_logo($my_role);
	$playerheader = "
	$mimg
	$leagueimg
	$playerchooser 	
	";
	$teamfooter = teamfooter($team);
	$teamind = get_teamind($team);
	if ($iform=='')
	$prs = player_report_section($my_role,$team,$player,"_alerts_$player",$flavor,$ggg,$formlib,$table,$next); else $prs='';
	$markup = <<<XXX
$page_header	
	<body >
  <div id='content'>
<div id='is_header'>
$playerheader
</div>
<div id='is_body'>
 <div id='is_a_section'>
 $playerstuff
 </div>
<div id='is_b_section'>
$reportchooser
$iform
$prs
</div>
<div style='clear:both'> </div>
</div>
<div id='is_footer'>
$teamfooter
</div>
</div>
</body>
</html>
XXX;
	return $markup;
}

function playerpage($team,$player,$flavor,$my_role,$ggg,$formlib,$table,$next)
{
	$playerind = get_playerind($player);
	return render_player('',"$my_role: player view",$playerind,$flavor,$my_role,$ggg,$formlib,$table,$next);
}
function caseheader($plugid,$ind,$playerind,$editview,$morelinks)
{
	$plugin = getPluginInfo($plugid);
	$rr=mysql_fetch_object($plugin);
	$player=getplayerbyind($playerind);
	// probably need a desc in here
	$r = get_appl_record($rr->table,'=',true,$ind);
	if ($r===false) die ("Cant find that player/injury combination");
	$r1 = get_appl_record($rr->table,'>',true,$ind);
	if ($r1===false) $nextlnk = ''; else $nextlnk = "<a class='is_next_link' title='next case of $player->name' href='p.php?edit=$r1->ind&plugid=$plugid&playerind=$playerind' >next</a> ";
	$r2 = get_appl_record($rr->table,'<',false,$ind);
	if ($r2===false) $prevlnk = ''; else $prevlnk = "<a class='is_next_link'  title='previous case of $player->name' href='p.php?edit=$r2->ind&plugid=$plugid&playerind=$playerind' >previous</a> ";

	$time = strftime('%T %D',$r->time);
	$blurb = blurb ($r,'unspecified case');
	$id = urldecode($r->useropenid);
	if ($r->parentind!=0)
	{
		$r3 = get_appl_record($rr->table,'=',true,$r->parentind,$playerind);
		if ($r3===false) $derivedfrom = 'parentind messed up';
		else {
			$blurb3 = blurb($r3,'unspecified injury');
			$parentlnk = "<a class='is_parent_link'
			title='this case was edited to produce the case you are now viewing' 
			href='p.php?edit=$r3->ind&plugid=$plugid&playerind=$playerind' >'$blurb3'</a> ";
			$id3 = urldecode($r3->useropenid);
			$derivedfrom = "derived from $parentlnk by $id3";
		}
	}
	else $derivedfrom = '';
	$iform= "
	<h4>$rr->label Case</h4>
	<div class='show_case'>$prevlnk&nbsp;$nextlnk&nbsp$morelinks<br/>
		               <br/><span class='blurb'>'$blurb'</span>
		</div>
	";
	return $iform;
}
$my_role=check_login('team,league,is','player page'); // only returns if logged in
//print_r($_REQUEST);
$next = '';
if (isset($_REQUEST['next'] )) $next = $_REQUEST['next'];

if (isset($_REQUEST['atype']))
{
	// this code is for integrating alerts, we just  go bak to whatever next says
	$teamind = $_REQUEST['teamind'];
	$playerind = $_REQUEST['playerind'];
	$atype = $_REQUEST['atype'];
	$aprio = $_REQUEST['aprio'];
	$atext = mysql_escape_string($_REQUEST['atext']);

	$useropenid=$_COOKIE['u'];//record who has done this
	if (!isset($_REQUEST['cancel']))
	{

		dosql ("insert into alerts (plugid,relatedind,playerind,teamind,useropenid,type,priority,text,time) value (0,0,'$playerind', '$teamind','$useropenid','$atype','$aprio','$atext',now())");
	}
	if ($next=='') $next="p.php";
	redirect("$next?playerind=$playerind");
	exit;
}
else
{// new stuff from iform, must integrate
	if (isset($_REQUEST['report'])) $report = $_REQUEST['report']; else $report='0';

	$playerind = $_REQUEST['playerind'];
	$result = dosql("Select * from players where playerind='$playerind' ");
	$r=isdb_fetch_object($result);
	$team = $r->team;
	$league = getLeague($team);
	if (isset($_REQUEST['publish']))
	{
		set_consents($playerind);
		exit;
	}
	else
	if (isset($_REQUEST['consents']))
	{
		echo show_consents($playerind);

		exit;
	}
	else
	if(isset($_REQUEST['updatehurl']))
	{
		dbg("updating hurl");
		$playerind = $_REQUEST['playerind'];
		$hurl = $_REQUEST['healthURL'];
		$callback = get_trust_root()."p.php?authorize_player=true&playerind=$playerind";
		// send the user over for authorization
		list($req_token,$url)= ApplianceApi::authorize($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$hurl,$callback);
		// set cookie with token and secret
		setcookie('oauth', $req_token->key.",".$req_token->secret.",".$hurl.",".$playerind, time()+300); // expire after 300 seconds
		redirect("$url");
		exit;
	}
	else
	if(isset($_REQUEST['authorize_player'])) {
		if(!isset($_COOKIE['oauth']))
		die('<html><body><h3>Error updating HealthURL</h3><p>An error occurred while attempting to update the HealthURL you entered - missing cookie</p></body></html>');
		$oauth = explode(",",$_COOKIE['oauth']);
		$hurl = $oauth[2];
		$playerind = $oauth[3];
		$api = ApplianceApi::confirm_authorization($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$oauth[0], $oauth[1],$hurl);
		dbg("got access token ".$api->access_token->key." / ". $api->access_token->secret);
		$result = dosql("update players set healthurl='".mysql_real_escape_string($hurl)."', oauthtoken='{$api->access_token->key},{$api->access_token->secret}' where playerind=$playerind");
		if(!$result) {
			error_log("Error upating player $playerind with healthurl $hurl ".mysql_error());
			die('<html><body><h3>Error updating HealthURL</h3><p>An error occurred while attempting to update the HealthURL you entered - unable to update player details</p></body></html>');
		}
		redirect("p.php?playerind=$playerind");
		exit;
	}
	else


	if (isset($_REQUEST['repost']))
	{
		$plugid = plugidFromReport($report);
		$plugin = getPluginInfo($plugid);
		$rr=mysql_fetch_object($plugin); // or crash
		$flavor = $report;
		if ($rr==false )
		{
			$ggg= false; $url=false; $table='notable';
		} else {
			$url = $rr->url; $table = $rr->table;
			require_once $url;
			$ggg = schema(); // must be inside $formlib
		}
		$parentind = $_REQUEST['repost'];
		$atype = $_REQUEST['alerttype'];
		for ($i=0; $i<$MAX_RADIO_GROUP; $i++)
		{
			if (isset($_REQUEST["f_r_$i"]))
			{
				$fin = $_REQUEST["f_r_$i"];
				$_REQUEST[$fin] = 'on'; //
			}
		}

		dosql(generate_db_insert($playerind,$parentind,$ggg,$url,$table,$_REQUEST)); // generate an insert using posted values
		$ind = isdb_insert_id();
		$result = dosql("Select * from players where playerind='$playerind' ");
		$r=isdb_fetch_object($result);
		$teamind = get_teamind($r->team); // find theteam
		$result = dosql("Select * from $table where ind='$ind' "); // reread the record we just inserted
		$r=isdb_fetch_object($result);

		$aprio=1; //signal special case
		$useropenid = $_COOKIE['u'];
		if ($atype==-2) {
			$atext = blurb ($r,'query template');
			dosql ("insert into qtemplates (relatedind, plugid,teamind,useropenid,type,priority,text,time) value ($ind,'$plugid', '$teamind','$useropenid','$atype','$aprio','$atext',now())");
		}
		else

		{ $qtab = "alerts";
		$atext = blurb ($r,'unspecified injury..');
		dosql ("insert into alerts (plugid,relatedind, playerind,teamind,useropenid,type,priority,text,time) value ('$plugid',$ind,'$playerind', '$teamind','$useropenid','$atype','$aprio','$atext',now())");
		}

		if ($next=='') $next='p.php';
		redirect("$next?playerind=$playerind");
		exit;
	}
	else
	if (isset($_REQUEST['edit']))
	{
		$ind = $_REQUEST['edit'];
		$r = getplayerbyind($playerind);
		if ($r===false) return "couldnt find player $playerind";
		$player = $r->name;
		$team = $r->team;
		$league = getLeague($team);
		$plugid =($_REQUEST['plugid']);
		$plugin = getPluginInfo($plugid);
		$rr=mysql_fetch_object($plugin); // or crash
		$flavor = $report;
		if ($rr==false )
		{
			$ggg= false; $url=false; $table='onotable';
		} else {
			$url = $rr->url; $table = $rr->table;
			require_once $url;
			$ggg = schema(); // must be inside $formlib
		}

		$r = get_appl_record($table,'=',false,$ind);
		if ($r===false) die ("Cant find that player/injury combination");
		$rsumm = rsummary($r,$ggg);

		$editlink = <<<XXX
	<a class='is_edit_link' title=' you can edit this report, which will create a new case when you press the submit button' href='#' onclick="showhide('___$url'); return(false);">edit</a>
	<div style='display: none;' id='___$url' >
XXX;

		$morelinks = "<a class='is_query_link' title=' You can query the Informed Sports DataWarehouse, and find other players on the $team on in the $league->name with
			similar conditions.' href='dw.php?qlike=$ind&plugid=$plugid&leagueind=$league->ind' >find more</a>";
		$iform = "<div id='ajaxarea'>".
		caseheader($plugid,$ind,$playerind,'edit',$morelinks.'&nbsp;'.$editlink).

		"<div class='form_summary' >
			summary: $rsumm</div>".
		javascriptstuff().

		render_form(3*($plugid-1),'Create New Case', '', $playerind,$ind,$ggg,$url,$table,$next,reform($r,$ggg))."</div>"; // cr
		echo render_player($iform,"$my_role: Edit Injury",$playerind,$flavor,$my_role,$ggg,$url,$table,$next);
	}
	else {
		// this is the bitter end choice
		//echo$v[0].'     '.$v[1].'   '.$v[2].'<br/>' ;
		if (isset($_REQUEST['plugid']))
		{
			$plugid = $_REQUEST['plugid'];$report=-99;// put something in there
			$plugin = getPluginInfo($plugid);
			$rr=mysql_fetch_object($plugin); // or crash
		}
		else if ($report!=0)
		{
			$plugid=plugidFromReport($report);
			$plugin = getPluginInfo($plugid);
			$rr=mysql_fetch_object($plugin); // or crash
		}

		else $rr=false;
		if ($rr==false )
		{
			$ggg= false; $url=false; $table='notable';
		} else {
			$url = $rr->url; $table = $rr->table;
			require_once $url;
			$ggg = schema(); // must be inside $formlib
		}
		$v = playernamefromind($_REQUEST['playerind']);
		echo playerpage( $v[1],$v[0],$report,$my_role,$ggg,$url,$table,$next); // flavor zero doesnt need much
		exit;
	}
}
?>
