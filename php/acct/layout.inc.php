<?php
require_once "dbparamsidentity.inc.php"; // this becomes a dependency on /var/www/php as include
require_once "alib.inc.php";
// routines to layout pages

function check_personal(){return true;}
function check_ephr(){return true;}
function check_group(){return true;}
function check_rls(){return true;}
function check_backup(){return true;}
function check_settings(){return true;}




function std ($desc,$title,$customhead,$onload,$custombody)
{
	if ($onload===false) $onload = "'initBasic()'";
	$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="$desc"/>
        <meta name="robots" content="noindex,nofollow"/>
        <title>$title</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="stylesheet" type="text/css" href="tabs.css"/>
        <link rel="stylesheet" type="text/css" href="rls.css"/>
        <link rel="stylesheet" type="text/css" href="autoComplete.css"/>
        <style type="text/css" media="all"> @import "main.css";</style> 
        <!-- <style type="text/css" media="all"> @import "acctstyle.css";</style> -->
        <style type="text/css" media="all"> @import "theme.css"; </style>
        <style type="text/css" media="all"> @import "theme.css.php"; </style>
        <script src="MochiKit.js" type="text/javascript"></script>
        <script src="tabs.js" type="text/javascript"></script>
        <script src="blender.js" type="text/javascript"></script>
        <script src="utils.js" type="text/javascript"></script>
        <script src="autoComplete.js" type="text/javascript"></script>
$customhead
   </head>
    <body id="css-zen-garden" onload=$onload>
    <div id="container">
 $custombody
    </div>
    </body>
    </html>
XXX;
	return $html;
}
function stdlayout ($middlediv)
{
	// put the create,import, etc links directly to the secure site, there is no longer any value in redirecting thru /www
	// which just always ends up back in the same place
	// of course, if this file makes it back to the old website, it will need to be adjusted
	$base = $GLOBALS['Homepage_Url'];
	$secure = $GLOBALS['Commons_Url'];
	$startpage = $GLOBALS['SecureLoginUrl'];
	$time = "from JS";
	list($accid,$fn,$ln,$email,$idp,$cl)=testif_logged_in();
  // ssadedin: added IE6 hack to correctly render logo below
	$top = <<<XXX
    <!--[if lt IE 7]>
      <script type="text/javascript">
        function initLogo() {
          var logo = $('logo');
          logo.src = 'images/blank.gif';
          logo.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='images/mc_logo.png', sizingMethod='scale')";
        }
        addLoadEvent(initLogo);
      </script>
    <![endif]-->
      
       <div id="intro">
         <div id="introcontainer">
           <table width="100%"><tr>
              <td align=left>
                <div id="pageHeader">
                  <a href="$base">
                    <img alt="MedCommons" 
                      id="logo"
                      width="246"
                      height="60"
                      src="images/mc_logo.png"
                       /></a>
                 </div>
            </td>
            <td align=right>    
                   <div id="trackingBox">
                    <div id="trackboxOuter"> 
                      <div style="background-color: rgb(243,243,243)"><span style="border-right: rgb(223,228,235) 0px solid; border-top: rgb(223,228,235) 0px solid; display: block; font-size: 1px; margin-left: 3px; overflow: hidden; border-left: rgb(223,228,235) 2px solid; margin-right: 0px; border-bottom: rgb(223,228,235) 0px solid; height: 1px; background-color: rgb(203,214,227)"></span><span style="border-right: rgb(223,228,235) 0px solid; border-top: rgb(223,228,235) 0px solid; display: block; font-size: 1px; margin-left: 2px; overflow: hidden; border-left: rgb(223,228,235) 1px solid; margin-right: 0px; border-bottom: rgb(223,228,235) 0px solid; height: 1px; background-color: rgb(203,214,227)"></span><span style="border-right: rgb(223,228,235) 0px solid; border-top: rgb(223,228,235) 0px solid; display: block; font-size: 1px; margin-left: 1px; overflow: hidden; border-left: rgb(223,228,235) 1px solid; margin-right: 0px; border-bottom: rgb(223,228,235) 0px solid; height: 1px; background-color: rgb(203,214,227)"></span><span style="border-right: rgb(223,228,235) 0px solid; border-top: rgb(223,228,235) 0px solid; display: block; font-size: 1px; margin-left: 0px; overflow: hidden; border-left: rgb(223,228,235) 1px solid; margin-right: 0px; border-bottom: rgb(223,228,235) 0px solid; height: 2px; background-color: rgb(203,214,227)"></span></div>
                      <div id="trackboxTop">  
                        <form method='post' action="$secure/trackingbox.php">
                          <input type='hidden' name='returnurl2' value="$base/alreadyin.php"/>
                          <input type='hidden' name='returnurl' value="$base/badtracknum.php"/>
                            <span id="tbTnLabel" style="vertical-align: middle;">Tracking#</span>&nbsp;
                            <span style="vertical-align: middle;">
                              <input type='text' name='trackingbox' size='16' maxlength='64'/>
                              <input id="goButton" type="submit" value="Go"/>
                            </span>
                        </form>
                        </div><!--/trackboxTop-->
                        <div id="trackboxBottom">
                           <div style="font-size: 10px; padding: 0px 6px">
                             <b>Welcome, <span id='tbName'>$fn $ln </span></b><br/>
                             Acct <a href='goStart.php'><span id='tbAccId'>$accid</span></a><br/>
                             Logged on, updated at <span id='tbDateTime'><span id='timeofday'>$time</span></span>
                           </div>
                        </div>
                        <div style="background-color: rgb(243,243,243)"><span style="border-right: rgb(237,237,237) 1px solid; border-top: rgb(237,237,237) 0px solid; display: block; font-size: 1px; margin-left: 0px; overflow: hidden; border-left: rgb(237,237,237) 0px solid; margin-right: 0px; border-bottom: rgb(237,237,237) 0px solid; height: 2px; background-color: rgb(230,230,230)"></span><span style="border-right: rgb(237,237,237) 1px solid; border-top: rgb(237,237,237) 0px solid; display: block; font-size: 1px; margin-left: 0px; overflow: hidden; border-left: rgb(237,237,237) 0px solid; margin-right: 1px; border-bottom: rgb(237,237,237) 0px solid; height: 1px; background-color: rgb(230,230,230)"></span><span style="border-right: rgb(237,237,237) 1px solid; border-top: rgb(237,237,237) 0px solid; display: block; font-size: 1px; margin-left: 0px; overflow: hidden; border-left: rgb(237,237,237) 0px solid; margin-right: 2px; border-bottom: rgb(237,237,237) 0px solid; height: 1px; background-color: rgb(230,230,230)"></span><span style="border-right: rgb(237,237,237) 2px solid; border-top: rgb(237,237,237) 0px solid; display: block; font-size: 1px; margin-left: 0px; overflow: hidden; border-left: rgb(237,237,237) 0px solid; margin-right: 3px; border-bottom: rgb(237,237,237) 0px solid; height: 1px; background-color: rgb(230,230,230)"></span></div>
                      </div>
                    </div><!--/trackingBox-->
       </td></tr>
     </table>         
      </div><!--/introcontainer-->
    </div><!--/intro-->
XXX;


	$host = $_SERVER['HTTP_HOST'];
	$acct = $GLOBALS['Accounts_Url'];
	$bottom=<<<XXX
	     <div >
   
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp;  <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>
            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
      
            <div class="p1">$host <a href='$acct/myPrefs.php'>&#169;</a> MedCommons 2006</div>
        </div>
XXX;
	$ccr = $secure."gwredir.php?p=gwredir";
	$rls = tryRls($accid);
  $newUrl = "";
  if($rls!==false) {
    if (isset($GLOBALS['Default_Repository']))
      $GLOBALS['RLS_Default_Repository'] = $GLOBALS['Default_Repository'];
    else 
      $GLOBALS['RLS_Default_Repository'] ='';
    $newUrl = $GLOBALS['RLS_Default_Repository'].'/tracking.jsp?tracking=new&accid='.$accid;
  }
	$eccr = tryECCR($accid);
	$groups = tryGroups($accid);

  // TODO:  make it look up the guid !!!!
  // It will NOT always be on the default gateway
  $currentCcrUrl = $GLOBALS['Default_Repository']."/CurrentCCR.action?a=".$accid;
  $currentCcr = tryCCCR($accid);

	$links = array(

	array ('Home',"<a href='$base/?p=commons'>Home</a>",true),

//	array ('My Account',"<a href=goStart.php>My Account</a>",true),

	array ('Group',"<a href='$groups'>Group</a>",($groups!==false)),
	
	array ('Group Worklist',"<a href='$rls'>Group Worklist</a>",($rls!==false)),

	array ('New User Account',"<a href='$newUrl' target='_blank'>New User Account</a>",($rls!==false)),

	array ('Current CCR',"<a href='$currentCcrUrl' target='blank'>Current CCR</a>", $currentCcr != false),

	array ('Draft CCR',"<a href='$ccr&a=OpenCCR'
                  title='Open a CCR that you previously saved' 
                  target=_new>Draft CCR</a>",true),

	array ('Emergency PHR',"<a href='$eccr'> <span class='emergencyccr'>Emergency PHR</span></a>",($eccr!==false)),

	array ('Import CCR',"<a href='$ccr&a=ImportCCR'
                  title='Create a CCR by uploading a file to MedCommons' 
                  target=_new>Import CCR</a>",true),

	array ('Create PHR',"<a href='$ccr&a=CreateCCR'
			title='Create a new CCR' 
				target=_new>Create PHR</a>",true),

	array ('Settings',"<a href=userinfo.php>Settings</a>",true),

  array ('Join Group',"<a href='GxGroup.php?op=join'>Join Group</a>",true),

	array ('Archive/Backup',"<a href='$base/?p=press'>Archive/Backup</a>",false),
		
	array ('Incoming Faxes',"<a href='$base/?p=press'>Archive/Backup</a>",false),
		
	array ('Code',"<a  href='explaincode.php'>Code</a>",true),

	array ('Personalize',"<a href='myPrefs.php'>Personalize</a>",check_settings()),
	
	array ('logout',"<a href='$base/logout.php' title='Log out from your MedCommons Account'>Logout</a>",true),


	);

	$ll='<ul>';
	foreach ($links as $link){
		if ($link[2]) $v = $link[1]; else $v = "<span class='off'>$link[0]</span>";
		$ll.="<li>$v</li>";
	}
	$ll.="</ul>";



	// should say
	$links = <<<XXX
             <div id="linkList" class="bulletLink">
                <div id="linkList2">
                    <div class="lside">
                        <h3 class="resources">
                            <span>Dynamic Links</span>
                        </h3>
                       $ll
                    </div>
                  </div>
                </div>
XXX;

	// just for fun

	$right = <<<XXX
	ads go here
XXX;

	//$upcall= layout ("TOP TOP TOP","LEFT LEFT LEFT",$bottom,"RIGHT RIGHT RIGHT","BOTTOM BOTTOM BOTTOM");

	$upcall= layout ($top,$links,$middlediv,false,$bottom);
	return $upcall;


}


function layout ($topdiv,$leftdiv,$middlediv,$rightdiv,$bottomdiv)
{

	$border = 0;
	$left_spacer = "<td valign=top align=left width='160px'>";
	$right_spacer ="<td valign=top align=right width='100px'>";
	if ($topdiv===false)$optionaltop='';
	else
	$optionaltop = <<<XXX
<tr><td>
<table border='$border' width='100%' id='top_table' bgcolor=white><tr>
<td align=left id = 'top' bgcolor=white>
<div id='header_div'>$topdiv
<div style="background-color: rgb(255,255,255)"><span style="border-right: rgb(249,249,249) 1px solid; border-top: rgb(249,249,249) 0px solid; display: block; font-size: 1px; margin-left: 0px; overflow: hidden; border-left: rgb(249,249,249) 1px solid; margin-right: 0px; border-bottom: rgb(249,249,249) 0px solid; height: 2px; background-color: rgb(243,243,243)"></span><span style="border-right: rgb(249,249,249) 1px solid; border-top: rgb(249,249,249) 0px solid; display: block; font-size: 1px; margin-left: 1px; overflow: hidden; border-left: rgb(249,249,249) 1px solid; margin-right: 1px; border-bottom: rgb(249,249,249) 0px solid; height: 1px; background-color: rgb(243,243,243)"></span><span style="border-right: rgb(249,249,249) 1px solid; border-top: rgb(249,249,249) 0px solid; display: block; font-size: 1px; margin-left: 2px; overflow: hidden; border-left: rgb(249,249,249) 1px solid; margin-right: 2px; border-bottom: rgb(249,249,249) 0px solid; height: 1px; background-color: rgb(243,243,243)"></span><span style="border-right: rgb(249,249,249) 2px solid; border-top: rgb(249,249,249) 0px solid; display: block; font-size: 1px; margin-left: 3px; overflow: hidden; border-left: rgb(249,249,249) 2px solid; margin-right: 3px; border-bottom: rgb(249,249,249) 0px solid; height: 1px; background-color: rgb(243,243,243)"></span></div></div>
</div></td></tr></table>
</td></tr>
XXX;
  // ssadedin: note, added extra divs above to soften bottom edge
	if ($bottomdiv===false)
	$optionalbottom='';	else {
		if ($leftdiv!==false)	$left = "$left_spacer &nbsp;</td>";else $left='';
		if ($rightdiv!==false)	$right = "$right_spacer &nbsp;</td>"; else $right='';



	}
	$optionalbottom=<<<XXX
<tr><td>
<table border='$border' width='100%' id='bottom_table' bgcolor=white><tr>
$left
<td  align=center id = 'bottom' bgcolor=white>
<div id ='footer2'>$bottomdiv</div></td>
$right</tr></table>
</td></tr>
XXX;

	if ($rightdiv===false)
	$optionalright = ''; else
	$optionalright =<<<XXX
$right_spacer
<table border='$border'   id ='right_col_table' bgcolor=white>
<tr><td><div id='right_div'>$rightdiv
</div></td></tr></table>
</td>
XXX;
	if ($leftdiv===false)
	$optionalleft = ''; else
	$optionalleft = <<<XXX
$left_spacer
<table border='$border'  id ='left_col_table' bgcolor=white>
<tr><td><div id='left_div'>$leftdiv</div></td></tr></table>
</td>
XXX;

	if ($middlediv===false)
	$optionalmiddle = ''; else
	$optionalmiddle = <<<XXX
	<td valign=top align=center>
<table border='$border'   id = 'middle_col_table' bgcolor='white' width='100%'>
<tr><td ><div id='middle_div'><div id='content'>$middlediv</div></div></td></tr></table>
</td>
XXX;

	$table = <<<XXX
<table border='$border' width='100%' border=2 id='outer_table' cellpadding="0" cellspacing="0" bgcolor=white>
$optionaltop
<tr><td>
<table border='$border' width='100%' id='middle_table' bgcolor=white><tr>
$optionalleft
$optionalmiddle
$optionalright
</tr>
</table>
</td></tr>

$optionalbottom
</table>
XXX;


	return $table;
}
?>
