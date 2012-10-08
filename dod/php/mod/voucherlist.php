<?php

require_once "modpay.inc.php";

//wld 26 june 2008 - claim this is safe - the /name/ arg has mysql_escape-string, the others are small integers

function iservicechooser($accid, $svc)
{
	global $PT, $ST, $NAME, $VIEW;
	
	$count = 0;
	// returns a big select statement
	$outstr = <<<XXX
	<select id='svcnum' name='svcnum' title='choose one of the serices you have registered with your account $accid' 
	onchange="location = 'voucherlist.php?name=$NAME&v=$VIEW&pt=$PT&st=$ST&i='+this.options[this.selectedIndex].value;" >
	<option value=0>--------all--------</option>
XXX;
	$result =sql("SELECT * from modservices  where accid= '$accid' and servicename != '__default__' ") or die("cant query modservices ". mysql_error());

	while ($r2 = mysql_fetch_object($result))
	{
		$count++;
		$name = substr($r2->servicename,0,19); //only show 1st 19 in select
		//$ename = urlencode($name);
		$selected = ($r2->svcnum  == $svc)?' selected ':'';
		$outstr .="<option value='$r2->svcnum' $selected >$name</option>
		";
	}
	$outstr.="</select>";

	if ($count==0)  return "<span>No provider defined services are defined for this account</span>";
	return $outstr;

}
function ptchooser( $svc)
{
	global $SN,$ST,$NAME, $VIEW;
	$vals = array ('no charge','amzfps','cash','paycard');
	$count = 0;
	// returns a big select statement
	$outstr = <<<XXX
	<select id='ptchoosenum' name='ptchoosenum' title='choose payment type filter'' 
	onchange="location = 'voucherlist.php?name=$NAME&v=$VIEW&i=$SN&st=$ST&pt='+this.options[this.selectedIndex].value;" >
	<option value=0>---any---</option>
XXX;
	foreach ($vals as $val)
	{
		$count++;
		$selected = ($count == $svc)?' selected ':'';
		$outstr .="<option value='$count' $selected >$val</option>
		";
	}
	$outstr.="</select>";
	return $outstr;
}
function stchooser( $svc)
{
	global $SN,$PT,$NAME, $VIEW;
	$vals = array ('issued','accessed','revoked','completed');
	$count = 0;
	// returns a big select statement
	$outstr = <<<XXX
	<select id='stchoosenum' name='stchoosenum' title='choose status filter'' 
	onchange="location = 'voucherlist.php?name=$NAME&v=$VIEW&i=$SN&pt=$PT&st='+this.options[this.selectedIndex].value;" >
	<option value=0>---any---</option>
XXX;
	foreach ($vals as $val)
	{
		$count++;
		$selected = ($count == $svc)?' selected ':'';
		$outstr .="<option value='$count' $selected >$val</option>
		";
	}
	$outstr.="</select>";
	return $outstr;
}

list($accid,$fn,$ln,$email,$idp,$mc,$auth)=logged_in();

$masteraccid = get_master_services_accid($accid);

$btk = wsGetBillingId($masteraccid);

list ($faxin,$dicom,$acc) =wsGetCounters($btk);


if (isset($_REQUEST['v'])) $VIEW=mysql_real_escape_string($_REQUEST['v']); else $VIEW=0;

if (isset($_REQUEST['name'])) $NAME=mysql_real_escape_string($_REQUEST['name']); else $NAME='';

if (isset($_REQUEST['st'])) $ST=$_REQUEST['st']; else $ST=0;

if (isset($_REQUEST['pt'])) $PT=$_REQUEST['pt']; else $PT=0;

if (isset($_REQUEST['i'])) $i=$_REQUEST['i']; else $i=0;
$SN=$i; // save as global variable for fancy selects$SN=
$header = page_header("page_list","Vouchers - MedCommons on Demand" );
$footer = page_footer();
$qm ='';
if ($i!=0) $qm .=" and s.svcnum='$i' ";
if ($PT!=0) {
	switch ($PT) {
	case 1:  $spt ='no charge'; break;
	
	case 2:  $spt ='amzfps'; break;
	
	case 3:  $spt ='cash'; break;
	
	case 4:  $spt ='cc'; break;
	
	default:  $spt ='badcode'; break;
	}
	$qm .= "and c.paytype='$spt' ";
}
if ($VIEW==0) $qm .= "and c.status='issued' "; else if ($ST!=0) 
{
		switch ($ST) {
	case 1:  $sst ='issued'; break;
	
	case 2:  $sst ='accessed'; break;
	
	case 3:  $sst ='revoked'; break;
	
	case 4:  $sst ='completed'; break;
	
	default:  $sst ='badcode'; break;
	}
	$qm .= "and c.status='$sst' ";
}
$servicechooser = iservicechooser($accid,$i);
$paytypechooser = ptchooser ($PT);
$statuschooser = stchooser ($ST);
$buf = '';
$counter=0;$createtotal=$utilizedtotal=0; $cashreceivedtotal=0;$cashpaidouttotal=0;$profit=0;
if ($NAME!='') $qm.="and c.patientname like '%$NAME%' "  ;
$masteraccid = get_master_services_accid($accid);
// ssadedin: note that services and coupons have identical columns, so cannot select * from both
// or you may get wrong information
$result = sql ("Select c.*, s.servicedescription, s.servicename 
  from modcoupons c, modservices s
  where s.accid='$masteraccid' 
  and s.servicename != '__default__' 
  and c.svcnum = s.svcnum $qm 
  order by c.issuetime desc"); 


while ($r=mysql_fetch_object($result))
{
	$paidvia = paidvia ($r->patientprice,$r->paytype);
	$rpatientprice = $r->patientprice/100.;
	if ($r->paytype=='amzfps') $paidvia .= <<<XXX
 		<form method=post action='/fps/src/Amazon/FPS/MOD/voucherfpsrefund.php' > 
		<input type=hidden value=voucherlist.php  name=next />
		<input type=hidden value=$rpatientprice  name=price />
		<input type=hidden value=$r->paytid  name=paytid />
		<input type='submit'  name='Voucher'  class='mainshort smalltext' value='Refund' />
		</form>
XXX;

	$patprice = ($r->patientprice/100.);
	list  ($netpractice, $netmc, $amazonfee) = figure_money($patprice,$r->duration,$r->asize,$r->fcredits,$r->dcredits);
	if ($r->paytype!=''){$cashreceivedtotal += $patprice;
	$profit += $netpractice;}
	$patprice = mony($patprice);
	$np = mony($netpractice);
	$netmc = mony ($netmc);
	$amazonfee = mony($amazonfee);

	if (strlen($r->servicedescription)>30)
	$sd = substr($r->servicedescription,0,27).'...'; else
	$sd = substr($r->servicedescription,0,30);
	if (strlen($r->patientname)>30)
	$pn = substr($r->patientname,0,27).'...'; else
	$pn = substr($r->patientname,0,30);
	if (strlen($r->patientemail)>20)
	$pm = substr($r->patientemail,0,17).'...'; else
	$pm = substr($r->patientemail,0,20);

	if ($r->paytype=='') $paylink = "<a href=voucherpay.php?c=$r->couponum >pay</a>"; else $paylink='';
	if ($r->patientprice==0) $paylink = '';
	$ti = date("Y/m/d ",$r->issuetime);
	$otp = sha1($r->otp);
	if ($r->patientemail!='') $pn = "<a href='mailto:$r->patientemail' >$pn</a>";
	$createtotal++;
	if ($r->status!='issued') $utilizedtotal++;
		$faxcred = (0!=$r->fcredits)? "<br/>$r->fcredits fax pages":'';
		$dicomcred = (0!=$r->dcredits)? "<br/>$r->dcredits dicom uploads":'';
		$expires =  "expires: $r->expirationdate"; $addinfo  =($r->addinfo!='')?"<br/>$r->addinfo":'';
  if(($r->status == 'completed')||($r->status == 'accessed'))
  { $mode = 'view';$complete=''; $delete='';

  }
  else
  {$mode = 'edit'; $delete = "<a class=deleteLink href=voucherrevoke.php?c=$r->couponum&next=voucherlist.php >X</a><br/>";
    $complete = <<<XXX
 <form method=post action=vouchercomplete.php> 
		<input type=hidden value=voucherlist.php  name=next />
		<input type=hidden value=$r->couponum  name=c />
		<input type='submit'  name='Voucher'  class='mainshort smalltext' value='Complete' />
		</form>
XXX;
}
  
if ($VIEW>0)
	$buf.= <<<XXX
<tr>
<td  >
<a target='_new' title='open records in new window' 
href='$r->hurl' onclick='return open_hurl($r->couponum,"$mode")'>
<img border=0 src='/images/icon_healthURL.gif' alt=hurlimg />
$pn</a><br/>$ti</td>
<td title='$r->servicedescription'>$r->servicename</td>
<td>$expires $faxcred $dicomcred $addinfo</td>
<td>$r->status</td>
<td>$paidvia<br/>$patprice</td>
<td>amz: $amazonfee<br/>&nbsp;mc: $netmc</td>
<td>$np</td>

<td>
<a href=voucherprint.php?c=$r->couponum&reprint >reprint</a>
$delete
$complete		
$paylink</td>
</tr>
XXX;

	else 
		$buf.= <<<XXX
<tr>
<td  >$pn</td><td>$ti</td>
<td><a target='_new' title='open records in new window' 
href='$r->hurl' onclick='return open_hurl($r->couponum,"$mode")'>
<img border=0 src='/images/icon_healthURL.gif' alt=hurlimg />
$r->hurl</a></td>
<td title='$r->servicedescription'>$r->servicename</td>
<td>$expires $faxcred $dicomcred $addinfo</td>
<td>
$complete
</td>
</tr>
XXX;
}

$buf.= <<<XXX
</table>
</p>

</div>
$footer
XXX;

$cashreceivedtotal = mony($cashreceivedtotal);
$profit = mony($profit);
if ($VIEW>0)
$front  = <<<XXX
<script type='text/javascript'>
  function open_hurl(cnum,mode) {
    if(!mode)
      mode = 'view';
    window.open('hurlredir.php?cnum='+cnum+'&m='+mode);
    return false;
  }
</script>
<div id="CBIWide"  mainId='page_list' mainTitle="List vouchers"  >
<table width='100%'><tr><td align='left'><h2>Vouchers </h2><small><a href='?v=0' >patient list view</a></small></td><td width=100% >
</td><td valign='top' align='right'>
<table id='svctotals' title="totals">
	<tr><th>total vouchers</th><th>paid vouchers</th><th>revenue</th><th>profit</th></tr>
	<tr><td class='actual'>$createtotal</td><td class='actual'>$utilizedtotal</td><td class='actual'>$cashreceivedtotal</td><td class='actual'>$profit</td></tr>
	</table></td></tr></table>
<table id='svctable'>
<tr>
<th>issued to</th>
<th>service name</th>
<th>voucher parameters</th>
<th>voucher status</th>
<th>payment</th>
<th>fees</th>
<th>profit</th>
<th>actions</th>
</tr>
<tr>
<th><form method='get' action='voucherlist.php'>
<input type='hidden' name='v' value="$VIEW" />
<input type='hidden' name='pt' value="$PT" />
<input type='hidden' name='st' value="$ST" />
<input type='hidden' name='i' value="$SN" />
<input type='text' name='name' size='12' value="$NAME" />
</form></th>
<th>$servicechooser</th>
<th>&nbsp;</th>
<th>$statuschooser</th>
<th>$paytypechooser </th>

<th>&nbsp;</th>
<th>&nbsp;</th>
<th>&nbsp;</th>
</tr>
XXX;
else 
$front  = <<<XXX
<script type='text/javascript'>
  function open_hurl(cnum,mode) {
    if(!mode)
      mode = 'view';
    window.open('hurlredir.php?cnum='+cnum+'&m='+mode);
    return false;
  }
</script>
<div id="CBIWide"  mainId='page_list' mainTitle="List vouchers"  >
<h2>Patient List</h2><small><a href='?v=1' >full view</a></small>
<table id='svctable'>
<tr>
<th>issued to</th>
<th>issued time</th>
<th>health URL</th>
<th>service name</th>
<th>voucher parameters</th>
<th>actions</th>
</tr>
<tr>
<th><form method='get' action='voucherlist.php'>
<input type='hidden' name='v' value="$VIEW" />
<input type='hidden' name='pt' value="$PT" />
<input type='hidden' name='st' value="$ST" />
<input type='hidden' name='i' value="$SN" />
<input type='text' name='name' size='12' value="$NAME" />
</form></th>
<th>&nbsp;</th>
<th>&nbsp;</th>

<th>$servicechooser</th>
<th>&nbsp;</th>
<th>&nbsp;</th>
</tr>
XXX;
echo $header.$front.$buf;
?>
