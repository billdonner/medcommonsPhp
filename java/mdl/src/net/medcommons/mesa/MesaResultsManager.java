package net.medcommons.mesa;

import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
/**
 * Contains output of Mesa tests that is rendered in MesaTestResult.jsp.
 * TODO: Add other test results besides PatientInfoType.
 * @author mesozoic
 *
 */
public class MesaResultsManager {

	private String testname;
	
	private PatientInfoType [] patients;
	private RhioConfig rhioConfig;
	
	
	public void setTestname(String testname){
		this.testname = testname;
	}
	public String getTestname(){
		return(this.testname);
	}
	
	public void setPatients(PatientInfoType[] patients){
		this.patients = patients;
	}
	public PatientInfoType[] getPatients(){
		return(this.patients);
	}
	public void setRhioConfig(RhioConfig rhioConfig){
		this.rhioConfig = rhioConfig;
	}
	public RhioConfig getRhioConfig(){
		return(this.rhioConfig);
	}
	public void reset(){
		this.testname = "UNINITIALIZED";
		
		this.patients = null;
		this.rhioConfig = null;
		
	}
}
