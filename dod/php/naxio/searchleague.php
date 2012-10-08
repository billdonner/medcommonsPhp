<?php
require_once "is.inc.php";

function searchleaguepage($leagueind,$my_role,$body)
{
	$outbuf='';
	$rr = getleaguebyind($leagueind);
	$leagueimg = league_logo($rr,$my_role);
	$leaguename = $rr->name;
	$title = "League $rr->name";
	$page_header = page_header($title);
	$mimg = main_logo($my_role);
	$userpagefooter = userpagefooter();
	$xuserpagefooter=<<<XXX
<div id='linkarea'>$rr->customlinks
 <a href='l.php?leagueind=$leagueind' >league</a>&nbsp;|&nbsp;<a target='_new' href='launchsf.html?leagueind=$leagueind'  title='support on salesforce.com in new window'>support</a>
&nbsp;|&nbsp;<a href='signon.php?logout=league' title='logout from informed sports'>logout</a>
</div>
$userpagefooter
XXX;
	$topmarkup = <<<XXX
	$page_header
		<body onload='setAccordian("body_part");'>
  <div id='content'>
	<div id='is_header'>
	$mimg
	$leagueimg
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
		<p>Choose a stored query  to access the datawarehouse for  $r->label</p>
		<div  class='stored_query_form'>
		$querychooser
		</div>
	</div>
          
XXX;
}

$bottommarkup = <<<XXX
	  </div>
	  </div>
	<div id='is_footer'>
	$xuserpagefooter
	</div>
	</div>

	</body>
	</html>
XXX;

return $topmarkup.$markup.$bottommarkup;
}

// start here, should check league level credentials
$my_role=check_login('league,is','league page'); // only returns if logged in
$leagueind= $_REQUEST['leagueind'];
if ($my_role!='is') $disclaimer = "<p>Please call informed sports to add teams or players</p>"; else $disclaimer='';
$visualteamchooser=visualteamchooser($leagueind);
$body = "
<div id='queryarea'>$disclaimer<p>Choose a team to view its roster and status</p>
$visualteamchooser
<p> </p>
</div>
";
echo searchleaguepage($leagueind,$my_role,$body);
?>