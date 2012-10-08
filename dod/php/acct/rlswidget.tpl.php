<?
/**
 * Main widget page - renders outer HTML and BODY for the expanded RLS widget. 
 * The table body and data itself is rendered by a sub-template (rlstable.tpl.php) 
 * and is updated repeatedly by an ajax call.
 */
$sd = isset($GLOBALS['Script_Domain']) ? $GLOBALS['Script_Domain'] : "";
?>
      <script type="text/javascript">
        var practiceId = '<?=$pid;?>';
        RLSParams = { pid: '<?=$pid;?>', page: 1 };

        function start() {
          if(window.parent && window.parent.setMainSectionHeading) {
            window.parent.setMainSectionHeading('<?=$practicename?>');
          }
          setInterval(refreshRLS, 13000);
        }
        function calculateHeight() {
         if(el('acdiv').style.display == 'none') {
           return _calculateHeight();
         }
         else
           return (elementDimensions(el('acdiv')).h+elementDimensions($('records')).h); 
        }
        addLoadEvent(start);
        addLoadEvent(function(){window.setTimeout(addHeightMonitor,300);window.setTimeout(function() {bc_last_height = 0; broadCastHeight();},1000);});
      </script>
      <style type="text/css">
        #records {
          padding-left: 2px;
        }
        #buttons a {
          float: right;
        }
        #roirForm {
          display: none;
        }
        input#roirid {
          width: 6em;
        }
        #worklistButtons {
          position: relative;
          top: 14px;
        }
        #worklistButtons * {
         vertical-align: middle;
          font-size: 11px;
        }
        #worklistButtons img {
          cursor: pointer;
        }
      </style>
    <div id="records">
    <script type="text/javascript">
      var isIE = false;
      function setGWCookie(url) {
        setCookie("mcgw",url + ";<?=$accid?>", null, "/",<?= ($sd=='') ? 'null' : '".'.$sd.'"'?>);
        return true;
      }
      statusValues = '<?=addSlashes($statusValues)?>'.split(',');
      addLoadEvent(function() {
        forEach($$('.searchRow input'), function(inp) {
          connect(inp,'onkeyup',updateSearch);
        });
        connect($('searchLastUpdate'), 'onchange', updateSearch);
      });

    </script>
    <h2>Dashboard - <?=htmlentities($practicename)?></h2>
    <table id='registryTable' class="liveTable stdTable" width="100%">
      <thead>
      <tr id='registryTableHeaderRow' class='roundedHeader'>
        <th title='Patient Name' class='tableLeft rounded'><div class='registryTableHeader'>
          <?include "leftRoundHeader.inc.php";?>
          <h5>Patient</h5></div></th>
        <th title='Time Since Last Update' valign='top'><div class='registryTableHeader nowrap'><h5>Last Update</h5></div></th>
        <th><div class='registryTableHeader'><h5>Purpose</h5></div></th>
        <th width='105px'><div class='registryTableHeader'><h5>Status</h5></div></th>
        <th><div class='registryTableHeader'><h5>&nbsp;</h5></div></th>
        <th class='tableRight rounded'><div class='registryTableHeader'>
          <?include "rightRoundHeader.inc.php";?><h5>&nbsp;</h5></div></th>
      </tr>
      <tr class="searchRow">
        <th><input name="searchPatientName" type="text" value="<?=hsc($searchPatientName)?>"/></th>
        <th><select id="searchLastUpdate" name="searchLastUpdate">
          <option value='all'>All</option>
          <option value='week'>Last Week</option>
          <option value='month'>Last Month</option>
          <option value='year'>Last Year</option>
          </select>
        </th>
        <th><input name="searchPurpose" type="text"/></th>
        <th><input name="searchStatus" type="text"/></th>
        <th>&nbsp;<a class='deleteLink' style='top: 0px;' title='Clear search terms' href='javascript:clearRLSSearch();'>X</a></th>
      </tr>
      </thead>
      <tbody id='rlsRows'>
      <?=$content?>
      </tbody>
      </table>
    </div>
    <div id='rlsParser' class='invisible'>&nbsp;</div>
    <div id='acdiv' ></div>
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
    if(!$('searchROIRImg'))
      return;
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
  </script>
