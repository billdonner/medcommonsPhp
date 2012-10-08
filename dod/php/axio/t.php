<?php
require_once "is.inc.php";
function team_report_section ($my_role,$team,  $s,$flavor,$ggg)
{
	//echo "team report section $flavor flavor";
	//echo "midselction flavor $flavor ggg $ggg";
	$teamind = get_teamind($team);
	switch ($flavor)
	{
		case '-99': { header("Location: t.php?teamind=$teamind"); exit;}
		case '-1': {
			if ($my_role=='is'){
				redirect("is.php?addplayer&teamind=$teamind"); exit;
			}  else { $bo="Role mismatch in team_report_section"; break;}
		}
		case '0': {   $qqq= "select * from alerts where teamind='$teamind' order by alertind desc limit 20 ";
		$bo=alertlist("Pending Alerts for $team", $team, '??', dosql($qqq),$s,$flavor); break;
		}
		case '1': {   $qqq= "select * from alerts where teamind='$teamind' and priority>'0' order by alertind desc limit 20";
		$bo=alertlist("Important Alerts for $team",$team,'??',dosql($qqq),$s,$flavor); break;
		}
		case '2': {   $qqq= "select * from alerts where teamind='$teamind' and priority>'1'  order by alertind desc limit 20";
		$bo=alertlist("Critical Alerts for $team", $team,'??',dosql($qqq),$s,$flavor); break;
		}
		default: {   $bo="Case default $flavor not implemented"; break;
		}
	}

	// now pretty it up a bit
	return $bo;
	//return "<br/><fieldset>$bo</fieldset>";

}
function team_report_chooser($my_role,$teamind,$tis)
{

	if ($my_role=='is'){
		$selm1 = ($tis==-1) ? " selected=selected ":"";
		$privstuff = "<option value = '-1' $selm1 >Add New Player</option>";
	}
	else $privstuff='';
	$sel0 = ($tis==0) ? " selected=selected ":"";
	$sel1 = ($tis==1) ? " selected=selected ":"";
	$sel2 = ($tis==2) ? " selected=selected ":"";
	$x = <<<XXX
<select id='actionselect' name='report' onchange="location = 't.php?teamind=$teamind&report='+this.options[this.selectedIndex].value;">
<option value = '-99'  >-choose team report or function-</option>
$privstuff
<option value = '0' $sel0 >Pending  Team Alerts</option>
<option value = '1' $sel1 >Important Team Alerts </option>
<option value = '2' $sel2 >Critical Team Alerts</option>
</select>
XXX;
	return $x;
}
function roster($team)
{
	$league = getLeague($team);
	$result2 = dosql ("SELECT * from players where team = '$team'");
	$ob =  "<div id='teamroster'>";
	$oddeven = 'odd';
	while ($r2 = isdb_fetch_object($result2))
	{
		// one row per player
		$homepage = $r2->homepageurl;
		$healthurl = $r2->healthurl;
		$name = $r2->name;
		$result3 = dosql ("SELECT alertind from alerts  where playerind  = '$r2->playerind' ");
		$r3 = isdb_fetch_object($result3);
		if ($r3) $addicon = "<img src='images/addIcon.png' />"; else $addicon='';

		$playerurl = "p.php?playerind=$r2->playerind";
		$playeranchor = "<a class='player_name' href='$playerurl' title='Informed Sports page for $name'>$name</a>";

		$born = $r2->born;
		$playericons = "<img src='images/hurl_notification.gif' /> $addicon";
		
		/*
		$teamind = get_teamind($team);
		$nameind = get_playerind($name);
		$result = dosql ("SELECT * FROM alerts where teamind='$teamind' and playerind = '$nameind' order by priority desc limit 1");
		$r=isdb_fetch_object($result) ;
		$text='';
		*/

		switch ($r2->playingstatus)
		{
			case '':
			case 'Active': { $class = "_pri_normal"; break;}
			case 'Injured': { $class = "_pri_high"; break;}
			case 'Disabled': { $class = "_pri_critical"; break;}
			default : { $class = "_pri_none"; break;}
		}

		if ($league->showpics >0) {
			if ($r2->imageurl!='') $imgurl = $r2->imageurl;  else $imgurl = $GLOBALS['missing_image'];
			$playerimg = "<a href='$playerurl' class='player_image' title='Informed Sports page for $name born $born'><img width='60' src='$imgurl' alt='no player image'/></a>";
		}
		else $playerimg ="";
		$ob .=  "<div class='$class team_member'>$playerimg $playeranchor $playericons</div>
		";
		if ($oddeven == 'even') $oddeven='odd'; else $oddeven = 'even';
	}
	$ob .=  "<div class='clearfloat'></div></div>";
	return $ob;

}
function teampage($team,$flavor,$my_role)
{
	$teamind = get_teamind($team);
	$league = getleague($team);
	$teamimg =team_logo($teamind,$my_role);
	$leagueind = $league->ind;

	if ($my_role=='is' || $my_role=='league')
	//$teamchooser = teamchooser($league->ind,$team," id='playerselect' "); else $teamchooser='';
	$title ="$team - $league->name";
	$page_header = page_header($title);

	$mimg = main_logo($my_role);
	$teamheader = "$teamimg $mimg  ";
	$roster = roster($team);
	$footer = teamfooter($team);
	$teaminfo = teaminfo($team);
	$topmarkup = <<<XXX
$page_header
		<body onload='setAccordian("roster_part");'>
  <div id='content'>
	<div id='is_header'>
$teamheader
	</div>
	$teaminfo
	<div id='is_body'>
	   <div id='is_c_section'>
	   <div id='roster_part' class='amenu' onclick='toggle("roster_part")'>Team Roster for $team</div>
	<div  style='display: block;' id='roster_part_content' class='acontent'>	
$roster
	</div>
	  
XXX;
$markup = '';


$bottommarkup = <<<XXX
	  </div>
	</div>
	<div id='is_footer'>
$footer
	</div>

	</body>
XXX;
return $topmarkup.$markup.$bottommarkup;

}
$my_role=check_login('team,league,is','team page'); // only returns if logged in
$flavor =(isset($_REQUEST['report']) )? $_REQUEST['report']:'0';

if (isset($_REQUEST['teamind'])) {
	$league = getLeague(teamnamefromind($_REQUEST['teamind']));
	echo teampage( teamnamefromind($_REQUEST['teamind']),$flavor,$my_role);
} else
{
	echo "should not longer be getting here without a teamin in t.php";
}
?>