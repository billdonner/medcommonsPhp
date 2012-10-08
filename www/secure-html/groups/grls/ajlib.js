
// support functions for forms handling, tabs and ajax processing 



// ajax stuff
var xmlHttp;
var interval; //seconds
var timerid = 0;
var onIE = "";
var lasttimesynch =0;
var savedqueryparams;

var xDate = new Date(); // external date/time of ajax server

function createXMLHttpRequest(){ 
    if (window.ActiveXObject) {  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}
    else if (window.XMLHttpRequest) {xmlHttp= new XMLHttpRequest(); }}


/**
 * Paging support
 */
var currentPage = 1;    

function page(p) {
  currentPage = p;
  //this.ajaxServer("queryajax.php?lt="+lasttimesynch+"&"+savedqueryparams+"&page="+currentPage);
  timeHandler();
}

function timeHandler()
{
  // to here when the timer goes off
  
  // Don't do ajax update if user is editing
  if(currentStatusField != null)
    return;

  this.ajaxServer("queryajax.php?lt="+lasttimesynch+"&"+savedqueryparams+"&page="+currentPage);
}
  
  function refreshTime() {return;}   
     
function ajaxServer(url){
    //when dispatching to an external ajax service kill the timer
      if (timerid !=0) { 
                clearTimeout(timerid);
                timerid=0;
                }

    erl = encodeURI(url);
 //   alert ("Sending "+url);
    createXMLHttpRequest(); 
             xmlHttp.onreadystatechange=ajaxCallback;    
        xmlHttp.open("GET",erl,true);

    xmlHttp.send(null); 
}

function getTagContents(str,tag)
{
var taglen = tag.length;
var start = str.indexOf('<'+tag+'>');
var end = str.indexOf('</'+tag+'>');
if (start < 0) return false;
if (end <0) return false;
var sl = str.slice (start + taglen + 2, end);
var slsub = sl.slice(1,100)+"..."+sl.slice(sl.length-100,sl.length);
//alert ("tag "+tag+" start "+start+" end "+end+" slice "+slsub);
return sl;
}

/**
 * Give parent opportunity to adjust size of this windoe when framed
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

function ajaxCallback(){
  if (xmlHttp.readyState == 4) {
    if (xmlHttp.status !=200) {
        document.getElementById('timeofday').innerHTML = showtime(new Date(),'red')+
            " ("+xmlHttp.status+")";
    } 
    else { 
//    alert ("in ajaxcallback "+lasttimesynch);
           

      if(document.getElementById('timeofday'))
        document.getElementById('timeofday').innerHTML = showtime(new Date(),'black');

         if (xmlHttp.responseText != '') {    
          var xcontent = getTagContents(xmlHttp.responseText,'content');
          if (xcontent !=''){
 //                     alert ("Received content "+content);
                      document.getElementById('records').innerHTML = xcontent;
                      }
    
          var xtimesynch = getTagContents(xmlHttp.responseText,'timesynch');
          if (xtimesynch !='')    {
 //           alert ("Received timesynch "+xtimesynch);
            xDate.setTime(xtimesynch*1000); // convert seconds to milliseconds and format
            if(document.getElementById('timesynch')) {
              document.getElementById('timesynch').innerHTML = showtime(xDate,'black');            
              document.getElementById('timeofday').innerHTML = showtime(new Date(),'black');
            }
            // save last time so we can send it back
            lasttimesynch = xtimesynch;
          }     
      } // response != ''
    } // status == OK
  } // readystate == 4

  // get the timer going again if needed
  if (timerid == 0) {
      timerid = setTimeout("timeHandler()",interval*1000);
      refreshTime();
  }
 // window.setTimeout(parentSize, 100);
}

function ajaxInit() 
{
    //window.external.info(interval);
    timerid = setTimeout("timeHandler()",interval*1000);
    refreshTime();
}

/**
 * Valid statuses presented in the status dropdown
 */
var statusValues=[
    'New',
    'Ordered',
    'Scheduled',
    'Preliminary',
    'Final',
    'Alert',
    'Inactive'];

var currentStatusField = null;
function editStatus(cc) {
  var stxt = $('sTxt'+cc);
  stxt.cc = cc;
  init_autocomplete(stxt, statusValues);
  var behavior = clone(autocompleteBehavior);
  behavior.show_all = true;
  behavior.fill_value = statusSelect;
  behavior.offsetX = -20;
  behavior.offsetY = 20;
  behavior.message = "Click on a Status to select it &nbsp;&nbsp;<img src='images/closebutton.gif' style='margin-top:4px; cursor: pointer;' onclick='statusSelect(-1)'/>";
  behavior.auto_show = false;
  stxt.autocompleteBehavior = behavior;
  currentStatusField = stxt;
  stxt.autocomplete();
}

function statusSelect(i) {
  if(i>=0) {
    var stxt = currentStatusField;
    var savedStatus = statusValues[i];
    var url ='wsUpdateStatus.php?cc='+stxt.cc+'&status='+savedStatus; 
    loadJSONDoc(url).addCallbacks(saveStatusSuccess, genericErrorHandler);
  }
  else {
    var acdiv = $('acdiv');
    acdiv.style.display='none';
    auto_complete_reset_behavior();
  }
}

function saveStatusSuccess(result) {
  var stxt = currentStatusField;
  currentStatusField = null;
  var acdiv = $('acdiv');
  acdiv.style.display='none';
  auto_complete_reset_behavior();
  if(result.status == "ok") {
    stxt.originalValue = result.savedStatus;
    stxt.value = result.savedStatus;
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

// these functions run when the page is loaded

function initAjaxPage(queryparams,qint,synch) {
  var rDate = new Date();
  lasttimesynch = synch*1000;
  rDate.setTime(lasttimesynch);
  if(document.getElementById('timeofday')) {
    document.getElementById('timeofday').innerHTML = showtime(new Date(),'black');
    document.getElementById('timesynch').innerHTML = showtime(rDate,'black');
  }

  //window.external.info(qint);
  interval = qint; 
  savedqueryparams = queryparams;
  ajaxInit();
}

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
