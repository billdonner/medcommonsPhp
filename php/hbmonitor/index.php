<?php
// this is required of all facebook apps
//
// this is a separate, one file app that needs to get put in its own directory on some server, and connected up properly to facebook
//
// this file should be renamed index.php in the target directory, the appinclude should be customized, and the standard facebook collateral copied in as well


require 'appinclude.php';   // this is coming from the app-specific healthbook directory

$probepoints = array(
array("http://tenth.medcommons.net/hbmonitor/responder.php",'This app itself '),
array("http://tenth.medcommons.net/hbtest/responder.php",'HBTEST'),
array("http://frodo.medcommons.net/responder.php",'HealthBook'));



function css ()
{
	$css = <<<CSS
	<style type="text/css">
	a { color:  #3B5998;}
	a.tinylink  {font-size:xx-small; text-decoration:underline; color: gray;}
</style>
CSS;
	return $css;
}

function viewhbapps(){
	global $probepoints;
	$app = $GLOBALS['healthbook_application_name'];
	$markup = '';
	foreach ($probepoints as $probepoint)
	{
		$url = $probepoint[0];
		$label = $probepoint[1];
	$markup .= <<<XXX
<fb:explanation>
    <fb:message>$label</fb:message>
    <fb:iframe src='$url?facebook' height='100' width='500' frameborder='0' />
</fb:explanation>
XXX;
	}
	return $markup;
}

function facebookstats ($facebook,$user)
{
	$out = "<fb:explanation><fb:message>Facebook API Test Probes</fb:message>";
	//time some api calls into facebook from here 
	$t0 = microtime(true);
	//test 1 - smallest possible call asks if I am app user
	$info=$facebook->api_client->fql_query("SELECT is_app_user FROM user WHERE uid=$user;");
	$t1 = microtime(true);	
	//test 2 - get all my groups
	$ret = $facebook->api_client->fql_query("SELECT gid,pic_small,name FROM group WHERE gid IN
	                                   (SELECT gid FROM group_member WHERE uid='$user') ");
	$t2 = microtime(true);

	$delta1 = round($t1-$t0,3);
	$delta2 = round($t2-$t1,3);
            $self = $_SERVER['HTTP_HOST'];	
            $gmt = gmstrftime("%b %d %Y %H:%M:%S");
	$out.="<p>Running at $gmt GMT on $self";
	$out.="<p>Simplest FQL Api Call into Facebook returns in $delta1 seconds</p>";
	$out.="<p>Complex Api Call to get the group pics for all groups of which I am a member returns $delta2 seconds</p>";
	$out.="</fb:explanation>";
	return $out;
}

function monitor_dashboard ($user, $kind)
{
	$top = css();//dashboard($user);
	$bottom = <<<XXX
<fb:tabs>
 <fb:tab_item href='index.php?o=a' title='View Healthbook Apps' />
      <fb:tab_item href='index.php?o=f' title='View Facebook Stats' />
          <fb:tab_item href='index.php?o=s' title='View Summary' />
       
  </fb:tabs>
XXX;
	$needle = "title='$kind'";
	$ln = strlen($needle);
	$pos = strpos ($bottom,$needle);
	if ($pos!==false)
	{  // add selected item if we have a match
		$bottom = substr($bottom,0,$pos)." selected='true' ".
		substr ($bottom, $pos);
	}
	return $top.$bottom;
}
//**    start here
if (!isset($_REQUEST['o'])) $op=''; else $op=$_REQUEST['o'];

$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
switch ($op)
{
	case 'f': { $menu='View Facebook Stats';  $title="Monitor Facebook Stats";  $markup = facebookstats($facebook,$user) ;break;}

	case 'a': { $menu='View Healthbook Apps';  $title="Monitor HealthBook Apps";  $markup = viewhbapps()  ;break;}
	case 's':
	default :  { $menu='View Summary';  $title="Monitor FaceBook Status and HealthBook Apps";  
	$markup = facebookstats($facebook,$user).viewhbapps()  ;break;}
}
$dash = monitor_dashboard($user,$menu);
	$appname = $GLOBALS['healthbook_application_name'];
	$apikey = $GLOBALS['appapikey'];

$out =<<<XXX
	<fb:fbml version='1.1'><fb:title>$title</fb:title><fb:header>HealthBook and Facebook Information</fb:header>
	 <p>&nbsp;&nbsp;for MedCommons friends and family only...  <fb:if-user-has-added-app><fb:else><a class=applink href='http://www.facebook.com/add.php?api_key=$apikey&app_ref=home' >add $appname</a>  </fb:else></fb:if-user-has-added-app></p>
$dash   
$markup</fb:fbml>
XXX;
echo $out;
?>