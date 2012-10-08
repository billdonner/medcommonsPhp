/**
 * log - useful to send log statements to external logger
 * (used with Badboy to log statements to log file)
 */
var enableLog = true;
function log(msg) {
  try {
    if(enableLog) {
      if(window.console && window.console.log) { // Firebug
        window.console.log(msg);
      }
      else
      if(window.external) {
        window.external.info(msg); // Badboy
      }
      else
      if(logDebug)
        logDebug(msg);
    }
  }
  catch(er) {
    enableLog=false;
  }

}

/**
 * Shorthand for "document.getElementById()"
 */
function el(id) {
  return document.getElementById(id);
}

/**
 * Shorthand to hide one or more elements, allowing it might be null
 */
function hide() {
  for(i=0; i< arguments.length; ++i) {
    var id = arguments[i];
    var e = document.getElementById(id);
    if(e) e.style.display='none';
  }
}

/**
 * Shorthand to show an element, allowing it might be null
 */
function show() {
  for(i=0; i< arguments.length; ++i) {
    var id = arguments[i];
    var e = document.getElementById(id);
    if(e) e.style.display='block';
  }
}

function empty(x) {
  return (x==null) || (x=='');
}

/**
 * Return the absolute horizontal position of the given object within the page
 */
function findPosX(obj) {
  var curleft = 0;
  if (obj.offsetParent) {
    while (obj.offsetParent) {
      curleft += obj.offsetLeft
      obj = obj.offsetParent;
    }
  }
  else if (obj.x)
    curleft += obj.x;
  return curleft;
}

/**
 * Return the absolute vertical position of the given object within the page
 */
function findPosY(obj) {
  var curtop = 0;
  if (obj.offsetParent) {
    while (obj.offsetParent) {
      curtop += obj.offsetTop
      obj = obj.offsetParent;
    }
  }
  else if (obj.y)
    curtop += obj.y;
  return curtop;
}

/**
 * Utility function to prevent default handling of an event.
 * Supports IE and DOM Level 2 clients (Moz/FF)
 */
function cancelEventBubble(evtToCancel) {
  // We've handled this event.  Don't let anybody else see it.
  if (evtToCancel.stopPropagation)
    evtToCancel.stopPropagation(); // DOM Level 2
  else
     evtToCancel.cancelBubble = true; // IE
     
  if (evtToCancel.preventDefault)
    evtToCancel.preventDefault(); // DOM Level 2
  else
     evtToCancel.returnValue = false; // IE
}

function funcname(f) {
  var fmatch = f.toString().match(/function (\w*)/);
  if(fmatch && (fmatch.length > 0)) {
    var s = fmatch[1];
    if ((s == null) || (s.length==0)) return "anonymous";
    return s;
  }
  else {
    return "anonymous";
  }
}

function stacktrace() {
 var s = "";
 for (var a = arguments.caller; a !=null; a = a.caller) {
   s += "->"+funcname(a.callee) + "\n";
   if (a.caller == a) {s+="*"; break;}
 }
 return s;
}

/**
 * Utility function to set a cookie
 */
function setCookie(name, value, expires, path, domain, secure)
{
  document.cookie= name + "=" + escape(value) +
      ((expires) ? "; expires=" + expires.toGMTString() : "") +
      ((path) ? "; path=" + path : "") +
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

/**
 * Converts 0-255 int value to corresponding hex char pair
 */
function int2Hex(val) {
  var rem = val % 16;
  var highVal = (val - rem)/16;
  return hexChar(highVal)+''+hexChar(rem);
}

/**
 * Converts a single 0-15 range number from decimal to corresponding hex char
 */
function hexChar(val) {
  if(val==10)
   return 'a';
  else
  if(val==11)
   return 'b';
  else
  if(val==12)
   return 'c';
  else
  if(val==13)
   return 'd';
  else
  if(val==14)
   return 'e';
  else
  if(val==15)
   return 'f';
  else
    return parseInt(val);
}

/**
 * Converts a single hex character to its decimal equivalent (0-15)
 */
function hex2Int(hex) {
  if(hex=='a')
   return 10;
  if(hex=='b')
   return 11;
  if(hex=='c')
   return 12;
  if(hex=='d')
   return 13;
  if(hex=='e')
   return 14;
  if(hex=='f')
   return 15;
  return parseInt(hex);
}

var colorKeyItem = null;

/**
 * A debug/design function.  Adds event handler which 
 * allows dynamic adjustment of color on a particular item.
 */
function hotColor(obj) {
  colorKeyItem = obj;
  window.document.body.onkeydown=handleColorKey;
}

function handleColorKey(event) {

  if(!colorKeyItem) 
    return;

  if (!event) event= window.event; // IE

  var keyCode =
    document.layers ? event.which :
    document.all ? event.keyCode :
    document.getElementById ? event.keyCode : 0;

  var adj = colorKeyItem;
  var bgColor = getStyle(adj,'backgroundColor'); // fn from mochikit
  if(bgColor==null || bgColor=='') {
    bgColor='#6988bb';
  }
  var r = hex2Int(bgColor.charAt(1))*16 + hex2Int(bgColor.charAt(2));
  var g = hex2Int(bgColor.charAt(3))*16 + hex2Int(bgColor.charAt(4));
  var b = hex2Int(bgColor.charAt(5))*16 + hex2Int(bgColor.charAt(6));
  log(bgColor + ' r='+r + ' g=' + g + ' b=' + b + ' key='+keyCode);
  if(keyCode == 82) {
    r+=2;
    if(r>255)
      r=0;
    var newColor='#' + int2Hex(r)+int2Hex(g)+int2Hex(b);
    log('new color is ' + newColor);
    adj.style.backgroundColor=newColor;
  }
  else
  if(keyCode == 71) {
    g+=2;
    if(g>255)
      g=0;
    var newColor='#' + int2Hex(r)+int2Hex(g)+int2Hex(b);
    log('new color is ' + newColor);
    adj.style.backgroundColor=newColor;
  }
  else
  if(keyCode == 66) {
    b+=2;
    if(b>255)
      b=0;
    var newColor='#' + int2Hex(r)+int2Hex(g)+int2Hex(b);
    log('new color is ' + newColor);
    adj.style.backgroundColor=newColor;
  }
}

/**
 * Removes white spaces from beginning and end of string value
 */
function trim(aValue) {
    return aValue.replace(/^\s+/g, "").replace(/\s+$/g, "");
}


/**
 * The currently active drag handler
 */
var currentDragHandler;

/**
 * Support for handling dragging
 */
function DragHandler(e,doc) {

    log("Starting drag handler");

    if(!doc) {
      doc = window.document;
    }

    currentDragHandler = this;

    // Register the event handlers that will respond to the mousemove events
    // and the mouseup event that follow this mousedown event.  
    if (doc.addEventListener) {  // DOM Level 2 Event Model
      // Register capturing event handlers
      doc.addEventListener("mousemove", DragMoveHandler, true);
      doc.addEventListener("mouseup", DragUpHandler, true);
    }
    else if (doc.attachEvent) {  // IE 5+ Event Model
      // In the IE Event model, we can't capture events, so these handlers
      // are triggered when only if the event bubbles up to them.
      // This assumes that there aren't any intervening elements that
      // handle the events and stop them from bubbling.
      doc.attachEvent("onmousemove", DragMoveHandler);
      doc.attachEvent("onmouseup", DragUpHandler);
    }

    if(e) {
      cancelEventBubble(e);
    }
}

function DragMoveHandler(e) {
  if (!e) e = window.event;  // IE event model

  currentDragHandler.handleMove(e);

  cancelEventBubble(e);
}

function DragUpHandler(e) {
  if (!e) 
    e = window.event;  // IE event model

  // Unregister the capturing event handlers.
  if (document.removeEventListener) {    // DOM Event Model
      document.removeEventListener("mouseup", DragUpHandler, true);
      document.removeEventListener("mousemove", DragMoveHandler, true);
  }
  else if (document.detachEvent) {       // IE 5+ Event Model
      document.detachEvent("onmouseup", DragUpHandler);
      document.detachEvent("onmousemove", DragMoveHandler);
  }

  currentDragHandler.handleUp(e);
  cancelEventBubble(e);
}

/**
 * Default handlers for move/up events
 */
DragHandler.prototype.handleUp=function(e) {};

DragHandler.prototype.handleMove=function(e) {};

/**
 * CSS Helpers
 */
function getRule(sheet,selector) {
  for(j=0; j<document.styleSheets.length;j++) {
    var ss = document.styleSheets[j];
    //log("found stylesheet " + ss.href); 
    if(ss.href.lastIndexOf(sheet)==(ss.href.length - sheet.length)) {
      var rules = ss.rules || ss.cssRules;
      for(i=rules.length-1; i>=0; i--) {
        //log("found rule " + rules[i].selectorText);
        if(rules[i].selectorText==selector) {
          return rules[i];
        }
      }
      log("WARN: Exhausted rules search");
      break;
    }
  }
}


/**
 * Creates an alert box containing the properties of any JavaScript object.
*/
function dumpProperties(description, obj){
  dump(description,obj);
}

function dump(description, obj){
	if (obj == null)
    obj = description;

  var display = "Dumping properties ";
  display+= description;
  display+= "\n";
  for (var name in obj) {
      display +=name;
      display += ":";
      display += obj[name];
      display += "\n";
  }
  display+="\n";
  display+="at\n\n";
  display+=stacktrace();

  alert(display);
}

function formatDateOfBirth(date){
	var formattedDate = (date.getMonth() + 1) + "/" + date.getDate() + "/" + (date.getFullYear());
	return(formattedDate);
}

function formatLocalDateTime(date){
	var formattedDate = formatDateOfBirth(date);
	formattedDate += " ";
  
  // note: toLocaleTimeString() includes seconds, but for compact
  // display we don't want that.
	//formattedDate += date.toLocaleTimeString();
  var twodigits = numberFormatter('00');
  formattedDate += twodigits(date.getHours()) + ':' + twodigits(date.getMinutes());
  if(date.getHours()<12)
    formattedDate += ' AM';
  else
    formattedDate += ' PM';

	return(formattedDate);
}

function prettyTrack(tn) {
  if(tn.length < 12)
    return "????????????"; // Invalid tracking number!

  return tn.substr(0,4) + ' ' + tn.substr(4,4) + ' ' + tn.substr(8,4); 
}

function prettyAcctId(accId) {
  if(accId.length < 16)
    return "????????????????"; // Invalid account id

  return accId.substr(0,4) + ' ' + accId.substr(4,4) + ' ' + accId.substr(8,4) + ' ' + accId.substr(12,4); 
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

var modalMatch=/(^a$)|(input)|(textarea)/i;

function unmodal() {
  while(modalDisabled.length>0) {
    modalDisabled.pop().disabled = false;
  }
}

var modalDisabled = new Array();
function modal(x) {
  nodeWalk(document.body, function(n) {
    if(n.tagName && n.tagName.match(modalMatch)) {
      n.disabled=true;
      modalDisabled.push(n);
    }
    return n.childNodes;
  });
  nodeWalk(x, function(n) {
   if(n.tagName && n.tagName.match(modalMatch))
     n.disabled=false;
    return n.childNodes;
  });
}

var isFireFox = (navigator.userAgent.indexOf("Firefox")!=-1)

/******************* Support for JSON AJAX Calls via MochiKit *********************/

/**
 * MochiKit supports JSON calls itself, but a) it uses GET which is prone
 * to failure if the parameters overflow max url length and b) it doesn't 
 * interop with PHP JSON quite properly.  So this function adapts it
 * to work with around these problems.
 */
function execJSONRequest(url, postdata, success) {
  var  req = getXMLHttpRequest();
  if(postdata) {
    log("Opening request " + url);
    req.open("POST", url, true);
    log("Opened post request");
    log("sending with post data: " + postdata);
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  }
  else {
    req.open("GET", url, true);
  }
  sendXMLHttpRequest(req,postdata).addCallbacks( function(req) {
    try {
      log("Received response text: " + req.responseText);
      // Note we need to augment the json to work with PHP JSON formatting
      var js = eval(req.responseText);
      success(js);
    }
    catch(e) {
      dumpProperties("Error while evaluating returned result",e);
      log("Error while evaluating returned result: " + req.responseText);
      return;
    }
  }, genericErrorHandler);
}

/**
 * Adjusts the height of the given text area so that it doesn't show a scroll bar, up to the 
 * given maximum
 */
function adjustTextAreaHeight(t,max) {
  var h = "0";
  if(max) {
    h = t.scrollHeight<max?t.scrollHeight:max;
  }
  else
    h = t.scrollHeight;

  t.style.height = h + "px";
}

/******************* Support for Cookie Events *********************/

var ce_events = new Object();

var ce_last_event_src = null;

var ce_timer = null;

var ce_src_id = new Date().getTime() + "" + Math.floor(Math.random() * 100000);

var ce_known_windows = new Array();

/**
 * Connect given event e to function o.f or just f (note, f can be 2nd arg)
 */
function ce_connect( e, o, f ) {
  connect( ce_events, e, o, f );
  ce_init();
}

/**
 * Connect to specific window
 */
function ce_connect_src( src, e, o, f ) {
  ce_init();
  connect( ce_known_windows[src], e, o, f );
}

function ce_init() {
  if(ce_timer == null) {
    ce_known_windows[ce_src_id] = new Object();
    ce_timer = setInterval(ce_poll,1000);
  }
}

var ce_old_ce = getCookie('ce');
if(!ce_old_ce) {
  ce_old_ce = '';
}

function ce_poll() {
  var ce = getCookie('ce');
  if((ce != null) && (ce != ce_old_ce)) {
    ce_old_ce = ce;

    // Parse the instruction
    var x = ce_old_ce.split(/:/);
    var sig = x[0].split(/,/);
    var t = sig[0];
    ce_last_event_src=null;
    if((sig.length>1) && (sig[1]!=undefined)) {
      ce_last_event_src = sig[1];
      if(ce_known_windows[ce_last_event_src] == undefined) {
        ce_known_windows[ce_last_event_src] = new Object();
      }
    }

    log('Received event ' + x[1] + ' at time ' + t + " from src: " + ce_last_event_src);
    
    // Check for arguments
    var args = x[1] ? map(unescape,x[1].split(',')) : new Array();
    args.unshift(ce_events);
    signal.apply(this,args);

    // If src provided, signal listeners subscribed to that src
    if(ce_last_event_src) {
      args.shift();
      args.unshift(ce_known_windows[ce_last_event_src]);
      signal.apply(this,args);
    }
  }
}

function ce_signal( e ) {
  // IE built in arguments array does not support join
  var x = new Array();
  for(var i=0; i<arguments.length; ++i) {
    x.push(urlEncode(arguments[i]));
  }
  var new_ce = (new Date()).getTime() + ',' + ce_src_id + ':' + x.join(',');
  
  // Make sure we don't receive our own signal
  ce_old_ce = new_ce;
 
  setCookie('ce', new_ce, null, '/');

  forEach(ce_servers, function(url) {
    var head = document.getElementsByTagName("head").item(0);
    var script = document.createElement("script");
    script.setAttribute("type", "text/javascript");
    var ceUrl = url + '?e=' + urlEncode(new_ce);
    log("sending signal to server: " + ceUrl);
    script.setAttribute("src", ceUrl);
    head.appendChild(script);
  });
}

var ce_servers = new Array();
function ce_add_server(url) {
  ce_servers.push(url);
}
