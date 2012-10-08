package org.medcommons.modules.zxid;

/**
 * Holds results from zxid library. This permits results to be handled in different ways
 * (redirects happen differently in filters and servlets).
 * @author mesozoic
 *
 */
public class ZxidResult {

	public enum ResultType { ERROR, REDIRECT, OUTPUT,LOGGED_IN }
	private String rawOutput;
	private ResultType resultType;
	private String contents;
	private String contentType;
	// The SAML session id (if known)
	private String sessionId;
	
	// The zxid configuration string. Different 
	// servlets can have different values.
	private String conf;
	
	public void setRawOutput(String rawOutput){
		this.rawOutput = rawOutput;
	}
	public String getRawOutput(){
		return(this.rawOutput);
	}
	public void setResultType(ResultType resultType){
		this.resultType = resultType;
	}
	public ResultType getResultType(){
		return(this.resultType);
	}
	public void setContents(String contents){
		this.contents = contents;
	}
	public String getContents(){
		return(this.contents);
	}
	public void setContentType(String contentType){
		this.contentType = contentType;
	}
	public String getContentType(){
		return(this.contentType);
	}
	public void setSessionId(String sessionId){
		this.sessionId = sessionId;
	}
	public String getSessionId(){
		return(this.sessionId);
	}
	public void setConf(String conf){
		this.conf = conf;
	}
	public String getConf(){
		return(this.conf);
	}

}
