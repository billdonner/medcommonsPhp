package net.medcommons.mdl.action;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.rmi.RemoteException;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.PatientRecord;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.ResponseWrapper;
import net.medcommons.mdl.utils.MetadataFormat;
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
 * Constructs a XDS query
 * 
 * Note that the XDS server connections are initialized here from 
 * the config files. Need to pass down parameters in a more dynamic
 * way.
 */
public class DocumentQueryAction extends MdlAction {
	private static Logger logger = Logger.getLogger(DocumentQueryAction.class);

	
	private String queryid = null;
	
	


	public void setQueryid(String queryid) {
		this.queryid = queryid;
	}

	public String getQueryid() {
		return (this.queryid);
	}
	



	protected boolean initialized = false;
	@DefaultHandler
	public Resolution documentQuery() {
		ResponseWrapper response = new ResponseWrapper();
		logger.info("documentQuery: queryId =" + this.queryid);
		if (!isLoggedIn()){
			response = generateErrorResponse("No user logged in", "User not logged in");
			return(new JavaScriptResolution(response));
		}
		try{
			PatientRecordResultManager pdqResults = getPatientRecordResultManager();
			
			executeQuery();
			response.setStatus(ResponseWrapper.Status.OK);
			response.setContents(pdqResults.getDocuments());
			return new JavaScriptResolution(response);
		}
		catch (Exception e) {
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage(e.getLocalizedMessage());
			response.setContents(ResponseWrapper.throwableToString(e));
			return (new JavaScriptResolution(response));
		}
		
		
	}
	public Resolution documentQueryOld() throws ClientException, RemoteException,IOException{
		logger.info("documentQuery: queryId =" + this.queryid);
		
		PatientRecordResultManager pdqResults = getPatientRecordResultManager();
		
		if (queryid != null){
			PatientInfoType patientRecord = pdqResults.getCurrentPatientRecord();
		
			if (patientRecord == null){
				return(new JavaScriptResolution("Error: No current patient record set"));
			}
			else {
				String patientId = patientRecord.getPatientIdentifier().getIdNumber();
				if (!patientId.equals(queryid)){
					return(new JavaScriptResolution("Patient id '" + queryid +"' does not match patient id of current patient '" + patientId + "'"));
							
				}
				
			}
		}
		executeQuery();
		
		return new JavaScriptResolution(pdqResults.getDocuments());
		
		
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
        	
        	
	    	PatientIdType idType = new PatientIdType();
	    	
	    	idType.setIdNumber(queryid);
	    	
			
			// Should these universal ids be set from current patient?
			// Probably yes. 
			
			//OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = pdqResults.getAssigningAuthorityType();
			//assigningAuthorityType.setUniversalIdType("ISO");
			
			//OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
		    //assigningAuthorityType.setUniversalIdType("ISO");
			//assigningAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
			//assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2007.1.2.300");
			
			OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
		    assigningAuthorityType.setUniversalIdType("ISO");
			assigningAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
		    assigningAuthorityType.setUniversalId(pdqResults.getUniversalId());
		    boolean overrideXDSDomain = true;
		    if (overrideXDSDomain){
				AssigningAuthorityType registryAuthorityType = new AssigningAuthorityType();
				registryAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
				registryAuthorityType.setUniversalId(pdqResults.getRegistryUniversalId());
				registryAuthorityType.setUniversalIdType(pdqResults
						.getUniversalIdType());
				
				AssigningAuthorityType[] registryTypes = new   AssigningAuthorityType[1];
				assigningAuthorityType = registryAuthorityType;
			
			}
	        
		    logger.info("XDS Registry Settings:"  + MetadataFormat.toString(assigningAuthorityType));
		    
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
			        sessionContext.setUseSecuredConnectionWhenAvaliable(getUseSecureIfAvailable());
			        response = binding.queryDocumentsByPatientId(sessionContext, idType, queryPreferences);
					if (!response.isSuccess()){
						logger.error("Query failed:" + response.getFailMessage());
						throw new RuntimeException("Query failed:" + response.getFailMessage());
					}
					logger.info("QUERY Success. Returned " + response.getDocumentTypeArray().length + " documents.");
	           
					pdqResults.setDocuments(response.getDocumentTypeArray());
			}
	    }
	/*
	protected void executeQueryold() throws RemoteException,IOException{
		logger.info("Executing Document Query");
		SessionContext sessionContext = null;
    	PatientRecordResultManager pdqResults = getPatientRecordResultManager();
    	RhioConfig[] rhios = pdqResults.getFilteredRhios();
    	OhfBridgeSoapBindingStub binding = pdqResults.getOhfBindingStub();
    	Person person = getContext().getUser();
    	
    	
    	PatientIdType idType = new PatientIdType();
    	if (this.patientId != null)
    		idType.setIdNumber(patientId);
    	else{
    		PatientInfoType patientRecord = pdqResults.getCurrentPatientRecord();
    		idType.setIdNumber(patientRecord.getPatientIdentifier().getIdNumber());
    		// Kludge. Should clone object.
    		patientRecord.getPatientIdentifier().getAssigningAuthorityType().setUniversalIdType("");
    	}
		
		// Should these universal ids be set from current patient?
		// Probably yes. 
		
		OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
		
	    //  assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
	      assigningAuthorityType.setUniversalIdType("ISO");
		assigningAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
	    assigningAuthorityType.setUniversalId(pdqResults.getUniversalId());
	    //assigningAuthorityType.setUniversalIdType(pdqResults.getUniversalIdType());
	    
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
    */
	
	protected boolean isBlank(String value){
		if (value==null) return true;
		else if ("".equals(value)) return(true);
		else return(false);
	}
	
 
	
}
