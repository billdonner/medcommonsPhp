package net.medcommons.mesa.action;


import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import org.apache.log4j.Logger;

import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mesa.MesaResultsManager;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

/**
 *   * PDQ Exact Name Search
     * Test case 11311 covers an exact name search by the Patient Demographics
     * Consumer. Several ADT messages are sent to the Patient Demographics 
     * Supplier. Then, the PD Consumer sends a query. The consumer is expected
     * to query by exact patient name:
     *     PID.5.1 = MOORE
     *     PID.5.2 = CHIP
     * No other query keys should be present.
     * If populated, field RCP-2 should contain the value 10^RD or a higher 
     * number. This will allow a response with all records. There are later 
     * tests for limiting the number of records in a response. QPD-1 (Message
     * Query Name) is a field that the Supplier needs to be able to configure
     * on a site specific basis. For these tests, set the value to 
     * QRY_PDQ_1001^Query By Name^IHEDEMO
     * 
 * @author mesozoic
 *
 */
public class MesaPDC11311Action extends MesaBaseAction{

	
	private static Logger logger = Logger.getLogger(MesaPDC11311Action.class);
	 public void executeTest() throws Exception{
		
	     setTestname("PDC 11311");
	     
	   
	  OhfBridgeSoapBindingStub binding = null;
      binding=createBinding();
    
      
     
      RhioConfig[] fRhios = pdqResults.getFilteredRhios();
     
     //OHFBridgeStub.SessionContext sessionContext = new OHFBridgeStub.SessionContext();
      OHFBridgeStub.PatientNameType nameType = new OHFBridgeStub.PatientNameType();
      nameType.setFamilyName("MOORE");
      nameType.setGivenName("CHIP");
      
      OHFBridgeStub.PatientInfoType infoType = new OHFBridgeStub.PatientInfoType();
      infoType.setPatientName(nameType);
      
      OHFBridgeStub.PatientSearchPreferencesType patientSearchPreferencesType = new OHFBridgeStub.PatientSearchPreferencesType();
      OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
      assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
      assigningAuthorityType.setUniversalIdType("ISO");
      
      OHFBridgeStub.AssigningAuthorityType[] authorityTypes = new  OHFBridgeStub.AssigningAuthorityType[1];
      authorityTypes[0] = assigningAuthorityType;
      patientSearchPreferencesType.setDomainsReturned(authorityTypes);
      
      
      OHFBridgeStub.SearchPatientResponseType response = null;
      for(int i = 0; i < fRhios.length; i++){
          try{
          	logger.info("Attempting to context RHIO " + fRhios[i].getName());
          	mesaResults.setRhioConfig(fRhios[i]);
          	SessionContext sessionContext = createSessionContext(fRhios[i]);
          	logger.info(sessionContextToString(sessionContext));
          	response = binding.searchPatient(sessionContext, infoType, patientSearchPreferencesType);
          	String failMessage = response.getFailMessage();
          	logger.info("Failure message is " + failMessage);
          	PatientInfoType [] patients =response.getPatients();
          	mesaResults.setPatients(patients);
          	pdqResults.setPatientRecords(patients);
          	
          	String [] bridgeLog = binding.getMyLog(sessionContext, 0);
          	String log = saveLog(bridgeLog, getLogFile());
          	pdqResults.setBridgeLog(bridgeLog);
          	logger.info(log);
          	
          }
          catch(Exception e){
              e.printStackTrace(System.out);
          }
      }
  }
}
