<?php

// make a healthurl for each player in the players table as dictated by the appliances global


require_once '../is.inc.php';



$GLOBALS['db_connected'] = false;

$GLOBALS['teams'] =array(
array('hawks','Atlanta Hawks','atlanta.gif'),
array('celtics','Boston Celtics','boston.gif'),
array('bobcats','Charlotte Bobcats','charlotte.gif'),
array('bulls','Chicago Bulls','chicago.gif'),
array('cavaliers','Cleveland Cavaliers','cleveland.gif'),
array('mavericks','Dallas Mavericks','dallas.gif'),
array('nuggets','Denver Nuggets','denver.gif'),
array('pistons','Detroit Pistons','detroit.gif'),
array('warriors','Golden State Warriors','goldenstate.gif'),
array('rockets','Houston Rockets','houston.gif'),
array('pacers','Indiana Pacers','indiana.gif'),
array('clippers','LA Clippers','laclippers.gif'),
array('lakers','LA Lakers','lalakers.gif'),
array('grizzlies','Memphis Grizzlies','memphis.gif'),
array('heat','Miami Heat','miami.gif'),
array('bucks','Milwaukee Bucks','milwaukee.gif'),
array('timberwolves','Minnesota Timberwolves','minnesota.gif'),
array('nets','New Jersey Nets','newjersey.gif'),
array('hornets','New Orleans Hornets','neworleans.gif'),
array('knicks','New York Knicks','newyork.gif'),
array('magic','Orlando Magic','orlando.gif'),
array('sixers','Philadelphia 76ers','philadelphia.gif'),
array('suns','Phoenix Suns','phoenix.gif'),
array('blazers','Portland Trail Blazers','portland.gif'),
array('kings','Sacramento Kings','sacremento.gif'),
array('spurs','San Antonio Spurs','sanantonio.gif'),
array('sonics','Seattle SuperSonics','seattle.gif'),
array('raptors','Toronto Raptors','toronto.gif'),
array('jazz','Utah Jazz','utah.gif'),
array('wizards','Washington Wizards','washington.gif'));

function make_healthurl ($r2)
{
	// one row per player
	$mcid = $GLOBALS['appliance_group'];
	if ($mcid===false) die ("You must be logged on to execute this utility; your account will be granted access to all of the accounts you are creating");
	// split the phrase by any number of commas or space characters,
	// which include " ", \r, \t, \n and \f
	$name = preg_split("/[\s,]+/", $r2->name);
	$fn = urlencode($name[0]);
	$ln = urlencode($name[1]); $sex='male';
	$born = urlencode($r2->born);

	$remoteurl = $GLOBALS['appliance']."/router/NewPatient.action?familyName=$ln&givenName=$fn&dateOfBirth=$born&sponsorAccountId=$mcid&sex=$sex";
	echo "creating $fn $ln $born<br/>";
	$file = file_get_contents($remoteurl);
	//parse the return looking for mcid
	//echo $file;
	$m = "{status:'ok',patientMedCommonsId:'"; $ml = strlen($m);
	$pos1 = strpos($file,$m);
	$pos2 = strpos ($file,"',",$pos1);
	if ($pos2>$pos1)
	return $GLOBALS['appliance'].substr($file,$pos1+$ml,16);//$pos2-$pos1-$ml);
	else
	return false;
}

// only make hurls if we dont already have one
echo "nbamakehurls.php: creating new medcommons accounts on ".$GLOBALS['appliance']."<br/>";
$lc  = count($GLOBALS['teams']);
$result = dosql ("SELECT * from players ");

while ($r2 = mysql_fetch_object($result))
{     // only do it if blank
	$hurl = trim($r2->healthurl);
	if ($hurl=='') {
		$hurl = make_healthurl($r2);

		if (!$hurl) echo "cant make healthurl<br/>"; else
		{
			$name = clean($r2->name);
			$team = clean($r2->team);
			dosql("update players  set healthurl = '$hurl' where name='$name' and team = '$team'");
		}
	}
}
?>