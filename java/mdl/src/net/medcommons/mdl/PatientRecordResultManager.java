package net.medcommons.mdl;

import java.io.IOException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.TreeMap;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.ohf.OhfConfiguration;
import net.medcommons.mesa.MesaResultsManager;

import org.apache.log4j.Logger;
import org.apache.log4j.xml.DOMConfigurator;
// Note that the following classes depend on jars in the lib/ohf_jars directory. These should
// all be removed and replaced with classes 
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumer;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerException;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerQuery;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerResponse;

import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.XdsDocType;

public class PatientRecordResultManager {
	private static Logger logger = Logger
			.getLogger(PatientRecordResultManager.class);

	/** Sequence counter for ID generation. */
	private static int idSequence = 0;

	/** Stores the list of people in the system. */
	private  Map<Integer, PatientRecord> patientRecords = new TreeMap<Integer, PatientRecord>();

	List<PdqConsumerResponse> responses = new ArrayList<PdqConsumerResponse>();

	private PatientRecord currentPatientRecord;

	private MesaResultsManager mesaResultsManager;

	private String failureMessage;

	private String[] bridgeLog;

	private XdsDocType documents[];

	private RhioConfig[] filteredRhios = null;

	private RhioConfig[] allRhios = null;

	private OhfBridgeSoapBindingStub ohfBindingStub = null;
	
	private String resultMessage = null;
	
	private String cdaDocument;
	
	private String cdaCacheDocument = null;
	
	private XdsDownloadTaskStatus xdsDownloadTaskStatus = null;
	
	private String universalId=null;
	private String universalIdType= null;
	private String namespaceId=null;
	private String application = null;
	private String facility = null;
	private String registryUniversalId = "1.3.6.1.4.1.21367.2007.1.2.300";
	private OHFBridgeStub.AssigningAuthorityType assigningAuthorityType ;
	
	public PatientRecordResultManager() {
		super();
		DOMConfigurator.configure(OhfConfiguration.LOG4J_PATH);
		logger.debug("Initializing PatientRecordResultManager ");
		assigningAuthorityType= new OHFBridgeStub.AssigningAuthorityType();
		assigningAuthorityType.setUseDefaultAssigningAuthority(true);
		try{ 
			setUniversalId(Configuration.getProperty("DefaultUniversalId"));
			setUniversalIdType(Configuration.getProperty("DefaultUniversalIdType"));
			setNamespaceid(Configuration.getProperty("DefaultNamespaceId"));
			setFacility("XDSDEMO");
			setApplication("XDSDEMO_ADT");
		}
		catch(IOException e){
			logger.error("Failed to initialize configurations", e);
		}

	}
	/**
	 * This is a proxy for the 'global id'.
	 * @param registryUniversalId
	 */
	public void setRegistryUniversalId(String registryUniversalId){
		this.registryUniversalId = registryUniversalId;
	}
	public String getRegistryUniversalId(){
		return(this.registryUniversalId);
	}
	public void setApplication(String application){
		this.application = application;
	}
	public String getApplication(){
		return(this.application);
	}
	public void setFacility(String facility){
		this.facility = facility;
	}
	public String getFacility(){
		return(this.facility);
	}
	public OHFBridgeStub.AssigningAuthorityType getAssigningAuthorityType(){
		return(this.assigningAuthorityType);
	}
	public void setAssigningAuthorityType(OHFBridgeStub.AssigningAuthorityType assigningAuthorityType){
		this.assigningAuthorityType = assigningAuthorityType;
	}
	public void setUniversalId(String universalId){
		this.universalId = universalId;
		logger.info("setUniversalId:" + universalId);
	}
	public String getUniversalId(){
		return(this.universalId);
	}
	public void setUniversalIdType(String universalIdType){
		this.universalIdType = universalIdType;
		logger.info("setUniversalIdType:" + universalIdType);
	}
	public String getUniversalIdType(){
		return(this.universalIdType);
	}
	
	public void setNamespaceid(String namespaceId){
		this.namespaceId = namespaceId;
		logger.info("setNamespaceid:" + namespaceId);
	}
	public String getNamespaceid(){
		return(this.namespaceId);
	}
	
	public void setOhfBindingStub(OhfBridgeSoapBindingStub ohfBindingStub) {
		this.ohfBindingStub = ohfBindingStub;
	}

	public OhfBridgeSoapBindingStub getOhfBindingStub() {
		return (this.ohfBindingStub);
	}

	public void setFailureMessage(String f) {
		failureMessage = f;
	}

	public String getFailureMessage() {
		return (failureMessage);
	}

	public void setDocuments(XdsDocType[] docs) {
		documents = docs;
	}

	public XdsDocType[] getDocuments() {
		return (documents);
	}

	public void runQuery(PdqConsumer pdqConsumer, PdqConsumerQuery pdqQuery,
			String auditUser) throws PdqConsumerException {
		PdqConsumerResponse response = pdqConsumer.sendQuery(pdqQuery, true,
				auditUser);
		// readReturn(response);
		responses.add(response);

		while (response.getContinuationPointer() != null) {
			pdqQuery.addOptionalContinuationPointer(response);
			response = pdqConsumer.sendQuery(pdqQuery, true, auditUser);
			// readReturn(response);
			responses.add(response);
		}

	}

	public void reset() {
		idSequence = 0;
		responses = new ArrayList<PdqConsumerResponse>();
		patientRecords = new TreeMap<Integer, PatientRecord>();
		mesaResultsManager = new MesaResultsManager();
		currentPatientRecord = null;
		this.bridgeLog = new String[1];
		bridgeLog[0] = "EMPTY LOG";

	}

	/**
	 * Stores the patient objects returned by the bridge into PatientRecords
	 * which have an additional id field for access in JSP editing.
	 * 
	 * Are multiple results coming in here?
	 * 
	 * @param patients
	 */
	public void setPatientRecords(PatientInfoType[] patients) {
		for (int i = 0; i < patients.length; i++) {
			Integer id = new Integer(++idSequence);
			PatientInfoType pat = patients[i];
			//PatientIdType idt = pat.getPatientIdentifier();
		
			
			patientRecords.put(new Integer(idSequence), new PatientRecord(
					patients[i], id));
		}

	}

	/**
	 * Returns the person with the specified ID, or null if no such person
	 * exists.
	 */
	public PatientInfoType getPatientRecord(Integer id) {
		logger.info("getPatientRecord " + id);
		PatientInfoType patientRecord = patientRecords.get(id);
		logger.info("Returning patient record:" + patientRecord + " out of " +patientRecords.size() + " records");
		if (patientRecord == null){
			Set<Integer> records  = patientRecords.keySet();
			Iterator<Integer> iter = records.iterator();
			while (iter.hasNext()){
				Integer recordId = iter.next();
				logger.info("record id = " + recordId + "=>" + patientRecords.get(recordId));
			}
			
		}
		return patientRecord;
	}

	public void setCurrentPatientRecord(PatientRecord cpr) {
		if (cpr != null){
			if (cpr.getPatientName() != null)
				logger.info("setCurrentPatientRecord: name " + cpr.getPatientName().getFamilyName());
			if (cpr.getPatientIdentifier() != null)
				logger.info("setCurrentPatientRecord: id " + cpr.getPatientIdentifier().getIdNumber());
		}
		currentPatientRecord = cpr;

	
	}

	public PatientInfoType getCurrentPatientRecord() {
		if (currentPatientRecord != null)
			logger
					.info("getCurrentPatientRecord:"
							+ currentPatientRecord.getPatientIdentifier()
									.getIdNumber());
		else
			logger.info("getCurrentPdqRecord: this.currentPdqRecord is null");
		return (currentPatientRecord);
	}

	/** Gets a list of all the people in the system. */
	public List<PatientRecord> getAllRecords() {
		return Collections.unmodifiableList(new ArrayList<PatientRecord>(
				patientRecords.values()));
	}
	public List<XdsDocType> getSelectedDocuments(String[] uuids){
		ArrayList<XdsDocType> docs = new ArrayList<XdsDocType>();
		// Very inefficient algorithm - but arrays are small. 
		for (int i = 0;i<uuids.length;i++){
			String uuid = uuids[i];
			for (int j=0;j<documents.length; j++){
				XdsDocType doc = documents[j];
				if (doc.getUuid().equals(uuid)){
					logger.info("Adding doc with uuid " + uuid);
					docs.add(doc);
					continue;
				}
			}
		}
		if (docs.size() != uuids.length){
			String msg = "Requested number of uuids " + uuids.length + " does not match number of returned documents " + docs.size();
			logger.error(msg);
			throw new RuntimeException(msg);
		}
		
		logger.info("getSelectedDocuments returned " + docs.size() + " documents");
		return(docs);
	}
	

	/**
	 * Deletes a person from the system...doesn't do anything fancy to clean up
	 * where the person is used.
	 */
	public void deleteRecord(int id) {
		patientRecords.remove(id);
	}

	public MesaResultsManager getMesaResultsManager() {
		return (mesaResultsManager);
	}

	public void setMesaResultsManager(MesaResultsManager mrm) {
		mesaResultsManager = mrm;
	}

	public void setBridgeLog(String[] log) {
		bridgeLog = log;
	}

	public String[] getBridgeLog() {
		return (bridgeLog);
	}

	public void setFilteredRhios(RhioConfig[] filteredRhios) {
		this.filteredRhios = filteredRhios;
	}

	public RhioConfig[] getFilteredRhios() {
		return (this.filteredRhios);
	}

	public void setAllRhios(RhioConfig[] allRhios) {
		this.allRhios = allRhios;
	}

	public RhioConfig[] getAllRhios() {
		return (this.allRhios);
	}
	public void setResultMessage(String resultMessage){
		this.resultMessage = resultMessage;
	}
	public String getResultMessage(){
		return(this.resultMessage);
	}
	public void setCdaDocument(String cdaDoc){
		cdaDocument = cdaDoc;
	}
	public String getCdaDocument(){
		return(cdaDocument);
	}
	public void setCdaCacheDocument(String cdaCacheDoc){
		cdaCacheDocument = cdaCacheDoc;
	}
	public String getCdaCacheDocument(){
		return( cdaCacheDocument);
	}
	public void setXdsDownloadStatus(XdsDownloadTaskStatus xdsDownloadTaskStatus){
		this.xdsDownloadTaskStatus = xdsDownloadTaskStatus;
	}
	public XdsDownloadTaskStatus getXdsDownloadStatus(){
		return(this.xdsDownloadTaskStatus);
	}
	
}