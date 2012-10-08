/**
 * contextManager.js
 * 
 * These calls are used to interact with a local context manager application. These
 * set or clear the current authorization context and set or clear the current
 * document focus. 
 *
 * These routines require that a two HREF targets (typically hidden iFrames) exist 
 * in the page being rendered:
 *
 * contextFrame - the frame where the authorization and document focus state are set
 * loadFrame - the frame where the load command is triggered.<b>
 *
 * For example:
 * <div id="contextManager">
 *	<iframe style="display: none;" height="10" width="246" scrolling="no" name="contextFrame"
 *          frameborder="0">Context Manager</iframe>
 *  <iframe style="display: none;" height="10" width="246" scrolling="no" name="loadFrame"
 *          frameborder="0">Context Manager</iframe>
 *	</div>
 *
 * You can either add these explicitly to the page, or if you don't the context manager
 * will attempt to create them dynamically when you first access it.
 *  
 * Note that two targets are necessary to avoid a race condition where the results of 
 * the contextFrame request is returning and the load request from the data is then
 * processed. 
 
 */
var portNumber = true;

var baseURL = "http://localhost:16092/";

function initContextManager() {
  appendChildNodes(
    document.body, 
      DIV( {'style':'display:none'}, 
          createDOM('iframe',{'id':'ctxf1','name':'contextFrame'}), 
          createDOM('iframe',{'id':'ctxf2','name':'loadFrame'})
      )
  );
}

/**
 * Sets the Authorization context. Currently this is simply the MedCommons ID 
 * of the authenticated user; perhaps something more complex will be needed in
 * the future. It is typically called from the body's onload handler.
 */
function setAuthorizationContext (accountId) {
	//alert ("Authorization Context: " + accountId);
	setContextURL(baseURL + "setAuthorizationContext?accountId="
		+ accountId);
 	return (true);
}

/**
 * loadDocument is a utility function that sets the document focus and then 
 * requests the context manager to download a particular document from  
 * a gateway.
*/
function loadDocument(storageId, guid, cxpprotocol, cxphost, cxpport, cxppath){
	 //          setDocumentFocus( storageId, guid, cxpprotocol, cxphost, cxpport, cxppath);
	downloadDocumentAttachments( storageId, guid, cxpprotocol, cxphost, cxpport, cxppath);
	//triggerLoad();
}

/**
 * setDocumentFocus sets the document focus of the context manager.
 * The focus includes all parameters needed to access a particular document.
 * These are:
 * storageId - the MedCommonsID where the data is stored.
 * guid - the document's SHA-1 identifier within the context of that storageId.
 * cxphost - the hostname for a particular gateway.
 * cxpport - the port that the gateway is using.
 * 
 * Note: perhaps need to specify protocol (http vs. https) as for the cxp endpoint
 * as well. 
*/
function setDocumentFocus (storageId,guid, cxpprotocol, cxphost, cxpport, cxppath, applianceRoot) {
	
	if(!applianceRoot)
		applianceRoot = '';

	setContextURL(baseURL + "setDocumentFocus?storageId=" + storageId +
		"&guid=" + guid +
		"&cxpprotocol=" + cxpprotocol +
		"&cxphost=" + cxphost +
		"&cxpport=" + cxpport +
		"&cxpport=" + cxpport +
		"&applianceRoot=" + applianceRoot
		);
	return(true);
}

function downloadDocumentAttachments (storageId,guid, cxpprotocol, cxphost, cxpport, cxppath) {

	setContextURL(baseURL + "downloadDocument?storageId=" + storageId +
		"&guid=" + guid +
		"&cxpprotocol=" + cxpprotocol +
		"&cxphost=" + cxphost +
		"&cxpport=" + cxpport +
		"&cxppath=" + cxppath
		);
	return(true);
}

/**
 * Send the given command to the local DDL.
 * <p>
 * If opts is supplied then will be passed as parameters.
 * <p>
 * If opt 'gwUrl' is provided then it will be parsed and translated
 * into constituent parts.
 * <p>
 * Supports jsonp = supply opts in form { jsonp: 'someFunctionToCall'}
 */
function sendCommand(cmd, opts) {
  var args = [];
  if(opts) {
    if(opts.gwUrl) 
      parseCXPUrl(opts.gwUrl, opts);
    for(var i in opts) {
      args.push(i + '=' + encodeURIComponent(opts[i]));
    }
  }
  loadContextManagerURL(baseURL + "CommandServlet/?command="+cmd + '&' + args.join('&'));
}

function parseCXPUrl(gwUrl,opts) {
  var host = /:\/\/([^\/]*)\//.exec(gwUrl)[1];
  var path = /:\/\/([^\/]*)(\/.*$)/.exec(gwUrl)[2];
  var protocol =   gwUrl.substring(0,gwUrl.indexOf(':'));
  opts.cxpprotocol = protocol;
  opts.cxphost = host.match(":")?host.substring(0,host.indexOf(':')) : host;
  opts.cxpport = host.match(":")?host.substring(host.indexOf(':')+1) : (protocol=='https'?'443':'80')
  opts.cxppath = path;
  return opts;
}

/**
 * Sets the account focus
 */
function setAccountFocus(accountId, groupAccountId, groupName, auth, host, port, protocol, path, callback) {
	
  if(!callback)
	  callback = "confirmAccountFocus";

  var url = baseURL + "setAccountFocus/?"+
    "accountId=" + accountId +
    "&auth=" + auth +
    "&groupAccountId=" + groupAccountId +
    "&groupName=" + groupName +
    "&cxpprotocol=" + protocol +
    "&cxphost=" + host +
    "&cxpport=" + port +
    "&cxppath=" + path +
    "&jsonp=confirmAccountFocus"
    ;

  loadContextManagerURL(url);
	return true;
}

/**
 * Load the specified URL in a script tag
 */
function loadContextManagerURL(url) {
  log('loading url: ' + url);
  var script = document.createElement("script");
  script.setAttribute("type", "text/javascript");
  script.setAttribute("src", url);
  script.className = 'ctxmgr';
  var head = document.getElementsByTagName("head").item(0);
  head.appendChild(script);
}

function vacuumContextManagerScripts() {
	forEach(document.getElementsByTagName('script'), function(s) {
		if(s.className == 'ctxmgr') {
	    	removeElement(s);
		}
	});
}

var ddlDetected = false;

function confirmAccountFocus(result) {
  ddlDetected = true;
  if(window.onDDLDetected)
    onDDLDetected();
}

/**
 * Clears the currentAuthorization context.
*/
function clearAuthorizationContext(){
	setContextURL(baseURL + "clearAuthorizationContext");
}

/**
 * Clears the current document focus.
*/
function clearDocumentFocus(){
	setContextURL(baseURL + "clearDocumentFocus");
}
/**
 * Utility method for communication with the contextFrame
 */
function setContextURL(url){
  if(!window.contextFrame) {
    initContextManager();
  }
	//alert(url);
	contextFrame.location.href=url;
}

/**
 * Utility method for communication with the loadframe.
*/
/*
function triggerLoad(){
  if(!window.loadFrame) {
    initContextManager();
  }
	loadFrame.location.href=baseURL + "tnum";
}
*/
