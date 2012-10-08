<?php

/**
 * Enter description here...
 *
 * @param unknown_type $gid
 * @param unknown_type $limit
 * @param unknown_type $filter
 * @return unknown
 */
function acctlog ($gid,$limit,$filter)
{
	list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
	//build menu to present from arg
	// get settings for how to behave
	$db = aconnect_db(); // connect to the right database
	$q = "SELECT *,DATE_FORMAT(datetime, '%c/%d/%Y %H:%i') as prettydate from account_log where'$accid'=mcid $filter";
	$q.= " order by datetime DESC LIMIT $limit";

	$result = mysql_query($q) or die ("can not query $q ".mysql_error());

	//echo " rows =". mysql_numrows($result);

	$out= "<table class='ccrtable' cellspacing='0' cellpadding='0'>";

	while (true) {
		$l = mysql_fetch_object($result);
		if ($l===false) break;
		$date = $l->prettydate; // fool the code a bit

		$user = $l->username;
		if ($user==null) $user='(self)';
		$op = $l->operation;
		$idp = $l->provider_id;
		if ($idp==null) $idp = '(MedCommons Native)';

		$out.= "
          <tr >
            <td>$accid</td>
                <td class='tndate'>$date</td>
                <td class='tncell'>$op</td> <td class='tncell'>$user</td>  <td class='tncell'>$idp</td>
                ";

		$out.= "</tr>";
	}

	$out.= "</table>";
	return $out;
}
?>