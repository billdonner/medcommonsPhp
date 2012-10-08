<?
/**
 * Renders only table rows for a table displaying vouchers.
 * 
 * This file does not render the table element itself, only the
 * internals - see voucher_list_account_panel.tpl.php
 */
require_once "alib.inc.php";

if(isset($_GET['standalone'])) {
  nocache();
  echo "<table>";
}

try {
  $info = get_validated_account_info();

  // $query = " from modcoupons c, modservices s where s.svcnum = c.svcnum and s.accid = ?";
  $query = "from modcoupons c
                left join modcoupon_share sh on sh.couponum = c.couponum,
                modservices s
            where s.svcnum = c.svcnum 
            and  (s.accid = ? or sh.accid = ?)";

  $params = array($info->accid, $info->accid);

  if(($pn = req('patientname')) && ($pn != "")) {
    $query .= " and c.patientname like ?";
    $params[]="%".$pn."%";
  }

  if(($st = req('status')) && ($st != "")) {
    $query .= " and c.status like ?";
    $params[]="%".$st."%";
  }

  // Query the count of rows
  $count_query = "select count(*) as cnt ".$query;
  $counts = pdo_query($count_query,$params);
  if($counts === false) 
    throw new Exception("Failed to query counts for $query");
  $count = $counts[0]->cnt;

  // Query the actual data
  $data_query = "select c.* ".$query." order by c.couponum desc limit 10";
  $rows = pdo_query($data_query,$params);
  if($rows === false)
    throw new Exception("unable to query from modcoupons using $query");

  foreach($rows as $v) {
    echo "<tr><td><a href='/$v->mcid' target='ccr'><img src='images/hurl.png'/>&nbsp;".hsc($v->patientname)."</a></td><td>".hsc($v->expirationdate)."</td><td>".hsc($v->status)."</td></tr>";
  }
}
catch(Exception $e) {
    echo "<tr><td colspan='3'>Apologies - Unable to query patient list at this time</td></tr>";
    error_log("Failed to query vouchers: ".$e->getMessage());
}
// no support for paging yet
$pageLinks = "";
?>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr id='registryTableBottomRow'>
  <td style='text-align: left;' colspan='3'><?=$count?> Patients</td>
  <td align='right' colspan='1'><?=$pageLinks?></td>
</tr>
<?
if(isset($_GET['standalone'])) {
  echo "</table>";
}
?>

