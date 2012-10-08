/**
 * log - useful to send log statements to external logger
 * (used with Badboy to log statements to log file)
 */
var enableLog = true;
function log(msg) {
  try {
    if(enableLog) {
      window.external.info(msg);
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

