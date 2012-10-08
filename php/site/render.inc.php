<?php

$WEBSITE_PROTOCOL="https"; // to allow for dev boxes without https

require_once 'site_config.php';

function testif_logged_in()
{
	if (!isset($_COOKIE['mc'])) //wld 10 sep 06 strict type checking
	return false;
	$mc = $_COOKIE['mc'];

	$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
	if ($mc!='')
	{
		$accid=""; $fn=""; $ln = ""; $email = ""; $idp = ""; $auth="";
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
				case 'auth'; $auth = $val; break;
			}
		}
	}
	return array($accid,$fn,$ln,$email,$idp,$mc,$auth);
}
function renderas_webpage($contents=false,$vars=array())
{
	global $WEBSITE,$WEBSITE_PROTOCOL,$GLOBALREDIRECTOR, $HAS_LOCAL_APPLIANCE;
	$script = $_SERVER["SCRIPT_NAME"];
	$break = explode('/', $script);
	$pagename=str_replace('.php','.htm',$break[count($break) - 1]);
	if ($contents===false)  $contents = file_get_contents('htm/'.$pagename);
	$maintitle = "NO TITLE FOR THIS PAGE";

	$pos = strpos($contents,'mainTitle=');
	if ($pos!==false)
	{ $npos = $pos+strlen('mainTitle=');	$c = substr($contents,$npos,1); $pos2 = strpos($contents,$c,$npos+2);
	if ($pos2!==false) $maintitle = substr($contents,$npos+1,$pos2-$npos-1);	}
	
	$host=$_SERVER['HTTP_HOST'];
	$on_appliance = $HAS_LOCAL_APPLIANCE;
	$logged_in = testif_logged_in();
	$time = gmdate ("M d Y H:i:s");
	$islogged = ($logged_in!==false)?'logged in':'not logged in';
	$onappl = $on_appliance?'on appliance':'on website';
	$purchasedisabled = $GLOBALS['purchase_disabled']?'disabled':'';
	$comments = <<<XXX

<!-- 
Rendering this MOD website page $pagename from server $host script $script 
User is $islogged  $onappl at gmt $time
MOD website is $WEBSITE ubersite is $GLOBALREDIRECTOR	
-->	

XXX;
	$markup = "<!-- loading header from /var/www/html/htm/_header.htm -->".file_get_contents("htm/_header.htm").$contents.
	"<!-- loading footer from /var/www/html/htm/_footer.htm -->".file_get_contents("htm/_footer.htm");

	$topright =  "<span id='visi' class=right > </span>";

	// build nav differently based on whether we see a cookie or not
	// and whether running as a Website or as an appliance site
	$GLOBALS['footerlogin'] ='';

	if ($logged_in === false)
	{
		if ($on_appliance){
		/*container for on appliance but not logged on*/
		$GLOBALS['footerlogin'] ='<li class=footerlogin><a href="/acct/login.php" title="login directly to this appliance" >Local Login</a></li>';  
		$navcontainer = <<<XXX
<li><a class=menu_how href="help.php">Help</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li ><a class=menu_nil  href="$WEBSITE_PROTOCOL://$WEBSITE/personal.php" >Sign In</a></li>
XXX;
		}
		else /* on website not logged on */
		$navcontainer = <<<XXX
<li><a class=menu_how href="/help.php">Help</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li ><a class=menu_nil  href="/personal.php" >Sign In</a></li>
XXX;
}
else
{
	list ($accid,$fn,$ln,$email,$idp,$mc,$auth) = $logged_in;

	if ($on_appliance)  /*on appliance and logged on*/
	$navcontainer = <<<XXX
<li><a class=menu_how href="help.php">Help</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li><a class=menu_list  href="/acct/home.php ">My Account</a></li>
XXX;
	else /*container for logged on website users*/
	$navcontainer = <<<XXX
<li><a class=menu_how href="help.php">Help</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li><a class=menu_list  href="$GLOBALREDIRECTOR/login/?q=$accid ">My Account</a></li>
XXX;

}

$navcontainer = '<ul id="navlist" class="listinlinetiny" >'.$navcontainer.'</ul>';
$names = array('$$$htmltitle$$$','$$$navcontainer$$$','$$$topright$$$', '$$$globalredirector$$$', '$$$loginpage$$$','$$$pageid$$$', '$$$modcomments$$$','$$$locallogin$$$','$$$purchasedisabled$$$');
$values = array($maintitle,       $navcontainer,      $topright,        $GLOBALREDIRECTOR,        $GLOBALS['global_login_url'],     $pagename,          $comments      ,   $GLOBALS['footerlogin'] ,$purchasedisabled);
foreach($vars as $n => $v) {
	$names[]='$$$'.$n.'$$$';
	$values[]=$v;
}
echo str_replace($names,$values,$markup);
}
?>
