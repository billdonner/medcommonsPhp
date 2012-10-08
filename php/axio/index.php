<?php
//regrettably, until we get rid of all the phantom activations of index.php we are just going to ignore any activations where the cookie is already set
require_once 'is.inc.php';

function indexheader($title){

	//$leagueimg = league_logo($league,'is');

	$page_header = page_header($title);
	$mimg = main_logo('is');
	$header = <<<XXX
	           $page_header
		$mimg
XXX;
	return $header;
}

// start here
if (!isset($_REQUEST['err']))
{
	$err="";
	if  ((isset($_REQUEST['logout']))||(isset($_COOKIE['u'] )))
	{
		 $openid=my_identity();
		if (isset($_COOKIE['u']))  $cook='set'; else $cook ='unset'; 
		if (isset($_REQUEST['logout'])) {$logout=$_REQUEST['logout'];
		islog('logout',$openid,"logout: $logout cookie: $cook");
		setcookie('u',false);
		// fall into regular code
		$err = "You were logged out - $openid";
		} else
		if (isset($_COOKIE['u'])){
			islog('phantom',$openid,"cookie: $cook");
			//just exit quitely if somehow we are here
			//echo ("Please try ".$_SERVER['PHP_SELF']."/?logout to clear your previous session");
			redirect('is.php'); //just go around again
			exit;
		}
	}
}
else
$err=$_REQUEST['err'];

if (isset($GLOBALS['openid_hack'])) { // skip all the authentication with openid if this is set
	$openidhack = $GLOBALS['openid_hack'];
}
else {$openidhack='';}
$header = indexheader('Please Sign On');
$markup = <<<XXX
<body >
  <div id='content' style='width:700px;'>
   <div id='is_header'  >
$header
</div>
<div id='is_body'>
<div id='is_c_section'  style='text-align:center;width:100%;border:purple;'><p>To use this service, you must register with InformedSports.com and have a valid OpenId.</p>
<p>To optimally use this service, your Browser should be reasonably new: FF>2.0, Safari>3.0, Opera>9.1,IE>7.0</p>
<p>For more information contact InformedSports at 1-800-434-3154</p>
<p class='errfield'>$err</p>
<form id='is_signon_box' method='post' action='auth.php'>

      <label class='infield_prompt' for='openid_url'>OpenID:</label>
      <input class='infield' type='text' value='$openidhack' id='openid_url' name='openid_url' /><br />
      <button class=infield_button onclick='document.getElementById("is_signon_box").submit(); return false;'><img src="images/openid.jpg" width='38' height='35' alt='OpenID' />login</button>
			<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;
</form>
</div>
</div>
<div id='is_footer'>
</div>
</div> 
</body>
</html>
XXX;

echo $markup;
?>
