<?php
// reads the rss tables in the rss database
//

require "dbparamsidentity.inc.php"; //use this for now that we are on secure.test.medcommons.net

function newslog ($gid,$limit,$filter)
{

	//build menu to present from arg
	// get settings for how to behave
	$db=$GLOBALS['DB_Database'];
	mysql_pconnect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	mysql_select_db($db) or die ("can not connect to database $db");
	$q = "SELECT * from rssheadlines $filter";
	$q.= " order by id DESC LIMIT $limit";

	$result = mysql_query($q) or die ("can not query $q ".mysql_error());

	//echo " rows =". mysql_numrows($result);

	$out= "<table class='ccrtable ' cellspacing='0' cellpadding='0'>";

	while (true) {
		$l = mysql_fetch_object($result);
		if ($l===false) break;
		$link = $l->link;
		$description = $l->description;
		if (strlen($l->title)>42)$dots='...'; else $dots='';
		$title=substr($l->title,0,42).$dots;
		
		$pubdate = $l->pubDate;

		$out.= "
          <tr >
            <td ><a href='$link' title='$description' target='__healthnews' ><small>$title</small></a></td>
                </tr>
                ";
//<tr><td>$description</td>
		$out.= "</tr>";
	}

	$out.= "</table>";
	return $out;
}
?>