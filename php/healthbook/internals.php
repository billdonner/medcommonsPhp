<?php

require 'healthbook.inc.php';
function show_fbtab($user,$mcid,$appliance)
{
	$extra = '';
	if ($user) $extra.= "'$user'=fbid AND ";
	if ($mcid) $extra.= "'$mcid'=mcid AND ";
	if ($appliance) $extra.= "'$appliance'=appliance AND ";
	if ($extra!=='')$extra ="WHERE ".substr($extra,0,strlen($extra)-4);
	// return the appliance and the medcommons user id
	$appliance = false;
	$mcid = false;
	$out="<table><tr><th>fbid</th><th>mcid</th><th>viewing</th><th>facebook name</th></tr>";
	// utab=Select * from fbtab where fbid == '$user'
	$q = "select * from  fbtab $extra ";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($u!==false){
			$mcid=$u->mcid;
			$appliance = $u->applianceurl;
			$fbid = $u->fbid;

			$fbname = "<fb:name uid='$fbid' useyou='false'/></fb:name>";
			$out.="<tr><td><a href='internals.php?fbid=$fbid' >$fbid</a></td><td title='appliance: $appliance'>$mcid</td><td title='mcid: $u->targetmcid' >$u->targetfbid</td><td>$fbname</td></tr>";
		}
	}
	$out.="</table>";
	return $out;
}


function fbtab($fbid,$mcid,$appliance){
	$session_key = $_REQUEST['fb_sig_session_key'];
	$dash = dashboard($fbid);
	$appname = $GLOBALS['healthbook_application_name'];
	$table = show_fbtab(false,false,false);
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash

    <fb:explanation>
      <fb:message>$appname ==> MedCommons Mappings</fb:message>
      <p>This information is available to help us test the $appname Server; your fbid is $fbid; your session key is $session_key</p>
      $table
    </fb:explanation>
  </fb:explanation>
</fb:fbml>
XXX;

	return $markup;
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
else echo fbtab($user,$mcid,$appliance);
?>