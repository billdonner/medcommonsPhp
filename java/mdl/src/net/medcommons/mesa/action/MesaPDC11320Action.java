package net.medcommons.mesa.action;



import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import org.apache.log4j.Logger;

import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mesa.MesaResultsManager;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

/**
   * PDQ Complete ID Search - Unspecified Domain
     * Test case 11320 covers a complete ID search where the return domains
     * are unspecified by the Patient Demographics Consumer. Several ADT 
     * messages are sent to the Patient Demographics Supplier. Then, the PD
     * Consumer sends a query. The consumer is expected to query with a 
     * Patient identifier
     *     PID.3.1 = PDQ113XX05
     * No other query keys should be present.
     * If populated, field RCP-2 should contain the value 10^RD or a higher 
     * number. This will allow a response with all records. There are later 
     * tests for limiting the number of records in a response. QPD-1 (Message
     * Query Name) is a field that the Supplier needs to be able to configure
     * on a site specific basis. For these tests, set the value to
     * QRY_PDQ_1001^Query By Name^IHEDEMO
     * The field QPD-8 should be left empty.
     * 
 * @author mesozoic
 *
 */
public class MesaPDC11320Action extends MesaBaseAction{

	private static Logger logger = Logger.getLogger(MesaPDC11320Action.class);
	 
	 public void executeTest() throws Exception{
		
	     setTestname("PDC 11320");
	     
	     OhfBridgeSoapBindingStub binding = null;
	      binding=createBinding();
	      
	      PatientIdType idType = new PatientIdType();
	      idType.setIdNumber("PDQ113XX05");
	      
     
     
     
      
      OHFBridgeStub.PatientInfoType infoType = new OHFBridgeStub.PatientInfoType();
      infoType.setPatientIdentifier(idType);
      
      /*
       OHFBridgeStub.PatientSearchPreferencesType patientSearchPreferencesType = new OHFBridgeStub.PatientSearchPreferencesType();
      OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
      assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
      assigningAuthorityType.setUniversalIdType("ISO");
      
      OHFBridgeStub.AssigningAuthorityType[] authorityTypes = new  OHFBridgeStub.AssigningAuthorityType[1];
      authorityTypes[0] = assigningAuthorityType;
      patientSearchPreferencesType.setDomainsReturned(authorityTypes);
      */
      RhioConfig[] fRhios = pdqResults.getFilteredRhios();
      OHFBridgeStub.SearchPatientResponseType response = null;
      for(int i = 0; i < fRhios.length; i++){
          try{
          	System.out.println("Attempting to context RHIO " + fRhios[i].getName());
          	mesaResults.setRhioConfig(fRhios[i]);
          	SessionContext sessionContext = createSessionContext(fRhios[i]);
          	response = binding.searchPatient(sessionContext, infoType, null);
          	String failMessage = response.getFailMessage();
          	System.out.println("Failure message is " + failMessage);
          	PatientInfoType [] patients =response.getPatients();
          	mesaResults.setPatients(patients);
          	pdqResults.setPatientRecords(patients);
          	
          	
          	String [] bridgeLog = binding.getMyLog(sessionContext, 0);
          	
          	String log = saveLog(bridgeLog, getLogFile());
          	logger.info(log);
          }
          catch(Exception e){
              e.printStackTrace(System.out);
          }
      }
  }
}
