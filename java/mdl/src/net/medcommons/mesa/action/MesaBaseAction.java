package net.medcommons.mesa.action;

import java.io.File;
import java.util.ArrayList;
import java.util.List;

import OHFBridgeStub.AssigningAuthorityType;
import OHFBridgeStub.CodedMetadataType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import OHFBridgeStub.XdsDocType;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.action.MdlAction;
import net.medcommons.mesa.MesaResultsManager;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

public abstract class MesaBaseAction extends MdlAction{

	PatientRecordResultManager pdqResults = null;
	
	private String defaultUser = "doctor"; // For MESA tests only
	
	
	
   
    MesaResultsManager mesaResults = null;
	 public Resolution runTest() throws Exception{
		 pdqResults = getPatientRecordResultManager();
		
			 
		 pdqResults.reset();
		 mesaResults = new MesaResultsManager();
		 pdqResults.setMesaResultsManager(mesaResults);
		 executeTest();
		 return new RedirectResolution("/MesaTestResult.jsp");
	 }
	 
	 abstract public void executeTest() throws Exception;
	 
	  public SessionContext createSessionContext(RhioConfig pRhioConfig){
		  return(createSessionContext(pRhioConfig, defaultUser));
	  }
	 
	  public void setTestname(String testName){
		  mesaResults.setTestname(testName);
		  setLogFile(makeLogFile(mesaResults.getTestname() + ".log"));
	  }
	 
	
}
