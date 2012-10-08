<?php
// NBA SPIDER by Bill Donner

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
// tidbits needed to decode
$istr1 = 'var imageLocation = "';
$istr2 = '";';
$istr1l = strlen($istr1);
$istr2l = strlen($istr2);
$pstr1 = '<div class="playerInfoStatsPlayerInfoBorders">';
$pstr2 = '<span class="playerInfoValuePlayerInfoBorders">';
$pstr3 = '</span>';
$pstr1l = strlen($pstr1);
$pstr2l = strlen($pstr2);
$pstr3l = strlen($pstr3);
$base = "http://www.nba.com";
$turl1 = "";
$turl2 = "/roster/index.html";
$drafted = 'var site = "draft2007";';



echo "nbaspider.php: reading nba sites not writing player healthurls on ".$GLOBALS['appliance']."<br/>";

foreach ($GLOBALS['teams'] as $team){

	$teamname = $team[0];
	$pth = '/'.$teamname.'/';
	$url = $base.$turl1.$pth.$turl2;
	$hp = $base.$pth;
	$img = "nba/images/".$team[2];
	$sched = $hp.'schedule/';
	$rss = $hp.'rss.xml';
	dosql ("REPLACE INTO `teams` (
`homepageurl` ,
`schedurl` ,
`newsurl` ,
`logourl` ,
`name`
)
VALUES (
'$hp', '$sched', '$rss', '$img', '$teamname'
)");



	echo "<img src='$img' alt='$img'>&nbsp;&nbsp;&nbsp;";
	//a target='_new' href='$url'>".$team[1]."</a></h2>";
	//echo "<small>&nbsp;schedule:<a target='_new' href='$sched'>team schedule</a>";
	//echo "&nbsp;&nbsp;&nbsp;&nbsp;rss:<a target='_new' href='$rss'>team news</a></small></span><br/>";
	$input = @file_get_contents($url) or
	die("<br>Could not access team site: $url<br/>");
	$pos = strpos ($input,'&nbsp;2007-08 Roster');
	if ($pos===false) die ("<br>Cant find roster from $url<br/>");
	$inl = strlen($input);
	$input = substr($input,$pos+1);
	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
	if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
		foreach($matches as $match) {
			# $match[2] = link address
			# $match[3] = link text
			if (strpos($match[2],'/playerfile/')!==false)
			{
				//echo $match[0].' '.$match[1].' '.$match[2].' '.$match[3].'<br/>';
				$playername = clean($match[3]);
				$playerurl = $base.$match[2];
				//echo "&nbsp;&nbsp;&nbsp;&nbsp <a target='_new' href='$playerurl'>".$match[3]."</a><br/>";
				$playerfile=@file_get_contents($playerurl);
				if ($playerfile===false) echo("<br/>Could not open player site: 
				<a href='$playerurl'>$playerurl</a><br/>");
				else {
					$imgurl = false; //assume we dont find any of these properties
					$height = false;
					$weight = false;
					$years = false;
					$college = false;
					//$pos = strpos ($playerfile,$drafted);
					//if ($pos !== false)
					if (strlen($playerfile)<1024) // forget tiny files, they are redirected draft picks
					//echo "&nbsp;&nbsp;&nbsp;&nbsp;is a draft pick<br/>";
					$found = false;
					else {
						//see if we can nab an image file<br>
						$found = false;
						$pos = strpos ($playerfile,$istr1);
						if ($pos!==false){
							$pos1 = strpos ($playerfile,$istr2,$pos+$istr1l);
							if ($pos1!==false){
								$val = substr ($playerfile,$pos+$istr1l,$pos1-$pos-$istr1l);
								$imgurl = $base.$val;
							}
						}
						if ($found===false) $found = array();
						$pos = 0;
						$len = strlen ($playerfile);
						//echo "pos $pos len $len <br/>";
						while ($pos<$len) {
							$pos1 = strpos ($playerfile,$pstr1,$pos);
							if ($pos1===false) break;
							$pos2 = strpos ($playerfile,$pstr2, $pos1+$pstr1l);
							if ($pos2===false) break;
							$pos3 = strpos ($playerfile,$pstr3, $pos2+$pstr2l);
							if ($pos3===false) break;
							$prop = substr($playerfile,$pos1+$pstr1l+1,$pos2-$pos1-$pstr1l-1);
							$val =  substr ($playerfile, $pos2+$pstr2l,$pos3-$pos2-$pstr2l);
							$found []=array(trim($prop),trim($val));
							$pos = $pos3+$pstr3l;
			
						}
						if (count($found)==0) $status = 'draft-pick'; 
						else {
							foreach ($found as $foundone)
							{  $prop = trim($foundone[0]); $val = trim($foundone[1]);
							$p = strpos($val,'&nbsp');
							if ($p) $val = substr($val,0,$p);
							switch ($prop)
							{
								case 'Born:': { $born=clean($val); break;}
								case 'Height:':   { $height=clean1($val); break;}
								case 'Weight:': { $weight=clean1($val); break;}
								case 'College :': // note space, the nba has a bug
								{ $college=clean($val); echo "college-$college<br/>"; break;}
								case 'Years Pro:': { $years = clean1($val); break;}
								default:
							}
							//echo "&nbsp;&nbsp;&nbsp;&nbsp;".$prop." ".$val."<br/>";
							}
							$status = 'active';
							//$college= substr(clean($playerfile),0,255);
						}
						//
						$healthurl = "noHealthURL";
						dosql ("REPLACE INTO `players` (
`name` ,
`team` ,
`born` ,
`homepageurl` ,
`imageurl` ,

`college` ,
`years`,
`status`,
`healthurl`

)
VALUES (
'$playername' ,
'$teamname' ,
'$born',
'$playerurl' ,
'$imgurl' ,

'$college' ,
'$years',
'$status',
'$healthurl'
)");

					}
				}
			}
		}
	}
}
 ?>