<?php 
global $Secure_Url;
?>
<style type='text/css'>
  @import url('dod.css');
  table.summary {
    width: 100%;
  }
  table.summary th {
    background-color: #eee;
  }
  table.summary td {
    text-align: center;
  }
 
  table#dataSourceLayout tr {
    margin-top: 5px;
  }
  
  table#dataSourceLayout th {
    text-align: left;
  }
  table.summary th, table#dataSourceLayout th {
    background-color: #bbbbaa;
    padding: 0px 3px;
  }
  table.summary td, table#dataSourceLayout td,  #dataSourceLayout table  th {
    background-color: #fffcdd;
    padding: 1px 3px;
  }
   table#dataSourceLayout td {
    vertical-align: top;
   }
   
   table#dataSourceLayout td.first {
    padding-top: 5px;
  }
  
  #wholeStep1 {
	  margin-top: 20px;
  }
  #wholeDataSourceStep {
    margin-top: 20px;
  }
  textarea {
    overflow: auto;
  }
  #dicomOrderFormTable th {
    text-align: right;
  }
  #dicomOrderFormTable td {
    padding: 8px;
  }
  table#patientData {
    width: 100%;
  }
  table tr td.match {
	  text-align: center;
  }
  .invisible {
    visibility: hidden;
  }

  #voucherDetailsStep table#voucherTable {
        margin-top: 20px;
	    margin-left: 0px;
  }
  #voucherDetailsStep table#voucherTable td {
	  text-align: left;
  }
  
  #voucherDetailsStep table#voucherTable th {
	  text-align: left;
  }
  td#voucherId { width: 15%; }
  td#voucherPin { width: 12%; }
  td#progressCell { width: 26%; whitespace: no-wrap;}

  table#dataSourceLayout tr.buttonRow td,
  table tr.buttonRow td {
    background-color: white;
    vertical-align: middle;
  }
  table#patientData tr.mismatch td, 
  table#patientData tr.mismatch th {
    background-color: #f7f0cc;
  }
  p.error {
    background-color: #fff0f0;
    padding: 7px !important;
    border: solid 1px #a00;
  }
  </style>
<?
if(isset($GLOBALS['DEBUG_JS'])) {
  echo "<script type='text/javascript' src='MochiKitDebug.js'></script>";
  echo "<script type='text/javascript' src='sha1.js'></script>";
  echo "<script type='text/javascript' src='utils.js'></script>";
  echo "<script type='text/javascript' src='ajlib.js'></script>";
  echo "<script type='text/javascript' src='contextManager.js'></script>";
}
else
  echo "<script type='text/javascript' src='acct_all.js'></script>";
?>

<div id='upload'>

<h2>DICOM On Demand Image Upload</h2>

<table id='orderSummary' class='summary'>
        <tr>
            <th>Reference</th>
            <th>Patient ID</th>
            <th>Protocol</th>
            <th>Modality</th>
            <th>Scanned</th>
        </tr>
        <tr>
            <td><?=ent($order->callers_order_reference)?></td>
            <td><?=ent($order->patient_id)?></td>
            <td><?=ent($order->protocol_id)?></td>
            <td><?=ent($order->modality)?></td>
            <td><?=ent(strftime('%m/%d/%Y %H:%M',strtotime($order->scan_date_time)))?></td>
        </tr>
</table>

<? include "detectDDL.tpl.php"?>

<div id='wholeDataSourceStep' class='hidden'>
	<div id='fillOutFormStep' class='section'>
	<table width='100%' id='dataSourceLayout'>
	   <tr><th>Data Source</th><th>Patient / DICOM</th></tr>
	   <tr>
	   <td width='50%'>
	    <form name='dicomUploadForm' id='dicomUploadForm' method='POST'>
	      <table id='dicomOrderFormTable'>
	        <tr><th>Source</th><td class='first'><input type='radio' name='source' id='sourceCD' value='CD'/>CD 
	                               <input type='radio' name='source' id='sourcePACS' value='PACS'/>PACS   
	                            </td>
            </tr>
	        <tr><th>Comments</th><td><textarea id='order_comments' name='order_comments' rows='6' cols='42' ><?=$order->comments?></textarea></td></tr>
	       </table>
	    </form> 
	    
	    </td>
	    <td>
	       <table id='patientData'>
	           <tr><th>Study</th>
	               <td class='first' colspan='3'><select id='studies'>
	                           <option>Please Select a Source</option>
	                       </select>
	               </td>
		           <tr><th>Series</th>
	               <td><select multiple='true' id='series'>
	                       <option>Please Select a Source</option>
	                       </select>
	               </td>
	               <td>&nbsp;</td>
	               <td>&nbsp;</td>
	           </tr>
	           <tr><th id='scanningMsgRow'>&nbsp;</th><td id='scanningMsg' colspan='2'></td></tr>
	           <tr class='invisible'><th>Name</th><td id='patientName'></td><td>&nbsp;</td></tr>
	           <tr class='invisible even'><th>Date of Birth</th><td id='patientDateOfBirth'></td><td class='match'>Match</td></tr>
	           <tr class='invisible odd' id='patientIdRow'><th>Patient ID</th> <td id='patientId'></td> <td class='match'><img src='images/greentick.gif'/></td></tr>
	           <tr class='invisible even' id='scanDateRow'><th>Scan Date</th> <td id='scanDate'></td> <td class='match'><img src='images/greentick.gif'/></td></tr>
	           <tr class='invisible odd' id='modalityRow'><th>Modality</th> <td id='modality'></td> <td class='match'><img src='images/greentick.gif'/></td></tr>
	           <tr id='patientDataPadding' class='hidden'><th>&nbsp;</th> <td>&nbsp;</td> <td>&nbsp;</td></tr>
	         </table>
	    </td>
	    </tr>
	    <tr class='buttonRow'>
	       <td style='text-align: center;'><input type='button' id='beginUploadButton' value='Click to Begin Upload' disabled='true'/></td>
	       <td style='text-align: center;'><input type='checkbox' value='matched' id='matched' disabled='true' name='matched'/><span>Please check this box to confirm that details match</span></td>
        </tr>
	    </table>
	</div>
</div>

<div id='voucherDetailsStep' class='hidden section'>

    <table id='voucherTable' class='summary'>
	    <tr><th>Voucher</th><th>PIN</th><th>HealthURL</th><th>Progress</th></tr>
	    <tr>
		    <td id='voucherId'>Please Wait</td>
		    <td id='voucherPin'>Please Wait</td>
		    <td><a id='healthurl' target='ccr' title='Your patient data on MedCommons - Click to Review' href=''></a></td>
		    <td id='progressCell'><span id='progress'>Please Wait</span></td>
        </tr>
	    <tr class='buttonRow'><td>&nbsp;</td><td class='buttons' colspan='3'>
		    
            <span id='cancelUpload' class='hidden'><button id='cancelUploadButton'>Cancel</button></span>
		    <button id='printButton'>Print</button> 
			<span id='restartButtonWrapper' class='hidden'>
				<button id='restartButton' class='hidden'>Close</button>
		    </span>
	    </td></tr>
    </table>
    
    <p id='transferError' class='error hidden'>&nbsp;</p>
    
</div>

<?include "problemReport.tpl.php"; ?>  

</div>
<iframe name='printFrame' id='printFrame' style='position: absolute;  left: -500px; top: -500px; width: 400px; height: 500px;' src='about:blank'>
</iframe>
<script type='text/javascript' src='dod.js'> </script>
<script type='text/javascript'>

var order = <?=$orderJSON?>;
var callersOrderReference = '<?=ent($order->callers_order_reference)?>';
var localGatewayRootURL = '<?=$Secure_Url?>';
var groupAccountId = '<?=$groupAccountId?>'; 

// Make the order reference get appended to the cookie
var extraCookieValues = { callersOrderReference: callersOrderReference };

<? include "required_dod_ddl_version.tpl.php";?>
disconnectAll(window,'ddlStarted');
connect(window, 'ddlStarted', function() { 

    // fade('wholeStep1',{from: 1, to: 0, afterFinish: function() {
    //    appear('wholeDataSourceStep');
    //}});
    hide('wholeStep1');
    show('wholeDataSourceStep');
    
});

disconnectAll('restartButton', 'onclick');
connect('restartButton', 'onclick', function() { 
    setCookie('upload','');
	window.close();
});

connect(window,'uploadFinished', function() {
	if(window.opener) {
		removeElementClass('restartButtonWrapper','hidden');
	}
});

connect(window,'startScanning', function() {
    pingIntervalMs = 4000;
    $('scanningMsg').innerHTML = "<img src='/yui/2.6.0/tabview/assets/loading.gif'> Scanning Folders";
});

addLoadEvent(function() {	connect('sourcePACS', 'onclick', function() {
		alert('This option is not implemented yet.');
	});
	connect('sourceCD', 'onclick', function() {
        sendCommand("scanfolder", { jsonp: 'onScanResults'});
        // Higher rate for better responsiveness!
        pingIntervalMs = 1000;
	});
	
	connect(window,'transferStarted',function() {
	    disable('order_comments','sourcePACS','sourceCD','studies','series','matched','beginUploadButton');
	});
	connect('beginUploadButton','onclick',function() {
		var series = [];

        forEach($$('#series OPTION'),function(o) {
		    if(o.selected) 
			    series.push(o.value);
	    });
	    
	    if(series.length == 0) {
		    if(!confirm('No series were selected - send entire study?'))
			    return;
		    series = map(function(o) {o.value}, $$('#series OPTION'));
        }
        
	    log("Sending " + series.length + " series");
	    selectCDFolder({},{},'uploaddicom',{ 
		    callers_order_reference: callersOrderReference,
	        sourceLocation: sourceLocation,
	        series: series.join(','), 
	        groupAccountId: groupAccountId,
	        jsonp: 'onUploadDICOMResult'
		});
		
	    disable('beginUploadButton');
	    showVoucherDetails();
	});
});

function onUploadDICOMResult(result) {
    if(result.status != 'ok') {
        alert('A problem occurred uploading your data: \r\n\r\n' + result.error);
        return;
    }
    uploadStarted();

    execJSONRequest('update_order_status.php', 
        queryString({callers_order_reference:callersOrderReference, 
                     status:'DDL_ORDER_XMITING', 
                     comments:$('order_comments').value, 
                     desc:'Upload initiated from web form'
            }), 
        function(response) {
	        if(response.status != 'ok') {
	            alert('A problem occurred updating status on your order: \r\n\r\n' + response.error);
	        }
	    });
}

function isSaveRestorable(upload) {
    return upload.callersOrderReference == callersOrderReference;
}

var sourceLocation = null;

function onScanResults(result) {
  log('scan results command returned with status ' + result.status);   
}

function validate(id, match) {
    var src = match ? 'images/greentick.gif' : 'images/redcross.gif';
    $$('#' + id + 'Row img')[0].src = src;
    addElementClass($(id+'Row'), match ? 'matched' : 'mismatch');
    removeElementClass($(id+'Row'), match ? 'mismatch' : 'matched');
}

connect(commands,'scanfolderComplete',function(result) {

    pingIntervalMs = 4000;
    
    window.result = result;

    $('scanningMsg').innerHTML = '';

    if(result.status != 'ok') {
        alert('There was a problem scanning the folder you selected: \r\n\r\n' + result.error);
        return;
    }

    var studies = [];
    for(var i in result.studies) {
        studies.push(result.studies[i]);
    }

    if(studies.length == 0) {
        alert('No studies were selected.  Please try again.');
    }
        
    sourceLocation = result.selectedLocation;
    if(!sourceLocation) {
        alert('No source location was selected.  Please try again.');
        return;
    }
    
    var options = map(function(s) {  return OPTION(s.description); }, studies);
    partial(replaceChildNodes,'studies').apply(window,options);

    replaceChildNodes('series', OPTION('Please Select a Study'));

    connect('studies','onchange',function() {
        var study = studies[ $('studies').selectedIndex ];
        
        var options = [];
        for(var seriesUID in study.series) {
            options.push(OPTION({value: seriesUID},study.series[seriesUID].description));
        }
        partial(replaceChildNodes,'series').apply(window, options);
        forEach($$('#patientData tr'), function(tr) {removeElementClass(tr,'invisible'); });
        enable('matched');
        replaceChildNodes('patientName',study.patient.name);
        if(study.patient)
	        replaceChildNodes('patientId',study.patient.id);
        replaceChildNodes('patientDateOfBirth',study.patient.dateOfBirth);
        replaceChildNodes('modality',study.modality);
        if(study.date)
	        replaceChildNodes('scanDate',formatLocalDateTime(new Date(study.date)));

        if(study.patient && study.patient.id) 
	        validate('patientId',(study.patient.id == order.patient_id));
        
        if(study.modality) 
	        validate('modality',(study.modality == order.modality));
        
        if(study.date) {
            var od = isoTimestamp(order.scan_date_time);
            var dd = new Date(study.date);
            var dateMatch = (od.getFullYear() == dd.getFullYear()) && (od.getMonth() == dd.getMonth()) && (od.getDate() == dd.getDate());
            var timeMatch = (od.getHours() == dd.getHours()) && (od.getMinutes() == dd.getMinutes());
            validate('scanDate', dateMatch && timeMatch);
        }

        hide('scanningMsgRow');
        show('patientDataPadding');
        
        connect($('matched'),'onclick',function() { enable('beginUploadButton'); });
    });

    signal($('studies'),'onchange');
});
</script>
