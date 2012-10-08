<?php

require_once "setup.inc.php";
require_once './OAuth.php';
require_once 'mc_oauth_client.php';

function islog($type,$openid,$blurb)
{
	$time = time();
	$ip = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
	dosql("Insert into islog set time='$time',ip='$ip',id='$openid',type='$type', url='$blurb' ");
}
function isdb_fetch_object($result)
{
	return mysql_fetch_object($result);
}
function isdb_fetch_array($result)
{
	return mysql_fetch_array($result);
}

function  isdb_insert_id()
{
	return mysql_insert_id();
}

function dosql($q)
{
	if (!isset($GLOBALS['db_connected']) ){
		$GLOBALS['db_connected'] = mysql_connect($GLOBALS['DB_Connection'] ,$GLOBALS['DB_User'] );
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db ".mysql_error());
	}
	$status = mysql_query($q);
	if (!$status) die ("dosql failed $q ".mysql_error());
	return $status;
}
function clean($s)
{
	return mysql_real_escape_string(trim($s));
}

function getplayerbyind ($playerind)
{
	$result = dosql("Select * from players where playerind='$playerind'");
	$r=isdb_fetch_object($result);
	return $r;
}
function get_appl_record($table,$op, $ascending,$ind)
{           $desc = ($ascending) ?'':'desc';
$result = dosql( "select * from $table where ind $op '$ind' order by ind $desc limit 1 " ); // overspecifiy for safety
$r=isdb_fetch_object($result);
return $r;
}
function getteambyname($team){
	$result = dosql ("Select * from teams where name='$team' ");
	$r=isdb_fetch_object($result);
	return $r;
}
function getleagueteambyteamind($teamind){
	$result = dosql ("Select * from leagueteams where teamind='$teamind' ");
	$r=isdb_fetch_object($result);
	return $r;
}
function getallusers($clause)
{
	return dosql("Select * from users where $clause ") ;
}
function  fetch_alerts($minprio,$playerind,$teamind) {
	$qqq= "select * from alerts where playerind='$playerind' and teamind='$teamind' and  priority >= '$minprio' order by alertind desc limit 20 ";
	return dosql($qqq);
}
function  fetch_query_templates($leagueind,$plugid) {
	$qqq= "select l.name,a.* from leagues l,  qtemplates a,leagueteams lt where plugid='$plugid'
	                           and a.teamind=lt.teamind and lt.leagueind='$leagueind'
	                            and l.ind=lt.leagueind order by alertind desc limit 20 ";
	return dosql($qqq);
}
function getplayerbyname($player,$team)
{	$player = mysql_escape_string($player);
$r = dosql("SELECT * from players where name='$player' and team='$team'");
$f = isdb_fetch_object($r);
return $f;
}
function get_appl_record_ind($table,$ind)
{
	$result = dosql("Select * from $table where ind='$ind' "); // reread the record we just inserted
	$r=isdb_fetch_object($result);
	return $r;
}


function playernamefromind ($ind)
{
	$result = dosql("Select * from players where playerind = '$ind'");
	$rr = isdb_fetch_object($result);
	if ($rr==false) return false; else return array($rr->name,$rr->team);
}
function teamnamefromind ($ind)
{
	$result = dosql("Select * from teams where teamind = '$ind'");
	$rr = isdb_fetch_object($result);
	if ($rr==false) return false; else return $rr->name;
}
function firstplayer($team)
{
	$teamind =get_teamind($team);
	$result = dosql("SELECT playerind from teamplayers where teamind='$teamind' limit 1");
	$f = isdb_fetch_object($result);
	return $f->playerind;
}
function get_playerind ($player)
{
	$player=mysql_escape_string($player);
	$result = dosql("Select * from players where name='$player' ");
	$r = isdb_fetch_object($result);
	if ($r===false) return false; return $r->playerind;
}
function get_teamind ($team)
{
	$result = dosql("Select * from teams where name='$team' ");
	$r = isdb_fetch_object($result);
	if ($r===false) return false; return $r->teamind;
}
function getleagueind($league)
{
	$result = dosql("Select ind from leagues where name='$league' ");
	$r = isdb_fetch_object($result);
	if ($r==false) return false;
	return $r->ind;
}
function getLeague($team)
{
	$result= dosql("SELECT l.logourl,l.ind,l.name,l.showpics,l.customlinks
	from teams t, leagueteams lt, leagues l 
	where t.name='$team' and t.teamind=lt.teamind and lt.leagueind=l.ind");
	$f = isdb_fetch_object($result);
	if (!$f) return false;
	return $f;
}
function getleaguebyname($leaguename)
{
	$result = dosql("Select * from  leagues where name='$leaguename'");
	$rr = isdb_fetch_object($result);
	return $rr;
}
function getleaguebyind($leagueind)

{
	$result = dosql("Select * from  leagues where ind='$leagueind'");
	$rr = isdb_fetch_object($result);
	return $rr;
}
function plugidFromReport($report)
{
	return intval($report/3)+1;
}
// sql fence
function javascriptstuff()
{
	$javascriptstuff = <<<XXX
<script>
function showhide(id){
if (document.getElementById){
obj = document.getElementById(id);
if (obj.style.display == "none"){
obj.style.display = "";
} else {
obj.style.display = "none";
}
}
}
</script> 
XXX;
	return $javascriptstuff;
}
function makevarname($s)
{
	return str_replace(array(' ','/','$','-'),array('_','_','_','_'),$s);
}
function my_identity()
{
	if (!isset($_COOKIE['u']))
	{
		return "NotLoggedIn";
	}
	return  urldecode($_COOKIE['u']);
}

function my_role()
{
	if (!isset($_COOKIE['u']))
	{
		redirect("index.php?logout=checkloginnocookie");
		//echo "redirecting to index.php?logout";
		//exit;
	}
	$openid = urldecode($_COOKIE['u']);
	$result = dosql ("Select * from users where openid='$openid'  ");

	$r=isdb_fetch_object($result);

	if (!$r)
	{
		redirect("index.php?logout=nouser");
		//echo "redirecting to index.php?logout&err=nouser";
		//exit;
	}

	return $r->role;
}

function redirect($url)
{
	islog('redirect to',my_identity(),$url);
	header("Location: $url");
	exit;
}
function check_login($roles,$blurb)
{
	$time = time();
	$ip = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];
	if (!isset($_COOKIE['u'])) $openid='--no cookie--'; else
	$openid = urldecode($_COOKIE['u']);
	islog('check',$openid,"roles:$roles page:$blurb");
	if (!isset($_COOKIE['u']))
	{
		redirect("index.php?logout=checkloginnocookie");
		//echo "redirecting to index.php?logout";
		//exit;
	}
	$openid = urldecode($_COOKIE['u']);
	$result = dosql ("Select * from users where openid='$openid'  ");

	$r=isdb_fetch_object($result);

	if (!$r)
	{
		redirect("index.php?logout=nouser");
		//echo "redirecting to index.php?logout&err=nouser";
		//exit;
	}

	$urole = $r->role;
	$role = explode(',',$roles);
	$count = count($role);
	for ($j=0; $j<$count; $j++)
	if ($role[$j] == $urole)
	{
		islog('view',$openid,$blurb);
		return $urole;
	}

	redirect("index.php?logout=badrole+$urole+$roles");


	//echo "redirecting to index.php?logout&err=badrole";
	//exit;
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


function render_team_search_form($flavor,$submitvalue, $out, $teamind,$parentind, $x,$formlib,$tablename,$next,$vals)
{
	global $MAX_RADIO_GROUP;

	if ($submitvalue=="Submit New Case") $hid = -1; else $hid=-2;
	$out .=<<<XXX
	<form action=search.php method=post>
	<input type=hidden value='$parentind' name=repostsearch />
	<input type=hidden value='$formlib' name=formlib />
	<input type=hidden value='$tablename'  name=table />
	<input type=hidden value='$teamind'  name=teamind />
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

function render_player_search_form($flavor,$submitvalue, $out, $playerind,$parentind, $x,$formlib,$tablename,$next,$vals)
{
	global $MAX_RADIO_GROUP;

	if ($submitvalue=="Submit New Case") $hid = -1; else $hid=-2;
	$out .=<<<XXX
	<form action=search.php method=post>
	<input type=hidden value='$parentind' name=repostsearch />
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

	$out .="<input type=submit name=submit value='Active Player' />";
	
	
	$out .="<input type=submit name=submit value='Disabled Player' />";
	
	
	$out .="<input type=submit name=submit value='Injured Player' />
	 </form></div>";
	
	return $out;
}
function player_focus_chooser($team,$player)
{
	$league = getLeague($team);
	$plugins = getAllPluginInfo($league->ind);
	$playerind = get_playerind($player);
$istuff='';
while ($r=mysql_fetch_object($plugins))
{
	$up = ($r->ind-1); // turn plugin index into select
	$label=trim($r->label);
	$istuff .=<<<XXX
 		<option value='$up' >$label</option>
XXX;
}


$x = <<<XXX
<select id='focusselect' name='focus' title='pick a focus for $player'  onchange="location = 'p.php?playerind=$playerind&focus='+this.options[this.selectedIndex].value;">
<option value='-99' >-choose-</option>
$istuff
</select>
XXX;
return $x;
}
function teamchooser($leagueind,$myteam,$id)
{  // the id tag for the select should probably be changed

	// returns a big select statement
	$outstr = <<<XXX
	<select $id name='team' title='choose another team in this league' onchange="location = 't.php?teamind='+this.options[this.selectedIndex].value;">
XXX;
	//$outstr = "<select name='team'>";
	$result = dosql ("SELECT t.name,t.teamind from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind
	                                                               order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $myteam)?' selected ':'';
		$outstr .="<option value='$r2->teamind' $selected >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}

function teamchooserind($leagueind,$id)
{
	// returns a big select statement . adds  one extra choice
	$outstr = <<<XXX
	<select  $id name='teamind' title='choose another team in this league' onchange="location = 't.php?teamind='+this.options[this.selectedIndex].value;">
	<option value='-1' >-choose team-</option>
XXX;
	$result = dosql ("SELECT t.teamind,t.name from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind
	                                                               order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$tind = $r2->teamind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}
function visualteamchooser($leagueind)
{

	$outstr = <<<XXX
	<div  id='leagueroster'   title='choose teams' >
XXX;
	$result = dosql ("SELECT t.teamind,t.name,t.logourl from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind   order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$tind = $r2->teamind;
		$name = $r2->name;
		$img  = $r2->logourl;
		if ($img =='')  $img= $GLOBALS['missing_image'];
		//$ename = urlencode($name);

		$outstr .="<div class='league_member'>
		<a href='t.php?teamind=$tind' title='open team roster page for $name' class='team_image' >
		<img src='$img' width='50' alt='no image for team' /></a>
		<a href='t.php?teamind=$tind' title='open team roster page for $name' class='team_name' >$name</a>
		</div>
		";
	}
	$outstr.="
	</div>
	";
	return $outstr;

}
function teamchooserindquiet($leagueind,$id)
{
	// returns a big select statement . adds  one extra choice
	$outstr = <<<XXX
	<select   $id name='teamind' title='choose another team in this league' >
	"<option value='-1' >-all teams-</option>
	"
XXX;
	$result = dosql ("SELECT t.teamind,t.name from teams t, leagueteams lt where lt.leagueind='$leagueind'  and lt.teamind = t.teamind
	                                                               order by t.name");

	while ($r2 = isdb_fetch_object($result))
	{
		$tind = $r2->teamind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>
		";
	}
	$outstr.="</select>";
	return $outstr;

}

function leaguechooser()
{
	// returns a big select statement

	$outstr = <<<XXX
	<select name='leagueind' title='choose another league on this informed sports service' onchange="location = 'l.php?leagueind='+this.options[this.selectedIndex].value;">
XXX;

	$result = dosql ("SELECT name, ind from leagues order by name");


	while ($r2=isdb_fetch_object($result))

	{
		$tind = $r2->ind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>";
	}
	$outstr.="</select>";
	return $outstr;

}

function leaguechooserquiet()
{
	// returns a big select statement

	$outstr = <<<XXX
	<select name='leagueind' title='choose another league on this informed sports service'>
XXX;

	$result = dosql ("SELECT name, ind from leagues order by name");


	while ($r2=isdb_fetch_object($result))

	{
		$tind = $r2->ind;
		$name = $r2->name;
		//$ename = urlencode($name);

		$outstr .="<option value='$tind' >$name</option>";
	}
	$outstr.="</select>";
	return $outstr;

}
function playerchooser($team, $player)
{
	// returns a big select statement
	$outstr = <<<XXX
	<div class=playerselect> <select name='name' title='choose another player on $team' onchange="location = 'p.php?name='+this.options[this.selectedIndex].value;">
XXX;
	$result = dosql ("SELECT * from players  where team = '$team'");
	$eteam = urlencode ($team);
	while ($r2 = isdb_fetch_object($result))
	{
		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $player)?' selected ':'';
		$outstr .="<option value='$name' $selected >$name</option>
		";
	}
	$outstr.="</select></div>";
	return $outstr;

}function playerchooserquiet($team, $player)
{
	// returns a big select statement
	$outstr = <<<XXX
	<div class=playerselect> <select name='name' title='choose  player on $team' >
XXX;
	$result = dosql ("SELECT * from players  where team = '$team'");
	$eteam = urlencode ($team);
	while ($r2 = isdb_fetch_object($result))
	{
		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $player)?' selected ':'';
		$outstr .="<option value='$name' $selected >$name</option>
		";
	}
	$outstr.="</select></div>";
	return $outstr;

}
function playerchooserind($team, $player)
{
	// returns a big select statement
	$outstr = <<<XXX
	<div class=playerselect> <select  name='playerind' title='choose another player on $team' onchange="location = 'p.php?playerind='+this.options[this.selectedIndex].value;">
XXX;
	$result = dosql ("SELECT * from players  where team = '$team'");

	$eteam = urlencode ($team);
	while ($r2 = isdb_fetch_object($result))
	{

		$name = $r2->name;
		//$ename = urlencode($name);
		$selected = ($name == $player)?' selected ':'';
		$outstr .="<option value='$r2->playerind' $selected >$name</option>
		";

	}
	$outstr.="</select></div>";
	return $outstr;

}


function clean1($s)
{

	$s = preg_split("/[\s,]+/",$s);
	return clean($s[0]);
}
function page_header($title)
{
	return str_replace(array('$$$title$$$'),array($title),file_get_contents("_header.html"));
}
function adminpage_header($title)
{
	return str_replace(array('$$$title$$$'),array($title),file_get_contents("_adminheader.html"));
}
function userpagefooter()
{ // starts with tail end of the linkarea section

	$time = strftime('%D %T');
	$myid = my_identity();
	$my_role = my_role();
	"you are signed on as $myid role $my_role"
	;
	$ret = <<<XXX
 <div id='footer'>$time - you are signed on as $myid; your role is: $my_role<br/><a href="index.php" title="Axio Sports">Axio Sports</a>
 is built on the <a href='https://www.medcommons.net/'>MedCommons Appliance</a>
 <br/>
For more information  please contact <a href='http://axiosports.com/'>Axio Sports</a>
</div>
XXX;
	return $ret;
}
function teaminfo ($team)
{
	$teamind = get_teamind($team);
	$result = dosql ("Select * from teams where teamind='$teamind'");
	$rr = isdb_fetch_object($result);
	if ($rr) if ($rr->teaminfo!='') return "<div class=teampanel>$rr->teaminfo</div>"; else return "<div class=teampanel><div class=teaminfo></div></div>";
	return $rr;
}
function leagueinfo ($league)
{
	$result = dosql ("Select * from leagues where name='$league'");
	$rr = isdb_fetch_object($result);
	if ($rr) if ($rr->leagueinfo!='') return "<div class=teampanel>$rr->leagueinfo</div>"; else return "<div class=teampanel><div class=teaminfo></div></div>";
	return $rr;
}
function teamfooter($team,$search='',$extra='')
{
	$teamind = get_teamind($team);
	if ($search=='') $search = "<a href='search.php?teamind=$teamind' >search</a>";
	
	$league = getLEague($team);
	$userpagefooter=userpagefooter();
	$result = dosql ("Select * from teams where teamind='$teamind'");
	$rr = isdb_fetch_object($result);
	if ($rr)
	{
		if ($rr->schedurl!='')$sched = "
&nbsp;|&nbsp;<a target='_new' target='_new' href='$rr->schedurl' title='schedule  $team'>sched</a>";
		else $sched='';
		if ($rr->newsurl!='')$news = "
&nbsp;|&nbsp;<a target='_new' target='_new' href='$rr->newsurl' title='news for  $team'>rss</a>";
		else $news='';
		$eteam=urlencode($team);

		$my_role = my_role();
		$leaguelink = '';
		if (($my_role=='is')||($my_role=='league')) $leaguelink =
		"<a href='l.php?leagueind=$league->ind' >league</a>&nbsp;|&nbsp;" ;

		$x=<<<XXX
    <div id='linkarea'>$extra $league->customlinks $leaguelink
    <a href='t.php?teamind=$teamind' >team</a>&nbsp;|&nbsp;
   $search $sched 
    $news&nbsp;|&nbsp;<a target='_new' href='launchsf.html?team=$eteam' title='support on salesforce.com for $team in new window'>support</a>
&nbsp;|&nbsp;<a href='index.php?logout=footer' title='logout from informed sports'>logout</a>
</div>
$userpagefooter
XXX;

		return $x;
	}
	return false;
}





function istoday($time)
{
	$now = date('Y-m-d h:i A');
	//echo "Now $now     time $time<br/>";
	return (substr($time,0,10)==substr($now,0,10));
}
function isyesterday($time)
{
	$now = time();
	$yesterday =
	date('Y-m-d h:i A',$now- (24 * 60 * 60));
	return (substr($time,0,10)==substr($yesterday,0,10));
}
function nicetime ($time)
{

	if (istoday($time)) return substr($time,11,8);
	else
	if (isyesterday($time)) return "yesterday";
	else return substr($time,5,2).'/'.substr($time,8,2).'/'.substr($time,2,2);

}

function allteamchooser($id)
{
	// returns a big select statement
	$outstr = "<select $id name='teamind'>
	";
	$result = dosql ("SELECT t.name,t.teamind,l.name from teams t, leagueteams lt, leagues l where  lt.teamind = t.teamind and lt.leagueind=l.ind
	                                                               order by l.name, t.name");
	$first = true;
	while ($r2 = isdb_fetch_array($result))
	{
		$team = $r2[0]; $teamind = $r2[1]; $league = $r2[2];
		//$ename = urlencode($name);
		$selected = ($first)?' selected ':'';
		$outstr .="<option value='$teamind' $selected >$league:$team</option>
		";
		$first = false;
	}
	$outstr.="</select>";
	return $outstr;
}
function alertlist($tag,$team,$playerind,$result, $tableid,$flavor)
{
	$counter = 0;
	$addlink ="<a href='?playerind=$playerind&report=-13' >new alert</a>";
	$player = playernamefromind($playerind);
	$outstr= "<div class='lhajaxarea'>
	 $tag $addlink
	<table id='$tableid' class='is_alerts' >
	";

	$lastplayer=false;
	while ($r=isdb_fetch_object($result))
	{
		switch ($r->priority)
		{
			case '0': { $prio="normal"; break; }
			case '1': { $prio="high"; break; }
			case '2': { $prio="critical"; break; }
			default : { $prio="bad"; break; }

		}
		$atext = $r->text;
		$playerind = $r->playerind;
		switch ($r->type)
		{
			case '-2': { $type="query"; $useropenid = urldecode($r->useropenid);
			$atext ="<a title='view query template authored by $useropenid' href='p.php?plugid=$r->plugid&edit=$r->relatedind&playerind=$playerind' >$r->text</a> ";
			break; }
			case '-1': { $type="report"; $useropenid = urldecode($r->useropenid);
			$atext ="<a title='view report details authored by $useropenid' href='p.php?plugid=$r->plugid&edit=$r->relatedind&playerind=$playerind' >$r->text</a> ";
			break; }
			case '0': { $type="general"; break; }
			case '1': { $type="medical"; break; }
			case '2': { $type="head"; break; }
			case '3': { $type="cervical"; break; }
			case '4': { $type="upper"; break; }
			case '5': { $type="torso"; break; }
			case '6': { $type="lower"; break; }
			default : { $type="bad"; break; }

		}

		$v = playernamefromind($playerind);
		$player = $v[0];

		$playerurl = "p.php?playerind=$playerind&ra=$r->alertind";
		$playercell = "<td><a href='$playerurl' title='remove this alert for  $player'>x</a></td>";

		$time = nicetime($r->time);
		$css_class="_pri_$prio";
		//if (($flavor ==0) || (($flavor>0) && ($lastplayer !== $player))) // if flavor >0 then only put out first for each
		$outstr.="<tr class='$css_class'>$playercell<td>$time</td><td title='$r->priority alert generated $r->time' >$type</td><td>$atext</td></tr>
		";
		$counter++;
		$lastplayer = $player;
	}
	$outstr.='</table></div>';
	if ($counter==0) $outstr="<div class='lhajaxarea'>
	No $tag $addlink
	</div>";
	return $outstr;
}

function querylist($plugid,$tag,$leagueind, $result, $classid,$flavor)
{
	$plugin = getPluginInfo($plugid);
	$rr = mysql_fetch_object($plugin);

	$league = getleaguebyind($leagueind);
//class='ajaxarea'
	$counter = 0;
	$outstr= "   <div >
	 $tag
	<table id='$classid' class='is_stored_query' >
	";

	while ($r=isdb_fetch_object($result))
	{
		switch ($r->priority)
		{
			case '0': { $prio="normal"; break; }
			case '1': { $prio="high"; break; }
			case '2': { $prio="critical"; break; }
			default : { $prio="bad"; break; }

		}
		$atext = $r->text;

		$url = "dw.php?qlike=$r->relatedind&plugid=$rr->ind&leagueind=$league->ind";
		switch ($r->type)
		{
			case '-2': { $type="query"; $useropenid = urldecode($r->useropenid);
			$atext ="<a title='query for similar cases' href='$url' >$r->text</a> ";
			break; }
			default : { $type="bad"; break; }
		}
		$time = nicetime($r->time);
		$css_class="_pri_$prio";
		//if (($flavor ==0) || (($flavor>0) && ($lastplayer !== $player))) // if flavor >0 then only put out first for each
		$outstr.="<tr class='$css_class'><td>$time</td><td title='$r->priority alert generated $r->time' >$type</td><td>$atext</td></tr>
		";
		$counter++;

	}
	$outstr.='</table></div>';

	if ($counter==0) $outstr="<div class='ajaxarea'>No $tag</div>";
	return $outstr;
}

function injurylistplayer($tag,$player, $rr)
{
	$playerind = get_playerind($player);

	$newcase = "<a href='?playerind=$playerind&report=1' >new case</a>";
	$result= dosql("select * from $rr->table  where playerind='$playerind' order by playerind, ind desc limit 20");
	$counter = 0;
	$outstr= "<div class='ajaxarea'>$rr->label for player $player &nbsp;$newcase<br/><table id ='$rr->table' class='is_injurylist' >";
	$lastplayer=false;
	while ($r=isdb_fetch_object($result))
	{
		$blurb = blurb($r,"unspecified $rr->type");
		$time = strftime('%D %T',($r->time));
		$useropenid = urldecode($r->useropenid);
		$outstr.="<tr><td><a href='#'  title='close this injury case' >c</a></td><td><a href='#' title='extend this case with new report' >e</a></td><td>$time</td><td><a title='view $rr->type case report entered by $useropenid' href='p.php?playerind=$playerind&plugid=$rr->ind&edit=$r->ind' >$blurb</a></td></tr>
		";
		$counter++;
	}
	$outstr.='</table></div>';
	if ($counter==0) $outstr="<div class='ajaxarea'>No $rr->label for player $player &nbsp;$newcase<br/></div>";
	return $outstr;
}


function dbg($m) {
	error_log("XXX: $m");
}
function querychooser($leagueind,$plugid,$classidstuff)
{
	return querylist($plugid,"Query History", $leagueind,  fetch_query_templates($leagueind,$plugid),$classidstuff,0);
}
function main_logo($my_role,$extra='')
{
	if ($my_role=='is') $mimg = "<a href='is.php'><img width=200 src='images/AxioSports.png' alt='main logo'/></a>
	"; else $mimg = "<a href='#'><img width=200 src='images/AxioSports.png'  alt = 'main.logo' /></a>
	";
	return "<div id='logo'>".$mimg.$extra."</div>";
}

function league_logo($league,$my_role)
{  // hack to show in ie

	$myid = my_identity();
	if ($league->logourl!='')
	$imgurl =$league->logourl;
	else $imgurl= $GLOBALS['missing_image'];
	if ($my_role=='is'||$my_role=='league')
	$leagueimg = "<a href='l.php?leagueind=$league->ind' title='you are signed on as $myid role $my_role' >
	<img src='$imgurl' id='leagueimg' alt='you are signed on as $myid role $my_role' border='0' /></a>";
	else 	$leagueimg = "<a href='#' title='you are signed on as $myid role $my_role' ><img src='$imgurl' alt='you are signed on as $myid role $my_role' border='0' /></a>";
	return  "<div id='leaguelogo' >".$leagueimg."</div>"; // this div misnamed - check with michael
}


function team_logo($teamind,$my_role)
{  // hack to show in ie
	$result  =  dosql("Select * from teams where teamind='$teamind'  ");
	$team = mysql_fetch_object($result);
	$myid = my_identity();
	if ($team->logourl!='')
	$imgurl =$team->logourl;
	else $imgurl= $GLOBALS['missing_image'];
	$leagueimg = "<span title='you are signed on as $myid role $my_role' ><img src='$imgurl' alt='you are signed on as $myid role $my_role' border='0' /></span>";
	return  "<div id='teamlogo' >".$leagueimg."</div>"; // this div misnamed - check with michael
}

function countof($q)
{
	$result = dosql("Select count(*) from $q ");
	$r = isdb_fetch_array($result);
	if (!$r) return -1;
	return $r[0];
}

function getstats()
{
	$lc = countof ("leagues");
	$tc = countof ("teams");
	$pc = countof ("players");
	$hu = countof ("players where healthURL!='' ");
	$tr = countof ("users where role='team' ");
	$lm = countof ("users where role='league' ");
	$us = countof ("users where role='is' ");
	$al = countof ("alerts");
	return array($lc,$tc,$pc,$hu,$tr,$lm,$us,$al);
}
function  getAllPluginInfo($leagueind)
{
	$plugins = dosql("select * from plugins,leagueplugins where plugins.ind=leagueplugins.plugin and showonmenu='1' and leagueind='$leagueind' ");
	return $plugins;
}
function  getPluginInfo($id)
{
	$plugin = dosql("select * from plugins where plugins.ind='$id' ");
	return $plugin;
}

function blurb ($r,$blurbdef)
{
	$blurb = '';
	if (isset($r->Background_Info_short_case_description)) $blurb.="$r->Background_Info_short_case_description ";
	if (isset($r->Predicted_Outlook_)) $blurb.="$r->Predicted_Outlook_ ";
	$blurb = trim($blurb);	if ($blurb == '') $blurb = $blurbdef;
	return $blurb;
}


/**
 * Create and return an appliance API object initialized
 * for the given oauth access token for the given appliance. 
 *
 * @param appliance - the base url of the appliance
 * @param oauth_token - <key>,<secret> of access token
 */
function get_appliance_api($appliance,$oauth_token) {
	$token_parts = explode(",",$oauth_token);
	if(count($token_parts)!=2)
	throw new Exception("token $oauth_token is in an invalid format");
	return new ApplianceApi($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$appliance, $token_parts[0], $token_parts[1]);
}

function get_request_token($appliance,$accid) {
	$api = new ApplianceApi($GLOBALS['appliance_access_token'],$GLOBALS['appliance_access_secret'],$appliance);
	return $api->get_request_token($accid);
}

/**
 * Return a version of $x escaped for javascript
 */
function jsesc($x) {
	return preg_replace("/\n/","\\n",addslashes($x));
}
?>
