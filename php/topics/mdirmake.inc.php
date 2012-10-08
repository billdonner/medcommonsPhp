<?php
function issubcloned($plink)
{
	// see if any of the subordinate pages have clones
	$iquery = "select * from mcdirlinks where parentlink='$plink'";
	$iresult = mysql_query($iquery) or die ($iquery.' '.mysql_error());

	while ($rr = mysql_fetch_object($iresult))
	{
		$ilabel = $rr->label; $ilink = $rr->link; $ilink=essence($ilink);

		$labelslashes = addslashes($ilabel);
		$qqquery = "select count(*) from clonedpages where roottopic='$labelslashes'";
		$qqqresult = mysql_query($qqquery) or die ($qqquery.' '.mysql_error());
		$qqqcount = mysql_fetch_array($qqqresult);
		$clones=$qqqcount[0]; // desperate for the count
		mysql_free_result($qqqresult);
		if ($clones>0) return true;


	}

	mysql_free_result($iresult);
	return false;
}

function rcounts($max)
{
	$clones = rand(0,$max);
	$vetted = rand(0,$clones/3);
	return array($clones,$vetted);
}
function essence($s)
{
	$pre = '/interests/';
	$pos = strrpos($s,'/'); // find last
	return $pre.substr($s,$pos+1); // just take the rest as the page
}
// starts here
function mdirmake()
{
	require_once 'mlib.inc.php';
	$db = 'mcx'; //$_REQUEST['db'];
	mysql_pconnect("mysql.internal",
	"medcommons",
	''
	) or die ("can not connect to mysql");
	mysql_select_db($db) or die ("can not connect to database $db");
	$topcat=0;
	$count=0;
	$maxcat = 0;
	$query = "select * from mcdirlinks where level='1' order by label";
	$result = mysql_query($query) or die ($query.' '.mysql_error());
	$out =<<<XXX
<script language='JavaScript' >
function tog(x) {
if (document.getElementById(x).style.display== 'none')
document.getElementById(x).style.display=  'block'; else
document.getElementById(x).style.display=  'none';
return false;
}
</script>
<div id='nav'><table><tr valign=top>
XXX;

	while ($r = mysql_fetch_object($result))

	{
		if ( ( $topcat % 15) == 0)
		{
			if ($count!=0) $out.='</ul></td>';
			$out.="<td width=280px><ul class='top'>\r\n";
		}
		$topcat++;

		$label = $r->label; $plink= $r->link; $link=essence($plink);// now find the count
		$qquery = "select count(*) from mcdirlinks where parentlink='$plink'";
		$qresult = mysql_query($qquery) or die ($qquery.' '.mysql_error());
		$qcount = mysql_fetch_array($qresult);
		$qcount=$qcount[0]; // desperate for the count
		mysql_free_result($qresult);
		// get clone counts, etc
		$labelslashes = addslashes($label);
		$qqquery = "select count(*) from clonedpages where roottopic='$labelslashes'";
		$qqqresult = mysql_query($qqquery) or die ($qqquery.' '.mysql_error());
		$qqqcount = mysql_fetch_array($qqqresult);
		$qqqcount=$qqqcount[0]; // desperate for the count
		mysql_free_result($qqqresult);
		list($clones,$vetted)=array($qqqcount,0); //rcounts(999);
		$label = stripslashes($label);
		$mlinks ='';
		//if ($vetted!=0) $mlinks.= "&nbsp;<a class='vetted' href='/interests/iclones.php?i=v&a=$label'>$vetted</a>";
		//if ($clones!=0) $mlinks.= "&nbsp;<a class='clones' href='/interests/iclones.php?a=$label'>$clones</a>";
		if ($clones>0) $aclass='clones'; else $aclass='tiny';
		$color = 'bluecycle.gif';
		if (issubcloned($plink)) $color = 'redcycle.gif';
		$out.= <<<XXX
	<li id='cat$topcat' >
	<a  href='#' onclick="tog('sub$topcat');"><img  src='../images/$color' /></a>&nbsp;<a href='$link' class='$aclass' >$label</a>
	                    </li>\r\n
XXX;
		$count++;
		$iquery = "select * from mcdirlinks where parentlink='$plink' order by label";
		$iresult = mysql_query($iquery) or die ($iquery.' '.mysql_error());
		$thiscat = 0;
		$out .="<ul class='sub' style='display: none;' id='sub$topcat'>\r\n";

		while ($rr = mysql_fetch_object($iresult))
		{
			$ilabel = $rr->label; $ilink = $rr->link; $ilink=essence($ilink);
			list($subclones,$subvetted)=rcounts($vetted);
			//$mlinks = "&nbsp;<a class='vetted' href='/interests/iclones.php?i=v&a=$ilabel'>$subvetted</a>&nbsp;
			//   <a class='clones' href='/interests/iclones.php?a=$ilabel'>$subclones</a>";
			$mlinks = '';
			$labelslashes = addslashes($ilabel);
			$qqquery = "select count(*) from clonedpages where roottopic='$labelslashes'";
			$qqqresult = mysql_query($qqquery) or die ($qqquery.' '.mysql_error());
			$qqqcount = mysql_fetch_array($qqqresult);
			$clones=$qqqcount[0]; // desperate for the count
			mysql_free_result($qqqresult);
			if ($clones>0) $aclass='clones'; else $aclass='tiny';
			$out.= "<li id='cat$topcat-$thiscat'><a class='$aclass'  href='$ilink'>$ilabel</a>$mlinks</li>\r\n";
			$count++; $thiscat++;

		}
		$out .="</ul>\r\n";
		mysql_free_result($iresult);
		if ($thiscat>$maxcat) { $maxcat = $thiscat; $maxtopic=$label; }
	}
	$out .='</ul></td>';
	$out .='</tr></table></div></body></html>';
	mysql_free_result($result);
	return $out;
}
?>