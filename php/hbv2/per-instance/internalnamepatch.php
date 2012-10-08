<?php
// rewrite names in our database
require 'healthbook.inc.php';
function update_fbtab ($facebook, $user,$mcid,$appliance)
{
	$extra = "";
	if ($user) $extra.= "'$user'=fbid AND ";
	if ($mcid) $extra.= "'$mcid'=mcid AND ";
	if ($appliance) $extra.= "'$appliance'=applianceurl AND ";
	if ($extra!=='')$extra ="WHERE fbid!='0' AND ".substr($extra,0,strlen($extra)-4);
	// return the appliance and the medcommons user id
	$appliance = false;
	$mcid = false;
	$out="<table><tr><th>fbid</th><th>mcid</th><th>viewing</th><th>facebook name</th><th>status</th></tr>";
	// utab=Select * from fbtab where fbid == '$user'
	$q = "select * from  fbtab $extra ";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($u!==false){
			$mcid=$u->mcid;
			$appliance = $u->applianceurl;
			$fbid = $u->fbid;
			if ($u->lastname=='')
			{
				// **** go to facebook and get their name
				// all these cases presume we are logged on to facebook
				$ret= ($facebook->api_client->users_getInfo($fbid,array('first_name','last_name','pic_small', 'sex'))); //sex
				if (!$ret) {
					$out.="<tr><td>$fbid</td><td>$mcid</td><td>no facebook info</td><td>not updated</td></tr>";
				} else
				{

					$fn = mysql_real_escape_string($ret[0]['first_name']);
					$ln = mysql_real_escape_string($ret[0]['last_name']);
					$ps = mysql_real_escape_string($ret [0] ['pic_small']);
					$sx = mysql_real_escape_string($ret [0]['sex']);
					$q = "update fbtab set firstname = '$fn', lastname = '$ln', photoUrl = '$ps', sex='$sx' where fbid='$fbid' ";
					$result2 = mysql_query($q) or die("cant update from  $q ".mysql_error());

					$fbname = "<fb:name uid='$fbid' useyou='false'/></fb:name>";
					$out.="<tr><td><a href='internals.php?fbid=$fbid' >$fbid</a></td><td title='appliance: $appliance'>$mcid</td><td title='mcid: $u->targetmcid' >$u->targetmcid</td><td>$fbname updated</td></tr>";
				}
			}
		}
	}
	$out.="</table>";
	return $out;
}



//**start here
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
connect_db();

list($mcid,$appliance) = fmcid($user); // allow even if not logged in to medcommons
if (isset($_GET['fbid']) ){
	$buf ='';
	$fbid = $_GET['fbid'];
	$dash = dashboard($user);
	$appname = $GLOBALS['healthbook_application_name'];

	$markup = <<<XXX
	<fb:fbml version='1.1'>
	$dash
    <fb:explanation>
      <fb:message>My HealthBook Log</fb:message>
     <fb:wall>
XXX;
	$q = "select * from hblog where fbid='$fbid' order by ind desc limit 50";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	while ($r = mysql_fetch_object($result))
	{
		$markup .= <<<XXX
		<fb:wallpost linked=false uid="$fbid" t="$r->time" ><span style="font-size:12px">$r->title</span><br/><span style="font-size:10px">$r->body</span></fb:wallpost>
XXX;
	}
	$markup .= <<<XXX
  </fb:wall></fb:explanation>
</fb:fbml>
XXX;
	echo $markup;

}
else echo update_fbtab($facebook, false,false,false);
?>