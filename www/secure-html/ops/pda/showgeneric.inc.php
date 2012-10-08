<?php


require_once  "../dbparamsmcback.inc.php";

class showgeneric {

	function show_generic($table, $query)
	{
		//$query = "SELECT * from gatewayprobes where (cthost = '$gw')";

		$result = mysql_query ($query) or die("can not query table $table - ".mysql_error());
		$count=0;
		if ($result=="") {echo "?no records in $table?"; return;}

		echo "<tr><td><b>$gw<b></td></tr>";

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
			//			$updatetime = strtotime($l['time']);
			$timenow = time();




			$hp = $kind.$cthost;
			if ($kind=='') $hp = $cthost; else {$hp=$kind.$dbdatabase.".".$cthost;};

			if ($ss=="ER") $hpcolor="<FONT COLOR=#ff0000>".$hp."</FONT>"; else $hpcolor = $hp;
			if ($ss=="ER") $ss="<FONT COLOR=#ff0000>".$ss."</FONT>";
			$ct = $ctprot."://".$cthost.":".$ctport;

			if (($timenow-$updatetime)>100){$ss="<strike>$ss</strike>";
			$notes="<strike>$notes</strike>";
			$ct="<strike>$ct</strike>";
			};



			$count++;
			$xx=<<<XXX
	<tr><td>
$ct
</td>
</tr><tr><td>$description</td>
</tr><tr><td><large>$ss</large>$ipaddr</td></tr><tr><td>$swversion:$swrevision</td>
<tr><td>$notes</td>
</tr>
XXX;

			echo $xx;

		}
		mysql_free_result($result);

	}


	function doit ($table, $query)
	{


		$db=$GLOBALS['DB_Database'];
		$a = $GLOBALS['DB_Connection'];
		$r = $GLOBALS['Default_Repository'];

		mysql_connect($GLOBALS['DB_Connection'],
		$GLOBALS['DB_User'],
		$GLOBALS['DB_Password']
		) or die ("can not connect to mysql");
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db");
		$this->show_generic($table,$query);

	}

	function openit($title)
	{
		// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		// HTTP/1.1
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		// HTTP/1.0
		header("Pragma: no-cache");
		$gmt = gmstrftime("%b %d %Y %H:%M:%S")." GMT";
		$uri = htmlspecialchars($_SERVER ['REQUEST_URI']);
		$s1=$_SERVER["SSL_CLIENT_S_DN"];
		if ($s1!='') $s1="<tr><td><small>$s1</small></a></td></tr>";
		$s2=$_SERVER["SSL_CLIENT_I_DN"];
		if ($s2!='') $s2="<tr><td><small>$s2</small></a></td></tr>";
		$dynamic = $_REQUEST['dynamic'];

		$metastr = ($dynamic==1)?"<meta http-equiv=refresh content=60>":"";
		$dynamiclink = ($dynamic==1)?"":" <a href=$uri&dynamic=1><tiny>dynamic</tiny></a>";
		$globe = ($dynamic==1)?"<td><img src=globe.gif></td>":"<td> </td>";
		//main
		// get a select list of all gatways
		$x=<<<xxx
<html><head><title>MedCommons $title Display</title>$metastr</head>
<body>
<table border=0 width=300> <tr><td><img src="MEDcommons_logo_246x50.gif" width=246 height=50 alt='medcommons, inc.'></td>$globe</tr>
<tr><td align=left><small>$gmt </small><a href=index.html><small>Menu<small></a>$dynamiclink</td></tr>
$s1
$s2
xxx;
		echo $x;
	}

	function closeit()
	{
		echo "</table></body></html>";

		mysql_close();
	}

}

?>