package net.medcommons.mdl.action;

import java.io.IOException;
import java.rmi.RemoteException;

import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.ResponseWrapper;
import net.medcommons.mdl.XdsDownloadTaskStatus;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;

public class DocumentDownloadStatusAction extends MdlAction {
	
	    
		@DefaultHandler
	    public Resolution status() {
			ResponseWrapper response = new ResponseWrapper();
			try{
		    	PatientRecordResultManager pdqResults = getPatientRecordResultManager();
		    	
		    	XdsDownloadTaskStatus xdsStatus = pdqResults.getXdsDownloadStatus();
		    	response.setStatus(ResponseWrapper.Status.OK);
		    	response.setContents(xdsStatus);
				return new JavaScriptResolution(response);
			}
			catch (Exception e) {
				response.setStatus(ResponseWrapper.Status.ERROR);
				response.setMessage(e.getLocalizedMessage());
				response.setContents(ResponseWrapper.throwableToString(e));
				return (new JavaScriptResolution(response));
			}
	    	
	    }
}
