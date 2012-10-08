/**************************************************************/

// NOTE: used to set content for tracking box here.  This is 
// done differently now, see secureredir.php

/**************************************************************/

// ajax stuff
var xmlHttp;
var interval = 30; //seconds
var timerid = 0;
var onIE = "";
var lasttimesynch =0;

var xDate = new Date(); // external date/time of ajax server
function createXMLHttpRequest(){ 
    if (window.ActiveXObject) {  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}
    else if (window.XMLHttpRequest) {xmlHttp= new XMLHttpRequest(); }}
    

function refreshTime()
{
//nothing much to do here, as the time is updated only when the server responds right now
// but we could display our time as well as the server time
}

function timeHandler()
{
// to here when the timer goes off
//alert ("in timehandler "+lasttimesynch);
log('timeHandler');
this.ajaxServer("wjtimerpoll.php?lt="+lasttimesynch+"&interval="+interval);
}
        
function ajaxServer(url){
    //when dispatching to an external ajax service kill the timer
      if (timerid !=0) { 
        //        clearTimeout(timerid);
                timerid=0;
                }

    erl = encodeURI(url);
    //alert ("Sending "+url);
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
//var slsub = sl.slice(1,100)+"..."+sl.slice(sl.length-100,sl.length);
//alert ("tag "+tag+" start "+start+" end "+end+" slice "+slsub);
return sl;
}

function ajaxCallback(){
        if (xmlHttp.readyState == 4) {

        if (xmlHttp.status !=200) {
      if(xmlHttp.status >= 400) {
        window.setTimeout(statusred,200);
      }
        } 
    else {            
      window.setTimeout(statusgreen,200);
      if (xmlHttp.responseText != '') {    
        
            var xredirect = getTagContents (xmlHttp.responseText,'redirect');
            log('xredirect='+xredirect);
            if (xredirect !='') { // redirect instruction from ajax server to change entire universe 
              document.location.href =xredirect; // that should do it
              return;
            }

            var xcontent = getTagContents(xmlHttp.responseText,'content');
            if (xcontent !='') { 
                document.getElementById('content').innerHTML = xcontent;
            }
                
            var xstatus = getTagContents(xmlHttp.responseText,'status');
            if(xstatus !='') {
              if($('status')) {
                $('status').innerHTML = xstatus;
              }
            }
            
            var xemergencyccr = getTagContents(xmlHttp.responseText,'emergencyccr');
            if(xemergencyccr !='') {
              if($('emergencyccr'))
                $('emergencyccr').innerHTML = xemergencyccr;
            }

            var xpatientcard = getTagContents(xmlHttp.responseText,'patientcard');
            var xdomain = getTagContents(xmlHttp.responseText,'domain');
            if(xdomain && (xdomain != '') && (xdomain != document.domain)) {
              window.document.domain = xdomain;
            }

            var accountInfoJSON=getTagContents(xmlHttp.responseText,'account');
            if(accountInfoJSON && (accountInfoJSON!='')) {
              window.accountInfo = evalJSON(accountInfoJSON);
            }
            else
              window.accountInfo = null;

            updateAccountInfo();

            var xtimesynch = getTagContents(xmlHttp.responseText,'timesynch');
            if (xtimesynch !='')    {
            //alert ("Received timesynch "+xtimesynch);
            xDate.setTime(xtimesynch*1000); // convert seconds to milliseconds and format
            document.getElementById('timesynch').innerHTML = showtime(xDate,'lightgrey');
            // save last time so we can send it back
            lasttimesynch = xtimesynch;
            }
        }
    }
    }
        // get the timer going again if needed
        if (timerid == 0) {
    //        timerid = setTimeout("timeHandler()",interval*1000);
            refreshTime();}

    var so = $('statusinfo');
    if(so && !so.rounded) {
      $('statusouter').style.display = 'block';
      $('accountOuter').style.display = 'block';
      roundElement('statusouter',  { corners: 'bottom'});
      roundElement('accountOuter',  { corners: 'top'});
      loggedIn = true;
      so.rounded = true;
    }
}

function updateAccountInfo() {
  var tbt = $('trackboxTop');
  if(window.accountInfo) {
    var ai = window.accountInfo;
    $('tbName').innerHTML = ai.firstName + ' ' + ai.lastName;
    $('tbDateTime').innerHTML =  toISOTime(new Date());
    $('tbAccId').innerHTML = '#'+prettyAccId(ai.accountId);
    show('trackboxBottom','leftAccountLink');
    hide('leftRegisterLink','menuBarLogInLink');
    $('menuBarAddDocLink').style.display = 'inline';
    //tbt.style.backgroundColor=tbt.originalBackgroundColor;
    removeElementClass(tbt,"notLoggedInTrackboxTop");
    if(!tbt.rounded) {
      roundElement('trackboxTop',  { corners: 'tl'});
      roundElement('trackboxBottom',  { corners: 'br'});
      tbt.rounded = true;
    }
    tbt.childNodes[0].style.display='block';
    loggedIn = true; 
  }
  else {
    hide('trackboxBottom','leftAccountLink','menuBarAddDocLink');
    show('leftRegisterLink');
    $('menuBarLogInLink').style.display='inline';
    
    //tbt.style.backgroundColor='transparent';
    setElementClass(tbt,"notLoggedInTrackboxTop");
    if(tbt.rounded) {
      tbt.childNodes[0].style.display='none';
    }
    loggedIn = false;
  }
}

function ajaxInit() 
{
//    timerid = setTimeout("timeHandler()",interval*1000);
    refreshTime();
}

// parse URL

function PageQuery(q) {
    if(q.length > 1) this.q = q.substring(1, q.length);
    else this.q = null;
    this.keyValuePairs = new Array();
    if(q) {
        for(var i=0; i < this.q.split("&").length; i++) {
            this.keyValuePairs[i] = this.q.split("&")[i];
        }
    }
    this.getKeyValuePairs = function() { return this.keyValuePairs; }
    this.getValue = function(s) {
        for(var j=0; j < this.keyValuePairs.length; j++) {
            if(this.keyValuePairs[j].split("=")[0] == s)
                return this.keyValuePairs[j].split("=")[1];
        }
        return false;
    }
    this.getParameters = function() {
        var a = new Array(this.getLength());
        for(var j=0; j < this.keyValuePairs.length; j++) {
            a[j] = this.keyValuePairs[j].split("=")[0];
        }
        return a;
    }
    this.getLength = function() { return this.keyValuePairs.length; }    
}
function queryString(key){
var page = new PageQuery(window.location.search); 
return unescape(page.getValue(key)); 
}

       

function initMainPage() {
  // Load logo for non-IE browsers
  if(! document.all) {
    $('logo').src='images/mc_logo.png';
  }
  $('trackboxTop').originalBgColor = computedStyle($('trackboxTop'),'backgroundColor','background-color');

  initTheme();

  var startPage = queryString('p');
  if (startPage == 'false') 
    startPage = 'commons';

  ajaxInit();

  // have we been asked to show a specific frame?
  var f = queryString('f');
  if((f!='false') && (f!='')){
    showContentFrame(f+document.location.search);
  }
  else {
    this.ajaxServer("idinfo.php?a="+startPage);
  }
  setInterval(checkLogin,1000);
  window.onresize = sizecontent;
}

var loggedIn = false;
function checkLogin() {
  //log("loggedIn = " + loggedIn + " mc=["+getCookie("mc")+"]");
  sizecontent();
  if(!loggedIn && getCookie('mc')) {
    ajaxServer("idinfo.php");
  }
  else
  if(loggedIn && !getCookie('mc')) {
    ajaxServer("idinfo.php");
    loggedIn = false;
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

/********************* MochiKit Based AJAX *******************/
function replaceContent(url, el) {
  if(!el) {
    el = 'content';
  }
  // prevent caching
  if(url.indexOf('?')<0)
    url += '?';
  url += 'v='+new Date().getTime();
  var req = getXMLHttpRequest();
  req.open('GET', url, true);
  var res = sendXMLHttpRequest(req);
  res.addCallbacks(partial(replaceContentSuccess, $(el)),partial(error,$(el)));
}

function replaceContentSuccess(el,req) {
  // HACK:  if it starts with <redirect>
  if(req.responseText.match(/^<\?xml version='1.0' encoding='UTF-8'\?><ajreturnblocks><redirect>/)) {
    // do a redirect
    document.location.href=getTagContents(req.responseText,'redirect');
    return;
  }

  el.innerHTML=req.responseText;
  window.setTimeout(statusgreen,200);
  initTheme(); // hack, ensure theme gets initialized if its showing
}

function statusgreen() {
  if($('accountLink')) {
    $('accountLink').style.backgroundImage="url('images/greenled.gif')";
  }
}

function statusred() {
  if($('accountLink')) {
    $('accountLink').style.backgroundImage="url('images/redled.gif')";
  }
}

function error(el,e) {
  if($('accountLink')) {
    $('accountLink').style.backgroundImage="url('images/redled.gif')";
  }
  if(el) {
    el.innerHTML='<div id="preamble"><h3>A problem occurred contacting the web site.</h3><p>'+e.description+'</p></div>';
  }
  log('Error occurred retrieving content: '+e.description);
}

var sizeTime = 0;
function sizecontent() {
  var newSizeTime = (new Date()).getTime();
  try {

    if(!window.frames || (window.frames.length == 0)) {
      log("no contentframe.  returning.");
      return;
    }

    var contentwin = frames[0];
    var contentBody = contentwin.document.body;
    if(!contentBody) {
      log("no contentbody.  returning.");
      return;
    }

    var contentHeight = contentBody.scrollHeight+30;
    var contentWidth = contentBody.scrollWidth;
    var frameSize = elementDimensions('contentframe');

    // hack: prevent possibility of infinite loops!
    if(contentwin.nosize) {
      //contentwin.nosize = false;
      return;
    }

    // Get left padding of content frame
    var leftPadding = parseInt(computedStyle($('contentframe'),'paddingLeft','padding-left'));
    var widthDelta = contentWidth - (frameSize.w-leftPadding);
    var heightDelta = contentHeight - frameSize.h;
    var availableWidth = document.body.clientWidth-20-leftPadding;

    if((heightDelta>0) || (heightDelta<-60) || (widthDelta > 0) || (widthDelta < -60) ) {
      log(sizeTime+' nosize='+contentwin.nosize + ' h='+contentHeight+' w='+contentWidth+' currh='+frameSize.h+' currw='+frameSize.w); 

      // if content width is smaller than the width we have, might as well make as large as possible.
      if(contentWidth < availableWidth) {
        contentWidth = availableWidth;
      }

      setElementDimensions('contentframe', {h: contentHeight, w: contentWidth});

      if((newSizeTime - sizeTime) < 60) {
        //contentwin.nosize = true;
        log("NOSIZE");
      }
      else
        contentwin.nosize = false;

      sizeTime = newSizeTime;
    }

    //contentwin.document.body.onresize=sizecontent;
  }
  catch(e) {
    // for now just log it
    log(document.location.href + ' : ' +'error sizing content window: ' + e + ' - ' + e.description);
  }
}

function showContentFrame(url) {
  var width = document.body.offsetWidth - 180;
  var src = url;
  if(!src) {
    src = 'contentframe.php';
  }
  var succeeded = false;
  if(window.contentwindow) {
    try {
      window.contentwindow.document.location.href=url;
      succeeded = true;
    }
    catch(e) {
    }
  }

  if(!succeeded)
    $('content').innerHTML="<div style='width: 100%; padding: 0px;'><iframe name='contentwindow' id='contentframe' onload='sizecontent();' allowtransparency='true' background-color='transparent' name='ccrlog' width='"+(width-10)+"' height='600' frameborder='0' scrolling='no' src='"+src+"'/></div>";
}

/**
 * Utility function to set a cookie
 */
function setCookie(name, value, expires, path, domain, secure)
{
  document.cookie= name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires.toGMTString() : "") +
      ((path) ? "; path=" + path : "/") +
      ((domain) ? "; domain=" + domain : "") +
      ((secure) ? "; secure" : "");
}

/**
 * gets the value of the specified cookie.
 */
function getCookie(name)
{
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1) {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}

function initTheme() {
  // select theme according to user's setting
  var theme = getCookie('theme');
  if(theme || (theme == '')) {
    var themes = $('theme');
    if(themes) {
      for(i=0; i<themes.length; ++i) {
        if(themes[i].value == theme) {
          themes.selectedIndex = i;
        }
      }
    }
  }
  else {
    setCookie('theme', 'lightgray', new Date(new Date().getTime() + (365*24*60*60*1000)), '/');
  }
}

/**
 * Resets the users theme to a new selection
 * note - causes page to reload.
 */
function retheme() {
   var index = $('theme').selectedIndex;
   setCookie('theme',$('theme').options[index].value, new Date(new Date().getTime() + (365*24*60*60*1000)),'/','');
   document.location.reload();
}

function checkId() {
  var tn = document.trackingForm.trackingbox.value.replace(/[- \t]/g,'');
  if(tn.length > 13) { // treat as account id
    document.trackingForm.action = 'loginredir.php';
  }
  else
    document.trackingForm.target = '';
}

