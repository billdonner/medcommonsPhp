<?php
require_once "is.inc.php";

function leaguepage($leagueind,$my_role,$body)
{
	$outbuf='';
	$rr = getleaguebyind($leagueind);
	$leagueimg = league_logo($rr,$my_role);
	$leaguename = $rr->name;
	$title = "League $rr->name";
	$page_header = page_header($title);
	$mimg = main_logo($my_role);
	$leagueinfo = leagueinfo($leaguename);
	$userpagefooter = userpagefooter();
	$xuserpagefooter=<<<XXX
<div id='linkarea'>$rr->customlinks
<a href='search.php?leagueind=$leagueind' >search</a>&nbsp;|&nbsp; <a href='l.php?leagueind=$leagueind' >league</a>&nbsp;|&nbsp;<a target='_new' href='launchsf.html?leagueind=$leagueind'  title='support on salesforce.com in new window'>support</a>
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
	$leagueinfo
	<div id='is_body'>
	   <div id='is_c_section'>
	   <div id='body_part' class='amenu' onclick='toggle("body_part")'>$leaguename Administration</div>
	<div  style='display: block;' id='body_part_content' class='acontent'>	
$body
	</div>

XXX;
	$markup = '';

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
echo leaguepage($leagueind,$my_role,$body);
?>