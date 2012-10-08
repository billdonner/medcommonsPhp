package net.medcommons.ohfbridge;

import java.util.ArrayList;
import java.util.List;

import localhost.bridge.services.ohf_bridge.OHFBridgeServiceLocator;
import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;

import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;

public class TestOHFBridge {

	 public void test8OhfBridgeSearchPatient() throws Exception {
	        OhfBridgeSoapBindingStub binding = null;
	        try {
	            binding = (OhfBridgeSoapBindingStub)
	                          new OHFBridgeServiceLocator().getOhfBridge();
	        }
	        catch (javax.xml.rpc.ServiceException jre) {
	            if(jre.getLinkedCause()!=null)
	                jre.getLinkedCause().printStackTrace();
	           jre.printStackTrace(System.out);
	        }
	        if (binding==null) throw new NullPointerException("Binding is null");
	        // Time out after a minute
	        binding.setTimeout(60000);
	        OHFBridgeStub.RhioConfig[]  rhios = binding.getRhios();
	        for (int i=0;i<rhios.length; i++){
	        	System.out.println(i + " " + rhios[i].getName() + " " + rhios[i].getDescription());
	        }
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
	        
	        RhioConfig[] fRhios = filterRhios("ibm", rhios);
	        OHFBridgeStub.SearchPatientResponseType response = null;
	        for(int i = 0; i < fRhios.length; i++){
	            try{
	            	System.out.println("Attempting to context RHIO " + fRhios[i].getName());
	            	SessionContext sessionContext = createSessionContext(fRhios[i]);
	            	response = binding.searchPatient(sessionContext, infoType, patientSearchPreferencesType);
	            	String failMessage = response.getFailMessage();
	            	System.out.println("Failure message is " + failMessage);
	            	PatientInfoType [] patients =response.getPatients();
	            	if (patients != null){
	            		for (int j=0;j<patients.length; j++){
	            			System.out.println("Response " + j);
	            			System.out.println(patients[j].getPatientName().getFamilyName() + patients[j].getPatientName().getGivenName());
	            			System.out.println(patients[j].getPatientDateOfBirth());
	            			System.out.println(patients[j].getPatientIdentifier().getIdNumber() + "," + patients[j].getPatientIdentifier().getAssigningAuthorityType());
	            		}
	            	}
	            	String [] mylog = binding.getMyLog(sessionContext, 0);
	            	for (int j=0;j<mylog.length;j++){
	            		System.out.println(j + " " + mylog[j]);
	            	}
	            	
	            }
	            catch(Exception e){
	                e.printStackTrace(System.out);
	            }
	        }
	    }
	 public RhioConfig[] filterRhios(String filter, RhioConfig[] configs){
	        if(isEmptyOrNull(filter)){
	            return configs;
	        }
	        List<RhioConfig> rhioTemp = new ArrayList<RhioConfig>();
	        for (int i = 0; i < configs.length; i++) {
	            if (null != configs[i].getName() &&
	            		configs[i].getName().startsWith(filter)){
	                rhioTemp.add(configs[i]);
	            }
	        }
	        return (RhioConfig[])rhioTemp.toArray(new RhioConfig[rhioTemp.size()]);
	    }
	  public static String trimNull(String pString){
	        if(null == pString){
	            return null;
	        }
	        pString = pString.trim();
	        if(pString.length() == 0 || "".equals(pString)){
	            return null;
	        }
	        return pString;
	    }

	    public static boolean isEmpty(String pString){
	        pString = pString.trim();
	        if(pString.length() == 0 || "".equals(pString)){
	            return true;
	        }
	        return false;
	    }

	    public static boolean isEmptyOrNull(String pString){
	        if(null == pString){
	            return true;
	        }
	        return isEmpty(pString);
	    }

	    public static boolean notEmpty(String pString){
	        return !isEmptyOrNull(pString);
	    }

	    
	 protected SessionContext createSessionContext(RhioConfig pRhioConfig){
	        SessionContext context = new SessionContext();
	        context.setUser("tester");
	        context.setUserApplicationName("");
	        context.setUserFacilityName("");
	        context.setRhioName(pRhioConfig.getName());
	        context.setReturnLogLevel("INFO");
	        return context;
	    }

	 
	 public static void main(String args[]){
		 TestOHFBridge test = new TestOHFBridge();
		 try{
			 test.test8OhfBridgeSearchPatient();
		 }
		 catch(Exception e){
			 e.printStackTrace(System.out);
		 }
	 }
}
