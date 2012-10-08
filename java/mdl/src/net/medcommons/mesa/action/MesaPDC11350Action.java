package net.medcommons.mesa.action;



import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import org.apache.log4j.Logger;

import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mesa.MesaResultsManager;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

/**
/**
     * PDQ Multi Key Search 1
     * In test 11350, a Patient Demographics Consumer is required to send a
     * search using two search keys, patient name and date of birth.
     * Several ADT messages are sent to the Patient Demographics Supplier. 
     * Then, the PD Consumer sends a query. The consumer is expected to query 
     * with a Patient identifier
     *      PID.5.1 = MOORE
     *      PID.7 = 19380224
     * Field RCP-2 should contain the value 10^RD or a higher number. This 
     * will allow a response with all records. There are later tests for 
     * limiting the number of records in a response.
     * No other query keys should be present.
     * QPD-1 (Message Query Name) is a field that the Supplier needs to be 
     * able to configure on a site specific basis. For these tests, set the 
     * value to QRY_PDQ_1001^Query By Name^IHEDEMO
     * The field QPD-8 should have the value Ò^^^&1.3.6.1.4.1.21367.2005.1.1&ISOÓ 
     * to specify that domain in the response or can be left empty.     
     * 
     * 
 * @author mesozoic
 *
 */
public class MesaPDC11350Action extends MesaBaseAction{

	private static Logger logger = Logger.getLogger(MesaPDC11350Action.class);
	 
	 public void executeTest() throws Exception{
		
	     setTestname("PDC 11350");
	     
	     OhfBridgeSoapBindingStub binding = null;
	      binding=createBinding();
	      
	      PatientNameType nameType = new PatientNameType();
	      nameType.setFamilyName("MOORE");
	      
   
     
      
      OHFBridgeStub.PatientInfoType infoType = new OHFBridgeStub.PatientInfoType();
      infoType.setPatientName(nameType);
      infoType.setPatientDateOfBirth("19380224");
     
      
       OHFBridgeStub.PatientSearchPreferencesType patientSearchPreferencesType = new OHFBridgeStub.PatientSearchPreferencesType();
      OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
      assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
      assigningAuthorityType.setUniversalIdType("ISO");
      
      OHFBridgeStub.AssigningAuthorityType[] authorityTypes = new  OHFBridgeStub.AssigningAuthorityType[1];
      authorityTypes[0] = assigningAuthorityType;
      patientSearchPreferencesType.setDomainsReturned(authorityTypes);
      
      RhioConfig[] fRhios = pdqResults.getFilteredRhios();
      OHFBridgeStub.SearchPatientResponseType response = null;
      for(int i = 0; i < fRhios.length; i++){
          try{
          	System.out.println("Attempting to context RHIO " + fRhios[i].getName());
          	mesaResults.setRhioConfig(fRhios[i]);
          	SessionContext sessionContext = createSessionContext(fRhios[i]);
          	response = binding.searchPatient(sessionContext, infoType, patientSearchPreferencesType);
          	
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
