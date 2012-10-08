<?php

require_once "dbparamsmcextio.inc.php";
function blurb ($link,$tooltip,$text)
{ return <<<XXX
<a href="$link" onmouseover="this.T_WIDTH=200;this.T_FONTCOLOR='#003399'; return escape('$tooltip')">$text</a>
XXX;
}
function safechars ($x)
{
	$avoid = array("'", ".", '"', "\\","=",":",")","\r","\n");
	return str_replace($avoid, "*", $x);
}
function tooltip ($tooltip,$text)
{
	$tooltip = safechars ($tooltip);
	$text = safechars ($text);

	return <<<XXX
<a href = "#"; onmouseover="this.T_WIDTH=200;this.T_FONTCOLOR='#003399'; return escape('$tooltip')">$text</a>
XXX;
}


function email_extract ($t,$field,$timefield)
{
	// get  all of the email addresses out of a table
	//get standard parameters
	$limit = $_REQUEST['limit'];
	if ($limit=="") $limit=2000;
	$filter = $_REQUEST['filter'];

	//connect to database

	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or xmlend ("can not connect to mysql");

	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");

	if ($filter!="") $f = "Filter $filter"; else $f=''; // set up filter header
	$query = "SELECT  $field,$timefield FROM $t LIMIT $limit";

	//build custom query
	$result = mysql_query ($query) or die ("can not query $t - ".mysql_error());
	$count = 0;
	if ($result!="") {
		// the while statement is generic
		while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$lf=$l[$field];
			if (($lf!="")&&(strpos($lf,',')==false))
			if (!isset($emails[$lf]))	$emails[$lf]=array($l[$timefield],$l[$timefield]);//new entry
			else {//get newest
			if ($l[$timefield]>$emails[$lf][1]) $emails[$lf][1]=$l[$timefield];
			if ($l[$timefield]<=$emails[$lf][0]) $emails[$lf][0]=$l[$timefield];

			$count++;}
		}
		mysql_free_result($result);
	}
	return $emails;
}


// main starts here
/*
$content = "<p><h2>These parties have been sent emails from CXP or Medcommons:</h2><br>".
implode('<br>',email_extract("emailstatus","rcvremail"))."</p>&nbsp;".
"<p><h2>These parties have downloaded the Firefox extension:</h2><br>".
implode('<br>',email_extract("downloaders","email"))."</p>&nbsp;".
"<p><h2>These parties have written to the CCR blog:</h2><br>".
implode('<br>',email_extract("cxpproblems","email"))."</p>";
*/

$rcvd = email_extract("emailstatus","rcvremail","time");
foreach ($rcvd  as $r=>$value) { $giant[$r]['rcvd'] = $value;}

$down = email_extract("downloaders","email","time");
foreach ($down as $d=>$value) { $giant[$d]['down'] = $value;}
$blog=	email_extract("cxpproblems","email","timestamp");
foreach ($blog as $b=>$value) { $giant[$b]['blog'] = $value;}

//
// now check out the entire giant array
//
$out = "<table border=1><tr>
<th>   email address   </th><th>  1st notification   </th><th>  last notification   </th>
<th>   1st downloaded   </th><th>   last downloaded   </th>
<th>  1st wrote blog   </th><th>   last wrote blog   </th></tr>";
$gkeys = (array_keys($giant));
sort($gkeys);
foreach ($gkeys as $key)
{ $r=$giant[$key]['rcvd'];
$d=$giant[$key]['down'];
$b=$giant[$key]['blog'];

$out.= "<tr><td>$key</td><td>".$r[0]."</td><td>".$r[1]."</td><td>".$d[0]."</td><td>".$d[1]."</td><td>".
$b[0]."</td><td>".$b[1]."</td></tr>";

}
$out .= "</table>";

$x=<<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>MedCommons WhoIs Interested?</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="icon" href="../favicon.gif" type="image/gif">
<link rel="shortcut icon" href="../favicon.gif" type="image/gif">
<link href="main.css" rel="stylesheet" type="text/css">
</head>
<body> 


<!-- Begin Page Content //-->


 
<div id="content"> 
  <h1>Who Is Interested in MedCommons?</h1> 
  $out
</div> 



<!-- End Page Content //--> 
<div id="footer">&copy; 2006 MedCommons all rights reserved.&nbsp;&nbsp;&nbsp;page last modified:&nbsp; 
  <script type="text/javascript"> document.write(document.lastModified); </script> 
</div> 

<p class=MsoNormal>&nbsp;</p>
<p class=MsoNormal>&nbsp;</p>
</div>
</body>
</html>
XXX;

echo $x;
?>