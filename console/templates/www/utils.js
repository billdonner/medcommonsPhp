/**
 * log - useful to send log statements to external logger
 * (used with Badboy to log statements to log file)
 */
var enableLog = true;
window.log = function(msg) {
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
  var bgColor = computedStyle(adj,'backgroundColor','background-color'); // fn from mochikit
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
	if (obj == null)
		alert("Dumping properties " + description + ": null object");
	else{
	    var display = "Dumping properties ";
	    display+= description;
	    display+= "\n";
	    for (var name in obj) {
	        display +=name;
	        display += ":";
	        display += obj[name];
	        display += "\n";
	        }
	    alert(display);
    }
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

function genericErrorHandler(e) {
  alert("An error occurred while performing last operation.\r\n\r\n"
   + "Error: " + e.message + "\r\n\r\n"
   + "Code: " + e.number + "\r\n\r\n"
   + "Try the operation again or contact support for help.");
  window.lastError = e;
}

function prettyAccId(accid) {
  return accid.replace(/([0-9]{4})([0-9]{4})([0-9]{4})([0-9]{4})/,'$1 $2 $3 $4');
}

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
      var js = (eval("var x="+req.responseText+"; x;"));
    }
    catch(e) {
      dumpProperties("Error while evaluating returned result",e);
      log("Error while evaluating returned result: " + req.responseText);
      return;
    }
    success(js);
  }, genericErrorHandler);
}


/******************* Support for Cookie Events *********************/

var ce_events = new Object();
ce_events.id = Math.floor(Math.random()*100000);

var ce_last_event_src = null;

var ce_timer = null;

var ce_src_id = new Date().getTime() + "" + Math.floor(Math.random() * 100000);

var ce_known_windows = new Array();

var MAX_CE_AGE = 4000;

var ce_begin_time = (new Date()).getTime();

var ce_is_loaded = false;

/**
 * List of all ce_events received
 */
var ce_history = new Array();

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

function ce_init_timer() {
  if(ce_timer == null) {
    ce_known_windows[ce_src_id] = new Object();
    ce_timer = setInterval(ce_poll,1000);
  }
}

function ce_init() {
  if(ce_is_loaded) {
    //log("ce_is_loaded true: initializing time");
    ce_init_timer();
  }
  else {
    //log("ce_is_loaded false: waiting for load");
    addLoadEvent(ce_init_timer);
  }
}

function ce_loaded() {
  //log("setting ce_loaded to true");
  ce_is_loaded = true;
}

if(window.addLoadEvent) {
  //log("adding ce_loaded event");
  addLoadEvent(ce_loaded);
}

var ce_old_ce = getCookie('ce');
if(!ce_old_ce) {
  ce_old_ce = '';
}

/**
 * Check if the given cookie event is one we already processed or if it's
 * too old to be interesting. 
 */
function is_old_ce(ce) {
  var t = parseInt(ce,10);
  var now = (new Date()).getTime();
  if(t < (now - MAX_CE_AGE)) {
    return true;
  }

  if(t < ce_begin_time) {
    return true;
  }

  // Is it in our history array?
  if(findValue(ce_history,ce)!=-1) {
    return true;
  }

  return false;
}

function ce_add_history(ce) {
  ce_history.push(ce);
  var maxAge = (new Date()).getTime() - MAX_CE_AGE;
  var i =0;
  while((i<ce_history.length) && (parseInt(ce_history[i],10)<maxAge)) {
    ++i;
  }
  if(i>0) {
    ce_history.splice(0,i);
  }
}

function ce_poll() {
  var current_ce = getCookie('ce');
  if((current_ce == null) || (current_ce == ce_old_ce)) { // no change since last poll
    return;
  }

  ce_old_ce = current_ce;

  // Something changed - check the cookie events in the cookie
  //log("examining new current_ce " + current_ce);
  var ces = current_ce != null ? current_ce.split(/;/) : new Array();
  forEach(ces, function(ce) {
    if(is_old_ce(ce)) {
      return;
    }

    // TODO: trim the ce_history array to prevent mem leaks
    ce_add_history(ce);

    // Parse the instruction
    var x = ce.split(/:/);
    var sig = x[0].split(/,/);
    var t = sig[0];
    ce_last_event_src=null;
    if((sig.length>1) && (sig[1]!=undefined)) {
      ce_last_event_src = sig[1];
      if(ce_known_windows[ce_last_event_src] == undefined) {
        ce_known_windows[ce_last_event_src] = new Object();
      }
    }

    if(ce_src_id != ce_last_event_src) {
      log('['+window.name+'] Received event ' + x[1] + ' at time ' + t + " from src: " + ce_last_event_src);
    }
    
    // Check for arguments
    var args = x[1] ? map(unescape,x[1].split(',')) : new Array();
    args.unshift(ce_events);
    //log("sending signal(" + args.join(",")+")");
    signal.apply(this,args);

    // If src provided, signal listeners subscribed to that src
    if(ce_last_event_src) {
      args.shift();
      args.unshift(ce_known_windows[ce_last_event_src]);
      signal.apply(this,args);
    }
  });
}


/**
 * Sends a cookie event to all listeners by setting the ce cookie.
 *
 * The cookie is formatted in the following way which facilitates
 * multiplexing multiple events into the same cookie:
 *
 *   <time1>,<src1>:<event name>,<arg1>,<arg2>,...;<time2>,<src1>:<event name>,<arg1>,<arg2>,...
 */
function ce_signal( e ) {

  // Before signalling, check if there is any contention on the cookie channel
  ce_poll();

  // IE built in arguments array does not support join
  var x = new Array();
  for(var i=0; i<arguments.length; ++i) {
    x.push(urlEncode(arguments[i]));
  }

  var current_ce = getCookie('ce');
  var ces = current_ce != null ? current_ce.split(/;/) : new Array();
  var now = (new Date()).getTime();
  var new_ces = new Array();
  forEach(ces, function(ce) {
    var t = parseInt(ce);
    if(t > (now - MAX_CE_AGE)) {
      new_ces.push(ce);
    }
  });
  new_ces.push(now + ',' + ce_src_id + ':' + x.join(','));
  var new_ce = new_ces.join(';');
  
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
  if(findValue(ce_servers,url)<0) 
    ce_servers.push(url);
}


/************ Managing IFrame Heights ****************/

var broadCasting = false;

/**
 * Add a frame height monitor to monitor current window, presumed to be an iframe
 */
function addHeightMonitor() {
  //connect( document.body, 'onresize', broadCastHeight);
  log("Adding height monitor in window " + window.name);
  document.body.onresize = broadCastHeight;
  broadCastHeight();
  broadCasting = true;
}

function _calculateHeight() {
  if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
    h = document.body.scrollHeight;
  } 
  else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari
    h = document.body.offsetHeight;
  }

  if(!document.all && (h<document.body.parentNode.offsetHeight))
    h = document.body.parentNode.offsetHeight;

  return h;
}

var bc_last_height = 0;
function broadCastHeight() {
  var h = 0;
  if(window.calculateHeight) { // custom defined?
    h = calculateHeight();
  }
  else { // use default
    h = _calculateHeight();
  }

  if(h != bc_last_height) {
    log("Calculated window " + window.name + " has height " + h);
    ce_signal( 'windowResized', window.name, h);
    bc_last_height = h;
  }
}

function addHeightSync() {
  ce_connect('windowResized', adjustFrameHeight);
}

function adjustFrameHeight(n, h) {

  var f = null;
  forEach(document.getElementsByTagName('iframe'),  function(frm) {
    if(frm.name == n) {
      f = frm;
    }
  });

  if(!f) {
    log("["+window.name+"] size reported for unknown child frame " + n + " : ignoring.");
    return;
  }

  if(f.height != h) {
    log("sizing ["+n+"] to " + h);
    f.height = h;
    f.style.height = h + 'px';
  }

  signal(ce_events,'ce_resized',f,h);

  if(broadCasting) {
    broadCastHeight();
  }
}
