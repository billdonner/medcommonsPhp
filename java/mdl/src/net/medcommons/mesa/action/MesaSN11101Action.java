package net.medcommons.mesa.action;


import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import org.apache.log4j.Logger;

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
 This sequence tests your ability to send an audit record to the 
 MESA Audit Record Repository. This test covers the basic functionality 
 of transmitting the message and the proper XML format of the message. 
 The Actor Start message is chosen as that is required of all actors and 
 is independent of other IHE transactions. This can be run using the IETF or 
 INTERIM audit record format.
     * 
 * @author mesozoic
 *
 */
public class MesaSN11101Action extends MesaBaseAction implements AtnaConstants{

	
	private static Logger logger = Logger.getLogger(MesaSN11101Action.class);
	 public void executeTest() throws Exception{
		
	     setTestname("SN 11101");
	     
	   
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
          	response= binding.auditActorStartEvent(sessionContext, eventOutcome);
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
