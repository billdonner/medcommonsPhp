<?php
require 'healthbook.inc.php';
function fail($err)
{
	$markup = <<<XXX
<fb:fbml version='1.1'>
  <fb:error>
    <fb:message>Error: $err</fb:message>     
       <p>we're sorry, check back soon</p>  
  </fb:error>
</fb:fbml>
XXX;
	echo $markup;
	exit;
}
//**start here
connect_db();
$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();
$dash = hurl_dashboard($user,'plug-ins'); // make it pretty
$u = HealthBookUser::load($user);
$t = $u->getTargetUser();
if ($t===false||$t->mcid===false) {
	// redirect back to indexphp
	$page = $GLOBALS['facebook_application_url'];
	$markup =  "<fb:fbml version='1.1'>redirecting via facebook to $page". "<fb:redirect url='$page' /></fb:fbml>";
	echo $markup;
	exit;
};

if (isset($_REQUEST['title'])) $package = $_REQUEST['title'];else $package='?no title?';
if ((isset($_REQUEST['map']))&& ($_REQUEST['map']!='') ) 
	$map = "<fb:success><fb:message>$package was successfully invoked</fb:message><p>Here's the work that would have been done, 
	if we were really doing something</p>
	<p>".$_REQUEST['map']."</p></fb:success>"; else $map='';
if ((isset($_REQUEST['err']))&& ($_REQUEST['err']!='') )
	$err = "<fb:error><fb:message>$package encountered errors</fb:message><p>".$_REQUEST['err']."</p></fb:error>"; else $err='';
if (isset($_REQUEST['v']))
{
	// display a piece of code so it can be copied out nicely
	$ind = $_REQUEST['v'];
	$q = "SELECT * from fbmlcode where authorfbid='$t->targetfbid' and ind='$ind' ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	$r = mysql_fetch_object($result);
	if (!$r) fail("Invalid index");
	echo "<fb:fbml version='1.1'>$dash"; //see if we can get away with this and more
	echo "<fb:explanation><fb:message>HBML Code Dump for $r->description <a class='tinylink'
  href='http://apps.facebook.com/profile.php?id=$r->authorfbid'>author</a>
<a class='tinylink' href=hbmlexec.php>hbml directory</a> </fb:message>
<textarea rows=20 cols=160 name=code readonly=readonly> $r->code </textarea>
</fb:explanation></fb:fbml>";
	exit;
}

if (!isset($_REQUEST['i']))
{
	// if nothing specified, put up a little directory
	$innards = '';
	$more = "<a class='tinylink' href=hbmledit.php>add more</a>";
	$q = "SELECT * from fbmlcode where authorfbid='$t->targetfbid'  order by ind desc limit 20 ";
	$result = mysql_query($q) or die("Cant $q ".mysql_error());
	while ($r = mysql_fetch_object($result))
	{
		$innards .= "<a href='hbmlexec.php?i=$r->ind' >$r->description</a><br/>";
	}
	if ($innards=='')
	$guts = "<fb:message>No Installed HBML Plug-ins for  <fb:name uid='$t->targetfbid'  useyou='true' possessive=false linked=false /> $more</fb:message>";
	else $guts =<<<XXX
<fb:message><fb:name uid='$t->targetfbid'  useyou='true' possessive=true linked=false /> HBML Plug-in Rack $more</fb:message> 
<p>For now, click on a package name to activate a form for updating your health record. A  future version will present the form directly in line here, in the Rack, with custom graphics</p>
    $innards
XXX;
	$samples = $GLOBALS['base_url'].'/hbmlsamples.html';

	$markup = <<<XXX
<fb:fbml version='1.1'>
$dash
$map
$err
  <fb:explanation>
    $guts 
     <p>for the near term, all HBML code is kept on a per-user basis, and must be separately added by each user via a cut and paste into the
      builtin <a class='tinylink' href=hbmledit.php>HBML Editor</a></p>
  <p>HBML code examples can be found <a target='_new' class=tinylink href='$samples'>here</a></p>   
  </fb:explanation>
</fb:fbml>
XXX;
	echo $markup;
	exit;
}
$ind = $_REQUEST['i'];
$q = "SELECT * from fbmlcode where authorfbid='$t->targetfbid' and ind='$ind' ";
$result = mysql_query($q) or die("Cant $q ".mysql_error());
$r = mysql_fetch_object($result);
if (!$r) fail("Invalid index");

// at this point, we should just echo back the fbml and let facebook deal with this
echo "<fb:fbml version='1.1'>$dash"; //see if we can get away with this and more
echo "<fb:explanation><fb:message>HBML Code Sandbox for $r->description <a class='tinylink'  href='hbmlexec.php?v=$r->ind' >view</a> <a class='tinylink'
  href='http://apps.facebook.com/profile.php?id=$r->authorfbid'>author</a>
<a class='tinylink' href=hbmlexec.php>hbml directory</a> </fb:message>";
echo $r->code;
echo "<p>This code is not sanctioned by MedCommons Healthbook. Use it at your own risk.</p></fb:explanation></fb:fbml>";


?>