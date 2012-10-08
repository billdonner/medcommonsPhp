package net.medcommons.mdl.action;




import net.medcommons.mdl.PatientRecord;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.ohf.OhfConfiguration;
import net.medcommons.mdl.ohf.TestPDQ;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.DontValidate;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

import net.sourceforge.stripes.validation.SimpleError;
import net.sourceforge.stripes.validation.Validate;
import net.sourceforge.stripes.validation.ValidateNestedProperties;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.List;

import org.apache.log4j.Logger;
import org.eclipse.ohf.hl7v2.core.definitions.formats.PrivateFormat;
import org.eclipse.ohf.hl7v2.core.utilities.HL7V2Exception;
import org.eclipse.ohf.hl7v2.core.validators.CPValidator;
import org.eclipse.ohf.ihe.common.hl7v2.mllpclient.ClientException;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumer;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerException;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerQuery;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerResponse;

import OHFBridgeStub.PatientInfoType;

/**
 * ActionBean that deals with selecting a patient.
 * Should have other actions for getting more info on patients that isn't on the screen.
 */
public class PdqQueryResponseAction extends MdlAction {
	
	private static Logger log = Logger.getLogger(PdqQueryResponseAction.class);
    /** Populated during bulk add/edit operations. 
    @ValidateNestedProperties({
        @Validate(field="patient", required=true, maxlength=75),
        @Validate(field="phone", required=true, minlength=25),
        @Validate(field="email", required=true)
    })
    */
    private int selectedRecordId;
    
    private List<PatientRecord> pdqRecords = new ArrayList<PatientRecord>();

    /** Populated by the form submit on the way into bulk edit. */
    private int[] pdqRecordIds;

    /** Gets the array of bug IDs the user selected for edit. */
    public int[] getPdqRecordIds() { return pdqRecordIds; }

    /** Sets the array of bug IDs the user selected for edit. */
    public void setPdqRecordIds(int[] pdqRecordIds) { this.pdqRecordIds = pdqRecordIds; }

    public void setSelectedRecordId(int selectedRecordId){
    	this.selectedRecordId = selectedRecordId;
    }
    public int getSelectedRecordId(){
    	return(this.selectedRecordId);
    }
    /**
     * Simple getter that returns the List of Bugs.  Not the use of generics syntax - this is
     * necessary to let Stripes know what type of object to create and insert into the list.
     */
    public List<PatientRecord> getPdqRecords() {
        return pdqRecords;
    }

    /** Setter for the list of bugs. */
    public void setPdqRecords(List<PatientRecord> pdqRecords) {
        this.pdqRecords = pdqRecords;
    }

    @DefaultHandler
    public Resolution selectPatient() throws PdqConsumerException{
    	log.info("Selecting patient");
    	log.info("selectedRecordId is " + selectedRecordId);
    	
    	MdlActionContext ctx = (MdlActionContext) getContext();
    	PatientRecordResultManager resultsManager = ctx.getPdqResultsManager();
    	PatientRecord currentPatientRecord = resultsManager.getAllRecords().get(selectedRecordId);
    	resultsManager.setCurrentPatientRecord(currentPatientRecord);
    	
        // Get patient id - then query for list of XDS documents.
    	log.info("selectedPatient last name is " + currentPatientRecord.getPatientName().getFamilyName());
        return new RedirectResolution("/XdsDocumentList.jsp");
    }
    /*
    @DontValidate
    public Resolution preEdit() {
        log.info("selectedRecordId is " + selectedRecordId);
        return new RedirectResolution("/PatientLogin.jsp").flash(this);
    }
    */
   
   
}
