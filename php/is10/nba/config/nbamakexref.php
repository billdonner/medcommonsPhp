<?php

// build the aux indices
require_once "../is.inc.php";


$leagueind = 1; // just shove everyone in the single nba league for now
$result = dosql ("Select * from teams");
while ($r=mysql_fetch_object($result))
xdosql ("Replace into leagueteams set leagueind = '$leagueind', teamind = '$r->teamind' ");


$result = dosql ("Select * from players,teams where players.team=teams.name");
while ($r=mysql_fetch_object($result))
xdosql ("Replace into teamplayers set playerind = '$r->playerind', teamind = '$r->teamind' ");

?>