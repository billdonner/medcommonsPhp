
var ddlRunning = null;
var pingTimer = null;
var pongTimer = null;
var noping = false;
var transferKey = null;
var transferStarted = false;
var importStatus = null;
var originalImportLength = null;
var transfer = null;
var transferFinished = false;
var progressDisplayed = false;
var pingIntervalMs = 3000;
var requiredDDLVersion = 0;
var ddlVersion = 0;
var restarting = false;

var consoleLog = log;

var logLines = [];
log = function(msg) {
    logLines.push(msg);
    if(logLines.length > 300)
        logLines.shift();
    consoleLog(msg);
};

function pingDDL(callback) {

    if(noping) {
        setTimeout(pingDDL,5000);
        return;
    }

    if(pongTimer) {
        clearTimeout(pongTimer);
        pongTimer = null;
    }

    sendCommand("ping", {jsonp: 'pongDDL'});
    pingTimer = setTimeout(partial(pingTimeout,callback),4000);
}

var startedStatuses = ['Scanning','Importing','Uploading','Finished'];

/**
 * Object to be the target of command results from DDL.
 * Event listeners should bind to this object to receive asynchronous
 * command results.
 */
var commands = {};

function pongDDL(response) {
    log("pong => events= " + response.events + ", commands= " + response.result );
    
    clearTimeout(pingTimer);
    pingTimer = null;

    if(pongTimer) { // already in pong phase ... must have been a timeout
        return;
    }
    
    window.response = response;
    
    if(response.events) {
        forEach(response.events.split(','),function(e) {
	        signal(window,e);
        });
    }
    
    if(response.result) {
        for(var c in response.result) {
	        signal(commands, c + 'Complete', response.result[c]);
        };
    }

    if(originalImportLength == null && response.imports) {
        originalImportLength = response.imports.length;
        log('Found ' + originalImportLength + ' existing imports');
    }
    else
    if(response.imports && (response.imports.length > originalImportLength)) {

        importStatus = response.imports[response.imports.length-1];

        log('received import status ' + importStatus.status + ' (transfer started = ' + transferStarted + ')' );
        
        if(importStatus.status == 'Error')
            signal(window,'transferError');
        else
        if(importStatus.status == 'Cancelled')
            signal(window,'transferCancelled');
        else
        if(findValue(startedStatuses,importStatus.status)>=0) { // import is started
            if(transferStarted) {
              signal(window,'transferProgress', response);
            }
            else {
              transferStarted = true;
              transferKey = importStatus.transferKey;
              saveUpload();
              signal(window,'transferStarted');
            }
        }
    }

    // Detect when a new transfer occurs
    if(transferStarted && response.transfers) {
        var txs = filter(function(tx) { return tx.key == transferKey; }, response.transfers);
        if(txs.length>0)
            transfer = txs[txs.length -1];
    }
    
    if(!ddlRunning) {
        
        // Check version
        if(response.version) {
	        ddlVersion = parseInt(response.version,10);
        }
	        
        
        if(ddlVersion < requiredDDLVersion ) {
            if(!restarting) {
			    appear('restartDDLStep',{to:1.01});
			    hide('detecting','waiting','installDDLStep');
            }
        }
        else {
	        log("DDL started");
	        signal(window,'ddlStarted');
	        checkExistingUpload();
        }
    }
    ddlRunning = true;
    
    pongTimer = setTimeout(pingDDL,pingIntervalMs);
}

function pingTimeout(callback) {

    if(noping)
        return;

    log("Ping timeout at " + (new Date()).getTime());
    
    if(ddlRunning) {
        log("DDL stopped"); 
        signal(window,'ddlStopped');
    }
    ddlRunning = false;
    
    if(callback && (typeof callback == 'function')) {
        callback();
    }
    
    pongTimer = setTimeout(pingDDL,3000);
}

function hideDDLDetectionStep() {
    blindUp('step1boxes',{duration:0.5});
}

/**
 * Allow embedding pages to define a function that 
 * selectively abandons saves.  Used to ensure
 * that if a user comes back with a new callers order
 * reference that they get a new page not a saved one
 * in DODX.
 */
function isSaveRestorable(upload) {
    return true;
}

function checkExistingUpload() {
    var upload = getSavedUpload();
    if(!upload || !response.imports) 
        return;
        
    log("Found existing upload " + upload.key);
    var is = filter(function(i) { return i.transferKey == upload.key;}, response.imports);
    if(is.length == 0) { 
        log("Existing upload "+ upload.key + " was not found in known uploads for active DDL");
        return;
    }
    
    if(!isSaveRestorable(upload)) {
        log("Existing upload "+ upload.key + " was determined not to be restorable");
        return;
    }
    
    // We have to initialize the whole state as if import had started
    importStatus = is[0];
    transferKey = upload.key;
    patient = { patientMedCommonsId: upload.patientMedCommonsId, auth: upload.auth };
    gwUrl = upload.gwUrl;
    healthUrl = upload.healthUrl;
    transferStarted = true;
    originalImportLength = 0;
    
    hideDDLDetectionStep();
    uploadStarted();
    signal(window,'transferStarted');
    forEach(["#selectDataHeader img","#fillOutFormHeading img","#wholeStep1 h3 img"], function(expr) {
        var toShow = $$(expr);
        if(toShow.length > 0)
		    removeElementClass(toShow[0],"hidden");
    });
    
    if(findValue(['Finished','Cancelled','Error'],importStatus.status)>=0) {
        log("Existing upload is in a completed state");
        printed = true;
        $('progress').innerHTML = importStatus.status;
	    removeElementClass('restartButton','hidden');
	    signal(window,'uploadFinished');
    }
    else {
        log("Existing upload is in an active state");
                
        $('progress').innerHTML = 'Synchronizing ...';
    }
    checkForVoucher(showVoucherDetails);
}

function saveUpload() {
    var values = { 
            key: transferKey, 
            patientMedCommonsId: (patient?patient.patientMedCommonsId:null), 
            auth: (patient?patient.auth:null),
            gwUrl: gwUrl,
            healthUrl: healthUrl
        };
    
    if(typeof(extraCookieValues) != 'undefined')
        update(values,extraCookieValues);
        
    var c = '';
    for(i in values) {
        if(c!='')
            c += ',';
        c += i + '=' + values[i];
    }
    var expires = new Date( (new Date()).getTime() + (24 * 60 * 60 * 1000) );
    setCookie('upload',c, expires);
}

function getSavedUpload() {
    var u = getCookie('upload');
    if(!u)
        return null;
    var upload = {};
    forEach(u.split(','), function(kvp) {
        kvp = kvp.split('=');
        upload[kvp[0]] = kvp[1];
    });
    return upload;
}

connect(window, 'ddlStarted', function() { 
    hide('restartDDLStep','restartingDDL');
    appear('foundDDL',{to:1.01});
    hide('detecting','waiting','installDDLStep');
    enable('submitUploadForm');
});

connect(window, 'ddlStopped', function() {
    appear('installDDLStep',{to:1.01});
    hide('detecting','waiting','foundDDL');
    disable('submitUploadForm');
});

var patient = null;
var healthUrl = null;
var gwUrl = null;
var browsed = false;

addLoadEvent(function() {

    roundElement('helpers');

    connect($$('#helpbar a')[0], 'onclick', function(e) {
        e.stopPropagation();
        e.preventDefault();
        show('helpers');
        hide('problemResultFields');
        show('problemInputFields');
    });
    
    setTimeout(partial(pingDDL,function() {
        hide('detecting');
        if(!ddlRunning) 
            appear('installDDLStep',{to:1.01});

    }),500);
    connect($('startDDLLink'), 'onclick', function() {
        hide('installDDLStep');
        appear('waiting',{to:1.01});
    });
    
    if($('submitUploadForm'))
	    connect('submitUploadForm','onclick', selectCDFolder);
    
    connect('cancelUploadButton','onclick', function() {
        sendCommand('cancelUpload',{jsonp: 'cancelledUpload', transferKey: transferKey});
    });
    
    connect('submitProblem', 'onclick', function(evt) {
        var problemId = (new Date()).getTime() + '' + Math.round(Math.random()*10000);
        document.supportForm.problemId.value = problemId;
        evt.preventDefault();
        log("Sending problem info for id " + problemId);
        $('submitProblem').disabled = true;
        execJSONRequest('/router/ProblemReport.action', queryString(document.supportForm), function(response) {
            if(response.status == "ok") {
                if(ddlRunning) {
              execJSONRequest('/router/ProblemReport.action', {jslog:true,problemId:document.supportForm.problemId.value, description: logLines.join('\n')}, function(){});
                  sendCommand("uploadlog", {problemId:problemId, to: localGatewayRootURL + '/router/ProblemReport.action', jsonp:'logUploaded'});
                }
                else
                    logUploaded({status: "ok"});
            }
            else {
          noping = false;
            $('submitProblem').disabled = false;
                alert("A problem occurred in creating your problem report:\n\n" + response.error);
            }
        }, function(error) {
        $('submitProblem').disabled = false;
        genericErrorHandler(error);       
        });
    });
    
    connect('restartButton','onclick', partial(cancelReload,true));
});

function selectCDFolder(event, dataSource, action, params) {
    if(!dataSource) 
        dataSource = 'dicomUploadForm';
    if(!action)
        action = 'quickupload';
    if(!params)
        params = {};
    
    disable('submitUploadForm');
    hide('transferError','transferError2');
    execJSONRequest('create_upload_patient.php',queryString(dataSource)+'&'+queryString(params),function(result) {
	    log("Got upload result: " + result.status);
	    hide('transferError');
	    browsed = true;
	    var f = $('dicomUploadForm');
	    try {
	        if(result.status == 'ok') {
	            sendCommand(action, merge({ auth: result.authToken, 
	                                   gwUrl: result.gwUrl,
	                                   groupname: result.groupName,
	                                   groupAccountId: result.groupAccountId,
	                                   accountid: result.accid,
	                                   storageid: result.patient.patientMedCommonsId,
	                                   jsonp: 'uploadStarted'}, params));
	            patient = result.patient;
	            gwUrl = result.gwUrl;
	            healthUrl = result.healthUrl;
	            saveUpload();
	        }
	        else {
	            log('submission failed: ' + result.error);
	            alert("There was a problem with your submission: \r\n\r\n" + result.error);
	            enable('submitUploadForm');
	        }
	    }
	    catch(e) { 
            enable('submitUploadForm');
	        dumpProperties("Failed to send command to DDL " , e);
	    }
    });
} 

/**
 * Patched version of blinddown that restores height to auto
 * since this seems to screw up IE in some situations.
 */
function blindDownX(id) {
	blindDown(id,{duration:0.5,afterFinish:function(){$(id).style.height='auto';}}); 
}

function showVoucherDetails() {
    if(patient && patient.auth)
	    renderHealthURL();
    if(getStyle('voucherDetailsStep','display') == 'none') {
        log('showing voucher details');
	    blindDownX("voucherDetailsStep"); 
    }
}

function logUploaded(response) {
    noping = false;
    $('submitProblem').disabled = false;
    if(response.status == 'ok') {
        hide('problemInputFields');
        show('problemResultFields');
        $('problemIdResult').innerHTML = document.supportForm.problemId.value;
    }
    else {
        alert("A problem occurred uploading your session log:\n\n"+response.error);
    }
}

// Called when user cancels actual upload (not selection dialog)
function cancelledUpload(result) {
    if(result.status == 'ok') {
        addElementClass('cancelUpload','hidden');
        printed = true; // hack: pretend they have already printed so they don't get a warning
        
        $('progress').innerHTML = 'Cancelled!';
    }
    else {
        alert('A problem occurred while cancelling your upload: \r\n\r\n' +
                result.error);
    }
}

function cancelReload(noSuppressPrint) {
    if(!noSuppressPrint)
	    printed = true;
    setCookie('upload','');
    window.location.reload();
}

var voucherCheckInterval = null;
function uploadStarted() {
    connect(window,'transferStarted',function() {

        log("transferStarted event");
        
        vacuumContextManagerScripts();
        
        forEach($$("#selectDataHeader img"),function(i){removeElementClass(i,"hidden");});

        if(!voucherCheckInterval)
            voucherCheckInterval = setInterval(checkForVoucher, 4000);

        $('progress').innerHTML = importStatus.status + ' ...';

        if($('selectDataStep'))
	        blindUp('selectDataStep',{duration: 0.5});
        
        showVoucherDetails();
        
        connect(window, 'transferProgress', function(evt) {
            if(!transfer) {
                log('received transfer progress but no transfer in progress yet');
                return;
            }
            

            // Can't cancel while scanning
            if(importStatus.status != 'Scanning') 
              removeElementClass('cancelUpload','hidden');

            var tx = transfer;
            var studyCount = tx.queuedStudies + (tx.queuedStudies > 1 ? ' studies' : ' study');
            log("Got progress: " + tx.progress);

            
            if(importStatus.status == 'Scanning' && importStatus.message && importStatus.message != '') {
                $('progress').innerHTML = importStatus.message;
            }
            else
            if(tx.state == 'UPLOADING') {
                $('progress').innerHTML = '' + Math.floor(100 * Math.min(1.0,tx.progress)) + ' % of ' + studyCount;
                progressDisplayed = true;
                if(tx.progress >= 1) 
                    uploadFinished();
            }
            else
            if(tx.state == 'SCANNING') {
                $('progress').innerHTML = 'Scanning files ... '+((tx.queuedStudies > 1) && (tx.queuedStudies) ?' ('+tx.queuedStudies+' studies found)':'');
            }
            else
            if(tx.state == 'ERROR') {
                $('progress').innerHTML = 'Error occurred!';
            }
        });
    });
    connect(window, 'transferError', function() {
        log("Transfer error");
        enable('submitUploadForm');
        var msg = 'A problem occurred with importing your selection.  Please try again. <br/><br/>'+escapeHTML(importStatus.message?importStatus.message:'No Message')
                 +'<br/><a href="javascript:cancelReload();">Cancel Upload</a>';
        $('transferError').innerHTML = msg;
        appear('transferError');
        
        if($('transferError2')) {
	        $('transferError2').innerHTML = msg;
	        appear('transferError2');
        }
    });
    connect(window, 'transferCancelled', function() {
        log("Transfer cancelled");
        enable('submitUploadForm');
        $('transferError').innerHTML = 'The file selection was cancelled.  Please try again.';
        appear('transferError');
    });
}

function renderHealthURL() {
    $('healthurl').href = healthUrl;
   setCookie('mc_anon_auth',patient.auth, null, '/');
   $('healthurl').innerHTML = healthUrl;
}

function uploadFinished() {

  log("Upload finished");
  
  renderHealthURL();
 
  addElementClass('cancelUpload','hidden');
  removeElementClass('restartButton','hidden');
  
  // An extremely fast upload might finish without starting
  if(!transferStarted) {
      blindUp('selectDataStep',{duration: 0.5});
      blindDownX('voucherDetailsStep');
  }

  // If no transfer reported progress then set the progress manually
  // manually
  if(!progressDisplayed) {
      $('progress').innerHTML = 'Finished';
  }

  if(!voucher) 
      checkForVoucher();
  
  disconnectAll(window,'transferProgress');
  
  signal(window,'uploadFinished');
}

var printed = false;
var voucher = null;
function checkForVoucher(callback) {
    execJSONRequest('query_patient_voucher.php', {accid: patient.patientMedCommonsId, auth: patient.auth}, function(result) {
        log('query patient result: ' + result.status + ' / voucher:  ' + (result.voucher?result.voucher.couponum:'null'));
        if(result.status == 'ok') {
            if(result.voucher) {
                voucher = result.voucher;
                // alert('Your voucher is : ' + result.voucher.voucherid + ' and your PIN is ' + result.voucher.otp);
                $('voucherId').innerHTML = voucher.voucherid;
                $('voucherPin').innerHTML = voucher.otp;
                $('patientName').innerHTML = voucher.patientname;
                connect($('printButton'), 'onclick', function() {
                    vacuumContextManagerScripts();
                    printed = true;
                    var printUrl = 'displayVoucher.php?t=print&accid='+patient.patientMedCommonsId+'&auth='+patient.auth+'&vcode='+result.voucher.voucherid;
                    log('printing using url ' + printUrl);
                    if(window.navigator.appName.indexOf('Internet Explorer')>0)
                        window.open(printUrl,'prtvoucher','width=500,height=500,toolbars=no');
                    else
                        $('printFrame').src = printUrl;
                    removeElementClass($$("#voucherDetailsStepHeader img")[0],"hidden");
                });
                window.onbeforeunload = function() {
                    if(!printed)
                       return "Please make sure you have printed this page or recorded your Voucher ID and PIN before leaving.  You will need these to enable the recipient to access your data.";
                };
                clearInterval(voucherCheckInterval);
                voucherCheckInterval = null;
            }
            callback(result.voucher);
        }
        else {
            log("Voucher query failed: " + result.error);
        }
    });
}

connect('restartDDLLink','onclick', function(evt) {
    disconnectAll(window,'ddlStopped');
    evt.stopPropagation();
    evt.preventDefault();
    sendCommand("shutdown", { jsonp: 'onShutdown'});
});

function onShutdown() {
    hide('restartDDLStep');
    restarting = true;
    appear('restartingDDL',{to:1.01});
    window.location.href = $('restartDDLLink').href;
} 