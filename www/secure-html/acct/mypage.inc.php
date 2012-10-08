<?php
$GLOBALS['Trackers_Url'] = "https://secure.test.medcommons.net/trackers/"; // hopefully terry and i will fix soon

// wld 8/31/06 = merged in changes from simon for current ccr

// it is assumed this include file is started with

// $valid = those sections that can be present in this user's page
//
// $switches = those sections on the users page that are present
//
// $open == those sections on the users page that are pre-opened
//
//

// $__flat == give the page a flat look with no toggle options

// build standard account page, as a flat page of separate sections
// the sections are specified via the ?s param, which is a list of single char abbrevs for different sections
//

require_once "alib.inc.php";
require_once "ccrloglib.inc.php"; // the hard work is all in here
function sectionEnd($section)
{
	return "
	 </div>
	<!-- end of section $section -->
	";
}
function sectionHead($flat,$exposed, $section,$title,$counter){
	if ($flat) return "<div id = '$section'><small><i>$title</i></small><br>";
	$display = ($exposed)?'block':'none';
	$GLOBALS['csssec'] .=<<<XXX
div#$section { margin: 0px 20px 0px 20px; 
display: $display;
}
XXX;
	if ($counter==1)$items='item'; else $items='items'; //we seemed to have a loose /span that should have been /div
	$myDocs= <<<XXX
<!-- start of $section $title-->
<div class="p3">
<table><tr><td><a href="javascript:toggleLayer('$section');" title="Show $title">
<img src='images/tancycle.gif' alt='toggle'></a>
</td><td><h3>$title</h3></td><td><small>$counter $items</small></td></tr></table></div><div id = '$section'>
XXX;
	return $myDocs;
}


/////////////// main program starts here /////////////////

$__flat = true;

list($accid,$fn,$ln,$email,$idp,$cookie) = aconfirm_logged_in (); // does not return if not lo
//build menu to present from arg
// get settings for how to behave
$db = aconnect_db(); // connect to the right database

if (isset($__switches)) $switches = $__switches; // set from MCWidget.php
else {
	// apply switches from account record
	list($aswitches,$avalid) = get_switches($accid); // get whatever the account says
	if ($aswitches!='')	$switches=$aswitches;//apply default solo probs =
	if ($avalid=='') $avalid='abcdefghijklmnopqrstuvwxyz';
	$valid = $avalid; // just overwrite
}

$info = make_acct_form_components($accid);

//this builds the info about the user

$wwwUrl =$GLOBALS['BASE_WWW_URL'];
$logoutlink ="<a href='$wwwUrl/logout.html' target='_parent'>logout</a>";

$lasttime = time(); // pass this in as the time the server satisfied this request
$secureHost = $GLOBALS['Commons_Url'];
/* this is wrong, but not quite sure what happened to $einfo and the new phplint is compalining
$einfoDeclaration = '';
if($einfo != '') {
$einfoDeclaration = "window.einfo = evalJSON('$einfo');";
}
*/
$einfoDeclaration = "window.einfo = evalJSON('');";

if (isset($_COOKIE['theme']))
$theme = $_COOKIE['theme']; else $theme = '';


// Display message if any passed.
if (isset($_REQUEST['msg']))
$myMsg= $_REQUEST['msg']; else $myMsg='';

// go thru all the args and do whatever they say
$out='';
if (((strpos($switches,'c')!== false) || (strpos($switches,'e')!==false)))
{
	// only do this hard work if we really have to


	$prettyAccId = prettyaccid($accid);
	// at some point this should be broken into a separate module, but is currently full of local variables
	$miniview = false;
	$from = ''; // waiting for reconnection to federaiton
	// do a bunch of database reads to get rows from ccr log, sorted by idp

	$count = readdb($miniview,$accid,$from,$content,$tab,$emailbuf,$fn,$ln,$email,$street1,$street2,
	$city,$state,$postcode,$country,$mobile,$emergencyccr,$patientcard,$einfo,$trackerdb);

	// tell the user via email his page was viewed

	$ajstatline =  notifyuser($email,$accid,$fn,$ln,$emailbuf);

	// put together tab0
	$tab0content = ($miniview? '':tab0(true,$email,$mobile,$ln,$fn,$street1,$street2,$city,$state,$postcode));

	// assemble all the tabs
	$alltabs = assembletabs($miniview,$count,$content,$tab,$tab0content);

}
$count = strlen($switches);

for ($i=0; $i<$count; $i++){
	$func = substr($switches,$i,1);
	if ($func=='|') break; //quit if we hit a pipe
	$permitted = (strpos($valid,$func) !==false); // make sure this is permitted by role
	$exposed = (strpos($open,$func)!==false);

	if ($permitted) switch ($func) {
		// audit log for practice???
		case 'l': { // system log is framed
			require_once "auditlog.inc.php";
			$out .=
			sectionHead($__flat,$exposed,'auditlogSection',"System Audit Log",33).
			auditlog(0,30,'').
			sectionEnd('auditlogSection');
			break;
		}



		// acct docs is assembled here
		case 'd':{
			$acctDocs = getAccountDocuments($accid);
			$myDocs = "
			<div id='accountDocuments'>";
			$counter=0;
			if($acctDocs && (count($acctDocs) > 0)) {
				$myDocs .=
				"<ul style='font-size: 12px'>";

				foreach($acctDocs as $doc) {
					// ssadedin: temporary hack to display current ccr correctly:
					$raw="true";
					if($doc->dt_type=="CURRENTCCR") {
						$raw="false";
					}
					$myDocs.= "<li> <a href='".$GLOBALS['Commons_Url']."/gwredirguid.php?guid=".$doc->dt_guid."&raw=true' title='Open this document in your browser' target='accountDocument'>".$doc->dt_type;
					if($doc->dt_comment && ($doc->dt_comment != '')) {
						$myDocs.= "&nbsp;<i>".$doc->dt_comment."</i>";
					}
					$myDocs.= "</a>&nbsp;(".$doc->dt_create_date_time.")
                  &nbsp;&nbsp;<a href='deleteDocument.php?dtId=".$doc->dt_id."' title='Remove this document from your Account'><img class='clickable' src='images/trash.gif' /></a></li>";
				}
				$myDocs.= "</ul>";
				$counter++;
			}
			$myDocs .='</div>';
			$out .= sectionHead($__flat,$exposed,'docSection','Key Documents',$counter).$myDocs.sectionEnd('docSection');
			break;
		}

		/*		case 'm': {
		// this is the top menu, the menu links was built above
		$myMenu= "<div style='font-size: 11px;'>$menu<br></div>";
		$out .= $myMenu; break;
		}
		*/

		case 't':{ // trackers are framed ****** must fix up the call to ggtrckers.php
			$counter=1;
			$acct = $GLOBALS['Trackers_Url'].'viewTrackers.php';
			$iframe=<<<XXX
   <font size="-1">
      <div id="remote_14" style="border:0px;padding:0px;margin:0px;">
        <iframe src="$acct"  style="border:0px;padding:0px;margin:0px;width:100%;overflow:auto;" 
                                        frameborder="0" scrolling="auto"></iframe>
      </div>
    </font>
XXX;
			$out .= sectionHead($__flat,$exposed,'trackerSection','Trackers',$counter).$iframe.sectionEnd('trackerSection');

			break;
		}
		case 'n':{

			require_once "../rss/newslog.inc.php";
			$db = aconnect_db(); //not sure why we have to re-connect, but lets use other library

			$g=newslog(0,10,'');
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'newsSection','Healthcare News',$counter).$g.
			sectionEnd('newsSection');
			$out .= $body; break;
		}

		case 'p':{

			require_once "prefs.inc.php";
			$db = aconnect_db(); //not sure why we have to re-connect, but lets use other library

			$g=set_prefs($accid,$valid);
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'prefSection','Preferences',$counter).$g.
			sectionEnd('prefSection');
			$out .= $body; break;
		}
		case 'a':{
			// show admingroups if we have any

			// show all the group stuff
			require_once "../groups/glib.inc.php";
			require_once "../groups/mygroups.inc.php";
			$db = connect_db(); //not sure why we have to re-connect, but lets use other library
			$g=my_admingroups($accid);
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'adminSection','Group Administration',$counter).$g.
			sectionEnd('adminSection');

			$out .= $body; break;
		}


		case 'g':{
			// show groups if we have any

			// show all the group stuff
			require_once "../groups/glib.inc.php";
			require_once "../groups/mygroups.inc.php";
			$db = connect_db(); //not sure why we have to re-connect, but lets use other library
			$g=my_groups($accid);
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'memberSection','Memberships',$counter).
			$g.
			sectionEnd('memberSection');

			$out .= $body; break;
		}

		case 'x':{
			require_once "../groups/glib.inc.php";
			require_once "../groups/mygroups.inc.php";
			$db = connect_db(); //not sure why we have to re-connect, but lets use other library
			$g=my_adminpractices($accid);
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'myadminpracticeSection','Group Info',$counter).
			$g.
			sectionEnd('myadminpracticeSection');

			$out .= $body; break;
		}
		case 'y':{
			// show groups if we have any

			// show all the group stuff
			require_once "../groups/glib.inc.php";
			require_once "../groups/mygroups.inc.php";
			$db = connect_db(); //not sure why we have to re-connect, but lets use other library
			$g=my_practices($accid);
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'mypracticesSection','Providers Only',$counter).
			$g.
			sectionEnd('mypracticesSection');

			$out .= $body; break;
		}
		case 'z':{
			// show groups if we have any

			// show all the group stuff
			require_once "../groups/glib.inc.php";
			require_once "../groups/mygroups.inc.php";
			$db = connect_db(); //not sure why we have to re-connect, but lets use other library
			$g=my_providers($accid);
			$counter=($g=='')?0:1;
			$body =
			sectionHead($__flat,$exposed,'patientSection',"Providers",$counter).
			$g.
			sectionEnd('patientSection');

			$out .= $body; break;
		}

		case 'r':{
			// show extensions
			require_once "appsrvlib.inc.php";
			$g=my_appservices($accid);
			$counter=($g=='')?0:1;
			$myAppServices =
			sectionHead($__flat,$exposed,'extensionSection','Extensions to MedCommons',$counter).
			$g.
			sectionEnd('extensionSection');

			$out .= $myAppServices;
			break;
		}


		case 'b':{
			// show bill
			require_once "alib.inc.php";
			require_once "showbill.inc.php";
			require_once "appsrvlib.inc.php";
			list($balance,$g) = showbill($accid);
			$pp=prettyprice($balance);
			$myAppServices =
			sectionHead($__flat,$exposed,'billSection',"Charges: $pp",'').
			$g.
			sectionEnd('billSection');

			$out .= $myAppServices;
			break;
		}

		case 'i':{
			// show uerinfo
			require_once "../acct/userinfo.inc.php";
			$db=aconnect_db(); // again? why

			$out .= sectionHead($__flat,$exposed,'userInfoSection','Personal Info',1).
			userinfo($accid,0). // just basic stuff
			sectionEnd('userInfoSection');
			break;
		}

		case 'j':{
			// show uerinfo
			require_once "../acct/puinfo.inc.php";

			$db=aconnect_db(); // again? why

			$out .= sectionHead($__flat,$exposed,'personaSection','Personas',1).
			puinfo ($accid,0).
			sectionEnd('personaSection');
			break;
		}

		case 'f':{
			// show uerinfo
			require_once "../acct/acctlog.inc.php";
			$db=aconnect_db(); // again? why

			$out .= sectionHead($__flat,$exposed,'acctlogSection','Acct Log',1).
			acctlog($accid,10,'').
			sectionEnd('acctlogSection');
			break;
		}


		/*
		case 'i':{

		// emergency info
		$myEinfo=<<<XXX
		<div id="patientCardOuter">
		$patientcard
		</div>
		XXX;
		$out.= sectionHead($__flat,$exposed,'einfoSection','My Emergency Information',1). $myEinfo. sectionEnd('einfoSection');
		break;
		}
		*/
		case 'e':{
			// the emergencyCCR
			$myECCR=<<<XXX
             <div id='emergencyccr' class='rounded'> $emergencyccr</div>
XXX;
			$out .=sectionHead($__flat,$exposed,'eccrSection',"Emergency CCR",1).
			$myECCR.
			sectionEnd('eccrSection');
			break;
		}
		case 'c':{
			// the CCR Log
			$myCCRs=<<<XXX
                <div id="content" > 
                    $alltabs
                    <br>
                    <br>
                </div> 
XXX;
			$out .= sectionHead($__flat,$exposed,'ccrSection','CCRs',1).$myCCRs.sectionEnd('ccrSection');
			break;
		}
		/*		case 'h':{
		$myPageHeader = $info->header;
		$out.=$myPageHeader;	break;
		}
		*/

	}// end of switch on $func for permitted items
}// end of for loop

$desc = "Account Information Page";
$title = 'Account Information';
$startpage='acct/'.$startpage;
if (isset($_noheader)){
	$top='';
} else

$top = make_acct_page_top ($info,$accid,$email,'',$desc,$title,$startpage,"myPrefs.php?valid=$valid");

if (isset($_nofooter)){
	$bottom='';
} else
$bottom = make_acct_page_bottom ($info);

if ($__flat){ $togglehead='';} else {
	$css = $GLOBALS['csssec'];
	$togglehead = <<<XXX
<style type="text/css" media="all">
$css
</style>
<script>
function toggleLayer(whichLayer)
{
	if (document.getElementById)
	{
		// this is the way the standards work
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display? "":"block";
	}
	else if (document.all)
	{
		// this is the way old msie versions work
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
	else if (document.layers)
	{
		// this is the way nn4 works
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display? "":"block";
	}
}
function disableSubmit(whichButton)
{
	if (document.getElementById)
	{
		// this is the way the standards work
		document.getElementById(whichButton).disabled = true;
	}
	else if (document.all)
	{
		// this is the way old msie versions work
		document.all[whichButton].disabled = true;
	}
	else if (document.layers)
	{
		// this is the way nn4 works
		document.layers[whichButton].disabled = true;
	}
}
</script>
 
XXX;
}

require_once "layout.inc.php";
$layout = stdlayout (   $out );

$styleline = ''; //forthcoming
$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="My MedCommons Account Page $accid"/>
        <meta name="robots" content="all"/>
        <title>My MedCommons Account Page for $accid</title>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <style type="text/css" media="all"> @import "acctstyle.css";</style>
        $styleline
           <script src="MochiKit.js" type="text/javascript"></script>
        <script src="tabs.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="tabs.css"/>
        <script type="text/javascript" src="blender.js"></script>
        <script src="utils.js" type="text/javascript"></script>
        <script type="text/javascript" >
               <!-- 
function paymentpopup(url) {
	newwindow=window.open(url,'_payment','height=600,width=450,toolbar=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
function personapopup(url) {
	newwindow=window.open(url,'_persona','height=300,width=500');
	if (window.focus) {newwindow.focus()}
	return false;
}

// --> 
			</script>

$togglehead
   </head>
    <body id="css-zen-garden" onload="initMyCCRLog('$accid','$lasttime');"  >
    <div id="container">
 $layout
    </div>
    </body>
    </html>
XXX;
echo $html;
?>
