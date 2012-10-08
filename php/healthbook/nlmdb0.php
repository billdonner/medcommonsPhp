<?php

/* how it works

two examples:

<li>Cardiovascular Diseases see <A HREF="http://www.nlm.nih.gov/medlineplus/heartdiseases.html">Heart Diseases</a> ; <A HREF="http://www.nlm.nih.gov/medlineplus/vasculardiseases.html">Vascular Diseases</a>

<li><A HREF="http://www.nlm.nih.gov/medlineplus/carcinoidtumors.html">Carcinoid Tumors</A>


generally:

<li>[frontmatter see]<a>anchor1</a> [;<a>anchor2</a>] [;<a>anchor3</a>]</li>

writes multiple rows in db

[frontmatter], anchor1
[frontmatter], anchor2

*/
require_once "healthbook.inc.php";


$GLOBALS['pat1']='<A HREF="http://www.nlm.nih.gov/medlineplus/';
$GLOBALS['pat2']='see <A HREF="http://www.nlm.nih.gov/medlineplus/';
$GLOBALS['pat3']='</a> ; <A HREF="http://www.nlm.nih.gov/medlineplus/';
$GLOBALS['pat4']='</li>';
$GLOBALS['pat5']='<li>';
$GLOBALS['pat6']='</a>';
$GLOBALS['pat7']='</A>';

for ($i=1;$i<7;$i++)
$GLOBALS['pat'.$i.'_len']=
strlen($GLOBALS['pat'.$i]);

function pparse_url($s)
{
	$pos1 = strpos($s,'href=');
	$pos2 = strpos($s,'>',$pos1);
	return substr($s,$pos1+9,$pos2-$pos1-10);
}
function shred3($multi, $s)
{
	$multiple = $multi?" MULTIPLE ":'';
	// just the <li> entries for now
	$pos = 0; $len = strlen($s); $outstr="'<select name='NLM_Complete_Subject_List' $multiple>\r\n"; $counter=0;$topic=0;
	while ($pos<$len)
	{
		$pos1 = strpos ($s,$GLOBALS['pat5'],$pos);
		if ($pos1===false) break;

		$pos2 = strpos ($s,$GLOBALS['pat4'],$pos1);
		if ($pos2===false) break;
		//$outstr.=
		$counter++;
		$xxx = decipher(substr($s,$pos1+$GLOBALS['pat5_len'],$pos2-$pos1-$GLOBALS['pat4_len']-$GLOBALS['pat5_len']));
		$pos3=strpos($xxx,$GLOBALS['pat2']);
		if ($pos3!==false){
			$yyy = (trim(substr($xxx,0,$pos3)));
			$xxx = substr($xxx,$pos3);
			$vals = explode(';',$xxx);
			foreach ( $vals as $v) {
				$v=strip_tags($v);
				$v =  trim(str_replace('see','',$v));
				$v = str_replace(' ','-',$v);
				$ttop=$GLOBALS["__$v"];
				//$outstr.= "<option value='$ttop'>$yyy ==> $v ($ttop)</option>\r\n ";
				$q = "SELECT nlmxtra from  nlmtab where ord='$ttop' ";
				$result = mysql_query($q) or die("Cant $q ".mysql_error());
				$r = mysql_fetch_array($result);
				$yyy.="   ".$r[0];
				$yyy = mysql_escape_string($yyy);
				
				$q = "UPDATE nlmtab set nlmxtra='$yyy' where ord='$ttop' ";
				mysql_query($q) or die("Cant $q ".mysql_error());
				//echo "$q <br/>";
			}
		}
		//echo "subject: $counter $insert ".$xxx."<br/>\r\n";
		$pos = $pos2+$GLOBALS['pat5_len'];
	}
	return;
}
function shred($s)
{
	// just the <li> entries for now
	$pos = 0; $len = strlen($s); $outstr='<select name="NLM_Categories">\r\n'; $counter=0;$topic=0;
	while ($pos<$len)
	{
		$pos1 = strpos ($s,$GLOBALS['pat5'],$pos);
		if ($pos1===false) break;

		$pos2 = strpos ($s,$GLOBALS['pat4'],$pos1);
		if ($pos2===false) break;
		//$outstr.=
		$counter++;
		$xxx = decipher(substr($s,$pos1+$GLOBALS['pat5_len'],$pos2-$pos1-$GLOBALS['pat4_len']-$GLOBALS['pat5_len']));
		if (strpos($xxx,$GLOBALS['pat2'])===false){
			$url = $xxx;
			$val =trim(strip_tags($xxx));  // not a see...
			$topic++;  $outstr.= "<option value='$topic'>$val</option>\r\n ";
			$v = str_replace(' ','-',$val);

			$GLOBALS["__$v"] = $topic;  // set up cross reference
			//echo "Setting $topic $v as global<br/>";
			//$now = time();
			$val = mysql_escape_string($val);
			$url = pparse_url($url);
			$q="Replace into nlmtab set nlmtopic='$val',nlmurl='$url',time=NOW(),nlmxtra=''";
			mysql_query($q) or die ("Cant $q ".mysql_error());

		}

		//echo "subject: $counter $insert ".$xxx."<br/>\r\n";
		$pos = $pos2+$GLOBALS['pat5_len'];
	}
	$outstr.="</select>";
	echo "Table nlmtab is now setup.<br/>Here's a select statement you can put in your application:  ".$outstr;
	return;
}

function decipher($s)
{
	return $s;

}
echo "All NLM Topics written to nlmtab table<br/>";
connect_db();
$page = file_get_contents("http://www.nlm.nih.gov/medlineplus/all_healthtopics.html");
if (strlen($page)>1000) {

	$pos1 = strpos($page,'<TABLE  CELLSPACING="0" CELLPADDING="0" BORDER="0">'); // skip early crap
	$pos2 = strpos($page,'  <!-- end content -->',$pos1); // don't go too far
	//echo "Shred:  start $pos1 end $pos2<br/>";
	shred(substr($page,$pos1,$pos2-$pos1));

}
else die ("Coud not get all health topics page from NLM");
echo "<br/>All NLM References added as extra data in topics<br/>";
$page = file_get_contents("http://www.nlm.nih.gov/medlineplus/all_healthtopics.html");
if (strlen($page)>1000) {

	$pos1 = strpos($page,'<TABLE  CELLSPACING="0" CELLPADDING="0" BORDER="0">'); // skip early crap
	$pos2 = strpos($page,'  <!-- end content -->',$pos1); // don't go too far
	//echo "Shred:  start $pos1 end $pos2<br/>";
	shred3(false,substr($page,$pos1,$pos2-$pos1));

}
else die ("Coud not get all health topics page from NLM");