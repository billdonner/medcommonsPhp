<?php


function connect_db()
{
	$db=$GLOBALS['DB_Database'];
	mysql_connect($GLOBALS['DB_Connection'],
	$GLOBALS['DB_User'],
	$GLOBALS['DB_Password']
	) or die ("can not connect to mysql");
	$db = $GLOBALS['DB_Database'];
	mysql_select_db($db) or die ("can not connect to database $db");
	return $db;
}
function practice_ids($id,&$providersid,&$patientsid)
{
	$select = "SELECT providergroupid,patientgroupid from practice where practiceid='$id'";
	$res = mysql_query($select);
	$result = mysql_fetch_array($res);
	$providersid = $result[0];
	$patientsid = $result[1];
	return ;
}

function group_add_member($id, $mcid, $comment)
{
	$insert = "INSERT into groupmembers SET groupinstanceid='$id',memberaccid='$mcid',comment='$comment'";
	mysql_query($insert);
	$rowcount = mysql_affected_rows();
	$dupe = ($rowcount !=1 );
	return $dupe;
}
function group_del_member($id, $mcid)
{
	$delete = "Delete from groupmembers where groupinstanceid='$id' and memberaccid='$mcid'";
	mysql_query($delete);
	$rowcount = mysql_affected_rows();
	$found = ($rowcount ==1 );
	return $found;
}
function group_add_admin($id, $mcid, $comment)
{
	$insert = "INSERT into groupadmins SET groupinstanceid='$id',adminaccid='$mcid',comment='$comment'";
	mysql_query($insert);
	$rowcount = mysql_affected_rows();
	$dupe = ($rowcount !=1 );
	return $dupe;
}
function group_del_admin($id, $mcid)
{
	$delete = "Delete from groupadmins where groupinstanceid='$id' and adminaccid='$mcid'";
	mysql_query($delete);
	$rowcount = mysql_affected_rows();
	$found = ($rowcount ==1 );
	return $found;
}
/**
 * Enter description here...
 *
 * @param unknown_type $q
 * @return unknown
 */
function doquery($q)
{
	// execute query and return only first fow of interest
	$result=mysql_query($q) or die ("Cant execute query $q ".mysql_error());
	$r = mysql_fetch_assoc ($result);
	$rowcount = mysql_num_rows($result);
	//		echo "Rowcount in doquery $q is $rowcount <br>";
	return $r; // return whole associate array, might be null
}
function lookup_user($key)
{
	// might be either an id or email address, try id first


	$q1 = "SELECT * from users where '$key'=mcid";
	$rec = doquery($q1);
	if ($rec!==false) return $rec;
	$q2 = "SELECT * from users where '$key'=email";
	$rec = doquery($q2);
	return $rec;
}
function confirm_logged_in()
{
	// start here
	if (isset($GLOBALS['__mckey'])){
		list ($sha1,$accid,$email)=explode('|',base64_decode($GLOBALS['__mckey'])); //if starting automagically
		return array($accid,'','',$email,'','');
	}
	else if (isset( $_COOKIE['mc'])){
		$mc = $_COOKIE['mc'];

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

	header("Location: ".$GLOBALS['Homepage_Url']."index.html?p=notloggedin");
	echo "Redirecting to MedCommons Web Site";
	exit;

}
/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @param unknown_type $gid
 * @return unknown
 */
function confirm_admin_access($accid,$gid){
	// does not return if this user is not a group admin
	$q = "Select * from groupadmins where '$accid'=adminaccid and '$gid'=groupinstanceid";
	$rec = doquery($q);
	if ($rec!==false) return $rec;
	// try
	$q = "Select * from groupadmins where '$accid'=adminaccid and '0'=groupinstanceid";
	$rec = doquery($q);
	if ($rec!==false) return $rec;
	group_error(make_group_form_components($gid),"Sorry, you are not admin authorized for this function $q");
};

function confirm_member_access($accid,$gid){
	// does not return if this user is not a group member
	$q = "Select * from groupmembers where '$accid'=memberaccid and '$gid'=groupinstanceid";
	$rec = doquery($q);
	if ($rec!==false) return $rec;
	// try
	//	$q = "Select * from groupadmins where '$accid'=adminaccid and '0'=groupinstanceid";
	//	$rec = doquery($q);
	//	if ($rec!==false) return $rec;
	group_error(make_group_form_components($gid),"Sorry, you are not authorized for this function $q");
};

class InfoClass
{
	var $header;
	var $groupname;
	var $logo;
	var $leftphotourl;
	var $rightphotourl;
	var $id;
	var $value;
}

/**
 * Enter description here...
 *
 * @return unknown
 */
function myUserInfo()
{
	list($accid) = confirm_logged_in();
	$q="select photoUrl,picslayout,stylesheetUrl from users where mcid='$accid'";
	$result = mysql_query($q) or die("cant select from users ".mysql_error());
	$row = mysql_fetch_object($result);
	return $row;
}
function myGroupInfo($gid)
{
	list($accid) = confirm_logged_in();
	$q="select groupLogo,name from
	            groupinstances where  
	              groupinstanceid='$gid'";
	$result = mysql_query($q) or die("cant select from users ".mysql_error());
	$row = mysql_fetch_object($result);
	return $row;
}

function make_group_form_components ($id)
{
	$info = new InfoClass;
	$info->header = '';
	//	$q="select photoUrl,affiliationgroupid,picslayout,stylesheetUrl,groupLogo,name from

	$a = myGroupInfo($id); // all the dirty work is here
	$u = myUserInfo();
	$photoUrl = $u->photoUrl;

	if ($a->groupLogo!=0) $info->logo = "<img src='".$a->groupLogo.	"' alt='".$a->name."' />";
	else $info->logo="<span><b>".$a->name."</b></span>";

	if ($photoUrl=='') $photourl = "<div><small>set photo via My Prefs</small></div>"; else
	$photourl = "<img width='100px' src='".$photoUrl."' alt='".$photoUrl."' />";
	$info->header .=	"<div id='group_form_header'>".$info->logo."</div>";
	$info->groupname = $a->name;
	$info->id = $id;
	$info->stylesheeturl = $u->stylesheetUrl;
	$info->value = true;
	$info->rightphotourl = ''; $info->leftphotourl = '';
	if (substr($u->picslayout,0,1)=='S') $info->leftphotourl = $photourl;
	if (substr($u->picslayout,1,1)=='S') $info->rightphotourl = $photourl;
	return $info;
}
/**
 * Enter description here...
 *
 * @param InfoClass $info
 * @param unknown_type $accid
 * @param unknown_type $email
 * @param unknown_type $id
 * @param unknown_type $desc
 * @param unknown_type $title
 * @param unknown_type $startpage
 * @return unknown
 */
function make_group_page_top (InfoClass $info, $accid,$email, $id,$desc,$title,$startpage)
{
	if ($info->leftphotourl!='') $leftphotoblock="<td align=left>$info->leftphotourl</td>"; else $leftphotoblock ='';
	if ($info->rightphotourl!='') $rightphotoblock="<td alight=right>$info->rightphotourl</td>"; else $rightphotoblock = '';


//	if ($startpage=='') $sp=''; else  $sp="<a href=../acct/setStart.php?p=$startpage?id=$id>mark</a>&nbsp;";
	$iden =   "<a href='../acct/goStart.php'>$accid</a>";
	$x=<<<XXX
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
     <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content='$desc'/>
        <meta name="robots" content="all"/>
        <title>$title</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "groups.css"; </style>
   
    </head> 
    <body><div class='widecontainer'><table width="100%"><tr><td>
                <table><tr>$leftphotoblock<td align=left>$info->header</td><td><a href="http://medcommons.net"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="$title" /></a></td></tr></table>
                </td><td>
                <table><tr><td align=right><b>
                $title</b></td></tr><tr><td align=right>$iden $email</td></tr>
				</table>
              </td>
          $rightphotoblock</tr></table>
XXX;
	return $x;
}
/**
 * Enter description here...
 *
 * @param InfoClass $info
 * @return unknown
 */
function make_group_page_bottom (InfoClass $info)
{ 
	 $host = $_SERVER['HTTP_HOST'];
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
            <div class="p1">$host <a href=../../acct/myPrefs.php>&#169;</a> MedCommons 2006</div>

        </div></div></body></html>
XXX;

	return $html;
}


function group_error(Infoclass $info,$errorstring)
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
        <meta name="description" content="MedCommons Group Maintenance"/>
        <meta name="robots" content="all"/>
        <title>MedCommons $errorstring</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "groups.css"; </style>
    </head>
                <table><tr><td><a href="index.html"  ><img border="0" alt="MedCommons" 
                src="../images/mclogotiny.png" 
                title="$errorstring" /></a>
                </td><td>Group Maintenance Error<small> 
                &nbsp;
						
		                 <a href=../acct/goStart.php>start</a>&nbsp;
					</small></td></tr>
					</table>
$info->header

$errorstring
<form action=modGroups.php method=post>
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
