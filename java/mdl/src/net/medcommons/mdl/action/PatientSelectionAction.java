package net.medcommons.mdl.action;

import java.io.File;
import java.io.IOException;
import java.rmi.RemoteException;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.PatientRecord;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.ResponseWrapper;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;

import org.apache.log4j.Logger;
import org.eclipse.ohf.ihe.common.hl7v2.mllpclient.ClientException;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumer;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerException;

import OHFBridgeStub.AssigningAuthorityType;
import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.PatientSearchPreferencesType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;

/**
 * Constructs a PDQ query.
 * 
 * Note that the PDQ server connections are initialized here from 
 * the config files. Need to pass down parameters in a more dynamic
 * way.
 */
public class PatientSelectionAction extends MdlAction {
	private static Logger logger = Logger.getLogger(PatientSelectionAction.class);

	private PdqConsumer pdqConsumer = null;
	private String selectedId; // internally generated id - index into result set
	private String selectedPatientIdNumber; // patient identifier

	

	public void setSelectedId(String selectedId) {
		this.selectedId = selectedId;
	}

	public String getSelectedId() {
		return (this.selectedId);
	}

	public void setSelectedPatientIdNumber(String selectedPatientIdNumber){
		this.selectedPatientIdNumber = selectedPatientIdNumber;
	}
	public String getSelectedPatientIdNumber(){
		return(this.selectedPatientIdNumber);
	}
	

	protected boolean initialized = false;
	@DefaultHandler
	public Resolution selectPatient() {
		logger.info("selectPatient:selectedId " + this.selectedId + "," + selectedPatientIdNumber);
		ResponseWrapper response = new ResponseWrapper();
		try{
			//if (!initialized) initialize();
			if ((this.selectedId == null) ){
				return(new JavaScriptResolution("Selected patient is null"));
			}
			int intId = Integer.parseInt(selectedId);
			
			PatientRecordResultManager pdqResults = getPatientRecordResultManager();
			PatientInfoType selectedPatient = pdqResults.getPatientRecord(new Integer(intId));
			pdqResults.setCurrentPatientRecord(new PatientRecord(
					selectedPatient, 0));
		
			// Need to set current patient. 
			logger.info("Selected patient is :" + selectedPatient);
			response.setStatus(ResponseWrapper.Status.OK);
			response.setContents(selectedPatient);
			return new JavaScriptResolution(response);
		}
		catch (Exception e) {
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage(e.getLocalizedMessage());
			response.setContents(ResponseWrapper.throwableToString(e));
			return (new JavaScriptResolution(response));
		}
		
		
	}

	
	
	protected boolean isBlank(String value){
		if (value==null) return true;
		else if ("".equals(value)) return(true);
		else return(false);
	}
	
 
	
}
