<?php

require 'healthbook.inc.php';
function show_account_admins()
{
	$extra = '';

	// return the appliance and the medcommons user id
	$appliance = false;
	$mcid = false;
	$out="<table><tr><th>fbid</th><th>family</th><th>admin mcid</th><th>mc first</th><th>mc last</th><th>mc sex</th></tr>";
	// utab=Select * from fbtab where fbid == '$user'
$q = "select * from  fbtab where mcid!=0 and familyfbid = fbid"; 
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($u!==false){
			$mcid=$u->mcid;
			$appliance = $u->applianceurl;
			$fbid = $u->fbid;
	
				$family = "<fb:name uid='$u->familyfbid' useyou='false'/></fb:name>";	
			$out.="<tr><td><a href='internals.php?fbid=$fbid' >$fbid</a></td><td>$family</td><td title='appliance: $appliance'>$mcid</td>
			<td>$u->firstname</td><td>$u->lastname</td><td>$u->sex</td></tr>";

		}
	}
	$out.="</table>";
	return $out;
}
function show_facebook_only()
{
	$extra = '';

	// return the appliance and the medcommons user id
	$appliance = false;
	$mcid = false;
	$out="<table><tr><th>user</th></tr>";
	// utab=Select * from fbtab where fbid == '$user'
	$q = "select * from  fbtab where mcid=0 ";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($u!==false){
			$mcid=$u->mcid;
			$appliance = $u->applianceurl;
			$fbid = $u->fbid;
	
			$fbname = "<fb:name uid='$u->fbid' useyou='false'/></fb:name>"; //sponsor field should be retired
			$out.="<tr><td><a href='internals.php?fbid=$fbid' >$fbname</a></td></tr>";

		}
	}
	$out.="</table>";
	return $out;
}
function show_patient_accounts($user)
{
	
	

	// return the appliance and the medcommons user id
	$appliance = false;
	$mcid = false;
	$out="<table><tr><th>family</th><th>mcid</th><th>sponsor</th><th>first</th><th>last</th><th>sex</th></tr>";
	// utab=Select * from fbtab where fbid == '$user'
	$q = "select * from  mcaccounts ";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($u!==false){
			$mcid=$u->mcid;
			$appliance = $u->applianceurl;
				$fbname = "<fb:name uid='$u->sponsorfbid' useyou='false'/></fb:name>";
				$family = "<fb:name uid='$u->familyfbid' useyou='false'/></fb:name>";	
			$out.="<tr><td>$family</td><td title='appliance: $appliance'>$mcid</td><td>$fbname</td>
			<td>$u->firstname</td><td>$u->lastname</td><td>$u->sex</td></tr>";

		}
	}
	$out.="</table>";
	return $out;
}
function show_family_care_team_members($user)
{
	
	
	// return the appliance and the medcommons user id
	$appliance = false;
	$mcid = false;
	$out="<table><tr><th>caregiver</th><th>family</th><th>patient mcid</th><th>mc first</th><th>mc last</th><th>mc sex</th></tr>";
	// utab=Select * from fbtab where fbid == '$user'

$q = "select * from  fbtab where mcid!=0 and familyfbid != fbid";
	$result = mysql_query($q) or die("cant select from  $q ".mysql_error());
	while($u=mysql_fetch_object($result))
	{
		if ($u!==false){
			$mcid=$u->mcid;
			$appliance = $u->applianceurl;
				$family = "<fb:name uid='$u->familyfbid' useyou='false'/></fb:name>";	
							$giver= "<fb:name uid='$u->fbid' useyou='false'/></fb:name>";	
			$out.="<tr><td>$giver</td><td>$family</td><td title='appliance: $appliance'>$mcid</td>
			<td>$u->firstname</td><td>$u->lastname</td><td>$u->sex</td></tr>";

		}
	}
	$out.="</table>";
	return $out;
}
function fbtab($fbid,$mcid,$appliance){
	$session_key = $_REQUEST['fb_sig_session_key'];
	$dash = dashboard($fbid,false);
	$appname = $GLOBALS['healthbook_application_name'];
	$table1a = show_account_admins();
		$table1 = show_facebook_only();
	$table3 = show_patient_accounts($fbid);
	
	$table2 = show_family_care_team_members($fbid);
	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash

      <p>This information is available to help us test the $appname Server; your fbid is $fbid; your session key is $session_key</p>
    <fb:explanation>
      <fb:message>MedCommons Family Account Administrators</fb:message>
      $table1a
    </fb:explanation>
        <fb:explanation>
          <fb:message>Facebook App Lurkers</fb:message>
      $table1
    </fb:explanation>
    <fb:explanation>
      <fb:message>MedCommons Patient Accounts</fb:message>
      $table3
    </fb:explanation>
        <fb:explanation>
       <fb:message>Facebook Family Care Team Members</fb:message>
      $table2
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
	$dash = dashboard($user,false);
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