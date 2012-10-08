package net.medcommons.mdl.action;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.rmi.RemoteException;
import java.util.List;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.PatientRecord;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.ResponseWrapper;
import net.medcommons.mdl.XdsDownloadTask;
import net.medcommons.mdl.XdsDownloadTaskStatus;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;

import org.apache.axis.encoding.Base64;
import org.apache.log4j.Logger;
import org.eclipse.ohf.ihe.common.hl7v2.mllpclient.ClientException;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumer;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerException;

import OHFBridgeStub.AssigningAuthorityType;
import OHFBridgeStub.DocumentQueryPreferencesType;
import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.PatientSearchPreferencesType;
import OHFBridgeStub.QueryDocumentsResponseType;
import OHFBridgeStub.RetrieveDocumentResponseType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import OHFBridgeStub.XdsDocType;

/**
 * Constructs a XDS retrieval
 * 
 * Note that the XDS server connections are initialized here from 
 * the config files. Need to pass down parameters in a more dynamic
 * way.
 */
public class DocumentSelectionAction extends MdlAction {
	private static Logger logger = Logger.getLogger(DocumentSelectionAction.class);

	
	private String uuids;

	private String storageId;

	public void setUuids(String uuids) {
		this.uuids = uuids;
	}

	public String getUuids() {
		return (this.uuids);
	}

	public void setStorageId(String storageId){
		this.storageId = storageId;
	}
	public String getStorageId(){
		return(this.storageId);
	}

	protected boolean initialized = false;
	@DefaultHandler
	public Resolution documentSelect() {
		ResponseWrapper response = new ResponseWrapper();
		if (!isLoggedIn()){
			response = generateErrorResponse("No user logged in", "User not logged in");
			return(new JavaScriptResolution(response));
		}
		try{
			logger.info("documentSelect: storageId=" +storageId + ", uuids="+ this.uuids);
			PatientRecordResultManager resultsManager = getPatientRecordResultManager();
			String[] uuidArray = this.uuids.split(",");
			List<XdsDocType> selectedDocuments = resultsManager.getSelectedDocuments(uuidArray);
			
			XdsDownloadTaskStatus status =  new XdsDownloadTaskStatus();
			status.setName("XDS Download");
			status.setStartTime(System.currentTimeMillis());
			MdlActionContext ctx = (MdlActionContext) getContext();
			Person person = ctx.getUser();
			
			XdsDownloadTask downloadTask = new XdsDownloadTask(person, resultsManager, status);
			downloadTask.setUseAtna(getUseAtna());
			downloadTask.setDocumentList(storageId,selectedDocuments);
			downloadTask.start();
			resultsManager.setXdsDownloadStatus(status);
			response.setStatus(ResponseWrapper.Status.OK);
			response.setContents(status);
			return new JavaScriptResolution(response);
		}
		catch(Exception e){
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage(e.getLocalizedMessage());
			response.setContents(ResponseWrapper.throwableToString(e));
			return (new JavaScriptResolution(response));
		}
		
		
	}

	/**
	 * Eventually move to new page - this way we can show progress if needed.
	 * @throws PdqConsumerException
	 */
	protected void executeQuery() throws RemoteException,IOException{
			logger.info("Executing Document Query");
			SessionContext sessionContext = null;
	    	PatientRecordResultManager pdqResults = getPatientRecordResultManager();
	    	RhioConfig[] rhios = pdqResults.getFilteredRhios();
	    	OhfBridgeSoapBindingStub binding = pdqResults.getOhfBindingStub();
	    	MdlActionContext ctx = (MdlActionContext) getContext();
	    	Person person = ctx.getUser();
        	PatientInfoType patientRecord = pdqResults.getCurrentPatientRecord();
        	
	    	PatientIdType idType = new PatientIdType();
			idType.setIdNumber(patientRecord.getPatientIdentifier().getIdNumber());
		    logger.info(patientRecord.getPatientIdentifier().getIdNumber());
			
			// Should these universal ids be set from current patient?
			// Probably yes. 
		    //OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = pdqResults.getAssigningAuthorityType();
		    
		    
			OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
		    assigningAuthorityType.setUniversalIdType("ISO");
			assigningAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
		    assigningAuthorityType.setUniversalId(pdqResults.getUniversalId());
		    logger.info("Setting universal id to be " + pdqResults.getUniversalId());
			idType.setAssigningAuthorityType(assigningAuthorityType);
		      
			// set up query preferences
			DocumentQueryPreferencesType queryPreferences = new DocumentQueryPreferencesType();
			// Should get these preferences from JavaScxript..
			queryPreferences.setReturnReferencesOnly(false);
			queryPreferences.setStoredQuery(true);
			
			QueryDocumentsResponseType response = null;
			
			for(int i = 0; i < rhios.length; i++){
	           
	            	logger.info("Attempting to context RHIO " + rhios[i].getName());
			        sessionContext = createSessionContext(rhios[i], person.getUsername());
			        sessionContext.setUseSecuredConnectionWhenAvaliable(true);
			        response = binding.queryDocumentsByPatientId(sessionContext, idType, queryPreferences);
					if (!response.isSuccess()){
						logger.error("Query failed:" + response.getFailMessage());
						throw new RuntimeException("Query failed:" + response.getFailMessage());
					}
					logger.info("QUERY Success. Returned " + response.getDocumentTypeArray().length + " documents.");
	           
					pdqResults.setDocuments(response.getDocumentTypeArray());
			}
	    }
	
	
	protected boolean isBlank(String value){
		if (value==null) return true;
		else if ("".equals(value)) return(true);
		else return(false);
	}
	
 
	
}
