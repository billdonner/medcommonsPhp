<?php
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");

//ered.php
//
// if the user is not logged in, put up the standard form asking for a number
// if the user is logged in, just make believe he has entered the number (=accountid)


  include("dbparams.inc.php");


$action =   $GLOBALS['Accounts_Url']."/redccr.php";
$returnurl = $GLOBALS['Homepage_Url']."/ererr.php";
$infourl = $GLOBALS['Homepage_Url']."/index.html?p=aboutredccr";
$redirurl = $GLOBALS['Accounts_Url']."/eccrredir.php";

/* figure out whether indicate whether logged on or not */

$c1 = $_COOKIE['mc'];
if ($c1=='')
{	$returnurl = $GLOBALS['Homepage_Url']."/ererr.php";

	$inputstuff = <<<XXX
  <p> Enter 16 Digit ID </p><p>
    <input type="text" name="accid" size="16" maxlength="16"/>
  </p>
  <input type="submit" value="Go"/>
XXX;

	$theform = theform ($inputstuff,$action,$returnurl,$redirurl);
	// if not logged in , paint the form and exit
	$info = <<<XXX
   <div id="supportingText" title="Emergency CCR Access Service - enter 16 digit ID">
                <div id="preamble">
                    <h3><span><font color="red">Emergency CCR Access</font></span>
                    </h3>
                    <p class="p1">We will make our best efforts to provide you access to a particular CCR based upon the 16 digit code on the back of a MedCommons affiliated healthcare card. </p>
                    <p class="p1">By entering a 16 digit ID and hitting the Go button, you are accepting the terms and conditions of this single use access to the CCR <a href=$infourl>tour</a></p>
				$theform
                </div>
         </div>
    
    
XXX;
	$x="<?xml version='1.0' encoding='UTF-8'?><ajreturnblocks><content>".$info."</content></ajreturnblocks>";
	// path where not logged on
}
else {
	// we are logged on, just redirect to the /accnt server and let them worry about it from this point forward
	// but it appears that redirects don't work in this ajax context, nor do posts, so
	// just return the full redirection string and let the caller worry over it


	$mcid=""; $fn=""; $ln = ""; $email = ""; $from = "";
	$props = explode(',',$c1);
	for ($i=0; $i<count($props); $i++) {
		list($prop,$val)= explode('=',$props[$i]);
		switch($prop)
		{
			case 'mcid': $accid=$val; break;

			case 'fn': $fn = $val; break;

			case 'ln': $ln = $val; break;

			case 'email'; $email = $val; break;

			case 'from'; $from = stripslashes($val); break;

		}

	}
	$returnurl = $GLOBALS['Homepage_Url']."/ernone.htm?p=foo";
	$infourl = $GLOBALS['Homepage_Url']."/index.html?p=aboutredccr";
	$url = $GLOBALS['Accounts_Url']."/redccr.php?accid=$accid&returnurl=$returnurl&redirurl=$redirurl";
  /*$x="<div style='width: 100%; padding: 0px;'>
       <iframe id='contentframe' allowtransparency='true' 
             background-color='transparent' 
             name='ccrlog' width='600' height='600' 
             frameborder='0' src='$url'/>
      </div>";
   */
	$x="<?xml version='1.0' encoding='UTF-8'?><ajreturnblocks><redirect>".$url."</redirect></ajreturnblocks>";
}

// either way, get out of here
echo $x;
exit;


function theform ($inputstuff,$action,$returnurl,$redirurl)
{
	$x=<<<XXX
                <form method="post" name="ered" id="ered" target='_top'
                    action="$action">
                    <input type="hidden" name="returnurl"
                        value="$returnurl"/> 
                    <input type="hidden" name="redirurl"
                        value="$redirurl"/> 
                  $inputstuff
                </form>
XXX;
	return $x;
}


?>
