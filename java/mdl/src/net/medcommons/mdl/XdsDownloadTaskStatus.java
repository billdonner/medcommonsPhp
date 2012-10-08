package net.medcommons.mdl;

import java.util.List;

import OHFBridgeStub.XdsDocType;

public class XdsDownloadTaskStatus extends TaskStatus {


	XdsDocType currentDocument;
	
	String url = "UNKNOWN";
	
	private long totalBytes = 0;
	private long transferredBytes = 0;
	
	public void setTotalBytes(long totalBytes){
		this.totalBytes = totalBytes;
	}
	public long getTotalBytes(){
		return(this.totalBytes);
	}
	public void setTransferredBytes(long transferredBytes){
		this.transferredBytes = transferredBytes;
	}
	public long getTransferredBytes(){
		return(this.transferredBytes);
	}
	public void setUrl(String url){
		this.url = url;
	}
	public String getUrl(){
		return(this.url);
	}
}
