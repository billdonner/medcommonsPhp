<?php
require_once "is.inc.php";
function enumeratepluginstoredqueries($leagueind,$teamind)
{
	$plugins = getAllPluginInfo($leagueind);
	$markup = '';

	while ($r = mysql_fetch_object($plugins))
	{
		$querychooser = querychooser($leagueind,$r->ind,'class_querychooser');
		$nc = $r->name.'_content';
		$url = $r->url; $table = $r->table;
		$markup .= <<<XXX
        <div id='dw_part_$r->name' class='amenu' onclick='toggle("dw_part_$r->name")'>$r->label</div>
	<div  style='display: none;' id='dw_part_$nc' class='acontent'>
	<h4><a href="?teamind=$teamind&plugin=$r->ind" />New  query</a></h4>
	<h4>Or choose from the query history $r->label</h4>
		<div  class='stored_query_form'>
		$querychooser
		</div>
	</div>
XXX;
}
return $markup;
}
$my_role=check_login('team,league,is','team page'); // only returns if logged in

if (isset($_REQUEST['repostsearch']))
{
	if (isset($_REQUEST['playerind']))
	{
		$playerind = $_REQUEST['playerind'];
		$result = dosql("Select * from players where playerind='$playerind' ");
		$r=isdb_fetch_object($result);
		$team = $r->team;
		$teamind=get_teamind($team);
	}
	if (isset($_REQUEST['leagueind']))
	{
		// just grab the first team
		$leagueind = $_REQUEST['leagueind'];
		$playerind =-1;
		$result = dosql("Select l.teamind,t.name from leagueteams lt,teams t where lt.leagueind='$leagueind' and lt.teamind=t.teamind ");
		$r=isdb_fetch_object($result);
		$teamind = $r->teamind;
		$team = $r->name;

	}
	else {
		$playerind=-1; // is from  the direct search screen
		$teamind = $_REQUEST['teamind'];
		$team  = teamnamefromind($_REQUEST['teamind']);
	}
}else
if (isset($_REQUEST['leagueind']))
{
	// just grab the first team
	$leagueind = $_REQUEST['leagueind'];
	$playerind =-1;
	$result = dosql("Select lt.teamind,t.name from leagueteams lt,teams t where lt.leagueind='$leagueind' and lt.teamind=t.teamind ");
	$r=isdb_fetch_object($result);
	$teamind = $r->teamind;
	$team = $r->name;

}
else
{ // not repost
	$playerind=-1;
	$teamind = $_REQUEST['teamind'];
	$team  = teamnamefromind($_REQUEST['teamind']);
}
$league = getleague($team);
$leagueind = $league->ind;
$flavor =(isset($_REQUEST['report']) )? $_REQUEST['report']:'0';
$teamimg = team_logo($teamind,$my_role);


if ($my_role=='is' || $my_role=='league')
$teamchooser = teamchooser($league->ind,$team," id='playerselect' "); else $teamchooser='';
$title ="$team - $league->name";
$page_header = page_header($title);


$mimg = main_logo($my_role);
$teamheader = "$teamimg $mimg";
//$roster = roster($team);
$footer = teamfooter($team);
$teamind = get_teamind($team);

$topmarkup = <<<XXX
$page_header
<body onload='setAccordian("roster_part");'>
  <div id='content'>
	<div id='is_header'>
$teamheader
	</div>
	<div class=teampanel><div class=teaminfo>Open a section to issue a new query, or to re-issue an old query</div></div>
	<div id='is_body'>
	   <div id='is_c_section'>	  
XXX;

$bottommarkup = <<<XXX
	  
	</div>
	</div>
	<div id='is_footer'>
$footer
	</div>
</div>
	</body>
XXX;

if (isset($_REQUEST['repostsearch']))
{
	global $MAX_RADIO_GROUP;

	$plugid = plugidFromReport($flavor);
	$plugin = getPluginInfo($plugid);
	$rr=mysql_fetch_object($plugin); // or crash
	if ($rr==false )
	{
		$ggg= false; $url=false; $table='notable';
	} else {
		$url = $rr->url; $table = $rr->table;
		require_once $url;
		$ggg = schema(); // must be inside $formlib
	}
	$parentind = $_REQUEST['repostsearch'];
	$atype = $_REQUEST['alerttype'];
	for ($i=0; $i<$MAX_RADIO_GROUP; $i++)
	{
		if (isset($_REQUEST["f_r_$i"]))
		{
			$fin = $_REQUEST["f_r_$i"];
			$_REQUEST[$fin] = 'on'; //
		}
	}
	if ($playerind>0) {
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
			$atext = blurb ($r,'query player');
			dosql ("insert into qtemplates (relatedind, plugid,teamind,useropenid,type,priority,text,time) value ($ind,'$plugid', '$teamind','$useropenid','$atype','$aprio','$atext',now())");
			header("Location: dw.php?qlike=$ind&plugid=$plugid&leagueind=$leagueind"); // jump directly to query page
		}
		else

		{ $qtab = "alerts";
		$atext = blurb ($r,'unspecified injury..');
		dosql ("insert into alerts (plugid,relatedind, playerind,teamind,useropenid,type,priority,text,time) value ('$plugid',$ind,'$playerind', '$teamind','$useropenid','$atype','$aprio','$atext',now())");
		}
	} else
	{
		// team based query
		dosql(generate_db_insert(-1,$parentind,$ggg,$url,$table,$_REQUEST)); // generate an insert using posted values
		$ind = isdb_insert_id();
		$result = dosql("Select * from $table where ind='$ind' "); // reread the record we just inserted
		$r=isdb_fetch_object($result);
		$useropenid = $_COOKIE['u'];
		if ($atype==-2) {

			$aprio=1; //signal special case
			$atext = blurb ($r,'query team');
			dosql ("insert into qtemplates (relatedind, plugid,teamind,useropenid,type,priority,text,time) value ($ind,'$plugid', '$teamind','$useropenid','$atype','$aprio','$atext',now())");
			header("Location: dw.php?qlike=$ind&plugid=$plugid&leagueind=$leagueind"); // jump directly to query page

		}

	}

	$markup = enumeratepluginstoredqueries($leagueind,$teamind);  //page should have new stuff
} else
if (isset($_REQUEST['plugin']))
{
	// this is a repost to put up a query form
	$pluginind = $_REQUEST['plugin'];
	$result = dosql("Select * from plugins where ind='$pluginind' ");
	$r = isdb_fetch_object($result);
	$url = $r->url;
	$table = $r->table;
	require_once $url;
	$ggg = schema(); // must be inside $formlib
	$markup = render_team_search_form('flavorgoeshere','>>Next',
	"<div class=teampanel><div class=teaminfo><b>step 1</b> - Choose all of the fields you consider important and any values you want to match</div></div>" ,$teamind,0,$ggg,$r->url,$r->table,'nextgoeshere',array());

}

// otherwise put up a standard query based on what league we are in
else $markup= enumeratepluginstoredqueries($leagueind,$teamind);

echo $topmarkup.$markup.$bottommarkup;

?>