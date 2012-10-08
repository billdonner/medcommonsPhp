<?php
/**
todir query display main driver
**/

require_once "dbparamsidentity.inc.php";
require_once "todirargs.inc.php";  // get args

//main
 mysql_connect($GLOBALS['DB_Connection'],
    $GLOBALS['DB_User'],
    $GLOBALS['DB_Password']
    ) or die ("can not connect to mysql");
    $db = $GLOBALS['DB_Database'];
    mysql_select_db($db) or die ("can not connect to database $db");

// the limit parameter is the only display parameter, the rest are all about query
if ($limit=='') $limit=20; else if ($limit>20) $limit=20;
// build the query string based on supplied args
require_once "todirwhere.inc.php";		 
// build the body of the content

require_once "todircontent.inc.php";

$synch = time();
// make a html header and insert the first round of content in the body
{ 
	//simple display, no ajax, one shot
$body = <<<XXX
<html><head><title>ToDir Query Results</title>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<style type="text/css" media="screen">
.newrow {color: red; background: #FFF;}
h2, h3 {margin: 0; border: 1px solid gray;}
h2 {border-width: 0 0 0 1px; padding: 0 0 0 0.25em}
h3 {border-width: 1px 1px 0 0; padding: 0.1em 0.33em;}
table {width: 100%;}
th, td {text-align: right; padding: 0 0.5em;
  border-bottom: 1px solid #DDD;}
td {font: small Verdana, "Andale Mono", Courier, "Courier New", 
  monospace;}
thead th {vertical-align: bottom; border: 1px solid gray;
  border-width: 0 1px 1px 0;
  white-space: normal;}
th {border-right: 1px solid gray; border-bottom-style: dotted;
  white-space: nowrap;}
td {letter-spacing: -1px;}
td.profit {background: #CEC; border-bottom-color: white;
  border-right: 1px solid gray;}
td.neg {background: #FF3; color: red;}
tr.totals td {font-weight: bold; border-bottom: 1px solid gray;}
tr.totals td.profit {border: 1px solid black;}
tr.totals th {border-bottom-style: solid;}
</style>
<style type="text/css" >
table tr.odd *,tr.oddnew * {background: #EEE;}
tr.odd *, tr.even * {border-bottom: 1px solid #EEE;}
tr.oddnew *, tr.evennew * {color: red; border-bottom: 1px solid #EEE;}
td {border-right: 1px solid #CCC;}
td.profit, td.neg {color: #000; background: #FFF;}
td.profit {font-weight: bold;}
td.neg {font-style: italic;}
tr.totals * {border-top: 1px solid gray;}
tr.totals th {border-bottom: none; text-transform: uppercase;}
</style>
</head>

<body style='margin: 0 20px 0 20px;' >
<span><img src=$logo alt=$logo />$ti</span>
<div id="content"> 
                        $content
</div> 
</body></html>
XXX;

}// end of first time paint
echo $body;

?>