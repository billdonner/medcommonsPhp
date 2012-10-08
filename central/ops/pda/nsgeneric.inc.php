<?php
require_once  "../dbparamsmcback.inc.php";
class nsgeneric {

	private function show_entries($o,$p,$q)
	{
		$query = "SELECT * from $p";

		$result = mysql_query ($query) or die("can not query table $p - ".mysql_error());
		$count=0;
		if ($result=="") {echo "?no records in $p?"; return;}

		echo "<tr><td><b>$o<b></td></tr>";

		while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$nickname = $l['nickname'];
			$ctprot = $l['ctprot'];
			$cthost = $l['cthost'];
			$ctport= $l['ctport'];
			$ctfile = $l['ctfile'];
			$description = $l['description'];
			$status = $l['summarystatus'];
			$ss=substr($status,0,2);
			$updatetime = strtotime(substr($status,2));
			$notes = $l['notes'];
			$ipaddr = $l['ipaddr'];
			$dbconnection = $l['dbconnection'];
			$dbdatabase = $l['dbdatabase'];
			$swversion = $l['swversion'];
			$swrevision = $l['swrevision'];

			$hp = $kind.$cthost;
			if ($kind=='') $hp = $cthost; else {$hp=$kind.$dbdatabase.".".$cthost;};

			if ($ss=="ER") $hpcolor="<FONT COLOR=#ff0000>".$hp."</FONT>"; else $hpcolor = $hp;
			if ($ss=="ER") $ss="<FONT COLOR=#ff0000>".$ss."</FONT>";


			$ct = $ctprot."://".$cthost.":".$ctport.$ctfile;//wld now passed in from database
			$timenow = time();
	if (($timenow-$updatetime)>100){
				$hpcolor="<strike>$hpcolor</strike>";
				};
			$count++;
			$xx=<<<XXX
<tr><td>
<a href="$q$cthost">$hpcolor</a>
</td>
</tr>
XXX;
			echo $xx;

}
mysql_free_result($result);




	}

	function doit($title,$table,$url)
	{

		$db=$GLOBALS['DB_Database'];
		$a = $GLOBALS['DB_Connection'];
		$r = $GLOBALS['Default_Repository'];
		$srvname = $_SERVER['SERVER_NAME'];

		$srva = $_SERVER['SERVER_ADDR'];
		$srvp = $_SERVER['SERVER_PORT'];
		$gmt = gmstrftime("%b %d %Y %H:%M:%S")." GMT";
		$uri = htmlspecialchars($_SERVER ['REQUEST_URI']);
		$s1=$_SERVER["SSL_CLIENT_S_DN"];
		if ($s1!='') $s1="<tr><td><small>$s1</small></a></td></tr>";
		$s2=$_SERVER["SSL_CLIENT_I_DN"];
		if ($s2!='') $s2="<tr><td><small>$s2</small></a></td></tr>";


		//main
		// get a select list of all gatways
		$x=<<<xxx
<html><head><title>MedCommons $title</title></head>
<body>
<table border=0 width=250> <tr><td><img src="MEDcommons_logo_246x50.gif" width=246 height=50 alt='medcommons, inc.'></td></tr>
<tr><td align=left><small>$gmt </small><a href=index.html><small>Menu<small></a></td></tr>
$s1
$s2
<tr><td>
xxx;

		mysql_connect($GLOBALS['DB_Connection'],
		$GLOBALS['DB_User'],
		$GLOBALS['DB_Password']
		) or die ("can not connect to mysql");
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db");
		echo $x;// f here, we have a good database

		$this->show_entries($title,$table,$url);

		echo "</td></tr></table></body></html>";

		mysql_close();

		exit;

	}

}

?>