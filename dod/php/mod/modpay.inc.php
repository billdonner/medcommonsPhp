<?php

require_once "setup.inc.php";
require_once 'session.inc.php';
require_once 'utils.inc.php';

function fps_error_page()
{
$markup = '';
$header = page_header("page_setup","Services - MedCommons on Demand Marketplace Rules"  );
$footer = page_footer();
function sanitize($in) { if (isset($_REQUEST[$in])) return ($_REQUEST[$in]);  else return '(not set)' ; }


$error =sanitize('errorMessage');
$status = sanitize('status');
$signature = sanitize('Signature');
$transactionId =sanitize('transactionId');
$referenceId = sanitize('referenceId');
$status = sanitize('status');
$operation = sanitize('operation');
$paymentReason = sanitize('paymentReason');
$transactionAmount= sanitize('transactionAmount');
$transactionDate = sanitize('transactionDate');
$paymentMethod= sanitize('paymentMethod');
$recipientName = sanitize('recipientName');
$buyerName = sanitize('buyerName');
$recipientEmail= sanitize('recipientEmail');
$buyerEmail = sanitize('buyerEmail');


$markup .=  "<h2>FPS Marketplace says: $status</h2>";
$markup .=  "Error: $error<br/>";
$markup .=  "Transaction id: $transactionId <br/>";
$markup .=  "Reference id: $referenceId <br/>";
$markup .=  "Operation: $operation <br/>";
$markup .=  "Payment Reason: $paymentReason <br/>";
$markup .=  "Transaction Amount: $transactionAmount <br/>";
$markup .=  "Transaction Date: $transactionDate <br/>";
$markup .=  "Payment Method: $paymentMethod <br/>";
$markup .=  "Recipient Name: $recipientName <br/>";
$markup .=  "Buyer Name: $buyerName <br/>";
$markup .=  "Recipient Email: $recipientEmail <br/>";
$markup .=  "Buyer Email: $buyerEmail <br/>";
$markup =<<<XXX
$header
<div id="ContentBoxInterior" mainTitle="Amazon FPS Error" >
$markup
</div>
$footer
XXX;

return $markup;
}


// define hugh taylors services as the system defaults for these purposes
$MOD_DEFAULT_SERVICES = // I feel just a little bit guilty, but not much

array  (
array (0,'Complete Health Record'),
array (1,'Current Summary'),
array (2,'Immunizations'),
array (3,'Medications')
);

$MOD_DEFAULT_CHECKEDVEC = '0100'; // sets defaults on default form
function requested_services($accid, $servicename)
{
	
	global $MOD_DEFAULT_SERVICES,$MOD_DEFAULT_CHECKEDVEC ;
	//
	// read thru all my services , if accid=0, it reads the default services
	//
	$markup = ''; $counter=0;
	if($accid==0) {
		// this is the case where we are not logged on, show system defaults
		foreach ($MOD_DEFAULT_SERVICES as $svc)
		{
      if($servicename == $svc[1])
        $markup.='[X] '; else 
			$markup .='[&nbsp;&nbsp;] ';
			$markup .= $svc[1] ."  <br/>"; $counter++;
		}

  }
  else {

    dbg("Creating roir request for accid $accid");
		$result = sql("Select * from modservices  where accid='$accid' ");
		if (!$result) die("cant query modservices ". mysql_error);
		while($r=mysql_fetch_object($result))
		{
			if($servicename == $r->servicename) $markup.='[X] '; else 
			$markup .='[&nbsp;&nbsp;] ';
			$markup .= $r->servicename.'  <br/>';  $counter++;
		}
	}


	if ($counter==0)
	{
		$markup = "no services for accid $accid";

	}


	return $markup;
}
$GLOBALS['voucher_id_size']=7;

/**
 * Generates a coded voucher id that encodes server identity
 * <p>
 * There are three possible algorithms used depending on 
 * the configuration and the domain name.
 * <p>
 * <li>If the voucherid_solo flag is set then char[2] is set to be 'Z'
 * and all the other chars are random.
 * <li>If no voucherid_solo flag but the hostname is not compatible with
 * a numbered naming scheme then chars[2,4,6] are all set to 'Z'
 * and the other chars are random.
 * <li>if the voucherid_solo flag is set AND the host name is compatible
 * with a numbered naming scheme then chars 2,4,6 are set to ordinals
 * matching the range A-Z for the number of the host and all the chars 
 * are set to random AND are not 'Z'.
 * <p>
 * No duplicate checking is done - this algorithm will produce
 * duplicate voucher ids.  The caller must deal with this problem.
 * <p>
 * <i>code by bill, comments by simon</i>
 *
 * @param server  - hostname of server to encode
 */
function generate_voucher_id($server) {
	$voucherid = ''; $vourcher1=$voucher2=$voucher3='Z'; // if bad server then just Z encode it

	if ($GLOBALS['voucherid_solo']){
		$voucher1='Z'; // if Z, then always try to find the voucher on the server where presented
		$voucher2=chr(rand(ord('A'),ord('Z')));
		$voucher3=chr(rand(ord('A'),ord('Y')));
	}
	else {
		if((preg_match("/^[a-z][0-9]{4}\./",$server)!==1)||(substr($server,5,1)!='.')) {
			$voucher1=$voucher2=$voucher3='Z'; // if bad server then just Z encode it
		}
		else {
			$server = substr($server,1,4); // should be a number here     0131
      $v1 =  $server - 26*floor($server/26);  
      $server = floor($server/26); //  v1=1 server=5
			$v2 = $server - 26*floor ($server/26);  $v3 = floor($server/26); //   v2=5 server=0
			// v3=0;    BFA
			$voucher1 = chr(ord('A')+$v1);
			$voucher2 = chr(ord('A')+$v2);
			$voucher3 = chr(ord('A')+$v3);
		}
	}

	// inject random characters
	for ($j=0; $j<2; $j++) $voucherid.=chr(rand(ord('A'),ord('Z')));		$voucherid .=$voucher1;

	for ($j=0; $j<1; $j++) $voucherid.=chr(rand(ord('A'),ord('Z')));		$voucherid .=$voucher2;

	for ($j=0; $j<1; $j++) $voucherid.=chr(rand(ord('A'),ord('Z')));		$voucherid.=$voucher3;
	return $voucherid;
}

function modcomments()
{
	$website = $GLOBALS['mod_website'];
	$host=$_SERVER['HTTP_HOST'];
	$script = $_SERVER["SCRIPT_NAME"];
	$newhurlapp = $GLOBALS['appliance'];
	$time = gmdate ("M d Y H:i:s");
	$solo = $GLOBALS['voucherid_solo']?'is a single appliance MOD configuration ':'round robins thru multiple appliances for MOD';
	$activated = $GLOBALS['activate_accounts']?'has activated accounts ':'has not activated accounts ';

	$comments = <<<XXX

<!-- 
Rendering this MOD dynamic page from server $host script $script at gmt $time
This host is part of a configuration that $solo and $activated.
MOD website is $website; Voucher temporary healthurls and other provider accounts will be minted at $newhurlapp.	
-->	

XXX;
	return $comments;
}
function page_footer()
{
	$f = $GLOBALS['html_deploy_location'].'_modfooter.htm';
	
	return "<!-- loading footer from $f -->". file_get_contents($f);
}
function page_header($a,$b)
{
	$f= $GLOBALS['html_deploy_location'].'_header.htm';
	$header = file_get_contents($f);

	if (isset($_COOKIE['mc'])){
		$navcontainer = <<<XXX
<li><a class=menu_how href="/help.php">Help</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li><a class=menu_dashboard href="/acct/home.php">Dashboard</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li><a class=menu_setup href="/mod/svcsetup.php">Services</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li><a class=menu_settings href="/acct/settings.php">Settings</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li ><a class=menu_nil  href="/acct/logout.php" >Logout</a></li>
XXX;
		$topright = <<<XXX
 <span id='visi' class='right'>
                <a href='/acct/home.php'><img alt='' border='0' id='stamp' src='/acct/stamp.php' /></a>
 </span>
XXX;
}
else
{           $topright ="<span id='visi' class='right'></span>";
$website=$GLOBALS['mod_website'];
$navcontainer = <<<XXX
<li><a class=menu_how href="/help.php">Help</a></li>&nbsp;&nbsp;|&nbsp;&nbsp;
<li><a class=menu_how href="$website/personal.php" >Sign In</a></li>
XXX;
}
$navcontainer = '<ul id="navlist" class="listinlinetiny" >'.$navcontainer.'</ul>';
return str_replace(array('$$$pageid$$$','$$$htmltitle$$$','$$$navcontainer$$$','$$$topright$$$','$$$modcomments$$$'),
array($a,$b,$navcontainer,$topright,modcomments()) ,$header);
}
function page_header_nonav($a,$b)
{
	
	$f= $GLOBALS['html_deploy_location'].'_header.htm';
	$header = "<!-- loading header (w nonav)  from $f -->". file_get_contents($f);
	$topright ="<span id='visi' class='right'></span>";
	$header = file_get_contents($GLOBALS['html_deploy_location'].'_header.htm');
	$navcontainer='';

	return str_replace(array('$$$pageid$$$','$$$htmltitle$$$','$$$navcontainer$$$','$$$topright$$$','$$$modcomments$$$'),
	array($a,$b,$navcontainer,$topright,modcomments()) ,$header);
}
function page_header_cond($a,$b)
{
	if (isset($_COOKIE['mc'])) return page_header($a,$b);
	else return page_header_nonav($a,$b);
}
function dbconnect() {
	if (!isset($GLOBALS['db_connected']) )
	{
		$GLOBALS['db_connected'] =
		mysql_connect($GLOBALS['DB_Connection'] ,$GLOBALS['DB_User'] );
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db ".mysql_error());
	}
}
function sql($q)
{
  dbconnect();
	$status = mysql_query($q);
	return $status;
}
function encode_fax($mcid, $n) {
	$binary = pack('NNN',
	intval(substr($mcid, 0, 8), 10),
	intval(substr($mcid, 8, 8), 10),
	$n);
	// Use 'url-safe' base64 encoding, cuz + and / interfere with URL encoding
	return str_replace(array('+', '/'), array('-', '_'),
	base64_encode($binary));
}


function updatepaidstatus ($c,$o,$tid,$paytype)
{

	return sql("Update modcoupons set paytid='$tid',paytype='$paytype' where couponum='$c'  ");
}

function validateMoney($number)
{
	if (substr($number,0,1) =='$') $number = substr($number,1);
	if(ereg('^[0-9]+\.[0-9]{2}$', $number))
	return $number;
	else
	return false;
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
function dicomchooser ($d)
{

	$outstr = <<<XXX
	<select name=dcredits id = 'dcredits' >
XXX;
	for ($i=0; $i<20;  $i++)
	{
		$selected = ($d  == $i)?' selected ':'';
		$outstr.="<option value='$i'  $selected >".$i."</option>
";
	}
	$outstr.="</select>";
	return $outstr;
}
function faxinchooser ($d)
{

	$outstr = <<<XXX
	<select name=fcredits id = 'fcredits' >
XXX;
	for ($i=0; $i<20;  $i++)
	{
    $credits = $i * 5;
		$selected = ($d  == $credits)?' selected ':'';
		$outstr.="<option value='$credits'  $selected >$credits</option>
";
	}
	$outstr.="</select>";
	return $outstr;
}
function asizechooser ($d)
{
	$asizes = array ('.1MB','1MB','10MB','100MB','500MB' ); // used for computing charges

	$outstr = <<<XXX
	<select name=asize id = 'asize$d' >
XXX;
	for ($i=0; $i<count($asizes); $i++)
	{
		$selected = ($d  == $i)?' selected ':'';
		$outstr.="<option value='$i'  $selected >".$asizes[$i]."</option>
";
	}
	$outstr.="</select>";
	return $outstr;
}
function durationchooser ($d)
{
	$durs = array ('3 days','5 days','1 week','1 month','3 months','6 months' );

	$outstr = <<<XXX
	<select name=duration id = 'duration$d' >
XXX;
	for ($i=0; $i<count($durs); $i++)
	{
		$selected = ($d  == $i)?' selected ':'';
		$outstr.="<option value='$i'  $selected >".$durs[$i]."</option>
";
	}
	$outstr.="</select>";
	return $outstr;
}
function svctemplatechooser( $svc)
{
	// returns a big select statement
	$count = 0;
	$outstr  = <<<XXX
	<select id='svctemplatenum' name='svctemplatenum' title='pick a predefined template to start defining your service'
	onchange="if(this.selectedIndex>0) location = 'svcsetup.php?t='+this.options[this.selectedIndex].value + '#editsvc';" >
  <option value="-1">Select a Template</option>
XXX;
	$result =sql("SELECT * from modsvctemplates where templatenum>=0 order by templatenum  ") or die("cant query modsvctemplates ". mysql_error());

	while ($r2 = mysql_fetch_object($result))
	{
		$count++;
		$name = $r2->servicename;
		//$ename = urlencode($name);
		$selected = ($r2->templatenum  === $svc)?' selected ':'';
		$outstr .="<option value='$r2->templatenum' $selected >".htmlentities($name)."</option>
		";
	}
	$outstr.="</select>";
	if ($count==0)  return "<span>No templates are defined</span>";
	return $outstr;
}
function pservicechooser($accid, $svc)
{
	// returns a big select statement
	$count = 0;
	$outstr  = <<<XXX
	<select id='svcnum' name='svcnum' title='choose one of the services registered with account $accid' 
	onchange="location = 'vouchersetup.php?i='+this.options[this.selectedIndex].value;" >
XXX;
	$result =sql("SELECT * from modservices  where accid= '$accid' ") or die("cant query modservices ". mysql_error());

	while ($r2 = mysql_fetch_object($result))
	{
		$count++;
		$name = $r2->servicename;
		//$ename = urlencode($name);
		$selected = ($r2->svcnum  == $svc)?' selected ':'';
		$outstr .="<option value='$r2->svcnum' $selected >$name</option>
		";
	}
	$outstr.="</select>";
	if ($count==0)  return "<span>No provider defined services are defined for this account</span>";
	return $outstr;
}

function servicechooser($accid, $svc)
{
	$count  = 0;
	// returns a big select statement
	$outstr = <<<XXX
	<select id='svcnum' name='svcnum' title='choose one of the serices registered with account $accid' >
XXX;
	$result =sql("SELECT * from modservices  where accid= '$accid' ") or die("cant query modservices ". mysql_error());

	while ($r2 = mysql_fetch_object($result))
	{
		$count++;
		$name = $r2->servicename;
		//$ename = urlencode($name);
		$selected = ($name == $svc)?' selected ':'';
		$outstr .="<option value='$r2->svcnum' $selected >$name</option>
		";
	}
	$outstr.="</select>";

	if ($count==0)  return "<span>No provider defined services are defined for this account</span>";
	return $outstr;

}

function  wsAdjustCounters($btk,$faxin,$dicom,$acc)
{
  dbg("Adjusting billing token $btk => (fax=$faxin,dicom=$dicom,acc=$acc)");

	if (($faxin!=0) || ($dicom!=0) ||($acc!=0))
	{
		$REMOTE_WSCOUNTERS_SERVICE = $GLOBALS ['remote_wscounters_service'] ;
    $str = get_url("$REMOTE_WSCOUNTERS_SERVICE?btk=$btk&faxin=$faxin&dicom=$dicom&acc=$acc");
		$xml = @simplexml_load_string($str);
    if(!$xml || !isset($xml->counters)) 
      throw new Exception("Bad format XML returned from counters service: $str");

		$counters = $xml->counters;
		$status = $counters->status;
    if($status!='1') {
      throw new Exception($counters->comment);
    }
		return array ($faxin,$dicom,$acc);
	}
	else return array (0,0,0);
}

function  wsGetCounters($btk) {
	$REMOTE_WSCOUNTERS_SERVICE = $GLOBALS['remote_wscounters_service'];
	$str = file_get_contents("$REMOTE_WSCOUNTERS_SERVICE?btk=$btk");


	$xml = simplexml_load_string($str);

	$counters = $xml->counters;
	$status = $counters->status;

	if ($status!='1') return array (0,0,0); //false;

	$faxin = $counters->faxin;
	$dicom = $counters->dicom;
	$acc = $counters->acc;
	return array ($faxin,$dicom,$acc);
}

function  wsGetBillingBindings($btk) {
	$REMOTE_WSCOUNTERS_SERVICE = $GLOBALS['remote_wscounters_service'];
	$str = file_get_contents("$REMOTE_WSCOUNTERS_SERVICE?btk=$btk&dump");
	$xml = simplexml_load_string($str);

	$bindings = $xml->binding;
	$accounts = $bindings ->accounts;
	$buf = "<p>$accounts</p>";
	return $buf;
}

function  wsAddBillingBinding($btk,$accid,$pc,$ak) {
	$REMOTE_WSCOUNTERS_SERVICE = $GLOBALS['remote_wscounters_service'];
	$str = file_get_contents("$REMOTE_WSCOUNTERS_SERVICE?btk=$btk&accid=$accid&pc=$pc&ak=$ak");
	$xml = simplexml_load_string($str);

	$bindings = $xml->binding;
	$status = $bindings->status;

	return $status;
}
function  wsGetBillingId($accid) {
	$REMOTE_WSCOUNTERS_SERVICE = $GLOBALS['remote_wscounters_service'];
	$str = file_get_contents("$REMOTE_WSCOUNTERS_SERVICE?accid=$accid");
	$xml = simplexml_load_string($str);
	$bindings = $xml->binding;
	$status = $bindings->billingid;
	return $status;
}

function array_get($array, $key, $defaultValue) {
	return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
}

function testif_logged_in()
{
	global $SECRET;

	if (isset($_COOKIE['mc'])) {
		parse_str(str_replace(',', '&', $_COOKIE['mc']), $values);

		if (isset($values['enc'])) {
      $decrypted = decrypt_urlsafe_base64($values['enc'], $SECRET);
      dbg("decrypted cookie is ".$decrypted);

      // ssadedin: note can't use parse_str because it de-urlencodes values,
      // but the values set are not urlencoded themselves
      $params = explode("&",$decrypted);
      $enc_values = array();
      foreach($params as $p) {
        list($key,$value) = explode("=",$p);
        $enc_values[$key] = $value;
      }

			if (isset($enc_values['mcid']))
			return array(array_get($enc_values, 'mcid', ''),
			array_get($enc_values, 'fn', ''),
			array_get($enc_values, 'ln', ''),
			array_get($enc_values, 'email', ''),
			array_get($enc_values, 'from', ''),
			$_COOKIE['mc'],
			array_get($enc_values, 'auth', ''));
		}
	}

	return false;
}

function logged_in()
{
	$logged_in =  testif_logged_in(); $dis = false;
	if ($logged_in===false) // die( "must be logged on to medcommons appliance to tinker");
	{

		header ("Location: /site/index.php?pleaselogon");
		die("Redirecting to /site/index.php?pleaselogon ");
	}
	return $logged_in;
}

function show_coupon ($v)
{
	$money = '$'.money_format('%i', $v->price);

	$paid = paidvia ($v->price, $v->paytype);

	return <<<XXX
<div class=coupon>
<p>If you have any difficulties please contact your healthcare service provider at $v->supportphone
</p>

Service Name: <b>$v->servicename</b><br/>
Service Description: <b>$v->product</b><br/>
Service Provider ID: $v->accid<br/>
Patient Name: $v->name <br/>
Patient Temporary ID: $v->mcid<br/>
Expiration Date: $v->expirationdate<br/>
Price: $money $paid<br/>
</div>
XXX;
}

function timehurl($r)
{
	$time = date("M d Y H:i:s",  $r->time);
	return "
<p>
Issue Time: $time<br/>
Temporary HealthURL: $r->hurl<br/>
</p>
";
}
function paidvia ($price, $paytype){
	$paidvia = $paytype;
	if ($price == 0) $paidvia ='<i>no charge</i>';
  else
	if ($paidvia=='') $paidvia='<i>unpaid</i>';
  else
	if ($paidvia=='cash') $paidvia='<i>paid in cash</i>';
  else
	if ($paidvia=='amzfps') $paidvia='<i>paid by Amazon FPS</i>';

	return $paidvia;
}
function show_bump_counters($bumpcounters)
{
	$m='';
	if ($bumpcounters[0]!=0) $m.=" FAXIN counter will be incremented by ".$bumpcounters[0]."<br/>";
	if ($bumpcounters[1]!=0) $m.=" DICOM counter will be incremented by ".$bumpcounters[1]."<br/>";
	if ($bumpcounters[2]!=0) $m.=" ACC counter will be incremented by ".$bumpcounters[2]."<br/>";
	return "<div class=counters style='background: #FFFFF0' >$m</div>";
}



function getSignature($stringToSign) {
	global $secretKey;
	$hmac = new Crypt_HMAC($secretKey,"sha1");
	$binary_hmac = pack("H40", $hmac->hash(trim($stringToSign)));
	return base64_encode($binary_hmac);
}
function build_reference($shortdescription, $bumpcounters, $reference)
{
	return $reference.'-'.$bumpcounters[0].'-'.$bumpcounters[1].'-'.$shortdescription;//.'-'.$reference;
}
function AmzPayNowButtonForm($amount, $short, $description, $referenceId,
$ipnUrl, $returnUrl, $abandonUrl) {
	global $accessKey,$secretKey;
	$formHiddenInputs['accessKey'] = $accessKey;
	$formHiddenInputs['amount'] = $amount;
	$formHiddenInputs['description'] = $description;
	if ($referenceId) $formHiddenInputs['referenceId'] = $referenceId;
	$formHiddenInputs['immediateReturn'] ='1';
	$formHiddenInputs['processImmediate'] ='1';
	if ($returnUrl) $formHiddenInputs['returnUrl'] = $returnUrl;
	if ($abandonUrl) $formHiddenInputs['abandonUrl'] = $abandonUrl;
	//	if ($ipnUrl) $formHiddenInputs['ipnUrl'] = $ipnUrl;
	ksort($formHiddenInputs);
	$stringToSign = "";

	foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
		$stringToSign = $stringToSign . $formHiddenInputName . $formHiddenInputValue;
	}

	$formHiddenInputs['signature'] = getSignature($stringToSign, $secretKey);
	$pipeline = $GLOBALS['amazon_pipeline'];
	$form = "<form action='$pipeline' method=\"post\">\n";
	foreach ($formHiddenInputs as $formHiddenInputName => $formHiddenInputValue) {
		$form = $form . "<input type=\"hidden\" name=\"$formHiddenInputName\" value=\"$formHiddenInputValue\" />\n";
	}
	$form = $form . "<input type='image' alt='pay via Amazon'
	                                src='https://authorize.payments.amazon.com/pba/images/payNowButton.png' border='0' />";
	$form = $form . "</form>\n";
	return $form;
}
function AmzPayNowButton($price,$bumpcounters, $shortdescription,$longdescription,$reference)
{
	$reference = $reference.'-'.$bumpcounters[0].'-'.$bumpcounters[1].'-'.$shortdescription;//.'-'.$reference;
	return "<div id='$shortdescription'  >".
	AmzPayNowButtonForm($price,$shortdescription, $longdescription,
	$reference, $GLOBALS['fps_ipn'], $GLOBALS['fps_return'], $GLOBALS['fps_abandon']).
	"</div>\n";
}
function AmzPayNowVoucherButton($price,$shortdescription,$longdescription,$reference,$couponnum, $sotp,$return )
{
	$reference = 'pnv'.'-'.$couponnum.'-'.$sotp.'-'.$shortdescription;//.'-'.$reference;
	return "<div id='$shortdescription'  >".
	AmzPayNowButtonForm($price,$shortdescription, $longdescription,
	$reference, $GLOBALS['fps_ipn'], $return, $GLOBALS['fps_abandon']).
	"</div>\n";
}

function mony($mony)
{
	if ($mony<0.0) return "<span class='errorAlert nmf'>$".number_format(-$mony,2)."-</span>";
	else return "<span class='mf'>$".number_format($mony,2)."&nbsp;</span>";
}
function monynf($mony)
{
	if ($mony<0.0) return "$".number_format(-$mony,2)."-";
	else return "$".number_format($mony,2)."";
}
function figure_money($patprice,$duration,$asize,$fcredits,$dcredits)
{
	$faxinperpage = .50;
	$dicomupload = 5.00;
	$sizemultiplier = array (.1,1.0,10.0,100.0,500.0);
	$durationdays =  array (3,5,7,30,90,180);
	$priceperday = .20*.03*.001; // price per MB per day at amz
	$mcsplit = .10;
	$mcminimum = 1.00;
	$mcmultiplier = 10.0;

	$amazonfee = round($mcmultiplier*$durationdays [$duration]*$sizemultiplier[$asize]*$priceperday+.01,2) + $faxinperpage*$fcredits + $dicomupload*$dcredits;

	$netpractice = (1.0 -$mcsplit)*$patprice;
	$netmc = $mcsplit*$patprice;
	if ($netmc <$mcminimum)
	{           // always give mc the minimum
		$netpractice = $netpractice - ($mcminimum-$netmc);
		$netmc = $mcminimum;
	}
	$netpractice = $netpractice-  $amazonfee;
	return array ($netpractice, $netmc, $amazonfee);
}


function standardcoupon($couponnum,$markup)
{
	$now=time();

	$result = sql ("Select * from modcoupons  c, modservices s where c.couponum='$couponnum'  and c.svcnum=s.svcnum ");
	$r = mysql_fetch_object($result);

	$timenow = strftime ('%T %D', $now);
	paidvia($r->patientprice,$r->paytype);
	// add some fields for show_coupon
	$r->price = $r->patientprice/100.;
	$r->name = $r->patientname;
	$r->product = $r->servicedescription.' '.$r->addinfo;
	if (($r->paytype=='') &&($r->patientprice!=0)) $paynow = "<span id='paynowbutton'><form method='post' action='voucherpay.php'><input type='hidden' name='c' value='$r->couponum' />
<input type='submit' value='Pay Now' /></form></span>";
	else $paynow='';
	$printvoucher = <<<XXX
<div id='printcancelbuttons'>
  <form method='post' style='display: inline;' action='voucherprintpage.php' title='opens in new window' target='prt'>
    <input type='hidden' name='c' value="$r->couponum" />
    <input type='submit' class='mainwide' id='printbutton' value='Print Voucher' onclick='document.getElementById("cancelbutton").value="Done"; document.getElementById("cancelform").action="voucherlist.php";' />
  </form>&nbsp;
  <form method='post' action='svcsetup.php' id='cancelform' style='display: inline;' ><input type='hidden' name='i' value='$r->svcnum' />
    <input type='submit' id='cancelbutton' value='Cancel' class='altshort' />
  </form>
  <iframe name='prt' src='about:blank' style='position: absolute; left: -100px; top: -100px;' width="1" height="1">This page needs iframe support</iframe>
</div>
XXX;
	$backbutton = <<<XXX
<div id='backbutton'><form method='post' action='voucherlist.php'><input type='hidden' name='c' value='$r->couponum' />
<input type='submit' value='Back' /></form>
</div>
XXX;
	$payin = <<<XXX
Pay Now allows you to collect cash or a credit card on the spot. Otherwise, the patient will be asked to pay prior to access.<br/>
XXX;
	$providerphone=$r->supportphone;
	$servicename =$r->servicename;
	$servicedescription=$r->servicedescription;

	$addinfo=$r->addinfo;
	$servicelogo = "<img src='$r->servicelogo' border=0 />";
	$serviceproviderid=$r->accid;
	$patientname=$r->patientname;
	$patienttempid=$r->voucherid;
	$voucherexpirationdate=$r->expirationdate;

	$voucherpaystatus=paidvia($r->patientprice,$r->paytype);


	if ($r->patientprice==0) { $voucherprice='0.00'; $payin='';} else
	$voucherprice=money_format('%i', $r->patientprice/100.);
	$thurl = $r->hurl;
	$churl = "vouchercopyhurl.php?c=".$r->couponum."&o=".sha1($r->otp)."&vcopy";
	$issuetime = date("M d Y H:i:s",  $r->time);
	if ($GLOBALS['voucherid_solo']) $claimUrl = 'https://'.$_SERVER['HTTP_HOST'].'/pickuprecords.php'; else
	$claimUrl =$GLOBALS['voucher_pickupurl'] ; // $GLOBALS['mod_base_url']."/voucherclaim.php";
	$time = date("M d Y H:i:s",  $r->time);
	$voucherid = $r->voucherid;
	$huhheader = $r->servicename;
	$passw = $r->otp;


	$devpay_return_url = $GLOBALS['mod_base_url']."/payment_processed.php?copy=true&c=".$r->couponum."&o=".sha1($r->otp)."&vcopy";
	$devpay_url = $GLOBALS['devpay_redir']."?src=".urlencode($devpay_return_url);

	$markup = str_replace( array('**AI**','**TI**','**VI**','**PW**','**CU**','**SL**','**HD**','**PN**','**PV**',
	'**SPH**','**SN**','**SD**','**SPID**','**SPN**','**STID**','**SXD**','**SPR**','**SPS**','**IT**','**THURL**','**CHURL**','**BB**','**PAYIN**', '**PAYURL**')
	,
	array($addinfo, $time, $voucherid,$passw,$claimUrl,$servicelogo,$huhheader,$paynow,$printvoucher,
	$providerphone,$servicename,$servicedescription,$serviceproviderid,$patientname,$patienttempid,$voucherexpirationdate,$voucherprice,
	$voucherpaystatus,$issuetime,$thurl,$churl,$backbutton,$payin,$devpay_url)
	,
	$markup);
	return $markup;
}

/**
 * Attempts to make a default set of services for a user
 * based on defaults that are global to an appliance.
 * The defaults are expected to be numbered starting
 * with -1 and downwards.  They must be numbered contiguously.
 * <p>
 * <i>Note: this is implemented as an atomic transaction, separate
 * to any outer transaction.  If the outer code is executing 
 * a transaction then it may be commited.</i>
 */
function  make_svcs_from_templates($accid)
{
  require_once "db.inc.php";

  $db = DB::get();
  $db->begin_tx();
  try {
    // Load default email and phone from the default service for this account, if there is one
    $email=$phone='';
    $defsvc = $db->first_row("select * from modservices where accid=? and servicename='__default__' ",array($accid));
    if($defsvc) {
      $email = $defsvc->serviceemail; 
      $phone=$defsvc->supportphone;
    }

    $time = time();
    $templates = $db->query("select * from modsvctemplates where templatenum<0 order by templatenum desc") ;
    foreach($templates as $t) {
      $svcnum = $db->execute("INSERT INTO `modservices` 
        (accid, servicename, servicedescription, serviceemail, supportphone, duration, time, 
        voucherprinthtml, voucherdisplayhtml, consentblob, 
        asize, fcredits, dcredits, suggestedprice, servicelogo, 
        createcount, utilizedcount, cashreceived, cashpaidout) VALUES
        (?, ?, ?, ?, ?, ?, ?, '', '', '', ?, ?, ?, 0, 'https://s0000.myhealthespace.com/images/MEDcommons_logo_246x50.gif', 
        0,0, 0, 0)",
        array($accid, $t->servicename, $t->servicedescription, $email, $phone, $t->duration, $time, $t->asize, $t->fcredits, $t->dcredits));

      // Add user as having default consent
      $db->execute("insert into modservice_consents(svcnum, accid) values (?,?)", array($svcnum, $accid));
    }

    $db->commit();
  }
  catch(Exception $ex) {
    $db->rollback();
    throw $ex;
  }
}

function get_master_services_accid($accid) 
{
	$q = "select * from modfriends where friendmcid='$accid'";
	$result =sql($q);
	$r = mysql_fetch_object($result);
	if ($r===false) 	
	{
			$q = "insert into modfriends set friendmcid='$accid',mcid='$accid' ";
			$status = sql($q) or die("Cant insert into modfriends $q ".mysql_error());
			return $accid;  // if cant find record, just return self
	}
	return $r->mcid;
}
function check_add_friend ($mcid,$accid)
{
	$q = "insert into modfriends set friendmcid='$mcid',mcid='$accid' ";
	$status = sql($q);
	
//	if (!$status) die ("Cant $q " . mysql_error());
	return $status;	
}
function check_add_consent ($mcid,$accid)
{
	$q = "insert into modconsents set friendmcid='$mcid',mcid='$accid' ";
	$status = sql($q);
	
//	if (!$status) die ("Cant $q " . mysql_error());
	return $status;	
}
function remove_friends($accid)
{
	$q = "delete from modfriends where mcid='$accid' ";
	$status = sql($q);
	if (!$status) die ("Cant $q " . mysql_error());
	return $status;	
}

function get_appliance_info ($uber,$mcid)
{
	$url = $uber."/qappliance.php?q=$mcid";
	$result = file_get_contents($url);
	$xml = simplexml_load_string($result);
	if (1==$xml->response->status) {
		$appliance = $xml->response->info->appliance ;
		return $appliance;
	}
	else return false;
}
function get_user_demographics ($appliance,$mcid)
{
	try {
		$url = 'http://'.$appliance."/uinfo.php?q=$mcid";
		$result = file_get_contents($url);
		$xml = @simplexml_load_string($result);
		if ($xml===false) return false;
		if (1==$xml->response->status) {
			$info = $xml->response->info;
			return $info;
		}
		else return false;
	}
	catch(Exception $e) {
		return false;
	}
}

function load_address_book($accid) {
  require_once "db.inc.php";
  $db = DB::get();
  return $db->query("select d.id, d.td_contact_list as 'email', d.td_contact_accid as 'accid', coalesce(gi.name , concat(u.first_name,' ',u.last_name)) as 'name'
                                    from todir d
                                    left join groupinstances gi on gi.accid = d.td_contact_accid,
                                    users u
                                    where d.td_owner_accid = ?
                                    and u.mcid = d.td_contact_accid", array($accid));
}

function calculate_voucher_expiry_date($duration) {
  $daybumps = array (3,5,7,0,0,0);
  $monthbumps = array (0,0,0,1,3,6);
  return date("Y/m/d", mktime(0, 0, 0, date("m")+$monthbumps[$duration],	date("d")+$daybumps[$duration], date("y")));
}

?>
