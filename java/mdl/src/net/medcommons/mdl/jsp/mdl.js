/*
Invokes Ajax call to server; handles results. 

To do:

Finish filling out result table for query.
Add more/next button
Add redo query
Add selection for particular patient.
Generate list of XDS documents.
*/


var storageId="1013062431111407"; // Jane Hernandez
var consentPractice = "HIMSS Demo Clinic, LLP";
var consentPatientPHRService = "MedCommons";
var consentPHRAccess = "Read/Write";
var consentExpires = "June 2007";
var consentPatientNotification = "patient@aol.com";
var progressCounter = 0;


function launchSSOLogin(){
	alert("Placeholder for login to IdP");
}

function toggleVisible(elem) {
	toggleElementClass("invisible", elem);
}

function makeVisible(elem) {
	removeElementClass(elem, "invisible");
}

function makeInvisible(elem) {
	addElementClass(elem, "invisible");
}

function isVisible(elem) {
    // you may also want to check for
	// getElement(elem).style.display == "none"
	return !hasElementClass(elem, "invisible");
}

/*
Logout returns to initial state; re-enables login.
*/
function createLogoutResponse(){
	log("entering createLogoutResponse");
    var rows = new Array();
	rows[0] = new Array('Username', INPUT({'id':'username','type': 'text', 'value': '', 'name': 'username'}));
	rows[1] = new Array('Password', INPUT({'type': 'password', 'value': '', 'name' : 'password'}));
	rows[2] = new Array(INPUT({'type': 'button', 'style': 'float: right','value':'Login', 'name':'action','onclick':"invoke( \'loginFrame\', 'ADLogin.action', this.form, \'userLogin\');"}));
	
	    row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
		var newTable=  FORM({},TABLE({'class':'prettytable','style':'margin-left:14em'},
	                TBODY(null, map(row_display, rows))));
	   log("Exiting createLogoutResponse " + newTable);             
	   return(newTable);                 
}
/*
function createIdpLogin(){
	log("entering createIdpLogin");
    var rows = new Array();
<form method=post action="http://medcommons2:8090/mdl/hello?o=P"><h3>Login Using New IdP</h3>

<input type=submit name="l1https://medcommons1:8391/idp.xml" value=" Login to https://medcommons1:8391/idp.xml (A2) ">

<h3>Technical options</h3><input type=checkbox name=fc value=1 checked> Create federation, NID Format: <select name=fn>
<option value=prstnt>Persistent<option value=trnsnt>Transient<option value="">(none)</select><br>
<input type=hidden name=fq value=""><input type=hidden name=fy value=""><input type=hidden name=fa value="">
<input type=hidden name=fm value="">
<input type=hidden name=fp value=0>
<input type=hidden name=ff value=0>

}
*/
function createLoginResponse(req)
{
 	log("Entering createLoginResponse");
 
   var identity = req.identity;
   var rows = new Array();
   rows[0] = new Array(req.firstName  + " " + req.lastName + " "  + req.title, null, null);
   rows[1] = new Array(identity.idP, identity.identityToken);
   rows[2] = new Array(null,INPUT({'type': 'button', 'style': 'float: right','value':'Logout','name':'action','onclick':"invoke(\'loginFrame\', 'ADLogout.action', this.form, \'userLogout\');"}), "", "", "");
   
 
   row_display = function (row) {
		return TR(null, map(partial(TD, null), row));
	}
	
	var newTable = FORM({},TABLE({'class': 'prettytable'},
		TBODY(null, map(row_display, rows))));
	

     log("Exiting createLoginResponse:" + newTable);
   return(newTable)

 
}

function roundLabels() {
  nodeWalk(document.body, function(n) {
      if(n.className && (hasElementClass(n,'leftcolActive')||hasElementClass(n,'leftcolInactive'))) {
        if(!n.rounded) {
          roundElement(n);
          n.rounded = true;
        }
      }
      return n.childNodes;
  });
}

function showLoginRequiredItems(display) {
  if(!display) {
    display = 'block';
  }
  /*
  roundClass('div', 'leftcolActive');
  roundClass('div', 'leftcolInactive');
  */
  roundLabels();
  nodeWalk(document.body, function(n) {
      if(n.className && hasElementClass(n,'loginRequired')) {
        n.style.display=display;
      }
      return n.childNodes;
  });

}

/*

*/
function createPatientQuery(){
	log("entering createPatientQuery");
    var rows = new Array();
	rows[0] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Local Name')),
                      SPAN({'id':'patientName'},INPUT({'type': 'text', 'value': '', 'name': 'firstname'}),INPUT({'type': 'text', 'value': '', 'name': 'middlename'}), INPUT({'type': 'text', 'value': '', 'name': 'lastname'})));
	rows[1] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Date of Birth')),INPUT({'type': 'text', 'value': '', 'name': 'dob'}));
	rows[2] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Medical Record No')), INPUT({'type': 'text', 'value': '', 'name' : 'queryid'}));
	rows[3] = new Array(DIV({'class':'leftcolInactive'}, SPAN(null,'Federated ID')));
	rows[4] = new Array(DIV({'class':'leftcolInactive'}, SPAN(null,'PHR Updated')));
	rows[5] = new Array(DIV({'class':'leftcolActive'},
                      SPAN(null,'Max. Results Returned')), INPUT({'type': 'text', 'value': '-1', 'name' : 'maxQueryItems'}),
                      INPUT({'type': 'button', 'style': 'float: right','value':'Execute Query', 'name':'action','onclick':"invokePatientQuery( \'queryResultFrame\', 'PdqQuery.action', this.form, \'patientQuery\');"}));
	    row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
		var newTable=  FORM({},TABLE({'class':'patientQueryTable'},
	                TBODY(null, map(row_display, rows))));
	   log("Exiting createPatientQuery " + newTable);             
	   return(newTable);                 
}

function createRhioQuery(defaultRhio){
	log("entering createRhioQuery");
	var rows = new Array();
	rows[0] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Rhio')),INPUT({'type': 'text', 'value': defaultRhio, 'name': 'rhioFilter'}),
      INPUT({'type': 'button', 'style': 'float: right','value':'Select RHIO', 'name':'action','onclick':"invoke( \'rhioDisplayFrame\', 'RhioSelection.action', this.form, \'setRhio\');"}));
	
	    row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
		var newTable=  FORM({},TABLE({'class':'patientQueryTable'},
	                TBODY(null, map(row_display, rows))));
	   log("Exiting createRhioQuery " + newTable);             
	   return(newTable);      
}

function createXDSQuery(){
	log("entering createXDSQuery");
	var rows = new Array();
	rows[0] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'XDS')),INPUT({'type': 'text', 'value': '', 'name': 'patientId'}),
                      INPUT({'type': 'button', 'style': 'float: right','value':'Search XDS', 'name':'action','onclick':"invokeDocumentXDSQuery(  this.form );"}));
	
	    row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
		var newTable=  FORM({},TABLE({'class':'patientQueryTable'},
	                TBODY(null, map(row_display, rows))));
	   log("Exiting createXDSQuery " + newTable);             
	   return(newTable);      
}
function getPatientMRN(patientRecord){
	var patientMRN = "";
	if (patientRecord.patientIdentifier != null)
		patientMRN = patientRecord.patientIdentifier.idNumber;
	return(patientMRN);
}
function createPatientSelectionResponse(response){
	var patientMRN = getPatientMRN(response);
	
		
	var federatedIdentity = "";
	if (response.federatedIdentity != null){
		federatedIdentity = response.federatedIdentity.idP + " " + response.federatedIdentity.identityToken;
	}
	else
		federatedIdentity = INPUT({'type': 'button', 'style': 'float: right','value':'Patient Login Required', 'name':'action','onclick':"invokePatientLogin( \'sharingConsentFrame\', 'PdqQuery.action', this.form, \'patientQuery\');"});
	var rows = new Array();
	rows[0] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Local Name')), response.patientName.prefix + ' ' + response.patientName.givenName + ' ' + response.patientName.otherName + ' ' +  response.patientName.familyName + ' ' +response.patientName.suffix);
	rows[1] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Date of Birth')),response.patientDateOfBirth);
	rows[2] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Medical Record No')), patientMRN);
	rows[3] = new Array(DIV({'class':'leftcolInactive'},SPAN(null, 'Federated ID')), federatedIdentity);
	rows[4] = new Array(DIV({'class':'leftcolInactive'}, SPAN(null,'PHR Updated')));
	if (response.federatedIdentity != null){
		rows[5] = new Array(INPUT({'type': 'button', 'style': 'float: right','value':'View PHR', 'name':'action','onclick':"invokeViewPHR( \'queryResultFrame\', 'PdqQuery.action', this.form, \'patientQuery\');"}));
	}
	rows[rows.length] = new Array(INPUT({'type': 'button', 'style': 'float: right','value':'Query for another patient', 'name':'action','onclick':"invokeNewPatient( \'queryResultFrame\', '', this.form, \'newPatient\');"}));

	row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
	var newTable = FORM({},TABLE({'class':'currentPatientTable'},
        TFOOT(null, TR(null, TD(), TD())),
        TBODY(null, map(row_display, rows))
	)); 
	
	return(newTable);
	// Fill in patient table.
	// Response should have value for federated idp.
	// if blank - put in form to replace this value.
	// Must have in JavaScript some dummy value here so that there isn't a way to send PHR without login *or* identity.
}

function createPatientQueryResponse(response){
    var rows = new Array();
    
    
    row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
    for (var i = 0; i < response.length; i++) {
		var patientIdentifier = response[i].patientIdentifier;
		var idNumber = "Unknown";
		if (patientIdentifier.idNumber != null)
			idNumber = patientIdentifier.idNumber;
		var patientName = response[i].patientName;
		var pName = "Unknown";
		if (patientName != null);
			pName = patientName.givenName + " " + patientName.familyName;
		var selection = INPUT({'type':'radio', 'value' : response[i].id, 'name':'selectedId'});
		
    	rows[i] = new Array(selection,idNumber, pName, response[i].patientDateOfBirth, response[i].patientSex);
    	
		log("response[" + i + "]" + response[i]);
	
    }
    rows[rows.length] = new Array(INPUT({'type': 'button', 'style': 'float: right','value':'Select Patient', 'name':'action','onclick':"invoke( \'queryResultFrame\', 'PatientSelection.action', this.form, \'patientSelection\');"}));
//	 rows[rows.length] = new Array(INPUT({'type': 'button', 'style': 'float: right','value':'Query for another patient', 'name':'action','onclick':"invokeNewPatient( \'queryResultFrame\', '', this.form, \'newPatient\');"}));
	
 	var newTable = FORM({},TABLE({'class':'patientQueryResultTable'},
        THEAD(null, TR(null, TD(''), TD('MRN'), TD('Patient Name'), TD('DOB'), TD('Sex'))),
        TFOOT(null, TR(null, TD(), TD())),
        TBODY(null, map(row_display, rows))
	)); 
	return(newTable);
}

function createRhioSelectionResponse(response){
	var rows = new Array();
	rows[0] = new Array(DIV({'class':'leftcolActive'},'Selected Rhio'),INPUT({'type': 'text', 'value': response.name, 'name': 'rhioFilter'}));
	rows[1] = new Array(DIV({'class':'leftcolActive'},'Rhio'),INPUT({'type': 'text', 'value': response.name, 'name': 'rhioFilter'}));
	rows[2] = new Array(DIV({'class':'leftcolActive'},'PIX'),response.pixConfig);
	rows[3] = new Array(INPUT({'type': 'button', 'style': 'float: right','value':'Select RHIO', 'name':'action','onclick':"invoke( \'rhioDisplayFrame\', 'RhioSelection.action', this.form, \'setRhio\');"}));
	var newTable = FORM({},
		TABLE({'class':'patientQueryResultTable'}, 
			TFOOT(null, TR(null, TD(), TD())),
			TBODY(null, map(row_display, rows))
		
	)); 
	
	return(newTable);
}


function createDocumentSelectionResponse(response){

	var rows = new Array();
	rows[0] = [ SPAN({'id':'progressTitle'},'PHR Update progress') ];
	rows[1] = new Array(response.displayStatus);
	var progressBar = DIV({'class':'progressBar'},response.percentComplete + "%");
	
	
	var progressRows = new Array();
	var progressDimensions = new Object();
	progressDimensions.w = response.percentComplete;
	progressDimensions.h = 100;
	
	setElementDimensions(progressBar, progressDimensions,'%');
	rows[rows.length] = new Array(progressBar);
	
	var newTable = FORM(null,DIV({'id':'updateProgressPanel'},FORM({},
		TABLE({'class':'patientQueryResultTable'}, 
			TFOOT(null, TR(null, TD(), TD())),
			TBODY(null, map(row_display, rows))
	)))); 

  roundElement(newTable, {color:'#dfe1da'});
	
	return(newTable);
	
	
	
}
// See http://hcxw2k1.nist.gov/wiki/index.php/XD*_Testing_(2006-2007_Season)
function formatFormatCode(formatCode){
	if ('1.2.840.10008.5.1.4.1.1.88.59'==formatCode)
		return("DICOM Key Object");
	else if ('ScanPDF/IHE 1.x'==formatCode)
		return("Scanned PDF");
	else if ('ScanTEXT/IHE 1.x'==formatCode)
		return("Scanned Text");
	else if ('CDAR2/IHE 1.0'==formatCode)
		return("CDA Document");
	else if ('1.3.6.1.4.1.19376.1.5.3.1.1.2'==formatCode)
		return("XDS-MS");
	else if ('1.3.6.1.4.1.19376.1.5.3.1.1.9'==formatCode)
		return("PPHP");
	else if ('1.3.6.1.4.1.19376.1.5.3.1.1.9'==formatCode)
		return("PPHP");
	else if ('1.3.6.1.4.1.19376.1.5.3.1.1.10'==formatCode)
		return("EDR");
	else if ('1.3.6.1.4.1.19376.1.5.3.1.1.5' == formatCode)
		return("XPHR Extract");
	else if ('1.3.6.1.4.1.19376.1.5.3.1.1.6' == formatCode)
		return("XPHR Update");
	else
		return(formatCode);
		
}

function createDocumentQueryResponse(response){
    var rows = new Array();
    
    
    row_display = function (row) {
	                return TR(null, map(partial(TD, null), row));
	        };
			
	var disabledInputs = new Array();
    for (var i = 0; i < response.length; i++) {
		var doc = response[i];
		var practicesetting = doc.practiceSettingCode;
		var downloadURL = doc.uri;
		var typecode = doc.typeCode;
		var selectDocument = INPUT({'type':'checkbox', 'value' : doc.uuid, 'name':typecode.displayName});
		if (doc.mimeType=='application/dicom'){
			selectDocument.disabled = true;
			disabledInputs.push(selectDocument);
		}
		var uri = doc.uri;

    	rows[i] = new Array(selectDocument, typecode.displayName, practicesetting.displayName, doc.documentTitle, doc.documentSize, formatFormatCode(doc.formatCode),  doc.mimeType, A({ 'href': uri , 'target':'_blank'},
                        "view") );
    	
		log("response[" + i + "]" + ", " + practicesetting.displayName + ", " +  typecode.displayName + ", " + downloadURL);
		// Note that the constructed rows here should have checkboxes.
	
    }	
	
    rows[rows.length] = new Array('','','','',INPUT({'type': 'button', 'style': 'float: right','value':'Update PHR', 'name':'action','onclick':"invoke( \'progressDisplayFrame\', 'DocumentSelection.action', this.form, \'documentSelection\');"}));
	rows[rows.length] = new Array(INPUT({'type': 'hidden', 'value': storageId, 'name' : 'storageId'}));
 	var newTable = FORM({},TABLE({'class':'patientQueryResultTable'},
        THEAD(null, TR(null, TD('No. Documents:' + response.length), TD('Type'), TD('Practice'), TD('Title'), TD('Size'), TD('FormatCode'), TD('MimeType'), TD('Document'))),
        TFOOT(null, TR(null, TD(), TD())),
        TBODY(null, map(row_display, rows))
	)); 
	
	forEach(disabledInputs, function(i) {
	  i.parentNode.parentNode.className = 'disabledRow';
	});
	return(newTable);
}
/*
function getFormNames(form){
	log("getFormNames:" + form);
	var elements = form.elements;
    var names = new Array();
    for (var i = 0; i < elements.length; i++) {
    	names.push(elements[i].name);
    }
    return(names);
}
function getFormValues(form){
	log("getFormValues:" + form);
	var elements = form.elements;
    var values = new Array();
    for (var i = 0; i < elements.length; i++) {
    	values.push(elements[i].value);
    }
    return(values);
}
*/
function getFormArguments(form){
	log("getFormArguments " + form);
	var elements = form.elements;
	var names = new Array();
	var values = new Array();
	var checkboxName = "uuids";
	var checkboxInit = false;
	var uuids = "";
	for (var i = 0;i<elements.length;i++){
		var element = elements[i];
		// elements - get types and for text always put in; for checked put in if checked; for radio put in selected.
		if ((element.type == 'hidden') || (element.type == 'text') || (element.type == 'textarea') ||
			(element.type == 'password')){
			log("textual element " + element.name + ", " + element.value + "," + element.type);
			names.push(element.name);
			values.push(element.value);
		}
		else if (element.type == 'radio'){
			
			if (element.checked == true){
				log("radio element " + element.name + ", " + element.value + "," + element.checked);
				names.push(element.name);
				values.push(element.value);
			}
			else{
				log("+++ignored element " + element.name + ", " + element.value + "," + element.checked);
			}
		}
		else if (element.type == 'checkbox'){
			if (element.checked == true){
				if (checkboxInit == false){
					checkboxInit = true;
				}
				if (uuids == "")
					uuids = element.value;
				else 
					uuids = uuids + "," + element.value;
			}
		}
		else{
			log("+++Not handled: name=" + element.name + "=" + element.value);
		}
		
	}
	if (checkboxInit == true){
		names.push(checkboxName);
		values.push(uuids);
	}
	return(new Array(names, values));
}
/**
Returns an array of name/value pairs from an HTML form into what doSimpleXMLHttpRequest 
needs to digest.
**/
function getFormArray(form){
	log("getFormArray:" + form);
	var elements = form.elements;
    var parameters = new Array();
    log("Form elements:" + elements.length);
    for (var i = 0; i < elements.length; i++) {
      var value = elements[i].value
      var name = elements[i].name;
      var pair = new Array();
      pair[0] = name + ":";
      pair[1] = value;
      log("New pair:" + pair);
      parameters = parameters.concat(pair);
      log("New parameters = " + parameters);
    }
    return(parameters);
}


function invokeViewPHR(containerId, action, form, event){
	alert("If patient logged in _or_ provider has read access to PHR - then display page with MedCommons account here");
}

function invokePatientLogin(containerId, action, form, event){
	
	var message = "Launch patient login/registration here.";
	message += "\n  1. Register/Log into patient's IdP to obtain federated identity";
	message += "\n  2. Register/Log into patient's PHR (MedCommons).";
	message += "\n  3. Set Limited Consent preferences:";
	message += "\n    a) When consent expires";
	message += "\n    b) What access this practice has to the patient's PHR";
	message += "\n    c) How this patient wishes to be notified of access";
	message += "\n       and changes to their PHR by this practice.";
	
	alert(message);
	/*
	var consentPractice = "HIMSS Demo Clinic, LLP";
var consentPatientPHRService = "MedCommons";
var consentPHRAccess = "Read/Write";
consentExpires
var consentPatientNotification = "patient@aol.com";
*/
   var rows = new Array();

    rows[0] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Practice')),consentPractice);
	rows[1] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Patient\'s PHR Service')),consentPatientPHRService);
	rows[2] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'PHR Access')),consentPHRAccess);
	rows[3] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Expires')),consentExpires);
	rows[4] = new Array(DIV({'class':'leftcolActive'},SPAN(null,'Expires')),consentPatientNotification);
	
   
 
   row_display = function (row) {
		return TR(null, map(partial(TD, null), row));
	}
	
	var newTable = FORM({},TABLE({'class': 'patientQueryTable'},
		TBODY(null, map(row_display, rows))));
	replaceChildNodes(containerId, newTable);
  roundLabels();
}

function invokeNewPatient(containerId, action, form, event){
	var pQuery = createPatientQuery();
	replaceChildNodes('currentPatientFrame', pQuery);
	replaceChildNodes('queryResultFrame');
	
}
 function invokePatientQuery(containerId, action, form, event){

  var rows = new Array();
   rows[0] = new Array("Querying server for patients...");
   
   
 
   row_display = function (row) {
		return TR(null, map(partial(TD, null), row));
	}
	
	var newTable = FORM({},TABLE({'class': 'patientQueryTable'},
		TBODY(null, map(row_display, rows))));
	replaceChildNodes(containerId, newTable);
	return(invoke(containerId, action, form, event));
 }
 
 // *****
 function invokeDocumentXDSQuery(aform){
	
	var elements = aform.elements;
	for (var i = 0; i < elements.length; i++){
		log ("elements " + i + "  = " + elements[i].value);
	}
	var patientMRN =elements[0].value;
	
	log("invokeDocumentXDSQuery:" + patientMRN);
	
	return(invokeDocumentQueryForPatientId(patientMRN));
 }
 
function invokeDocumentQuery(patientRecord){
	log("invokeDocumentQuery");
   var patientMRN = getPatientMRN(patientRecord);
   return(invokeDocumentQueryForPatientId(patientMRN));
 }
 function invokeDocumentQueryForPatientId(patientMRN){
	log("invokeDocumentQueryForPatientId");
  
   log("about to query for documents with mrn " + patientMRN);
   var containerId = 'queryResultFrame';
   var action = 'DocumentQuery.action';
   var event = 'documentQuery';
   var rows = new Array();
   rows[0] = new Array("Querying server for documents...");
   rows[1] = new Array(INPUT({'type': 'hidden', 'value': patientMRN, 'name' : 'queryid'}));
  
 
   row_display = function (row) {
		return TR(null, map(partial(TD, null), row));
	}
	
	var newTable = FORM({},TABLE({'class': 'patientQueryTable'},
		TBODY(null, map(row_display, rows))));
	replaceChildNodes(containerId, newTable);
	return(invoke(containerId, action, newTable, event));
 }
 /* 
 	Generic routine called by all Ajax/forms
	
	Container is the id of the object to replace in the dom.
  */
  function invoke(containerId, action, form, event) {
  	  log("invoke containerId=" + containerId + "form=" + form + ", event =" + event);
  	  var url = document.location.protocol + "//" + document.location.host + "/mdl/" + action;
  	 // var names = getFormNames(form);
  	  //var values = getFormValues(form);
  	  var args = form.tagName.match(/form/i) ? getFormArguments(form) : new Array() ;
      new updateElementFromHTML(containerId, url, event, args);
	  log("invoke exits");
	  return(false); // Stops subsequent processing
  }



function loginResponse(resp) {
  try {
  	alert("Log in:" +resp);
  }
  catch(e){
  	dump(e);
  	}
}

function genericErrorHandler(e) {
  alert("An error occurred while performing last operation.\r\n\r\n"
   + "Error: " + e.message + "\r\n\r\n"
   + "Code: " + e.number + "\r\n\r\n"
   + "Try the operation again or contact support for help.");
  window.lastError = e;
}

 var cachedJSONResponse = null; // Debugging in FireBug 

updateElementFromHTML = function (containerId, url, event, arguments) {
	var names = arguments[0];
	var values = arguments[1];
	log("updateElementFromHTML " + containerId + " "  + url + " with names " + names + " and values " + values);
	 
                                                                                                                                                             
    var tempError = function (req) {
           log("Error: " + req);
		   //log("Error Text:" + req.responseText);
	};
	
    var d = doSimpleXMLHttpRequest(url,names, values);
    	d.addCallback(function (req) {
    	log("Response:" + req.responseText);
		var progressFrame = getElement('progressDisplay');
		
    		var jsonResponse = eval(req.responseText); //evalJSONRequest(req);
			cachedJSONResponse = jsonResponse;
			//log("jsonResonse:" + jsonResponse);
		//log("email:" + jsonResponse.email);
			log("Response status:" + cachedJSONResponse.status.name);
			var responseStatus =  cachedJSONResponse.status.name;
			var responseContents = cachedJSONResponse.contents;
			if (responseStatus != "OK"){
				message = "Request returned status " + responseStatus;
				message+= "\n" + cachedJSONResponse.message;
				
				alert(message);
				log(responseContents);
				makeInvisible(progressFrame);
				return;
			}
    		var processedResponse = "ERROR!";
			
    		if (event == 'userLogin'){
    			processedResponse = createLoginResponse(responseContents);
    			 replaceChildNodes(containerId, processedResponse);
    			var pQuery = createPatientQuery();
    			 replaceChildNodes('currentPatientFrame', pQuery);
          showLoginRequiredItems();

    			// var rhio = createRhioQuery('');
    			// replaceChildNodes('rhioDisplayFrame', rhio);
				//invokePatientLogin('sharingConsentFrame', 'PdqQuery.action', null, 'patientQuery');
    			}
			
			else if (event == 'userLogout'){
    			processedResponse = createLogoutResponse(responseContents);	
    			 replaceChildNodes(containerId, processedResponse);
    			 resetToStart();
    			}
    		else if (event == 'patientQuery'){
    			processedResponse = createPatientQueryResponse(responseContents);
    			 replaceChildNodes(containerId, processedResponse);
    			
    			}
				
    		else if (event == 'patientSelection'){
    			processedResponse = createPatientSelectionResponse(responseContents)
    			replaceChildNodes('currentPatientFrame', processedResponse);
          roundLabels();
    			var patientId = getPatientMRN(responseContents);
				//action, form, event
    			invokeDocumentQuery(responseContents);
    		}
			else if (event == 'documentQuery'){
    			processedResponse = createDocumentQueryResponse(responseContents);
    			 replaceChildNodes(containerId, processedResponse);
    			
    			}
    		else if (event == 'documentSelection'){
    			processedResponse = createDocumentSelectionResponse(responseContents);
    			replaceChildNodes(containerId, processedResponse);
          roundLabels();
				
				progressCounter = 0;
				makeVisible(progressFrame);
				Progress = {
					getProgress : function () {
						log("getProgress:" + progressCounter);
						progressCounter++;
						invoke( 'progressDisplayFrame', 'DocumentMigration.action', processedResponse, 'documentMigration');
					}
				}
				callLater(1, Progress.getProgress);
    		}
			
    		else if (event == 'setRhio'){
    			processedResponse = createRhioSelectionResponse(responseContents);
    			replaceChildNodes(containerId, processedResponse);
          roundLabels();
    		}
			else if (event = 'documentMigration'){
				log("Handling event " + event);
				
				processedResponse = createDocumentSelectionResponse(responseContents);
				//log ("past createDocumentSelectionResponse" + processedResponse);
    			replaceChildNodes(containerId, processedResponse);
				var status = responseContents.status;
				var statusName = status.name;
				
				if (statusName == 'Complete'){
					//alert("Finished!");
					var progressFrame = getElement('progressDisplay');
					makeInvisible(progressFrame);
					log("About to open new window for " + status.url);
					log("Progress counter = "+ progressCounter);
					window.open(responseContents.url, "CCR");
					return;
				}
				else if (statusName == 'Failed'){
					alert("Failed!");
					var progressFrame = getElement('progressDisplay');
					makeInvisible(progressFrame);
					return;
				}
				// need to test for status here..
				else{
					Progress = {
					getProgress : function () {
						log("getProgress - subsequent call");
						progressCounter++;
						invoke( 'progressDisplayFrame', 'DocumentMigration.action', processedResponse, 'documentMigration');
						}
					}
					callLater(1, Progress.getProgress);
					}

			}
			else{
				log("Unknown event type:" + event);
				alert("Unknown event type:" + event);
				return;
				}
    		log("responseText is " + processedResponse + "\n");	
          
           
            
    });
    d.addErrback(tempError);
    return d;

}; 

function resetToStart(){
	log("resetToStart");
	replaceChildNodes('currentPatientFrame');
	replaceChildNodes('sharingConsentFrame');
	replaceChildNodes('queryResultFrame');
  showLoginRequiredItems('none');
}


/*
Called by onLoad handler. Sets up tables for first time.
*/  
function initialize(){
 var processedResponse = createLogoutResponse();
 //swapDOM('loginFrame', processedResponse);
 replaceChildNodes('loginFrame', processedResponse);
 $('username').focus();
 var progressFrame = getElement('progressDisplay');
  makeInvisible(progressFrame);
  var rhio = createRhioQuery('');
 
 replaceChildNodes('rhioDisplayFrame', rhio);
 
 var xds =  createXDSQuery();
 replaceChildNodes('xdsQueryFrame',xds);
}






/**
Returns an array of name/value pairs from an HTML form into what doSimpleXMLHttpRequest 
needs to digest.
**/
function getFormArray(form){
	log("getFormArray:" + form);
	var elements = form.elements;
    var parameters = new Array();
    log("Form elements:" + elements.length);
    for (var i = 0; i < elements.length; i++) {
      var value = elements[i].value
      var name = elements[i].name;
      var pair = new Array();
      pair[0] = name + ":";
      pair[1] = value;
      log("New pair:" + pair);
      parameters = parameters.concat(pair);
      log("New parameters = " + parameters);
    }
    return(parameters);
}






function loginResponse(resp) {
  try {
  	alert("Log in:" +resp);
  }
  catch(e){
  	dump(e);
  	}
}

function genericErrorHandler(e) {
  alert("An error occurred while performing last operation.\r\n\r\n"
   + "Error: " + e.message + "\r\n\r\n"
   + "Code: " + e.number + "\r\n\r\n"
   + "Try the operation again or contact support for help.");
  window.lastError = e;
}

function XHRErrorHandler(e) {
  if(e.number) {
    alert("An error occurred while performing last operation.\r\n\r\n"
     + "Error: " + e.message + "\r\n\r\n"
     + "Code: " + e.number + "\r\n\r\n"
     + "Try the operation again or contact support for help.");
  }
  else {
    log("XMLHttpRequest failed without error code. Aborted?");
  }
  window.lastError = e;
}

