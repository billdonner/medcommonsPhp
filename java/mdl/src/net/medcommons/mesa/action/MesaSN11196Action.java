package net.medcommons.mesa.action;


import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import org.apache.log4j.Logger;

import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.ResponseType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.ohf.AtnaConstants;
import net.medcommons.mesa.MesaResultsManager;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

/**
In this test, the actor generates a log message that 
is specific to the actor. The Actor Start/Stop or User Authentication 
messages are general in nature.
     * 
 * @author mesozoic
 *
 */
public class MesaSN11196Action extends MesaBaseAction implements AtnaConstants{

	
	private static Logger logger = Logger.getLogger(MesaSN11196Action.class);
	 public void executeTest() throws Exception{
		
	     setTestname("SN 11196");
	     
	   
	  OhfBridgeSoapBindingStub binding = null;
      binding=createBinding();
    
      
     
      RhioConfig[] fRhios = pdqResults.getFilteredRhios();
     
    
      
      /*
      OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
      assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
      assigningAuthorityType.setUniversalIdType("ISO");
      
      OHFBridgeStub.AssigningAuthorityType[] authorityTypes = new  OHFBridgeStub.AssigningAuthorityType[1];
      authorityTypes[0] = assigningAuthorityType;
      */
     
      
      ResponseType response = null;
      for(int i = 0; i < fRhios.length; i++){
          try{
          	logger.info("Attempting to context RHIO " + fRhios[i].getName());
          	mesaResults.setRhioConfig(fRhios[i]);
          	SessionContext sessionContext = createSessionContext(fRhios[i]);
          	logger.info(sessionContextToString(sessionContext));
          	int eventOutcome = OUTCOME_SUCCESS;
          	String dataRecipientId = "dataRecipientId";
          	String exportedDataId = "urn:abcdefg";
          	String patientId = "95a1633c27e94eb";
          	OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
            assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
            assigningAuthorityType.setUniversalIdType("ISO");
            
            PatientIdType idType = new PatientIdType();
  	      	idType.setIdNumber(patientId);
  	      	idType.setAssigningAuthorityType(assigningAuthorityType);
  	      	
  	      	String queryText="query for patient documents for JM19400814";
  	      	String registryID="ibm-235";
  	      	
          	response = binding.auditPhiExport(sessionContext, eventOutcome, dataRecipientId, exportedDataId, idType);
  	     /// response = binding.auditQueryEvent(sessionContext, eventOutcome, registryID, queryText);
          	//response = binding.auditPatientRecordReadEvent(sessionContext, eventOutcome, idType);
          	
  	      	if (!response.isSuccess()){
  	      		logger.error("Failure message:" + response.getFailMessage());
  	      		
  	      	}
  	      	else 
  	      		logger.info("Success message:" + response.getSuccessMessage());
          	String message = null;
          	String [] bridgeLog = binding.getMyLog(sessionContext, 0);
          	String log = saveLog(bridgeLog, getLogFile());
          	
          	logger.info(log);
          	if (response.isSuccess()){
          		
          		message = response.getSuccessMessage();
          		logger.info("Success:" + message);
          	}
          	else{
          		message = response.getFailMessage();
          		logger.error("Failure:" + message);
          		//throw new RuntimeException("Bridge returned failure:" + message);
          	}
          
          	
          	
          	
          	
          }
          catch(Exception e){
              e.printStackTrace(System.out);
          }
      }
  }
}
