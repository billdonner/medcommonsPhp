<?php 
// library for the /acct service



require_once "dbparamsidentity.inc.php";

function put_switches($accid,$switches)
{
	$q="update users set startparams='$switches' where (mcid ='$accid')";
	mysql_query($q) or die("can't update users".mysql_error());

}

function get_switches($accid)
{
	$q="select startparams,validparams from users where (mcid ='$accid')";
	$result=mysql_query($q) or die("can't access users".mysql_error());
	$fow = mysql_fetch_array($result);
	return $fow;
}

function aconnect_db()
{
	$db=$GLOBALS['DB_Database'];
	mysql_pconnect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	return $db;
}
function adoquery($q)
{
	// execute query and return only first fow of interest
	$result=mysql_query($q) or die ("Cant execute query $q ".mysql_error());
	$r = mysql_fetch_assoc ($result);
	$rowcount = mysql_num_rows($result);
	//		echo "Rowcount in doquery $q is $rowcount <br>";
	return $r; // return whole associate array, might be null
}

function testif_logged_in()
{
	$mc = $_COOKIE['mc'];
	if ($mc =='')
	return false;


	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	if ($mc!='')
	{
		$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
		$props = explode(',',$mc);
		for ($i=0; $i<count($props); $i++) {
			list($prop,$val)= explode('=',$props[$i]);
			switch($prop)
			{
				case 'mcid': $accid=$val; break;
				case 'fn': $fn = $val; break;
				case 'ln': $ln = $val; break;
				case 'email'; $email = $val; break;
				case 'from'; $idp = stripslashes($val); break;
			}
		}
	}
	return array($accid,$fn,$ln,$email,$idp,$mc);
}

function aconfirm_admin_access($accid,$gid){
	// does not return if this user is not a group admin
	$q = "Select * from groupadmins where '$accid'=adminaccid and '$gid'=groupinstanceid";
	$rec = adoquery($q);
	if ($rec!==false) return $rec;
	// try
	$q = "Select * from groupadmins where '$accid'=adminaccid and '0'=groupinstanceid";
	$rec = adoquery($q);
	if ($rec!==false) return $rec;
	group_error(make_group_form_components($gid),"Sorry, you are not admin authorized for this function $q");
};

function aconfirm_member_access($accid,$gid){
	// does not return if this user is not a group member
	$q = "Select * from groupmembers where '$accid'=memberaccid and '$gid'=groupinstanceid";
	$rec = adoquery($q);
	if ($rec!==false) return $rec;
	// try
	//	$q = "Select * from groupadmins where '$accid'=adminaccid and '0'=groupinstanceid";
	//	$rec = doquery($q);
	//	if ($rec!==false) return $rec;
	group_error(make_group_form_components($gid),"Sorry, you are not authorized for this function $q");
};

/**
 * Enter description here...
 *
 * @return unknown
 */
function aconfirm_logged_in()
{

	if (isset($GLOBALS['__mckey']))
	{
		list ($sha1,$accid,$email)=explode('|',base64_decode($GLOBALS['__mckey'])); //if starting automagically
		return array($accid,'','',$email,'','');
	}
	else

	if (!isset($_COOKIE['mc']))
	{

		//header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
		//echo "Redirecting to MedCommons Web Site";
		$home = $GLOBALS['Homepage_Url'];
		$irl = $GLOBALS['Identity_Base_Url'];
		$trl = $GLOBALS['Commons_Url'].'trackinghandler.php';
		$errurl = $GLOBALS['Accounts_Url'].'goStart.php';
		if (isset($GLOBALS['Script_Domain'])) //svn 824 with enhanccement
		      $domain = $GLOBALS['Script_Domain']; else $domain=false;
      if($domain && ($domain!= "")) {
        $setDomain = "document.domain = '$domain';";
      }
		$html=<<<XXX
		

<?xml version='1.0' encoding='US-ASCII' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=US-ASCII" />
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Home Page"/>
        <meta name="robots" content="all"/>

        <title>MedCommons - Interoperable and Private Personal Health Records</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        <style type="text/css" media="all"><!--

td {
    vertical-align: top;
    padding: 10px;
    border: 1px;
    border-style: solid;
    border-color: #fff #fff #ccc #ccc;
}

td p, td a {
    font-size: x-small;
    padding-bottom: 0px;
    margin-bottom: 0px;
}

#forgotten {
    padding-top: 0px;
    margin-top: 0px;
}

.label {
    font-size: x-small;
}

.error {
	background-color: #c00;
	color: #fff;
}
 
h4 {
    background-color: #ccc;
}

// --></style>
    </head>
     <body onload="$setDomain;" >
        <div id="container">
            <div id="intro">
			<a href="$home" ><img src='images/mclogotiny.png' alt="MedCommons"></a>
            </div>
            <div id="supportingText">
	        <h3>
                    <span>Sign In</span>
                </h3>
		<div id='login'>
		  <table><tr><td>

		    <form method='post' action='$irl/login'>
		        <h4>Existing Account</h4>
		  <a class='label' href='$irl/register'>Create a New Account</a>
			<p>Your MCID or E-Mail Address:</p>
			<input name='mcid' size='19' value='' />

			<p>Your Password:</p>

			<input name='password' type='password' />
			<p id='forgotten'>
			    <a href='$irl/forgotten'>Forgotten Password?</a>
			</p>
			<input type='hidden' name='userId' value='' />
			<input type='hidden' name='sourceId' value='' />
			<input type='submit' value='Sign On>>' />
		    </form>

		    </td></tr></table>
		</div>

		<div id='viaTN'>
		  <table><tr><td>

		    <form method='post' action='$trl'>
		        <h4>Find CCR By Tracking #</h4>
			<p>Enter 12 Digit Tracking #</p>
			<input name='trackingNumber' size='19' value='' />
			<input type=hidden name='returnurl' value='$errurl' />
			<input type='submit' value='Lookup>>' />
		    </form>

		    </td></tr></table>
		</div>

            </div>
        </div>
        <div id="footer">
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp; <a
                href="http://creativecommons.org/licenses/by-nc-sa/1.0/" title="View details of the
                license of this site, courtesy of Creative Commons.">cc</a> &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>

            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
            <p class="p1">&#169; MedCommons 2006</p>
        </div>
    </body>
</html>
XXX;
		echo $html;
		exit;
	}

	// here if we have a cookie
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $cl="";

	$mc = $_COOKIE['mc'];
	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = "";
	$props = explode(',',$mc);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;
			case 'fn': $fn = $val; break;
			case 'ln': $ln = $val; break;
			case 'email'; $email = $val; break;
			case 'from'; $idp = stripslashes($val); break;
		}
	}


	return array($accid,$fn,$ln,$email,$idp,$cl);
}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function amyUserInfo($accid)
{
	$q="select photoUrl,picslayout,stylesheetUrl,affiliationgroupid from users
	where mcid='$accid'";
	$result = mysql_query($q) or die("cant select from users ".mysql_error());
	$obj = mysql_fetch_object($result);

	return $obj;
}
function amyAffiliationInfo($gid)
{
	$q="select affiliatename, affiliatelogo from
	            affiliates where  
	              affiliateid='$gid'";
	$result = mysql_query($q) or die("cant select from affiliates ".mysql_error());
	$row = mysql_fetch_object($result);
	return $row;
}

class aInfoClass
{
	var $header;
	var $logo;
	var $valid;
	var $groupname;
	var $leftphotourl;
	var $rightphotourl;
	var $stylesheeturl;
	var $picslayout;
	var $personaimg;
}
/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function make_acct_form_components ($accid)
{//photoUrl,picslayout,stylesheetUrl,affiliationgroupid
	$info = new aInfoClass;
	$info->header = '';

	$u = amyUserInfo($accid);
	if ($u->photoUrl=='') $photourl = "<div><small>set photo via My Prefs</small></div>"; else
	$photourl = "<img width='100px' src='".$u->photoUrl."' alt='".$u->photoUrl."' />";
	if ($u->affiliationgroupid!='-1')
	{
		$a = amyAffiliationInfo($u->affiliationgroupid); // all the dirty work is here
		$info->logo = "<img src='".$a->affiliatelogo."' alt='".$a->affiliatename."' />";
		$info->groupname = $a->affiliatename;$info->stylesheeturl = $u->stylesheetUrl;
	}
	else {
		$info->logo='';$info->stylesheeturl = '';
	}

	$info->header .=	"<div id='myacct_form_header'>".$info->logo."</div>";

	/*	$info->personaimg=<<<XXX
	<a  onclick="return personapopup('personainfo.php');" href='personainfo.php' >$img</a>
	XXX;

	*/


	$info->value = true;
	$info->rightphotourl = '';
	$info->leftphotourl = '';
	if (substr($u->picslayout,0,1)=='S') $info->leftphotourl = $photourl;
	if (substr($u->picslayout,1,1)=='S') $info->rightphotourl = $photourl;
	return $info;
}



/**
 * Enter description here...
 *
 * @param unknown_type $info
 * @param unknown_type $accid
 * @param unknown_type $email
 * @param unknown_type $id
 * @param unknown_type $desc
 * @param unknown_type $title
 * @param unknown_type $startpage
 * @param unknown_type $me
 * @return unknown
 */
function make_acct_page_top ($info, $accid,$email, $id,$desc,$title,$startpage,$me='')
{

	if ($info->leftphotourl!='') $leftphotoblock="<td align=left>$info->leftphotourl</td>"; else $leftphotoblock='';
	if ($info->rightphotourl!='') $rightphotoblock="<td align=right>$info->rightphotourl</td>"; else $rightphotoblock='';

	//	if ($startpage=='') $sp='';
	//	else  $sp="<a href=../acct/setStart.php?p=$startpage?id=$id>mark</a>&nbsp;";
	$iden =   $info->personaimg."<a href='../acct/goStart.php'>$accid</a>";
	$identityUrl = $GLOBALS['Identity_Base_Url'];
	$x=<<<XXX
           <table width="100%"><tr>
              $leftphotoblock
				<td align=right><table><tr><td><b>
                $title</b></td></tr></table>
                <table><tr><td>$iden $email</td></tr>
				<tr><td align=right><small> 
               <a href='${identityUrl}logout'>logout</a>
				</small></td></tr>
				</table>
              </td>
          <td align=right>$info->header</td>$rightphotoblock</tr></table>
XXX;
	return $x;
}
function make_acct_page_bottom ($info)
{  $host = $_SERVER['HTTP_HOST'];
   $acct = $GLOBALS['Accounts_Url'];
$html=<<<XXX
	     <div id="footer">
         <div class="noprint">
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp;  <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>
            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
            </div>
            <div class="p1">$host <a href='$acct/myPrefs.php'>&#169;</a> MedCommons 2006</div>
        </div></div>
XXX;

return $html;
}

function acct_error($info,$errorstring)
{
	$html = <<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
      <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="MedCommons Accounts Error"/>
        <meta name="robots" content="all"/>
        <title>MedCommons $errorstring</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "accstyle.css"; </style>
    </head>
                <table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="$errorstring" /></a>
                </td><td>Accts Maintenance Error<small> 
                &nbsp;
						&nbsp;
					</small></td></tr>
					</table>
$info->header

$errorstring
<form action=goStart.php method=post>
<input type=hidden name=id value='$info->id'>
<input type=submit value='Ok'>
</form>
</body>
</html>
XXX;
	echo $html;
	exit;
}
?>
