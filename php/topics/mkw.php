<?php
//
// add keywords and dublin metadata to existing mcdirpages

function extract_keywords ($page)
{
	$page = strtolower($page); // make lowercase
	$page = str_replace('	',' ',$page); // change tabs to spaces
	$page = str_replace('  ',' ',$page); // change two spaces to one
	$page = str_replace("'",'"',$page); //change single quotes to dubl
	$page =  str_replace(	array(	'< ',' >',' =','= ','" ',' "'),
	array('<','>','=','=','"','"'),
	$page); // squeeze out crufty stuff
	$pos = 0; $len = strlen($page); $keywords = '';
	while ($pos<$len)
	{
		//echo "$pos $len <br>";
		$mpos = strpos ($page,'<meta ',$pos);
		if ($mpos===false) break;
		$mpos += strlen('<meta ');
		$epos = strpos ($page,'>',$mpos);
		if ($epos===false) break;
		$metatag = substr($page,$mpos,$epos-$mpos);
		$npos1 = strpos ($metatag,'name="');
		$npos2 = strpos ($metatag,'"',$npos1+strlen('name="'));
		if (($npos1!==false)&&($npos2!==false))
		{
			$npos1 += strlen('name="');
			$cpos1 = strpos ($metatag,'content="',$npos2);
			$cpos2 = strpos ($metatag, '"',$cpos1+strlen('content="'));
			if (($cpos1!==false)&&($cpos2!==false))
			{
				$cpos1 += strlen('content="');
				$nameval = substr($metatag,$npos1,$npos2-$npos1);
				$contentval = substr($metatag,$cpos1,$cpos2-$cpos1);
				if ($nameval=='keywords') $keywords .= "$contentval,";
				if ($nameval=='dc.subject.mesh') {
					// add this mesh if its not already there
					if (strpos($keywords,$contentval)===false)
					$keywords.="$contentval,";
				}
			}
		}
		//echo "$metatag <br>";
		$pos = $epos + 1;
	}
	$keywords .= 'medcommons, personal health records, phr';
	return $keywords;
}

$db = $_REQUEST['db'];
echo"<html><head><title>Keyword Extractor</title></head><body><h4>Keyword Extractor</h4>";

mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");
//get list of pages to work on
$iter=0;
$urls=array();
$query = "select * from mcdirpages";
$result = mysql_query($query) or die ($query.' '.mysql_error());
while ($r = mysql_fetch_object($result))

{
	$pageid = $r->pageid; $url = $r->url;
	$keywords = extract_keywords ($r->pagedata);
	$update = "update mcdirpages set keywords ='$keywords' where pageid='$pageid'";
	mysql_query($update) or die("Cant $update".mysql_error());
	echo "mkw:  processed $url<br>mkw:  keywords $keywords<br>";
	@ob_flush();
	flush();
	$iter++;
}
echo "mkw: all done with $iter pages";
?>