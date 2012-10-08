<?php
require_once "dbparamsidentity.inc.php"; // this becomes a dependency on /var/www/php as include
require_once "alib.inc.php";
// routines to layout pages
/*
                <li id="menuBarAddDocLink">
                    <a href="secureredir.php?p=gwredir&a=AddDocument" target='contentwindow' onClick="showContentFrame();" title="Add a new Document to your account">Add Document</a></li>
                  <img id="spacer" src="images/blank.gif"/>
                  <li id="menuBarLogInLink"><a href="loginredir.php" title="Log in to your MedCommons Account">Log In</a></li>

                  */
function stdlayout ($middlediv)
{
	$base = $GLOBALS['Homepage_Url'];
	$secure = $GLOBALS['Commons_Url'];
	$startpage = $GLOBALS['SecureLoginUrl'];
	list($accid,$fn,$ln,$email,$idp,$cl)=testif_logged_in();
	$top = <<<XXX
            <div id="intro">                    
                <div id="pageHeader">
                  <a href="index.html">
                    <img alt="MedCommons" 
                      id="logo"
                      width="246"
                      height="50"
                      src="images/blank.gif"
                      style="filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='images/mc_logo.png', sizingMethod='scale');"
                    /></a>
                </div>
                <br/>
                <span id="logofootnote" class="p2">a patient centric ccr transport and storage network</span>
              </div><!--/introcontainer-->
            </div><!--/intro-->
            <div id="menubar">
              <div id="actionLinks" class="bulletLink">
                <div id="actionLabel">Get Started </div>
                <ul>
                  <li><A href="$secure/secureredir.php?p=gwredir&a=CreateCCR" title="Create a new CCR" target=_new>Create CCR</A></li>
                  <img id="spacer" src="images/blank.gif"/>
                  <li><A href="$secure/secureredir.php?p=gwredir&a=ImportCCR" title="Create a CCR by uploading a file to MedCommons" target=_new>Import CCR</A></li>
                  <img id="spacer" src="images/blank.gif"/>
                  <li><A href="$secure/secureredir.php?p=gwredir&a=OpenCCR" title="Open a CCR that you previously saved" target=_new>Open Saved CCR</A></li>
                  <img id="spacer" src="images/blank.gif"/>
                  <li><a href="$base/logout.php" title="Log out from your MedCommons Account" >logout</a></li>
                  </ul>
              </div>
            </div>
           </div><!--/menubar-->
           <div id="rhs">
              <div id="actionLinks" class="bulletLink">
              <ul>
                  <li><A href=$startpage title="account page" >$accid</A></li>
                  <img id="spacer" src="images/blank.gif"/>
                  <li>$email</li>
                </ul>
              </div>
            </div>
           </div><!--/rjs-->
XXX;

	$bottom = make_acct_page_bottom(false); // just use this from alib, no info block needed
            
// should say    <div id="linkList" class="bulletLink">	
	$links = <<<XXX
       <div>   <h3 class="resources">
                            <span>BASICS</span>
                        </h3>
                        <ul>
                          <li>

                            <a href="$base/?p=commons">Home</a>
                          </li> 
                          <li>
                            <a href="$base/ered.php"> <span class="emergencyccr">My Emergency CCR</span></a>
                          </li>
                            <li>
                          <a href=goStart.php>My Account</a>
                          </li>
                          <li>
                          <a href=myPrefs.php>Personalize</a>
                          </li>
                          <li>
                            <a onclick="alert('Enter PIN 99999 - Images Courtesy of Gordon Harris'); return true;"
                                                 href="https://gateway001.medcommons.net/router/tracking.jsp?enc=0iH4Q8sj5yGZJtLF-jBKqDqGhlKAyuu0ywvTElkJGCDqoernevG_3Q6xAkR39nt67uJkCwBEB7PoiVYlrmgeLwT8l0PlcJ8Y00uqY5NitrA=&hmac=1065ebd941b23afa9ec8bde57d41bcbf1b336347" );">Demo Data</a>

                          </li> 
                          <li>
                            <a target="_new" href="$base/tour.html">Tour</a>
                          </li>
                          <li>
                            <a href="$base/?p=faq">Frequently Asked Questions</a>
                          </li>
                          <li>

                            <a href="$base/?p=about">About Us</a>
                          </li>
                          <li>
                            <a href="$base/?p=termsofuse');">Terms of Use</a>
                          </li>
                        </ul>
            

                        <h3 class="archives">
                            <span>PAPERS</span>
                        </h3>
                        <ul>
                            <li>                          
                              <a href="$base/?p=cxp">
                                <acronym title="Commons eXchange Protocol">CXP</acronym> - A Public Domain Transfer Protocol <span style="font-size: smaller;"></span>

                              </a>
                            </li>
                            <li>
                                <a href="$base/affinity-driven_health_care_networks.pdf" target="_new"
                                    >Affinity Driven Health Information Networks</a>&nbsp;</li>
                            
                            <li>
                                <a href="$base/whitepapers/nhinrfiresponse.pdf" target="_new"
                                    title="MedCommons Response to Request for Information from Dr. David Brailer">MedCommons Response to <acronym title="National Health Information Network"
                                        >NHIN</acronym>
                                    <acronym title="Request for Information">RFI</acronym></a>

                            </li>
                        </ul>
        
                        <h3 class="resources">
                            <span>LINKS</span>
                        </h3>
                        <ul>

                          <li>
                              <a href="$base/?p=press">Press Room</a>
                          </li>
                          <li>
                              <a href="http://www.centerforhit.org/x1556.xml" target="_new"><span>CCR-</span>Compatible Product Gallery</a>
                          </li>
                          <li>

                              <a href="http://www.centerforhit.org/x201.xml" target="_new"><span>M</span>ore information on the CCR standard</a>
                          </li>
                          <li>
                            <a href="$base/?p=liberty">Liberty Alliance</a>
                          </li>
                        </ul>
                    </div>

XXX;

	// just for fun
	
	$right = <<<XXX
	ads go here
XXX;
	

	
	$upcall= layout ($top,$links,$middlediv,$right,$bottom);
	return $upcall;
	
	
}


function layout ($topdiv,$leftdiv,$middlediv,$rightdiv,$bottomdiv)
{



	$border = 0;

	if ($topdiv===false)$optionaltop='';
	else
	$optionaltop = <<<XXX
<tr><td>
<table width='100%' id='top_table' bgcolor=black><tr>
<td align=center id = 'top' bgcolor=white>
<div id='header_div'>$topdiv</div></td></tr></table>
</td></tr>
XXX;

	if ($bottomdiv===false)
	$optionalbottom='';
	else
	$optionalbottom=<<<XXX
<tr><td>
<table width='100%' id='bottom_table' bgcolor=black><tr>
<td  align=center id = 'bottom' bgcolor=yellow>
<div id ='footer_div'>$bottomdiv</div></td></tr></table>
</td></tr>
XXX;

	if ($rightdiv===false)
	$optionalright = ''; else
	$optionalright =<<<XXX
<td valign=top align=right width='100px'>
<table border='$border'   id ='right_col_table' bgcolor=azure>
<tr><td><div id='right_div'>$rightdiv
</div></td></tr></table>
</td>
XXX;
	if ($leftdiv===false)
	$optionalleft = ''; else
	$optionalleft = <<<XXX
<td valign=top align=left width='100px'>
<table border='$border'  id ='left_col_table' bgcolor=silver>
<tr><td><div id='left_div'>$leftdiv</div></td></tr></table>
</td>
XXX;

	if ($middlediv===false)
	$optionalmiddle = ''; else
	$optionalmiddle = <<<XXX
	<td valign=top align=center>
<table border='$border'   id = 'middle_col_table' bgcolor=white>
<tr><td><div id='middle_div'>$middlediv</div></td></tr></table>
</td>
XXX;

	$table = <<<XXX
<table width='100%' border=2 id='outer_table' bgcolor=grey>
$optionaltop
<tr><td>
<table width='100%' id='middle_table' bgcolor=pink><tr>
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