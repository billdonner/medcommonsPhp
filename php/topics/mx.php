<?php
//harvest more links
/*

$p - prefix of url to strip
$s - strip file of urls to ignore

-- the test for whether we are onsite or not is rather crude

*/

function find_skip($link,$skip)
{
	foreach ($skip as $sk)
	if (substr($link,0,strlen($sk))==$sk) return true;
	return false;
}
function never_probed($link)
{
	$q = "select url from mcdirpages where url='$link'";
	$result = mysql_query($q) or die ("$q".mysql_error());
	$count = mysql_num_rows($result);
	mysql_free_result($result);
	return ($count==0)	;
}
function set_pagedata ($link,$data)
{   $prepped = addslashes($data);
	$insert = "insert into mcdirpages set url='$link',pagedata='$prepped'";
	$result = mysql_query($insert) or die ("$insert".mysql_error());
}


// START HERE


 $db=$_REQUEST['db'];
if (isset($_REQUEST['p'])) $pre=$_REQUEST['p']; else $pre=''; $prelen=strlen($pre);

$iter = 0;

echo"<html><head><title>Harvest Links</title></head><body>";
if (!isset($_REQUEST['s'])){ $skip = array(); $s='';}
else {
	$s = $_REQUEST['s']; // skip file
	$skip = file($s); // entire file into array
}
$skcount = count($skip);
for ($i=0; $i<$skcount;$i++) {
	$skip[$i]=trim($skip[$i]);
//	echo "skipping over ".$skip[$i]."\r\n";
}

echo "File of URLs to skip: $s<br>";
echo "Prefix to remove from harvested URLs: $pre <br>";
echo "Harvested Links will be inserted into Database mcdirpages and mcdirlinks <br>";

mysql_pconnect("mysql.internal",
"medcommons",
''
) or die ("can not connect to mysql");
mysql_select_db($db) or die ("can not connect to database $db");
$more = true;
while ($more)
{
$anydone = false;
$query = "SELECT id,parentlink,link,level from mcdirlinks where probed='0'";
$result = mysql_query($query) or die('$query'.mysql_error());
while ($r=mysql_fetch_object($result))
{
	if ((substr($r->link,0,$prelen)==$pre)&& (never_probed($r->link)))
	{
		$timestart = microtime(true);
		$anydone = true;
		$file_contents = file_get_contents($r->link);
		$url = $r->link;

		$str = str_replace(array('</A>','" >','<A HREF'),array('</a>','">','<a href'),$file_contents);
		// just the "<a href= tags

		$len = strlen($str);
		$pos = 0;

		$match1 = '<a href="';
		$match1len = strlen($match1);

		$match1a = '"';
		$match1alen = strlen($match1a);

		$match2 = '">';
		$match2len = strlen($match2);

		$match3 = '</a>';
		$match3len = strlen($match3);


		while ($pos<$len)
		{
			$npos = strpos($str,$match1,$pos);
			if ($npos===false) break;

			$apos = strpos($str,$match1a,$npos+$match1len+1);
			if ($apos===false) break;

			$mpos = strpos ($str,$match2,$npos+$match1len+1);
			if ($mpos===false) break;

			$opos = strpos ($str,$match3,$mpos+$match2len+1);
			if ($opos===false) break;

			$link = trim(substr($str,$npos+$match1len,$apos-$npos-$match2len-$match1alen-6));
			$onsite = ( substr($link,0,$prelen)==$pre);
			
			$plink = ($onsite) ? substr($link,$prelen,128):substr($link,0,128); // pretty it up
			$label = trim(substr($str,$mpos+$match2len,$opos-$mpos-$match3len-$match2len+4));

//			echo "Plink: $plink Onsite: $onsite Label: $label ";
			if ((!find_skip($link,$skip))&&
			(substr($link,0,4)=='http') &&
			(substr($label,0,1)!='<') &&
			(substr($label,0,7)!='Spanish') &&
			(strlen($label)!=1)) // skip over bullshit
			{
					$label = addslashes($label); $rl = 1+$r->level; $rp = $r->id; $rk=$r->link;

					$insert = "REPLACE INTO mcdirlinks SET label='$label',link='$link',level='$rl',parentlink='$rk'";
					mysql_query($insert) or die("Cant $insert ".mysql_error());
					$id = mysql_insert_id();
					
					if (($onsite)){
						$nexturls[]=array ($id,$link);// store parent id and link for next round
						//echo " nextround $id\r\n";
					} //else echo " inserted $id \r\n";
			} //else echo " skipped \r\n";
			$pos = $opos +$match3len;
			//echo "$pos $npos $apos $opos<br>";
		}
		set_pagedata($r->link,$file_contents);
			$timeend = microtime(true);
	$elapsed = $timeend-$timestart; //float
	$elapsed = round ($elapsed,3);
	echo "mspider:  processed $url in $elapsed secs<br>";
	@ob_flush();
	flush();
}
	$update = "UPDATE mcdirlinks SET probed='1' where id='$r->id'";
	mysql_query($update) or die("$update ".mysql_error());
}
$more = $anydone;
} // end of if any urls at all
echo "mspider: all done, No More Progress Can Be Made";
?>