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
  * PDQ Continuation Test 1
     * Test 11365 is a test of the HL7 Continuation Protocol. In this test, a 
     * Patient Demographics Consumer needs to configure their request with a 
     * specific limit and successfully process the response from the PD 
     * Supplier.
     *      PID.5.1 = MOO*
     * No other query keys should be present.
     * Field RCP-2 should contain the value 1^RD to indicate one record per 
     * response. QPD-1 (Message Query Name) is a field that the Supplier needs
     * to be able to configure on a site specific basis. For these tests, set 
     * the value to QRY_PDQ_1001^Query By Name^IHEDEMO
     * The field QPD-8 should have the value Ò^^^&1.3.6.1.4.1.21367.2005.1.1&ISOÓ 
     * to specify that domain in the response or can be left empty.
     * The evaluation script will examine your query, but will not examine the
     * intermediate and subsequent queries sent by your system. If you do not 
     * send the proper continuation data, the MESA server will not respond and
     * will log information in the MESA log messages.
     * 
 * @author mesozoic
 *
 */
public class MesaPDC11365Action extends MesaBaseAction{

	private static Logger logger = Logger.getLogger(MesaPDC11365Action.class);
	 
	 public void executeTest() throws Exception{
		
	     setTestname("PDC 11365");
	     
	      OhfBridgeSoapBindingStub binding = null;
	      binding=createBinding();
	      
	      PatientNameType nameType = new PatientNameType();
	      nameType.setFamilyName("MOO*");
	      
      
     
     
      
      OHFBridgeStub.PatientInfoType infoType = new OHFBridgeStub.PatientInfoType();
      infoType.setPatientName(nameType);
     
      
       OHFBridgeStub.PatientSearchPreferencesType patientSearchPreferencesType = new OHFBridgeStub.PatientSearchPreferencesType();
      OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
      assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
      assigningAuthorityType.setUniversalIdType("ISO");
      
      OHFBridgeStub.AssigningAuthorityType[] authorityTypes = new  OHFBridgeStub.AssigningAuthorityType[1];
      authorityTypes[0] = assigningAuthorityType;
      patientSearchPreferencesType.setDomainsReturned(authorityTypes);
      patientSearchPreferencesType.setQuantityLimit(1);
      
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
