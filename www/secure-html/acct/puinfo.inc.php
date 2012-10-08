<?php

function puemit($s)
{
	$GLOBALS['uinfo'].=$s;
}
function xv ($a,$tag,$pre)
{
	puemit("<tr><td>$pre</td>");
	foreach ($a as $p)
	{  $value = $p->$tag;
	$pnum = $p->personanum;
	//    table|field|accid|id
	puemit("<td class='inputfield'><span id='personas|$tag|p|$pnum' class='editText'>$value</span></td>");
	}
	puemit('</tr>');
}

function xg ($a,$tag,$pre)
{
	puemit("<tr><td>$pre</td>");
	foreach ($a as $p)
	{
		$where = $p->$tag;
		$pnum = $p->personanum;
		$link=<<<XXX
	<a  onclick="return personapopup('personainfo.php?id=$pnum');" href='personainfo.php?id=$pnum' ><b>Persona $pnum</b></a> &nbsp;
	<a  onclick="return alert('Do you really want to delete this persona?');" href='personadelete.php?id=$pnum' >
	<small>remove</small></a>
XXX;
		puemit("<td>$link</td>");
	}
	puemit('</tr>');
}
function xh ($a,$tag,$pre)
{
	puemit("<tr><td>$pre</td>");
	foreach ($a as $p)
	{
		$value = $p->$tag;
		$pnum = $p->personanum;

		puemit("<td><span id='personas|$tag|p|$pnum||htag' class='editText'>$value</span></td>");
	}
	puemit('</tr>');
}
function xj ($a,$tag,$pre)
{
	puemit("<tr><td>$pre</td>");
	foreach ($a as $p)
	{
		$value = $p->$tag;
		$pnum = $p->personanum;

		puemit("<td class='checkboxfield'><span id='personas|$tag|p|$pnum||btag' class='checkboxText'>$value</span></td>");
	}
	puemit('</tr>');
}
function xall($p,$maintag,$label)
{
	puemit("<tr><td>$label</td>");
	$etag = 'expose'.$maintag;
	$itag = 'inherit'.$maintag;


	foreach ($p as $a)
	{
		$pnum = $a->personanum;
		//$eval = $a->$etag;
		//$ival = $a->$itag;
		$val =  $a->$maintag;
		// as it turns out, ids might not be unique unless we put some extra struff at
		puemit("<td>".

		//	<span id='personas|$etag|p|$pnum||etag' class='checkboxText'>$eval</span></td><td>
		//	<span id='personas|$itag|p|$pnum||itag' class='checkboxText'>$ival</span></td><td class='inputfield'>
		"<span id='personas|$maintag|p|$pnum' class='editText'>$val</span></td>
	");
	}
	puemit('</tr>');
}
function makename($a,$b,$c) { return $a." ".$b." ".$c; }

function makeaddr ($a,$b,$c,$d,$e,$f) { return $a." ".$b." ".$c." ".$d." ".$e." ".$f; }
function puinfo($accid,$glevel)
{
	//	echo "enter puinfo ";
	$GLOBALS['glevel']=$glevel;
	$GLOBALS['uinfo']=''; // someday I'll figure out how global variables work in php

	// load up everthing into objects
	$p = array();
	$q = "SELECT * from personas where accid='$accid' and isactive=1 order by personanum";
	$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
	while ($x = mysql_fetch_object($result))
	{
		$p[]=$x;

	}
	mysql_free_result($result);
	$q = "SELECT * from users where mcid='$accid'";
	$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
	$u = mysql_fetch_object($result);
	mysql_free_result($result);
	$q = "SELECT * from addresses where mcid='$accid'";
	$result = mysql_query($q) or die ("Can not select $q ".mysql_error());
	$a = mysql_fetch_object($result);
	$rc = mysql_numrows($result);
	mysql_free_result($result);
	if ($rc==0)
	{// add an address record
		$q="insert into addresses set mcid = '$accid'";
		mysql_query($q) or die ("Can not insert $q ".mysql_error());
	}
	//
	// analyze what we have gotten
	//


	$pcount = count($p);
	if ($pcount == 0) puemit ("<small><b>no personas defined</b>&nbsp;</small> ");
	if ($pcount <3) puemit ("<small><a href=personanew.php>add a persona</a></small><p>");
	if ($pcount>0)  {
		puemit("<table class=trackertable>\r\n");
		xg ($p, 'personagif','');
		xv($p,'persona','');
		//	xv ($p,'personanum','<small>used interally by medcommons</small>');
		xall($p,'myid','public Id');
		xall($p,'phone','phone');
		xall($p,'email','email');
		xall($p,'name','name');
		xall($p,'address','addr');
		xall($p,'dob','dob');
		xall($p,'sex','sex');

		xh ($p, 'ccrsectionconsents','ccr section consents');
		xh ($p, 'qualitativeandmultichoice','qualitative and multichoice');
		xh ($p, 'distancecalcmin','distance calculation min terms');
		xj ($p, 'nooldccrs','disallow access to old ccrs');
		xj ($p, 'excluderefs','exclude referenced documents');
		xj ($p, 'requiresms','require sms confirmation');

		xj ($p, 'promptmissing','prompt for missing elements');

		xj ($p, 'mergeccr','merge report into curent ccr');
		puemit("</table>\r\n");
	}
	return $GLOBALS['uinfo'];
}
?>