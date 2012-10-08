package net.medcommons.mdl.action;

import java.io.File;
import java.io.IOException;
import java.rmi.RemoteException;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.Configuration;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.ResponseWrapper;
import net.medcommons.mdl.utils.MetadataFormat;
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
public class PdqQueryAction extends MdlAction {
	
	// HIMSS Kludge - if true use the server info derived from the rhio config file
	boolean useBuiltInAuthority = false; 
	
	// HIMSS Kludge - if true restrict the domain of the PDQ queries. 
	boolean restrictPDQQueryDomains = true;
	
	private static Logger logger = Logger.getLogger(PdqQueryAction.class);

	private PdqConsumer pdqConsumer = null;

	private String queryid;

	private String firstname;

	private String middlename;

	private String lastname;

	private String suffix;

	private String dob;

	private int maxQueryItems = 0;

	public void setQueryid(String queryid) {
		this.queryid = queryid;
	}

	public String getQueryid() {
		return (this.queryid);
	}

	public void setFirstname(String firstname) {
		this.firstname = firstname;
	}

	public String getFirstname() {
		return (this.firstname);
	}

	public void setMiddlename(String middlename) {
		this.middlename = middlename;
	}

	public String getMiddlename() {
		return (middlename);
	}

	public void setLastname(String lastname) {
		this.lastname = lastname;
	}

	public String getLastname() {
		return (this.lastname);
	}

	public void setSuffix(String suffix) {
		this.suffix = suffix;
	}

	public String getSuffix() {
		return (this.suffix);
	}

	public void setDob(String dob) {
		this.dob = dob;
	}

	public String getDob() {
		return (this.dob);
	}

	public void setMaxQueryItems(int maxQueryItems) {
		this.maxQueryItems = maxQueryItems;
	}

	public int getMaxQueryItems() {
		return (this.maxQueryItems);
	}

	protected boolean initialized = false;

	@DefaultHandler
	public Resolution pdqQuery() {
		logger
				.info("createQuery:Patient name is firstname = "
						+ this.firstname + ", lastname =" + this.lastname
						+ ", queryId =" + this.queryid);
		ResponseWrapper response = new ResponseWrapper();
		if (!isLoggedIn()){
			response = generateErrorResponse("No user logged in", "User not logged in");
			return(new JavaScriptResolution(response));
		}
		try {
			
			if ((this.firstname == null) && (this.lastname == null)
					&& (this.queryid == null) && (this.dob == null)) {
				response.setStatus(ResponseWrapper.Status.ERROR);
				response.setMessage("No query arguments specified");
				return (new JavaScriptResolution(response));
			}

			PatientInfoType infoType = new PatientInfoType();

			PatientNameType patientNameQuery = makePdqPatientNameQuery(
					this.firstname, this.lastname);
			PatientIdType patientIdQuery = makePdqPatientIdQuery(this.queryid);
			if (patientNameQuery != null)
				infoType.setPatientName(patientNameQuery);
			if (patientIdQuery != null) {
				infoType.setPatientIdentifier(patientIdQuery);
			}
			if (dob != null) {
				infoType.setPatientDateOfBirth(dob);
			}

			MdlActionContext ctx = (MdlActionContext) getContext();
			ctx.setPdqQuery(infoType);

			//getContext().setPdqConsumer(pdqConsumer);
			//currentPdqConsumerQuery.
			executeQuery();
			PatientRecordResultManager pdqResults = getPatientRecordResultManager();
			response.setStatus(ResponseWrapper.Status.OK);
			response.setContents(pdqResults.getAllRecords());
			return new JavaScriptResolution(response);
		} catch (Exception e) {
			logger.error("pdqQuery", e);
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage(e.toString());
			response.setContents(ResponseWrapper.throwableToString(e));
			return (new JavaScriptResolution(response));
		}

	}

	/**
	 * Eventually move to new page - this way we can show progress if needed.
	 * @throws PdqConsumerException
	 */
	protected void executeQuery() throws RemoteException, IOException {
		logger.info("Executing PDQ Query");
		PatientRecordResultManager pdqResults = getPatientRecordResultManager();
		RhioConfig[] rhios = pdqResults.getFilteredRhios();
		OhfBridgeSoapBindingStub binding = pdqResults.getOhfBindingStub();

		pdqResults.reset();
		MdlActionContext ctx = (MdlActionContext) getContext();
		PatientInfoType pdqQuery = ctx.getPdqQuery();
		
		AssigningAuthorityType assigningAuthorityType = null;

		PatientSearchPreferencesType patientSearchPreferencesType = new PatientSearchPreferencesType();
		if (useBuiltInAuthority) {
			assigningAuthorityType = pdqResults.getAssigningAuthorityType();
		} else {
			assigningAuthorityType = new AssigningAuthorityType();
			assigningAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
			assigningAuthorityType.setUniversalId(pdqResults.getUniversalId());
			assigningAuthorityType.setUniversalIdType(pdqResults
					.getUniversalIdType());
			
		}
		logger.info("PDQ Settings:"
				+ MetadataFormat.toString(assigningAuthorityType));
		AssigningAuthorityType[] authorityTypes = new AssigningAuthorityType[1];
		authorityTypes[0] = assigningAuthorityType;
		
		if (restrictPDQQueryDomains){
			AssigningAuthorityType registryAuthorityType = new AssigningAuthorityType();
			registryAuthorityType.setNamespaceId(pdqResults.getNamespaceid());
			registryAuthorityType.setUniversalId(pdqResults.getRegistryUniversalId());
			registryAuthorityType.setUniversalIdType(pdqResults
					.getUniversalIdType());
			
			AssigningAuthorityType[] registryTypes = new   AssigningAuthorityType[1];
			registryTypes[0] = registryAuthorityType;
			patientSearchPreferencesType.setDomainsReturned(registryTypes);
		}
		if (maxQueryItems > 0) {
			logger.info("Setting PDQ quantity limit " + maxQueryItems);
			patientSearchPreferencesType.setQuantityLimit(maxQueryItems);

		}

		OHFBridgeStub.SearchPatientResponseType response = null;
		String useSecure = Configuration.getProperty("useSecureIfAvailable");
		boolean useSecureIfAvailalble = true;
		logger.info("Configuration: useSecureIfAvailable = " + useSecure);
		if ((useSecure != null) && ("false".equalsIgnoreCase(useSecure.trim())))
			useSecureIfAvailalble = false;
		logger.info("Configuration: useSecureIfAvailable = " + useSecure
				+ " ==> " + useSecureIfAvailalble);
		for (int i = 0; i < rhios.length; i++) {
			try {
				logger.info("Attempting to context RHIO " + rhios[i].getName());
				//mesaResults.setRhioConfig(rhios[i]);
				
				Person person = ctx.getUser();
				SessionContext sessionContext = createSessionContext(rhios[i],
						person.getUsername());
				sessionContext
						.setUseSecuredConnectionWhenAvaliable(useSecureIfAvailalble);
				response = binding.searchPatient(sessionContext, pdqQuery,
						patientSearchPreferencesType);
				String failMessage = response.getFailMessage();
				logger.info("Failure message is " + failMessage);

				PatientInfoType[] patients = response.getPatients();
				if (patients == null) {
					logger.info("No patients returned from query");
					return;
				}
				logger
						.info("Number of patients in response:"
								+ patients.length);
				//mesaResults.setPatients(patients);
				pdqResults.setPatientRecords(patients);

				String[] bridgeLog = binding.getMyLog(sessionContext, 0);
				String log = saveLog(bridgeLog, new File("TestLog.log"));
				logger.info(log);

			} catch (Exception e) {
				e.printStackTrace(System.out);
			}
		}
		// pdqResults.runQuery(pdqConsumer,pdqQuery,auditUser);
		//getContext().setPdqResultsManager(pdqResults);

		// TODO: On error - redirect to another page.
		// Also have action for redirect back to query page.
		// return new RedirectResolution("/mdl/PdqQueryResponse.jsp");
	}

	/**
	 * Creates the query object from the contents of the JSP.
	 * Much work to do here.
	 * 
	 * @param pdqQuery
	 * @return
	 * @throws PdqConsumerException
	 */
	/*
	 protected PdqConsumerQuery createMessage(PdqConsumer pdqQuery)
	 throws PdqConsumerException {
	 log.info("createMessage:start");
	 PdqConsumerQuery msg = pdqQuery.createQuery();
	 log.info("createMessage:query parameters:");
	 log.info("createMessage:First name:" + this.firstname);
	 log.info("createMessage:Last name:" + this.lastname);
	 log.info("createMessage:Patient id " + this.queryid);
	 // msg.addOptionalDemographicSearch("PID-8","F");
	 //msg.addQueryPatientSex("F");
	 //log.info("createMessage:Added Sex F");
	 
	 
	 if (!isBlank(this.lastname)){
	 msg.addQueryPatientNameFamilyName(this.lastname);
	 log.info("createMessage: Added last name " + this.lastname + " to query");
	 }
	 else{
	 log.info("createMessage: Lastname is blank:" + this.lastname);
	 }
	 if (!isBlank(this.firstname)){
	 msg.addQueryPatientNameGivenName(this.firstname);
	 log.info("createMessage: Added firstname name " + this.firstname + " to query");
	 }
	 else{
	 log.info("createMessage: firstname is blank:" + this.firstname);
	 }
	 

	 msg.addOptionalQuantityLimit(10);
	 log.info("createMessage:end");

	 return msg;
	 }
	 */

	protected boolean isBlank(String value) {
		if (value == null)
			return true;
		else if ("".equals(value))
			return (true);
		else
			return (false);
	}

}
