<?php
require_once "is.inc.php";
function dwpage($leagueind,$my_role,$body)
{
	$outbuf='';
	$rr = getleaguebyind($leagueind);
	$leagueimg = league_logo($rr,$my_role);
	$mimg = main_logo($my_role);
	$page_header = file_get_contents("_header.html");
	$userpagefooter = userpagefooter();
	$xuserpagefooter=<<<XXX
<div id='linkarea'>
 <a href='l.php?leagueind=$leagueind' >league</a>&nbsp;|&nbsp;<a target='_new' href='launchsf.html?leagueind=$leagueind'  title='support on salesforce.com in new window'>support</a>
&nbsp;|&nbsp;<a href='index.php?logout=dw.php' title='logout from informed sports'>logout</a>
</div>
$userpagefooter
XXX;
	$outbuf = <<<XXX
	$page_header
	<body >
            <div id='content'>
	<div id='is_header'>
	$mimg
	$leagueimg
	</div>
	<div id='is_body'>
	<div id='is_c_section'>
	$body
	</div>
	</div>
	<div id='is_footer'>
	$xuserpagefooter
	</div>
	</div>
	</body>
	</html>
XXX;
	return $outbuf;
}

function qbeform($plugid,$leaguename,$r,$x)
{
	$plugin = getPluginInfo($plugid);
	$rr = mysql_fetch_object($plugin);
	$league = getleaguebyname($leaguename);
	$counter=0;
	$teamchooser = teamchooserindquiet($league->ind,'');
	$vals = array();
	$qbeform ="<div id='queryarea'><h4>$league->name Data Warehouse: Query for Similar $rr->label Cases</h4><p>
	This query matches on all of the players in $league->name  whose corresponding fields in $rr->label are checked and match. 
	</p>
	 <form method=post action='dw.php' >
	 <input type=hidden name=league value='$league->name' />
	  <input type=hidden name=casetable value='$plugid' />
	 ";//<tr><th>database field</th><th>value</th></tr>";
	$qbeform .= "<table><tr><td><input type=checkbox  value='soloteam' name='cb_$counter'   /></td><td>select team</td><td>$teamchooser</td></tr>
	";
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
			for ($k=0;$k<$count3; $k++)
			{
				$cond = $remains[$k][0];
				$code = $remains[$k][1];

				if ($cond===false)
				{
					$name = makevarname($region.'-'.$zone);
					$var = $r->$name;
					if ($var!='')
					{
						$counter++;
						$cbox = "<input type=checkbox   value='$name' name='cb_$counter'   /> ";
						$qbeform .= "<tr><td>$cbox</td><td>$region $zone</td><td> <input type=text name='$name' value = '$var' /></td></tr>
					";
					}
				}
				else
				{
					$name = makevarname($region.'-'.$zone.'-'.$cond);
					$var = $r->$name;
					if ($var=='1'){
						$counter++;
						$cbox = "<input type=checkbox  checked=checked Value='$name' name='cb_$counter'   /> ";
						$qbeform .= "<tr><td>$cbox</td><td>$region $zone $cond</td>
						<td> <input type=radio name='$name' value = '1' checked=checked/>set
				                                                   <input type=radio name='$name' value = '0' />clear </td></tr>	";
					}
				}

			}
		}
	}
	$qbeform .= "</table><input type=submit name=submit value = 'query' />
		</form>
		</div>
	";			
	return $qbeform;
}


// start here, should check league level credentials
$my_role=check_login('team,league,is','dw page'); // only returns if logged in

if (isset($_REQUEST['qlike']))
{
	// handle a query
	$ind = $_REQUEST['qlike'];
	
	$plugid = $_REQUEST['plugid'];
	$leagueind = $_REQUEST['leagueind'];
	$league = getleaguebyind($leagueind)->name;
	$plugin = getPluginInfo($plugid);
	$rr = mysql_fetch_object($plugin);
	require_once $rr->url;
	$ggg = schema(); // must be inside $formlib

	$r = get_appl_record($rr->table,'=',false,$ind);
	if ($r===false) die ("Cant find that player/injury combination");
	$qbeform  =qbeform($plugid, $league, $r,$ggg);
	echo dwpage($leagueind,$my_role,$qbeform);
	exit;
}

else
if (isset($_REQUEST['casetable'])) {
	$league = $_REQUEST['league']; // must be present
	$leagueind= getleagueind($league);
	$plugin = getPluginInfo($_REQUEST['casetable']);
	$rr = mysql_fetch_object($plugin);


	//$sql = "Select c.ind, c.playerind, c.parentind, c.playerind, tp.teamind, p.name, p.team from $cases c,teamplayers tp,leagueteams lt, players p where c.playerind=tp.playerind
	$sql = "Select c.*, tp.teamind, p.name, p.team,p.imageurl,t.logourl
	 from $rr->table c,teamplayers tp,leagueteams lt, players p,teams t where c.playerind=tp.playerind
				and tp.teamind=lt.teamind 
				and lt.leagueind='$leagueind' 
				and p.playerind = c.playerind
				and t.teamind = tp.teamind ";

	if  (isset($_REQUEST["cb_0"]))
	{
		if ($_REQUEST["cb_0"]=='soloteam') // might be present
		{
			$sql .= "
				and tp.teamind ='".$_REQUEST["teamind"]."' ";
		}
	}

	for ($i=1; $i<100; $i++)
	if (isset($_REQUEST["cb_$i"]))
	{
		$var = $_REQUEST["cb_$i"];
		$val = $_REQUEST[$var];
		$sql.=  "
	and c.$var = '$val' ";
	} ; // jeez louise else break;

	$sql .= " order by p.name limit 30";

	$result = dosql($sql);
	$counter =mysql_num_rows($result);
	$lastind = -1;
	$buf = <<<XXX
	<div id='ajaxarea' >
	              <h4>$league Data Warehouse: $counter <span title="sql statement available soon" >Query Results</span> for $rr->label</h4>
	<div id='queryresults'>
XXX;
	while ($r=mysql_fetch_object($result))
	{

		$blurb = blurb($r,"unsepcified injury.....");$time = strftime('%D %T',($r->time));
		$useropenid = urldecode($r->useropenid);
		// aggregate by player
		$newplayer= ($r->playerind!=$lastind);
		if ($newplayer) {
			if ($lastind!=-1) $buf.="
			</table></div></div>
			";
			$lastind = $r->playerind;
			$ct = "__".$lastind."_content";
			if ($r->logourl=='') $logourl = $GLOBALS['missing_image']; else $logourl = $r->logourl;
			$buf .= <<<XXX
	      <div class='player'>
	      <br/>
		<div id='__$lastind' class='amenu' onclick='toggle("__$lastind")'>
		<div class='q_team_member'>
		<span class='player_image' title='go to $r->name on Informed Sports' href='p.php?playerind=$r->playerind' >
		<img width='50' src='$r->imageurl' alt='no image' />&nbsp;<img width='50' src='$logourl' alt='$r->team'></span><br/>
		<span class='player_name' title='go to $r->name on Informed Sports' href='p.php?playerind=$r->playerind'>$r->name</span></div>
	            </div>
		<div id='$ct' class='acontent'>
		<table>
XXX;
}
$buf .="
		<tr>
			<td>$time</td>
			<td>
			<a title='view injury report entered by $useropenid ' href='p.php?plugid=$rr->ind&playerind=$r->playerind&edit=$r->ind' > $blurb</a>
			</td>
		</tr>
	";
}
if ($lastind!=-1) $buf.="
	</table></div></div>
";
$buf.="
	</div>
	</div>
";
	
echo dwpage($leagueind,$my_role,$buf);

exit;
}

echo "Should not get here in dw.php"
?>