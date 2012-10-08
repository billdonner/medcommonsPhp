<?php
require_once "is.inc.php";


function teampage($team,$flavor,$my_role)
{
	$teamind = get_teamind($team);
	$league = getleague($team);
	$teamimg = team_logo($teamind,$my_role);
	$leagueind = $league->ind;

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
	<div id='is_body'>
	   <div id='is_c_section'>	  
XXX;

	$plugins = getAllPluginInfo($leagueind);
	$markup = '';

	while ($r = mysql_fetch_object($plugins))
	{
		$querychooser = querychooser($leagueind,$r->ind,'class_querychooser');
		$nc = $r->name.'_content';
		$markup .= <<<XXX
        <div id='dw_part_$r->name' class='amenu' onclick='toggle("dw_part_$r->name")'>$r->label</div>
	<div  style='display: none;' id='dw_part_$nc' class='acontent'>	
		<p>Choose a stored query  to access $r->label</p>
		<div  class='stored_query_form'>
		$querychooser
		</div>
	</div>
          </div>
XXX;
}


$bottommarkup = <<<XXX
	  </div>

	<div id='is_footer'>
$footer
	</div>
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