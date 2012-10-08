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
function setDocumentFocus (storageId,guid, cxpprotocol, cxphost, cxpport, cxppath) {

	setContextURL(baseURL + "setDocumentFocus?storageId=" + storageId +
		"&guid=" + guid +
		"&cxpprotocol=" + cxpprotocol +
		"&cxphost=" + cxphost +
		"&cxpport=" + cxpport +
		"&cxppath=" + cxppath
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
 * Sets the account focus
 */
function setAccountFocus(accountId, groupAccountId, groupName, auth, host, port, protocol, path) {
	setContextURL(baseURL + "setAccountFocus?"+
    "accountId=" + accountId +
		"&auth=" + auth +
		"&groupAccountId=" + groupAccountId +
		"&groupName=" + groupName +
		"&cxpprotocol=" + protocol +
		"&cxphost=" + host +
		"&cxpport=" + port +
		"&cxppath=" + path
		);
	return(true);
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
