/**
 * Javascript support for Account Pages (Primarily Worklist / Patient List / RLS)  
 */
 
/**
 * RLS Parameters
 */
var RLSParams = {
  pid: null,
  page: 1,
  showHidden: false
}

function page(p) {
  RLSParams.page = p;
  refreshRLS();
}

function toggleShowHiddenPatients() {
  RLSParams.showHidden = !RLSParams.showHidden;
  refreshRLS();
}

function updateSearch() {
  if(window.searchTimeout) {
    window.clearTimeout(window.searchTimeout);
  }
  // Have to reset this because if they have paged ahead and then refine their search
  // they can end up stranded on a page that doesn't exist
  RLSParams.page = 1;
  window.searchTimeout = window.setTimeout(refreshRLS,600);
}

function clearRLSSearch() {
  clearSearch("registryTable");
  refreshRLS();
}

function clearSearch(tableId) {
  forEach($$("#"+tableId + " input"), function(i) {
    i.value='';
  });
  forEach($$("#" + tableId + " select"), function(i) {
    i.selectedIndex  = 0;
  });
}

function refreshRLS() {
  refreshTable('rls',"rls.php?"+queryString($$('#registryTable tr.searchRow')[0])+'&lt=2&'+queryString(RLSParams));
}

function refreshTable(rowsId, url) {
  doSimpleXMLHttpRequest(url).addCallbacks(function(r) {
    // Note: cannot use innerHTML directly because IE will not set innerHTML on tbody
    // Therefore inject whole table
    // log('received:  '  +r.responseText);
    $(rowsId + 'Parser').innerHTML = r.responseText;
    var trs = [];
    forEach($(rowsId + 'Rows').getElementsByTagName('tr'), function(tr) { trs.push(tr) });
    forEach(trs, removeElement);
    forEach($$('#'+rowsId+'Parser tr'), function(tr) {
      // log('adding row '+tr.innerHTML);
      removeElement(tr);
      appendChildNodes($(rowsId + 'Rows'),tr);
    });
    broadCastHeight();
  },genericErrorHandler);
}

function unhidePatient(patientId,callback) {
  execJSONRequest('unhidePatient.php',authQueryString( { patientId:patientId }), function(result) {
      if(result.status=="ok") {
        refreshRLS();
        if(callback)
          callback();
      }
      else {
        alert("There was a problem restoring this Patient:\r\n\r\n" + result.error);
      }
  });
}


function hidePatient(patientId) {
  execJSONRequest('hidePatient.php',authQueryString( { patientId:patientId }), function(result) {
      if(result.status=="ok") {
        removeElement($('row_'+patientId));
      }
      else {
        alert("There was a problem hiding this Patient:\r\n\r\n" + result.error);
      }
  });
}

/**
 * Mode - patient vs group
 */
function switchToPatientMode() {
  setAccessMode('patient');
  document.location='home.php';
}
 

function setAccessMode(m) {
  if(m=='patient') {
    setCookie('mode','p',null,'/');
  }
  else
    setCookie('mode','g',null,'/');
}

function isPatientModeSet() {
  return getCookie('mode') == 'p';
}

/**
 * Give parent opportunity to adjust size of this window when framed
 */
function parentSize() {
  try {
    if(window.parent && window.parent.sizecontent) {
      window.parent.sizecontent();
    }
  }
  catch(e) {
  }
}

/**
 * Valid statuses presented in the status dropdown
 */
var statusValues=[];

var currentStatusField = null;
function editStatus(cc) {
  var stxt = $('sTxt'+cc);
  stxt.cc = cc;
  init_autocomplete(stxt, statusValues);
  var behavior = clone(autocompleteBehavior);
  behavior.show_all = true;
  behavior.fill_value = statusSelect;
  behavior.offsetX = -34;
  behavior.offsetY = 20;
  behavior.message = "Click on a Status to select it &nbsp;&nbsp;<img src='images/closebutton.gif' style='margin-top:4px; cursor: pointer;' onclick='statusSelect(-1)'/>";
  behavior.auto_show = false;
  stxt.autocompleteBehavior = behavior;
  currentStatusField = stxt;
  stxt.autocomplete();

  // Ensure that statuses are not clipped
  var h = (elementDimensions($('acdiv')).h+elementDimensions($('records')).h-40);
  $('records').style.height = h + 'px';
  try {
    if(window.parent.adjustFrameHeight) {
      window.parent.adjustFrameHeight(window.name,h);
    }
  }
  catch(e) {
  }
  broadCastHeight();
}

function statusSelect(i) {
  if(i>=0) {
    var stxt = currentStatusField;
    var savedStatus = statusValues[i];
    var url ='ws/wsUpdateStatus.php?cc='+stxt.cc+'&status='+savedStatus+'&pid='+practiceId; 
    loadJSONDoc(url).addCallbacks(saveStatusSuccess, genericErrorHandler);
  }
  else {
    var acdiv = $('acdiv');
    acdiv.style.display='none';
    $('records').style.height = 'auto';
    auto_complete_reset_behavior();
  }
}

function saveStatusSuccess(result) {
  var stxt = currentStatusField;
  currentStatusField = null;
  var acdiv = $('acdiv');
  acdiv.style.display='none';
  $('records').style.height = 'auto';
  auto_complete_reset_behavior();
  if(result.status == "ok") {
    stxt.originalValue = result.savedStatus;
    stxt.value = result.savedStatus;
    refreshRLS();
  }
  else {
    alert("Saving status failed: \r\n\r\n"+result.message);
  }
}

function over(tn) {
  if(statuses[tn] && (statuses[tn]!='')) {
    $('r'+tn).style.color='orange';
    $('sImg'+tn).style.visibility='visible';
    $('sImg'+tn).style.display='inline';
  }
}

function out(tn) {
  $('r'+tn).style.color='black';
  $('sImg'+tn).style.visibility='hidden';
  //$('sImg'+tn).style.display='none';
}

var statuses = new Array();;

// support highlighting and capture data when leaving fields
function highlight(field) {
  if(field.getAttribute('readonly')) {
    return;
  }
  if(field.select != null)
    field.select();
  field.onblur=unhighlight;
//  field.oldBackgroundColor = computedStyle(field,'backgroundColor','background-color');
  field.style.backgroundColor='#e6e6e6';
}

function unhighlight() {
  this.style.backgroundColor='#f3f3f3';
  if(this.oldBackgroundColor) {
    this.style.backgroundColor = this.oldBackgroundColor;
  }

}




function showtime(Digital,color)
{

var hours=Digital.getHours();
var minutes=Digital.getMinutes();
var seconds=Digital.getSeconds();
var dn="pm";
if (hours<12)
dn="am";
if (hours>12)
hours=hours-12;
if (hours==0)
hours=12;
if (minutes<=9)
minutes="0"+minutes;
if (seconds<=9)
seconds="0"+seconds;
var ctime=hours+":"+minutes+":"+seconds+" "+dn;
 return "<b style='color:"+color+"'>"+ctime+"</b>";
}

/**
 * Support for modifying / updating interests
 */
function addInterest() {
  // Add to bottom
  var tbl = $('interestsTable');
  var bd  = tbl.getElementsByTagName('tbody')[0];
  appendChildNodes(bd,TR(null, 
    TD(null,$('newInterest').value),
    TD(null,
      A({href:'javascript:;',onclick:'interestUp(this);'},IMG({src:'../images/black_arrow_up.gif'})),
      A({href:'javascript:;',onclick:'interestDown(this);'},IMG({src:'../images/black_arrow_down.gif'})),
      A({href:'javascript:;',onclick:'removeInterest(this);'},IMG({src:'../images/cross.jpg'}))
    )));
  saveInterests();
}


function interestUp(a) {
  var tbl = $('interestsTable');
  var bd  = tbl.getElementsByTagName('tbody')[0];
  var td = a.parentNode;
  var tr = td.parentNode;
  var prev = null;
  for(var i=0; i<bd.childNodes.length;++i) {
    var n = bd.childNodes[i];
    if(n == tr) {
      if(prev){
        removeElement(tr);
        bd.insertBefore(tr, prev);
        break;
      }
    }
    prev = n;
  }
  saveInterests();
}

function interestDown(a) {
  var tbl = $('interestsTable');
  var bd  = tbl.getElementsByTagName('tbody')[0];
  var td = a.parentNode;
  var tr = td.parentNode;
  var prev = null;
  for(var i=0; i<bd.childNodes.length;++i) {
    var n = bd.childNodes[i];
    if(n == tr) {
      if(i<bd.childNodes.length-2) {
        var s = bd.childNodes[i+2];
        removeElement(tr);
        bd.insertBefore(tr, s);
        break;
      }
      else {
        removeElement(tr);
        appendChildNodes(bd,tr);
      }
    }
  }
  saveInterests();
}

function removeInterest(a) {
  var tbl = $('interestsTable');
  var bd  = tbl.getElementsByTagName('tbody')[0];
  var td = a.parentNode;
  var tr = td.parentNode;
  var prev = null;
  for(var i=0; i<bd.childNodes.length;++i) {
    var n = bd.childNodes[i];
    if(n == tr) {
        removeElement(tr);
    }
  }
  saveInterests();
}

function saveInterests() {
  var tbl = $('interestsTable');
  var bd  = tbl.getElementsByTagName('tbody')[0];
  var interests = "";
  forEach(bd.childNodes, function(n) {
    if(n.getElementsByTagName) {
      if(interests!="")
        interests += "|";
      interests += scrapeText(n.getElementsByTagName("td")[0]);
    }
  });
  loadJSONDoc('../saveInterests.php?interests='+escape(interests)).addCallbacks(function(res) {
    log("interests saved");
  },genericErrorHandler);
}

/**
 * Support for dynamically populating hipaa consent box with patient id
 */
function checkCurrentPatient(mcId, given, family, age, gender, email) {
  var gw = getCookie('mcgw');
  if(mcId) {
    $('hippaPatientId').value=prettyAccId(mcId);
    document.coverForm.coverNotifyEmail.value = email;
    log("setting new patient id " + mcId + " and email " + email);
    // replaceChildNodes($('ccrCheckSpan'),createDOM('script',{'src': gw+'/CurrentCCRWidget.action?patientIdUpdate'}));
  }
  else
    clearHipaaPatient();
}

function clearHipaaPatient() {
  $('hippaPatientId').value='';
  document.coverForm.coverNotifyEmail.value = '';
}

function updatePatientId(mcid) {
  log("Patient id updated to " + mcid);
  $('hippaPatientId').value=prettyAccId(mcid);
}

var isFireFox = (navigator.userAgent.indexOf("Firefox")!=-1)

function openCcrWindow(url,unhide,patientId) {

  if(unhide) {
    log("Unhiding patient " + patientId);
    unhidePatient(patientId);
  }

  // Attempt to deal with FF's moronic tab handling - if there is a CCR tab, KILL IT
  // Unfortunately, this nutso javascript drives IE beserk
  // so, hack upon hack, only do this for FireFox itself.
  if(window.ccr) {
    window.ccr.close();
  }
  if(isFireFox) {
    var ccr = window.open('','ccr');
    ccr.close();
    window.setTimeout(function() { window.ccr = window.open(url,'ccr'); }, 500);
  }
  else  // note: setTimeout will invoke wrath of IE popup blocker
    window.open(url,'ccr');

  return false;
}

function authQueryString(x) {
  var qs = queryString(x);
  qs += '&enc='+urlEncode(hex_sha1(getCookie('mc')));
  return qs;
}

function saveConsents() {
  var qs = authQueryString(document.consentsForm);
  execJSONRequest(document.consentsForm.action,qs, function(result) {
    if(result.status=="ok") {
      alert("Consents saved successfully!");
    }
    else {
      alert("A problem occurred while saving your consents:\r\n\r\n " + result.message);
      window.location.reload();
    }
  });
}

/**
 * Fill all practice entries with value for group
 */
function fillConsentGroup(groupAcctId, rights) {
  forEach(document.consentsForm.getElementsByTagName('tr'), function(tr) {
    if(tr.id && tr.id.match( new RegExp(groupAcctId+".[0-9]{16}") )) {
      // select the value
      forEach(tr.getElementsByTagName('option'),function(o) {
        o.selected = ( o.value == rights );
      });
    }
  });
}
 
 
