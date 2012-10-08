package net.medcommons.mesa.action;

import java.io.File;
import java.io.FileOutputStream;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import net.medcommons.mdl.utils.MetadataFormat;

import org.apache.axis.encoding.Base64;
import org.apache.log4j.Logger;

import OHFBridgeStub.DocumentQueryPreferencesType;
import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.QueryDocumentsResponseType;
import OHFBridgeStub.RetrieveDocumentResponseType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import OHFBridgeStub.XdsDocType;

/**
 
 * 95a1633c27e94eb^^^&1.3.6.1.4.1.21367.2005.3.7&ISO
 * a782ec83e1e441^^^&1.3.6.1.4.1.21367.2005.3.7&ISO
 * @author mesozoic
 *
 */
public class MesaXDS11739Action extends MesaBaseAction {

	private static Logger logger = Logger.getLogger(MesaXDS11739Action.class);

	public void executeTest() throws Exception {

		setTestname("XDS 11739");

		OhfBridgeSoapBindingStub binding = null;
		binding = createBinding();

		PatientIdType idType = new PatientIdType();
		idType.setIdNumber("95a1633c27e94eb");// mc
		//idType.setIdNumber("PDQ113XX01");// mc
		OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
	    // assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1"); // ibm
		assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.3.7"); // nist
		
	      assigningAuthorityType.setUniversalIdType("ISO");
		idType.setAssigningAuthorityType(assigningAuthorityType);
	      
		// set up query preferences
		DocumentQueryPreferencesType queryPreferences = new DocumentQueryPreferencesType();
		queryPreferences.setReturnReferencesOnly(false);
		queryPreferences.setStoredQuery(false);

		
		QueryDocumentsResponseType response = null;

		RhioConfig[] fRhios = pdqResults.getFilteredRhios();
		
		SessionContext sessionContext = null;
		for (int i = 0; i < fRhios.length; i++) {
			try {
				System.out.println("Attempting to context RHIO "
						+ fRhios[i].getName());
				mesaResults.setRhioConfig(fRhios[i]);
				sessionContext = createSessionContext(fRhios[i]);
				response = binding.queryDocumentsByPatientId(sessionContext, idType, queryPreferences);
				if (!response.isSuccess())
					throw new Exception("Query failed:" + response.getFailMessage());
				String failMessage = response.getFailMessage();
				logger.info("QUERY Success. Returned " + response.getDocumentTypeArray().length + " documents.");
				
				
				
				if(response.getDocumentTypeArray().length == 0){
		        	logger.error("Query Returned no documents, cannot complete Retrieve test");
					throw new Exception("Query Returned no documents, cannot complete Retrieve test");
		        }
				
			} catch (Exception e) {
				e.printStackTrace(System.out);
			}
		}
		String[] bridgeLog = binding.getMyLog(sessionContext, 0);

		String log = saveLog(bridgeLog, getLogFile());
		logger.info(log);
		 for(int i = 0; i < fRhios.length; i++){
	        	XdsDocType documents[] = response.getDocumentTypeArray();
	        	pdqResults.setDocuments(documents);
	        	for (int j=0;j<documents.length; j++){
	        		if (j > 10) break; // For testing
	        		logger.info("Retrieving " + response.getDocumentTypeArray()[j].getUuid());
	        		String uuid = documents[j].getUuid();
	        		String uri = documents[j].getUri();
	        		String title = documents[j].getDocumentTitle();
	        		logger.info("\n Document[" + j + "]\n" + MetadataFormat.toString(documents[j]));
	        		if (uri == null){
	        			logger.info("Can't retrieve document " + uuid + " because URI is null ");
	        		}
	        		else{
		        		RetrieveDocumentResponseType response2 = binding.retrieveDocumentByUUID(sessionContext, uuid);
		        		if(!response2.isSuccess()){
		        			logger.error("Retrieve failed: " + response2.getFailMessage());
		        			//throw new Exception("Retrieve FAILED: " + response2.getFailMessage());
		        		}
		        		else{
			        		if (response2.getDocument() != null){
				        		logger.info("Retrieve Success. Returned " + response2.getDocument().getFormatCode());
				        		logger.info(response2.getDocument().getCreationTime());
				        		logger.info(response2.getDocument().getMimeType());
				        		logger.info(response2.getDocument().getUri());
				        		logger.info(response2.getDocument().getUuid());
				        		String doc64 = response2.getDocument().getBase64EncodedDocument();
				        		if (doc64 != null)
				        		{
				        			//String decodedDocument = Base64Coder.urlsafe_decode(doc64);
				        			//logger.info("returned  document: \n" + doc64);
				        			try {
				        				byte[] doc = Base64.decode(doc64);
				        				//File f = new File(response2.getDocument().getDocumentTitle() + "_" + uuid);
				        				File f = makeMDLCacheFile(fRhios[i].getName() + response2.getDocument().getDocumentTitle() + "_" + uuid);
				        				FileOutputStream out = new FileOutputStream(f);
				        				out.write(doc);
				        				out.close();
				        				logger.info("Document written to " + f.getAbsolutePath());
				        			}
				        			catch(Exception e){
				        				logger.error("Error decoding document " + uuid);
				        			}
				        		}
				        		try{
				        			logger.info(" Family name w/document:" + response2.getDocument().getPatientInfo().getPatientName().getFamilyName());
				        		}
				        		catch(Exception e){;}
			        		}
			        		else{
			        			logger.info("Retrieved document is null");
			        		}
		        		}
	        		}
	        	}
	        }
		 String[] bridgeLog2 = binding.getMyLog(sessionContext, 0);

			String log2 = saveLog(bridgeLog2, getLogFile());
			logger.info(log2);
	}
}
