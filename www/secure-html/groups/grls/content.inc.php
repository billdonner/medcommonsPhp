<?php
$odd = false;
$mb = $GLOBALS['RLS_Name'];
 $page = 1;
$start = ($page-1) * $limit;
$select="SELECT * FROM groupccrevents $whereclause ORDER BY CreationDateTime DESC LIMIT $start,$limit";
error_log($select);
$countSql="SELECT count(*) FROM groupccrevents $whereclause ORDER BY CreationDateTime";

$minibanner = ""; //"<small>query results at <span id='timeofday'> </span>&nbsp;remote server time is <span id='timesynch'>.....</span> $wherestring </small>";
#$content = "<h2>$mb $minibanner</h2>";
#
$result = mysql_query($countSql) or die("can not select from  table groupccrevents - $select".mysql_error());
$countRow = mysql_fetch_array($result);
$count = $countRow[0];
$result = mysql_query($select) or die("can not select from  table groupccrevents - $select".mysql_error());
$rows = mysql_numrows($result);
$pages = ceil($count/$limit);

$pageLinks = "Page ";
for($p=0; $p<$pages; $p++) {
  $pn = $p + 1;
  if($pn == $page)
    $pageLinks.="$pn&nbsp;";
  else
    $pageLinks.="<a href='javascript:page($pn);'>$pn</a>&nbsp;";
}

if ($rows == 0)
$content = "<p>No Rows Match</b>"; //phplint
else
{//rows
$content="<table id='registryTable' cellspacing='2' summary='$mb'>
	<thead>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align='right'>$pageLinks</td></tr>
      <tr><th title='derived from ccr'>Patient</th><th title='lt:$lasttime'>Time</th>
          <th title='should accommodate all vendors'>Conf Code</th> 
          <th title='passed literally'>Purpose</th><th width='105px'>Status</th>".
          /* <th title='Originating Provider'>Send Provider Id</th><th
          title='receiving provider'>Recv Provider Id</th>*/
          "</tr>
			</thead><tbody>";
	

while ($l = mysql_fetch_array($result,MYSQL_ASSOC)) {
	$odd = (!$odd); // flip polarity
	//guid is a link
	$guid = $l['Guid'];
	$ct = $l['CreationDateTime'];
	$time = strftime('%H:%M:%S',$ct);
	$date = strftime('%m/%d/%y',$ct);
	if (($lasttime!=0)&&($ct > ($lasttime -100))){
		// still new
		$rowclass = ($odd?"oddnew":"evennew");	
	}else {
		$rowclass = ($odd?"odd":"even");
	}	
	$cc = $l['ConfirmationCode'];
	$rs = $l['RegistrySecret'];
	$purpose = $l['Purpose'];
	$commonsid = $cc;
	$xdob = $l['DOB'];
		//$curl =$GLOBALS['Commons_Url'].'gwredirguid.php' ;
	//$href = $curl."?guid=$guid";
	$viewerurl = $l['ViewerURL']; //new
	$serverurl = $l['CXPServerURL'];
	$servervendor=$l['CXPServerVendor'];
	$comment = $l['Comment'];
	$href = $viewerurl."&p=".$l['RegistrySecret']."&registry=jaroka&idp=jaroka";//."?guid=$guid";
	$patientidentifier = $l['PatientIdentifier']; 
	 if ($patientidentifier=='') $patientidentifier = "-no patientid-";
	$patientidentifiersource = $l['PatientIdentifierSource']; 
	 if ($patientidentifiersource=='') $patientidentifiersource = "-no patientid source-";
//	$providerparsed = $l['providerparsed']; if ($providerparsed=='') $providerparsed = "-no provider:-";

	if ($viewerurl=='') $anchor = "$date $time"; else $anchor = <<<XXX
	<a title="cxpurl:$serverurl\nviewurl:$viewerurl\nvendor:$servervendor\nrs:$rs" 
	                              target="_parent" href="$href" >$date $time</a>
XXX;
  $content.="<script type='text/javascript'>statuses['$cc']='".$l['Status']."';</script>";
	$content.="<tr class='$rowclass'>";
	//$content.="<tr class='$rowclass' onmouseover='over(\"$cc\")' onmouseout='out(\"$cc\")'>";
	$content .= "<th title='dob:$xdob'>".$l['PatientFamilyName'].','.$l['PatientGivenName']."</th>";
	$content .= "<td >".$anchor."</td>";
	$content .= "<td title='$ct $date'>".$cc."</td>";
	//$content .="<td>".$rs."</td>";
	$content .= "<td title='comment:$comment'>".$purpose."</td>";
  $content .= "<td align='left' id='r$cc' title='Status'>
    <input type='text' readonly='true' id='sTxt$cc' class='statusInput' style='width: 70px;' value='".$l['Status']."'/>&nbsp;
    <img id='sImg$cc' onclick='editStatus(\"$cc\")' class='editStatusImg' src='images/black_arrow_down.gif'/></td>";

/* These are always Jaroka, not much point displaying them
	$content .= "<td title='source:tbs' 
					class='neg'>".$l['SenderProviderId']."</td>";
	$content .= "<td title='source:tbs' 
					class='neg'>".$l['ReceiverProviderId']."</td>";
*/
	$content.="</tr>";
}
$content .= "</tbody></table>";

}
?>
