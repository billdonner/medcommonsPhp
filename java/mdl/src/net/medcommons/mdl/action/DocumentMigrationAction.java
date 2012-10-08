package net.medcommons.mdl.action;

import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.ResponseWrapper;
import net.medcommons.mdl.TaskStatus;
import net.medcommons.mdl.XdsDownloadTaskStatus;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;

import org.apache.log4j.Logger;

/**
 * Displays status of XDS retrieval; maybe CXP upload too.
 */
public class DocumentMigrationAction extends MdlAction {
	private static Logger logger = Logger.getLogger(DocumentMigrationAction.class);

	
	

	protected boolean initialized = false;
	@DefaultHandler
	public Resolution statusDisplay() {
		ResponseWrapper response = new ResponseWrapper();
		try{
			PatientRecordResultManager resultsManager = getPatientRecordResultManager();
			
			XdsDownloadTaskStatus xdsStatus= resultsManager.getXdsDownloadStatus();
			logger.info("statusDisplay: " + xdsStatus.getDisplayStatus() + ", Percent Complete:" + xdsStatus.getPercentComplete());
			response.setContents(xdsStatus);
			if ((xdsStatus.getStatus().equals(TaskStatus.STATUS.InProcess)) ||
				(xdsStatus.getStatus().equals(TaskStatus.STATUS.Uninitialized))	||
				(xdsStatus.getStatus().equals(TaskStatus.STATUS.Complete))){
				response.setStatus(ResponseWrapper.Status.OK);
			}
			else{
				response.setStatus(ResponseWrapper.Status.ERROR);
			}
			return (new JavaScriptResolution(response));
		}
		catch(Exception e){
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage(e.getLocalizedMessage());
			response.setContents(ResponseWrapper.throwableToString(e));
			return (new JavaScriptResolution(response));
		}
		
		
		
	}

	
	
 
	
}
