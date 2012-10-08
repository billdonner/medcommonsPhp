<?php

define ('FAXSERVICEID','49283ab829cd9283900ab3edf33');

function check_payment ($accid, $appserviceid)
{
	// let's find whether this is covered

	$q = "select 1 from appservicecontracts where appserviceid='$appserviceid' and accid='$accid'";
	$result = mysql_query($q) or die ("can not query appservices $q ".mysql_error());
	$numrows = mysql_numrows($result);

	//	echo "check payment $accid $appserviceid $numrows <br>";
	return ($numrows<>0);
}
function todirlookup($alias,$gid)
{
	$q="select * from todir where groupid='$gid' and alias='$alias'";
	$result= mysql_query($q) or die ("can not select $q".mysql_error());
	$rowcount = mysql_num_rows($result);
	$state = ($rowcount!=0) ;
	$td= mysql_fetch_object($result);
	if ($td===false) return false; //phplint

	$vistag = $td->alias;
	$solo = $td->sharedgroup;
	$pinflag = $td->pinstate;
	$contactlist = $td->contactlist;
	$id = $td->id;
	return array ($state,$solo,$pinflag,$vistag,$contactlist,$id);

}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @param unknown_type $providergroupid
 * @param unknown_type $patientgroupid
 * @return unknown
 */
function rlsacl($accid,$providergroupid,$patientgroupid)
{
	//main

	$timenow = time();
	$savelist = array();
	$ontodirlist = array();
	$emaillist = array();

	//	$out.="</table></td></tr></table></div>".

	$q = "SELECT contactlist,sharedgroup,pinstate,alias,id from todir where groupid='$providergroupid'";
	$result = mysql_query($q) or die ("can not query todir $q ".mysql_error());

	// play out the accumulated tlist
	while (true)
	{
		$r = mysql_fetch_row($result);
		if ($r===false) break;
		$ontodirlist[] = $r;
		if ($r[3]==$r[0]) $emaillist[]=$r[3];
	}

	// the ontodirlist will be used later
	//
	// now
	$q="SELECT email FROM users LEFT JOIN groupadmins ON adminaccid=mcid
			WHERE groupinstanceid='$providergroupid' and mcid<>'$accid'";
	$result =  mysql_query($q) or die("can not select groupadmins $q ".mysql_error());
	$rowcount = mysql_numrows($result);
	for ($i=0;$i<$rowcount; $i++)
	{
		$row=mysql_fetch_object($result);
		$key = array_search($row->email,$emaillist);
		if ($key===false)
		array_push ($emaillist,$row->email);
		list($state,$solo,$pinflag,$vistag,$contactlist,$id)	= todirlookup($row->email,$providergroupid);
		array_push($savelist,array($row->email,$state,$solo,$pinflag,$vistag,'admin',$providergroupid,$contactlist,$id));

	}
	mysql_free_result($result);

	$q="SELECT email FROM users LEFT JOIN groupmembers ON memberaccid=mcid
	  		WHERE groupinstanceid='$providergroupid' and mcid<>'$accid'";
	$result =  mysql_query($q) or die("can not select groupmembers $q ".mysql_error());
	$rowcount = mysql_numrows($result);
	for ($i=0;$i<$rowcount; $i++)
	{
		$row=mysql_fetch_object($result);
		$key = array_search($row->email,$emaillist);
		if ($key===false)
		array_push ($emaillist,$row->email);
		// instead of calling todirlookup go thru todir list
		//
		list($state,$solo,$pinflag,$vistag,$contactlist,$id)	= todirlookup($row->email,$providergroupid);
		array_push($savelist,array($row->email,$state,$solo,$pinflag,$vistag,'',$providergroupid,$contactlist,$id));
	}
	mysql_free_result($result);
	// convert the resulting array to a form that is amenable to display
	$addprovider = "<a href='../groups/addgroupmember.php?id=$providergroupid'>new user</a>";
	$addadmin = "<a href='../groups/addgroupadmin.php?id=$providergroupid'>new admin</a>";;
	// other users
	$out =
	"<div><small><i>&nbsp;&nbsp;&nbsp;&nbsp;- Users with access to this group</i>&nbsp; $addprovider $addadmin</small>\r\n
	      <table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>
	       <table class=trackertable>\r\n";
	foreach ($savelist as $s)
	{
		list ($email,$state,$solo,$pinflag,$vistag,$admin,$gid,$contactlist,$id) = $s;
		$state = (array_search($email,$emaillist)===false);
		$bstate = ($state?"OFF":"ON");
		$vstate = ($state?"-TODIR":"+TODIR");
		//$pre=($state?'<i>To List</i>':'');
		if ($state) $link = $vstate; else
		$link = "<a href='../groups/toggletodir.php?gid=$providergroupid&op=NEW&cl=$email&email=$email'>$vstate</a>";
		$out.= '<tr><td>'.$email.'</td><td>'.$link.'</td><td>'.
		"<a href=../groups/delgroupmemberconfirm.php?id=$providergroupid&mcid=$email>del</a>".
		"</td><td><a>$admin</a></td></tr>\r\n";
	}
	$out.="</table></td></tr></table></div>".
	// the To List
	"<div><small><i>&nbsp;&nbsp;&nbsp;&nbsp;- To list</small>\r\n
		  <table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>
	       <table class=trackertable>\r\n";

	// play out the accumulated tlist
	foreach ($ontodirlist as $s)
	{
		list ($contactlist,$solo,$pinflag,$alias,$id) = $s;
		$newflag=1-$pinflag;
		$newsolo=1-$solo; // figure out toggled values incase
		//		$pinlink = "<a href='../acct/fieldupdate.php?fieldname=todir|pinstate||$id&content=$newflag'>$pinflag</a>";
		//    table|field|accid|id
		//$img=($pinflag==1)?"<img src=images/buttons/pinreq.gif alt=pinreq />" :
		//"<img src=images/buttons/pinwaived.gif alt=pinwaived />";
		$pinlink = "<span id='todir|pinstate|t|$id'
		                                     class='checkboxText'>$pinflag</span>";

		//		$grouplink = //"<a href='../acct/fieldupdate.php?fieldname=todir|sharedgroup||$id&content=$newsolo'>$solo</a>";
		//$img=($solo==1)?"<img src=images/buttons/viewsolo.gif alt=viewsolo />" :
		//"<img src=images/buttons/viewpractice.gif alt=viewpractice />";

		$grouplink = "<span id='todir|sharedgroup|t|$id' class='checkboxText'>$solo</span>";
		$vistag = "<span id='todir|alias||$id' class='editText'>$alias</span>";
		$contag = "<span id='todir|contactlist||$id' class='editText'>$contactlist</span>";

		$remlink = "<a href='../groups/toggletodir.php?gid=$providergroupid&id=$id&op=DELETE'>remove</a>";
		$out .= "<tr><td class=inputfield>".$contag."</td><td>$pinlink</td><td>$grouplink</td><td class=inputfield>$vistag</td><td>$remlink</td></tr>\r\n";
	}

	$out.="</table></td></tr></table></div>";
	//ends ToList, should be balanced

	//how show last 10
	// in the audit log
	$auditlog = "<a target='_auditlog' href='../acct/auditlog.php?gid=$providergroupid&filter=AUDIT'>more</a>";

	$out.="<div><small><i>&nbsp;&nbsp;&nbsp;&nbsp;- Audit Log&nbsp;$auditlog</small>\r\n
		  <table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>
	       \r\n";
	require_once '../acct/auditlog.inc.php';
	$out .= auditlog($providergroupid,10,'');
	$out.="</td></tr></table></div>";

	//if we are paying for faxes, lets show them
	if (check_payment($accid,FAXSERVICEID))
	{
		$faxlog = "<a target='_auditlog' href='../acct/auditlog.php?gid=$providergroupid&filter=FAX'>more</a>";

		$out.="<div><small><i>&nbsp;&nbsp;&nbsp;&nbsp;- Incoming Fax&nbsp;$faxlog</small>\r\n
		  <table><tr><td>&nbsp;&nbsp;&nbsp;</td><td>
	       \r\n";
		require_once '../acct/auditlog.inc.php';
		$out .= auditlog($providergroupid,10,'FAX');

		$out.="<br><br></td></tr></table></div>";
	}

	return $out;
}
function my_providers ($accid)
{
	// GROUP Practices I am a member of
	$mod = "<!-- begin my providers section -->";
	$head = '';
	$out1='<div>';
	$query = "SELECT * from practice q, groupmembers p, groupinstances i , users u
	where p.memberaccid='$accid' and  q.patientgroupid=i.groupinstanceid  and 
	i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
	p.memberaccid=u.mcid order by q.practicename ";

	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true;
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			if ($out1=='')
			$out1 .="$mod<table class='trackertable'>
                <tr>$head<th>group</th><th>name</th></tr>";
			$gid = $a['practiceid'];
			$logo = "<a href='".$a['practiceRlsUrl']."?pid=$gid'><img src='".$a['practiceLogoUrl']."' alt='".$a['practiceLogoUrl']."' /></a>";
			$out1.="<tr class='$rowclass'><td>".
			$logo."</td>".
			"<td>".$a['pacticename']."</td>".
			"</tr>\r\n";
		}
		$out1 .="</table>";
	}
	$out1 .= "</div>";

	return $out1;
}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function my_practices ($accid)
{
	// GROUP Practices I am a healthcare member of
	$mod = "<!-- begin pratice membership section -->";
	$head = '';
	$out1="<div>$mod<table class='trackertable'>
                <tr>$head<th>group</th><th>my Identity</th></tr>";
	$query = "SELECT * from practice q, groupmembers p, groupinstances i , users u
	where p.memberaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
	i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
	p.memberaccid=u.mcid order by q.practicename ";

	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true; $rowclass='';
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
		
			$gid = $a['practiceid'];
			$logo = "<a href='".$a['practiceRlsUrl']."?pid=$gid'><img src='".$a['practiceLogoUrl']."' alt='".$a['practicename']."' /></a>";
			$out1.="<tr class='$rowclass'><td>".
			$logo."</td>".
			"<td>".$a['practicename']."</td>".
			"</tr>";
		}
		$out1 .="</table>";
	}
	$out1 .= "</div>";

	return $out1;
}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function my_adminpractices ($accid)
{
	// GROUP Practices I can administer
	$mod = "<!-- begin adminpractices section -->";
	$head = '';
	$out1='<div>';
	$query = "SELECT * from practice q, groupadmins p, groupinstances i , users u
	where p.adminaccid='$accid' and  q.providergroupid=i.groupinstanceid  and 
	i.parentid>0 and  p.groupinstanceid= i.groupinstanceid and 
	p.adminaccid=u.mcid order by q.practicename ";

	$result = mysql_query ($query) or die("can not query table groupadmins - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true;
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			if ($out1=='')
			$out1 .="$mod<table class='trackertable'>".
			"";
			$pid = $a['practiceid'];
			$providergroupid = $a['providergroupid'];
			$patientgroupid = $a['patientgroupid'];

			$rowclass='';//found by phplint
			$rlsurl = $a['practiceRlsUrl'];
			$rlsacl = rlsacl($accid,$providergroupid,$patientgroupid); // build list
			if ($rlsurl=='') $rlsurl ="<a  href='../acct/rls.php?pgid=$pid'>Workflow</a>";

			//			$logo = "<a href='".$a['adminUrl']."?pid=$gid'><img src='".$a['practiceLogoUrl']."' alt='".$a['practiceLogoUrl']."' /></a>";
			$out1.="<table><tr class='$rowclass'>".
			//			"<td>".$logo."</td>".
			"<td>&nbsp;&nbsp;<b>".$a['practicename']."</b></td><td> $rlsurl</td>".
			"</tr></table>$rlsacl";
		}
		$out1 .='';//"</table>";
	}
	$out1 .= "</div>";

	return $out1;
}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function my_admingroups ($accid)
{
	// GROUP ADMINS

	$mod = "<!-- begin group admin section -->";

	$head = '';
	$out1='<div>';
	$query = "SELECT * from groupadmins p, groupinstances i , users u
where p.adminaccid='$accid' and  i.parentid=0 and  p.groupinstanceid= i.groupinstanceid and p.adminaccid=u.mcid order by p.groupinstanceid,p.adminaccid ";

	$result = mysql_query ($query) or die("can not query table groupadmins - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true; $rowclass='';
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			
			if ($first) $out1 .="$mod<table class='trackertable'>
                <tr>$head<th>group</th><th>persona</th></tr>";
			$gid = $a['groupinstanceid'];
			$logo = "<a href='".$a['adminUrl']."?id=$gid'>".$a['name']."</a>";
			$out1.="<tr class='$rowclass'><td>".
			$logo."</td>".
			"<td>".$a['comment']."</td>".
			"</tr>";
			$first = false;
		}
		$out1 .="</table>";
	}
	$out1 .= "</div>";

	return $out1;
}

/**
 * Enter description here...
 *
 * @param unknown_type $accid
 * @return unknown
 */
function my_groups($accid)
{
	// GROUP MEMBERS
	$mod = "<!-- begin group members section -->";
	$head = '';
	$out2='<div>';
	$query = "SELECT * from groupmembers p, groupinstances i , users u
where p.memberaccid='$accid' and i.parentid=0 and p.groupinstanceid= i.groupinstanceid and p.memberaccid=u.mcid order by p.groupinstanceid,p.memberaccid ";

	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true; $rowclass='';
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			if ($first) $out2 .="$mod<table class='trackertable'>
                <tr>$head<th>group</th><th>persona</th></tr>";
			$gid = $a['groupinstanceid'];
			$logo = "<a href='".$a['memberUrl']."?id=$gid'>".$a['name']."</a>";
			$out2.="<tr class='$rowclass'><td>".
			$logo."</td>".
			"<td>".$a['comment']."</td>".
			"</tr>";
			$first=false;
		}
		$out2 .="</table>";
	}
	$out2 .= "</div>";
	return $out2;
}
?>
