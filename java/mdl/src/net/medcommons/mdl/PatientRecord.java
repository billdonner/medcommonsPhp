package net.medcommons.mdl;

import OHFBridgeStub.PatientInfoType;

/**
 * Represents a PQQ Response.
 * Currently very unstructured to get app working. 
 * 
 * @author mesozoic
 *
 */
public class PatientRecord extends PatientInfoType{
	
	public PatientRecord(PatientInfoType patientInfoType){
		super(patientInfoType.getGenericAdtValues(), patientInfoType.getPatientAddress(), patientInfoType.getPatientDateOfBirth(),
				patientInfoType.getPatientIdentifier(), patientInfoType.getPatientName(), patientInfoType.getPatientPhoneBusiness(),
				patientInfoType.getPatientPhoneHome(), patientInfoType.getPatientSex(), patientInfoType.getPatientSuffixName());
		
	}
	public PatientRecord(PatientInfoType patientInfoType, Integer id){
		super(patientInfoType.getGenericAdtValues(), patientInfoType.getPatientAddress(), patientInfoType.getPatientDateOfBirth(),
				patientInfoType.getPatientIdentifier(), patientInfoType.getPatientName(), patientInfoType.getPatientPhoneBusiness(),
				patientInfoType.getPatientPhoneHome(), patientInfoType.getPatientSex(), patientInfoType.getPatientSuffixName());
		this.id = id;
		
		
	}
	private Integer id;
	private Identity federatedIdentity = null;

	
	public Integer getId(){
		return(this.id);
	}
	public void setId(Integer id){
		this.id = id;
	}
	
	public void setFederatedIdentity(Identity federatedIdentity){
		this.federatedIdentity = federatedIdentity;
	}
	
	public Identity getFederatedIdentity(){
		return(this.federatedIdentity);
	}
	
}
