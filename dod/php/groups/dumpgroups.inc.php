<?php

// show all the group stuff

function dump_groups ($accid, $id)
{
	$showEditors = ($id!=-1);
	$out = '';
	 $rowclass='';

	if ($id==-1){
		// GROUP TYPES

		$out .="<p><b>Group Types</b><table class='trackertable'>
                <tr><th>id</th><th>type</th><th>info</th><th>rules</th>
				<th>support</th><th>internal</th></tr>";

		$query = "SELECT * from grouptypes order by grouptypeid ";

		$result = mysql_query ($query) or die("can not query table grouptypes - ".mysql_error());
		$rowcount = mysql_num_rows($result);
		$odd = false;  $first = true;
		if ($rowcount != 0) {
			while (true) {
				$a = mysql_fetch_array($result,MYSQL_ASSOC);
				if ($a=='') break;
				$odd = !$odd;
				$out.="<tr class='$rowclass'><td>".$a['grouptypeid']."</td>".
				"<td>".$a['name']."</td>".
				"<td>".$a['infoUrl']."</td>".
				"<td>".$a['rulesUrl']."</td>".
				"<td>".$a['supportPageUrl']."</td>".
				"<td>".$a['internalgroup']."</td>".

				"</tr>";
			}
		}
		$out .="</table>";

		// GROUP INSTANCES
		$match = ($id!=-1)? " i.groupinstanceid='$id' and " : '';


		$out .="<p><b>Groups</b><table class='trackertable'>
                <tr><th> </th><th>group</th><th>group type</th><th>parent</th></tr>";

		$query = "SELECT *,i.name as iname, t.name as tname from groupinstances i ,grouptypes t
							where $match i.grouptypeid=t.grouptypeid order by i.grouptypeid, i.groupinstanceid";

		$result = mysql_query ($query) or die("can not query table grouptypes - ".mysql_error());
		$rowcount = mysql_num_rows($result);
		$odd = false;  $first = true;
		if ($rowcount != 0) {
			while (true) {
				$a = mysql_fetch_array($result,MYSQL_ASSOC);
				if ($a=='') break;
				$odd = !$odd;
				$gid = $a['groupinstanceid'];
				$logo = "<img src='".$a['groupLogo']."' alt='".$a['groupLogo']."' />";
				$logo = "<b>".$a['iname']."</b>";
				$mod = ($showEditors )? $id :
				"<form action=modGroups.php action=post>
				<input type=hidden value=$gid name='id'>
				<input type=submit value='mod'>
				</form>";
				//"<a href=modGroups.php?id=$gid>$gid</a>";

				$out.="<tr class='$rowclass'><td>".$mod."</td><td valign=center>".
				$logo."</td><td>".$a['tname']."</td><td>".$a['parentid']."</td>".
				"</tr>";
			}
		}

		$out .="</table>";

		// practices
		$match = ($id!=-1)? " i.groupinstanceid='$id' and " : '';


		$out .="<p><b>Practices</b><table class='trackertable'>
                <tr><th>id</th><th>practice</th><th>accid</th><th>rls</th></tr>";

		$query = "SELECT * from practice
							order by practiceid";

		$result = mysql_query ($query) or die("can not query table practices - ".mysql_error());
		$rowcount = mysql_num_rows($result);
		$odd = false;  $first = true;
		if ($rowcount != 0) {
			while (true) {
				$a = mysql_fetch_array($result,MYSQL_ASSOC);
				if ($a=='') break;
				$odd = !$odd;
				$out.="<tr class='$rowclass'><td>".$a['practiceid']."</td><td><b>".
				           $a['practicename']."</b></td><td >".
				$a['accid']."</td><td>".$a['practiceRlsUrl']."</td>".
				"</tr>";
			}
		}

		$out .="</table>";

	} // only show all that for showGroups, not modding

	// GROUP ADMINS
	$match = ($id!=-1)? " p.groupinstanceid='$id' and " : '';
	$head = ($id==-1)? "<th>group id</th><th>group name</th>":'';

	$mod = ($showEditors )? "<small>&nbsp;<a href=addgroupadmin.php?id=$id>add</a>&nbsp;<a href=delgroupadmin.php?id=$id>remove</a></small>":'';

	$out .="<p><b>Group Admins</b>$mod<table class='trackertable'>
                <tr>$head<th>medcommons id</th><th>medcommons email</th><th>comment</th></tr>";

	$query = "SELECT * from groupadmins p, groupinstances i , users u
where  $match p.groupinstanceid= i.groupinstanceid and p.adminaccid=u.mcid order by p.groupinstanceid,p.adminaccid ";

	$result = mysql_query ($query) or die("can not query table groupadmins - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true;
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			$adminid = ($a['adminaccid']==$accid)?"<b>$accid</b>":$a['adminaccid'];
			$f=	($id==-1)?($a['groupinstanceid']."</td><td>".
			$a['name']."</td><td>"):'';
			$out.="<tr class='$rowclass'><td>".$f.
			$adminid."</td><td>".$a['email']."</td>".
			"<td>".$a['comment']."</td>".
			"</tr>";
		}
	}

	$out .="</table>";
	// GROUP MEMBERS
	$match = ($id!=-1)? " p.groupinstanceid='$id' and " : '';
	$head = ($id==-1)? "<th>group id</th><th>group name</th>":'';

	$mod = ($showEditors )? "<small>&nbsp;<a href=addgroupmember.php?id=$id>add</a>&nbsp;<a href=delgroupmember.php?id=$id>remove</a></small>":'';

	$out .="<p><b>Group Members</b>$mod<table class='trackertable'>
                <tr>$head<th>medcommons id</th><th>medcommons email</th><th>comment</th></tr>";

	$query = "SELECT * from groupmembers p, groupinstances i , users u
where $match p.groupinstanceid= i.groupinstanceid and p.memberaccid=u.mcid order by p.groupinstanceid,p.memberaccid ";

	$result = mysql_query ($query) or die("can not query table groupmembers - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true;
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			$memberid = ($a['memberaccid']==$accid)?"<b>$accid</b>":$a['memberaccid'];

			$f=	($id==-1)?($a['groupinstanceid']."</td><td>".
			$a['name']."</td><td>"):'';
			$out.="<tr class='$rowclass'><td>".$f.
			$memberid."</td><td>".$a['email']."</td>".
			"<td>".$a['comment']."</td>".
			"</tr>";
		}
	}
	$out .="</table>";
	// GROUP PROPERTIES

	$match = ($id!=-1)? " p.groupinstanceid='$id' and " : '';

	$head = ($id==-1)? "<th>group id</th><th>group name</th>":'';

	$mod = ($showEditors )? "&nbsp;<small><a href=addgroupproperty.php?id=$id>add</a>&nbsp;<a href=delgroupproperty.php?id=$id>remove</a></small>":'';

	$out .="<p><b>Group Specific Properties</b>$mod<table class='trackertable'>
                <tr>$head<th>property</th><th>value</th><th>comment</th></tr>";

	$query = "SELECT * from groupproperties p, groupinstances i
where $match p.groupinstanceid= i.groupinstanceid order by p.groupinstanceid ";

	$result = mysql_query ($query) or die("can not query table groupproperties - ".mysql_error());
	$rowcount = mysql_num_rows($result);
	$odd = false;  $first = true; $rowclass='';
	if ($rowcount != 0) {
		while (true) {
			$a = mysql_fetch_array($result,MYSQL_ASSOC);
			if ($a=='') break;
			$odd = !$odd;
			$f=	($id==-1)?($a['groupinstanceid']."</td><td>".
			$a['name']."</td><td>"):'';
			$out.="<tr class='$rowclass'><td>".$f.
			$a['property']."</td>".
			"<td>".$a['value']."</td>".
			"<td>".$a['comment']."</td>".
			"</tr>";
		}
	}

	$out .="</table>";

	return $out;
}
?>
