<?php

require_once "utils.inc.php";
require_once "mc.inc.php";
require_once "db.inc.php";

set_exception_handler('unhandled_error');

// Defaults
$GLOBALS['DB_Connection'] = "mysql.internal";
$GLOBALS['DB_Database'] = "mcx";
$GLOBALS['DB_User']= "medcommons";

// Overrides
if(file_exists("modpay_conf.inc.php")) {
  include "modpay_conf.inc.php";
}

function unhandled_error($e) {
  echo "<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
<counters>
<comment>Unexpected error: ".htmlentities($e->getMessage())."</comment>
<status>0</status>
</counters>
</billing_api>";
exit;
}

function dosql($q)
{
	if (!isset($GLOBALS['db_connected']) )
	{
		$GLOBALS['db_connected'] =
		mysql_connect($GLOBALS['DB_Connection'] ,$GLOBALS['DB_User'] );
		$db = $GLOBALS['DB_Database'];
		mysql_select_db($db) or die ("can not connect to database $db ".mysql_error());
	}

	$status = mysql_query($q);
	if (!$status) die ("dosql failed $q ".mysql_error());
	return $status;
}
//  Counters Web Service, Gets and Adjust Payment Counter Values
function err_exit($x)
{
	header("Content-type: text/xml");
	echo
	<<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
<counters>
<status>0</status>
<reason>$x</reason>
</counters>
</billing_api>
XXX;

	exit;
}

function getCounters($bkt)
{
	$result = dosql("select * from prepay_counters where billingid='$bkt' ");

  if(mysql_num_rows($result)==0)
    return false;

  dbg("found counters");
  dbg("select * from prepay_counters where billingid='$bkt' ");
	return mysql_fetch_object($result);
}

function adjustCounters($btk,$faxin,$dicom,$acc)
{
  dbg("Adjusting counters for $btk");

	$r = getCounters($btk);

  // If an mcid is provided for billing token, automatically 
  // create a new billing token and bind the mcid to it
  $db = DB::get();
  if(!$r && is_valid_mcid($btk,true)) {

    // Check if the billing token already exists
    $billacc = $db->first_row("select * from billacc where accid = ?",array($btk));
    if($billacc) {
      $btk = $billacc->billingid;
      dbg("Found existing billing token tied to account id: $btk");
      $r = getCounters($btk);
    }
    else {
      $seed = time()."-".$btk."-".rand();
      dbg("Creating new billing id - seed = $seed");
      $billingId = sha1($seed);

      $db->execute("insert into billacc (billingid, accid, ProductCode,ActivationKey) values (?,?,?,?)",
                   array($billingId, $btk, '',''));
      $btk = $billingId;
    }
  }

  if(!$r) { // no existing counters
    // add new record
    if ($faxin<0) $faxin=0; if ($dicom<0) $dicom=0;if ($acc<0) $acc=0;

    $db->execute("insert into prepay_counters set billingid=?,faxin=?, dicom=?, acc=?",
                 array($btk,$faxin,$dicom,$acc));
  }
  else {
		$faxin+=$r->faxin; if ($faxin<0) $faxin=0;
		$dicom+=$r->dicom; if ($dicom<0) $dicom=0;
		$acc+=$r->acc; if ($acc<0) $acc=0;

    $db->execute("update prepay_counters set faxin=?, dicom=?, acc=? where billingid=?",
                  array($faxin,$dicom, $acc, $btk));
	}
	return array ($faxin,$dicom,$acc);

}


function bind_billing($btk,$accid,$pc,$ak)
{
	dosql ("Replace into billacc set billingid='$btk',  accid='$accid', ProductCode='$pc' , ActivationKey='$ak' ");

  // ssadedin: adjust the counters so that they already have values
  // otherise errors when querying this billing token
  adjustCounters($btk,0,0,0);
}
function get_billing_id($accid)
{
	$result =  dosql ("Select * from billacc where accid='$accid' ");
	$r = mysql_fetch_object($result);
	if ($r===false) return '0'; else
	return $r->billingid;
}

function dump_bindings ($btk){
	$buf = '';
	$result = dosql("Select * from billacc where billingid='$btk' ");
	while ($r = mysql_fetch_object($result))
	{
		$buf .= "$r->accid ";
	}
	return $buf;
}

if (!isset($_REQUEST['btk']))
{
	if (isset($_REQUEST['accid'])) {
		$outmsg = <<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
XXX;

		$accids = explode(',',$_REQUEST['accid']);  // support comma separated
		for ($i=0; $i<count($accids); $i++)
		{
			$accid = $accids[$i];
			$btk = get_billing_id($accid);
			if ($btk!='0') {
        $r = getCounters($btk);
        if($r) {
          $counters = <<<XXX
<counters>
<faxin>$r->faxin</faxin>
<dicom>$r->dicom</dicom>
<acc>$r->acc</acc>
</counters>
XXX;
        }
        $outmsg .= <<<XXX
<binding>
<comment>showing billing token associated with account</comment>
<accid>$accid</accid>
<billingid>$btk</billingid>
$counters
<status>1</status>
</binding>
XXX;
      }
			else
			$outmsg .= <<<XXX
<binding>
<comment>no billing token associated with account</comment>
<accid>$accid</accid>
<status>0</status>
</binding>
XXX;
}

$outmsg .= <<<XXX
</billing_api>
XXX;


header("Content-type: text/xml");
echo $outmsg;
exit;
	} else  err_exit("Needs Billing Token");
}
$btk = $_REQUEST['btk'];

if (isset($_REQUEST['accid'])) { // If accid is provided, operation is to bind the billing token to the accid
	$accid = $_REQUEST['accid'];
	$pc = $_REQUEST['pc'];
	$ak = $_REQUEST['ak'];
	bind_billing($btk,$accid,$pc,$ak);
	$outmsg = <<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
<binding>
<comment>connecting a billingid to an accid</comment>
<billingid>$btk</billingid>
<status>1</status>
<accid>$accid</accid>
</binding>
</billing_api>
XXX;
	header("Content-type: text/xml");
	echo $outmsg;
	exit;
}
else
if (isset($_REQUEST['dump'])) {
	$dump = dump_bindings($btk);
	$outmsg = <<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
<binding>
<comment>dumping bindings for a billingid</comment>
<billingid>$btk</billingid>
<status>1</status>
<accounts>$dump</accounts>
</binding>
</billing_api>
XXX;
	header("Content-type: text/xml");
	echo $outmsg;
	exit;
}


$adjust = false;

// change these requests to posts once we have this debugged

if (isset($_REQUEST['faxin'])) { $adjust = true; $faxin = $_REQUEST['faxin']; } else $faxin = 0;


if (isset($_REQUEST['dicom'])) { $adjust = true; $dicom = $_REQUEST['dicom']; } else $dicom = 0;


if (isset($_REQUEST['acc'])) { $adjust = true; $acc = $_REQUEST['acc']; } else $acc = 0;

if (isset($_REQUEST['pc'])) { $adjust = true; $pc = $_REQUEST['pc']; } else $pc = 0;

if (isset($_REQUEST['ak'])) { $adjust = true; $ak = $_REQUEST['ak']; } else $ak = 0;

dbg("wsCounters: btk=".$btk." faxin=$faxin, dicom=$dicom, acc=$acc");

if ($adjust)
{
	{
		list ($faxin,$dicom,$acc) = adjustCounters($btk,$faxin,$dicom,$acc);
		$outmsg = <<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
<counters>
<comment>adjusted counters, returning current values</comment>
<billingid>$btk</billingid>
<status>1</status>
<faxin>$faxin</faxin>
<dicom>$dicom</dicom>
<acc>$acc</acc>
</counters>
</billing_api>
XXX;
}
}
else {

	$outmsg = <<<XXX
<?xml version='1.0' encoding='UTF-8'?>
<billing_api>
XXX;

	$btks = explode(',',$btk); // allow for many btks
	for ($i=0; $i<count($btks); $i++)
	{
		$btk = $btks[$i];
		$r = getCounters($btk);
		if ($r===false) 	$outmsg .= <<<XXX
<counters>
<comment>bad billingid</comment>
<billingid>$btk</billingid>
<status>0</status>
</counters>
XXX;
		else
		$outmsg .= <<<XXX
<counters>
<comment>returned counters</comment>
<billingid>$btk</billingid>
<status>1</status>
<faxin>$r->faxin</faxin>
<dicom>$r->dicom</dicom>
<acc>$r->acc</acc>
</counters>
XXX;
}
$outmsg .= <<<XXX
</billing_api>
XXX;

}



header("Content-type: text/xml");
echo $outmsg;
?>
