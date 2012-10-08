<?php
// NBA Make Players  by Bill Donner

// Copies any prototype players into the Players table for each and every team
/*
  `name` varchar(255) NOT NULL,
  `team` varchar(255) NOT NULL,
  `born` varchar(255) NOT NULL,
  `homepageurl` varchar(255) NOT NULL,
  `imageurl` varchar(255) NOT NULL,
  `healthurl` varchar(255) NOT NULL,
  `height` varchar(255) NOT NULL,
  `weight` varchar(255) NOT NULL,
  `college` varchar(255) NOT NULL,
  `years` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `playerid` smallint(6) NOT NULL auto_increment,
  */

  
require "../is.inc.php";

echo "nbamakeplayers.php: reading protoplayers table  writing teamforce  players database on ".$GLOBALS['appliance']."<br/>";

foreach ($GLOBALS['teams'] as $team){

	$teamname = $team[0];
	$result = dosql ("SELECT * FROM protoplayers");
	while ($r=mysql_fetch_object($result))
	{
		$healthurl = "noHealthURL";
		dosql ("REPLACE INTO payers (
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
'$r->name-$teamname' ,
'$teamname' ,
'$r->born',
'$r->homepageurl' ,
'$r->imageurl' ,
'$r->college' ,
'$r->years',
'$r->status',
'$r->healthurl'
)");
	}

}

 ?>