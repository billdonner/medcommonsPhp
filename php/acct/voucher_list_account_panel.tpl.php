
<style type='text/css'>
  #voucherTableHeading  {
    position: relative;
  }
  #voucherTableHeading span img {
    cursor: pointer;
  }
  #voucherTableHeading span {
    position: absolute;
    right: 30px;
    top: 0px;
  }
  #roirForm {
    display: none;
  }
</style>
<div class="featurebox" id="worklist">
  <div id='voucherTableHeading'>
    <span>Request ID <input type='text' id='roirid'/> <img id='searchROIRImg' title='Look up Request for Information ID' src='images/magnifier.png'/></span> <h2>Dashboard - Patient List</h2>
  </div>
  <table id='voucherTable' class="liveTable stdTable" width="98%">
    <thead>
    <tr id='voucherTableHeaderRow' class='roundedHeader'>
      <th title='Patient Name' class='tableLeft rounded'><div class='registryTableHeader'>
        <?include "leftRoundHeader.inc.php";?>
        <h5>Patient</h5></div></th>
      <th><div class='registryTableHeader'><h5>Expiry</h5></div></th>
      <th width='105px'><div class='registryTableHeader'><h5>Status</h5></div></th>
      <th class='tableRight rounded' width='45'><div class='registryTableHeader'>
        <?include "rightRoundHeader.inc.php";?>
        <h5>&nbsp;</h5></div></th>
    </tr>
    <tr class="searchRow">
      <th><input name="voucherPatientName" id='voucherPatientName' type="text" value=""/></th>
      <th><select id="searchExpiry" name="searchExpiry" disabled="true">
        <option value='all'>All</option>
        <option value='week'>3 Days</option>
        <option value='month'>1 Week</option>
        <option value='year'>1 Month</option>
        </select>
      </th>
      <th><input id="voucherStatus" type="text"/></th>
      <th>&nbsp;<a class='deleteLink' style='top: 0px;' title='Clear search terms' href='javascript:clearVoucherSearch();'>X</a></th>
    </tr>
    </thead>
    <tbody id='voucherRows'>
      <? include "query_voucher_list.php"; ?>
    </tbody>
  </table>
</div>
<div id='voucherParser' class='invisible'>&nbsp;</div>
<div id='acdiv'></div>
<form id='roirForm' method='post' action='/mod/vouchersetup.php'>
  <input type='hidden' name='roirId' value=''/>
  <input type='hidden' name='svcnum' value=''/>
  <input type='hidden' name='servicename' value=''/>
  <input type='hidden' name='patientname' value=''/>
  <input type='hidden' name='patientemail' value=''/>
  <input type='hidden' name='patientnote' value=''/>
</form>
<script type='text/javascript'>
addLoadEvent(function() {
  ce_connect('voucher_change_state', updateVouchers);
  forEach($$('.searchRow input'), function(inp) {
    connect(inp,'onkeyup',updateVouchers);
  });
  connect($('searchExpiry'), 'onchange', updateVouchers);
  connect($('searchROIRImg'), 'onclick', function() {
    $('roirid').disabled = true;
    execJSONRequest('/mod/lookup_roir.php',
      'roirId='+encodeURIComponent($('roirid').value)+'&accid='+encodeURIComponent(get_mc_attribute('mcid')), 
      function(result) {
      if(result.status == "ok") {
        if(result.roir == null) {
          alert("No request with ID " + $('roirid').value + " could be found.\n\nPlease check the value you entered and try again.");
          $('roirid').disabled = false;
        }
        else {
          $('roirForm').roirId.value = $('roirid').value;
          $('roirForm').svcnum.value = result.roir.svcnum;
          $('roirForm').servicename.value = result.roir.servicename;
          $('roirForm').patientemail.value = result.roir.patientemail;
          $('roirForm').patientnote.value = result.roir.patientnote;
          $('roirForm').patientname.value = result.roir.patientname;
          $('roirForm').submit();
        }
      }
      else {
        alert('Unable to look up the Request ID you entered: ' + result.message);
        $('roirid').disabled = false;
      }
    });
  });
});
var VoucherState = {
  patientname: '',
  'status': '',
  standalone: true
};
function updateVouchers() {
  VoucherState.patientname = $('voucherPatientName').value;
  VoucherState.status = $('voucherStatus').value;
  refreshTable('voucher','query_voucher_list.php?' + queryString(VoucherState));
}
function clearVoucherSearch() {
  clearSearch('voucherTable');
  updateVouchers();
}
</script>
