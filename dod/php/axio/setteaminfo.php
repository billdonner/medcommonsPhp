<?php
require_once "is.inc.php";

if (isset($_POST['content']))
{
$teamind = $_POST['teamind'];
$content = mysql_real_escape_string($_POST['content']);
dosql("Update teams set teaminfo = '$content' where teamind='$teamind' ");
//header ("Location: t.php?teamind=$teamind");
echo "Successfully set teaminfo for $teamind";
exit;
}

// start here
$leagueind = $_REQUEST['leagueind'];
$teamchooser = teamchooserindquiet($leagueind,123);

$html = <<<XXX
<html>
<head><title>Set Team Info</title></head>
<body>
<form action=setteaminfo.php method=post>
choose a team from this league: $teamchooser<br/>
paste in the html you want to publish<br/> <textarea rows=20 cols=60 name=content ></textarea><br>
<input type=submit name="publish" />
</form>
</body>
 


XXX;

echo $html;













//$result = 

//if (!$result) die ("Cant set teaminfo ".mysql_error());





?>