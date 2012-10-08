<?php

require_once "render.inc.php";
require_once "utils.inc.php";

// define hugh taylors services as the system defaults for these purposes

$MOD_DEFAULT_SERVICES = // I feel just a little bit guilty, but not much

array  (
array (0,'Complete Health Record'),
array (1,'Current Summary'),
array (2,'Immunizations'),
array (3,'Medications')
);

$MOD_DEFAULT_CHECKEDVEC = '0100'; // sets defaults on default form


function servicemenu($servicename)
{
	// <input type=checkbox name=svc$sid $acm  /><span class=r>&nbsp;</span>
	global $MOD_DEFAULT_SERVICES;
	$markup= '';
	foreach ($MOD_DEFAULT_SERVICES as $svc)
	{	$sid =$svc[0]; $sdesc=$svc[1];
		$acm =($servicename == $svc[1])?'checked':'';
		$markup .= "
      <div class=field><span class=n>$sdesc</span><span class=q>
      <input type='radio' name='servicename' value='".htmlentities($svc[1])."' $acm  /><span class=r>&nbsp;</span>
      </span></div>";
  }

return $markup;

}
function checkValidDate($sDate) {
	$sDate = str_replace(' ', '-', $sDate);
	$sDate = str_replace('/', '-', $sDate);
	$sDate = str_replace('--', '-', $sDate);
	if ( ereg("^[01][0-9]-[0-3][0-9]-[0-9]{4}$",$sDate) ) {
		list( $month , $day, $year ) = explode('-',$sDate);
		return( checkdate( $month , $day , $year ) );
	} else {
		return( false );
	}
}
function checkEmail($email)
{
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	}
	else
	{
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if
		(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
		str_replace("\\\\","",$local)))
		{
			// character not valid in local part unless
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
			str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
	}
	return $isValid;
}
// start here
$v = new stdClass;
$v->err = $v-> name_err = $v->dob_err = $v->name = $v->dob = $v->email_err = $v->email =$v->note= '';
$v-> prname_err = $v->prname =  $v->premail_err = $v->premail ='';
$acm = $isr = $lr = $csr = $hds =  $cmr = '';

// error check these args

$errs =array()  ;

if (isset($_POST['name']))
{
	if (isset($_POST['repost']))	$on_error_return = $_POST['repost']; else $on_error_return = false;
	
	$servicename=req('servicename');
	
	$v->name=$_POST['name'];
	if (strlen($v->name)==0) $errs[] = array('name_err',"First name Last name required");
	if (isset($_POST['prname']))
	$v->prname=$_POST['prname']; else $v->prname='';
	$v->dob=$_POST['dob'];
	if (strlen($v->dob)!=0)
	if (!checkValidDate($v->dob)) $errs[] = array('dob_err',"Valid date in form MM/DD/YYYY ");
	$v->email=$_POST['email'];
	if (strlen($v->email)==0) $errs[] = array('email_err',"Valid email address required");
	if (!checkEmail($v->email)) $errs[] = array('email_err',"Valid email address required");
	if (isset($_POST['premail']))
	$v->premail=$_POST['premail']; else $v->premail='';
	if (strlen($v->premail)!=0)
	if (!checkEmail($v->premail)) $errs[] = array('premail_err',"Provider's email appears invalid");

	$v->note =$_POST['note'];
	if ($servicename==null)$errs[] = array('err',"Please select a service.");

	if (count($errs)==0) {
		$randomappliance = select_random_appliance();
		$modurl = "$randomappliance/mod/roireq.php";
		$narg= "$v->name|$v->dob|$v->email|$v->note|$v->prname|$v->premail|$servicename||";

    dbg($narg);
		// echo "$narg<br>";
		//alright, its good to go, pick an appiance and deal with the rest of it there
		$narg =base64_encode($narg);
		header ("Location: $modurl?n=$narg");
		die("Location: $modurl?n=$narg");
	}
	//
	// if invoked from some other site, go back there

	if ($on_error_return) {
		$narg = '';$i=0;
		foreach ($errs as $err ){

			if ($i!=0) $narg.='&';
			$narg .=("p$i=".$err[0])."&e$i=". urlencode ($err[1]);
			$i++;
		}
		header ("Location: $on_error_return?$narg");
		die("Location: $on_error_return?$narg");
	}
}

if (count($errs)!=0)
for ($i=0; $i<count($errs); $i++) $v->$errs[$i][0]=$errs[$i][1];

// provide some defaults if the user happens to be logged on

$me = testif_logged_in();
if ($me!==false)
{
	list ($accid,$fn,$ln,$email,$idp,$mc,$auth)= $me;
	if ($v->name =='') $v->name = $fn.' '.$ln;
	if ($v->email == '') $v->email = $email;
}

$my_requests_for_records = servicemenu('');

// the form contained herein might be on somone else's site
// if the hidden variable repost is set, it is the address to go back to with errors
$content =  <<<XXX
<div id="ContentBoxInterior" mainTitle="Services Request">
<h2>Services Request</h2>
<div class=fform>
<form action=hipaaroireq.php method=post>
<input type=hidden name=repost value=''>
<div class=inperr id=err>
$v->err
</div>
<h4>Patient</h4>
<div class=field><span class=n>Name</span>
<span class=q><input type=text name=name value='$v->name' /><span class=r>firstname and lastname please</span>
<div class=inperr id=name_err>$v->name_err</div></span>
</div>
<div class=field><span class=n>Date of Birth</span>
<span class=q><input type=text name=dob value='$v->dob' /><span class=r>optional, mm/dd/yyyy</span>
<div class=inperr id=dob_err>$v->dob_err</div></span>
</div>
<div class=field><span class=n>Email</span>
<span class=q><input type=text name=email value='$v->email' /><span class=r>eg bilbo@baggins.com</span>
<div class=inperr id=email_err>$v->email_err</div></span>
</div>
<h4>Services</h4>
$my_requests_for_records
<div  class=field><span class=n>Additional Instructions to  Provider</span><span class=q >
<textarea  name="note"   cols=50 rows=6 maxlength="300">$v->note</textarea></span>
</div>

<div class=field><span class=n>&nbsp;&nbsp;</span>
<span class=q><input type=submit class='mainwide'
value='Create Request' /><span class=r>&nbsp;</span></span>
</div>
</form>
</div>

<table class=tinst >
<tr ><td class=lcol >Instructions</td><td class=rcol >Fill out and print this form to take to take with you to your healthcare provider.
<br/>Or call your provider and give them the code so they can begin work immediately.
<br/>This request will remain online for 90 days
<br/>You can <a href=embedroireq.php >post this form on your own site</a> and provide a branded experience</td></tr>
</table>
</div>
XXX;
// ok, we've made the body, throw the standard stuff around it

renderas_webpage($content);

?>
