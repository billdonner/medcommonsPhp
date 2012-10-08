package net.medcommons.mdl.action;

import java.io.IOException;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.PersonManager;
import net.medcommons.mdl.ResponseWrapper;
import net.medcommons.mdl.ohf.AtnaConstants;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptBuilder;
import net.sourceforge.stripes.ajax.JavaScriptResolution;
import net.sourceforge.stripes.validation.Validate;

import org.apache.log4j.Logger;

import OHFBridgeStub.ResponseType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;

/**

 */
public class ADLoginAction extends MdlAction {

	private static Logger logger = Logger.getLogger(ADLoginAction.class);

	@Validate(required = true)
	private String username;

	@Validate(required = true)
	private String password;

	private String targetUrl;

	/** The username of the user trying to log in. */
	public void setUsername(String username) {
		this.username = username;
	}

	/** The username of the user trying to log in. */
	public String getUsername() {
		return username;
	}

	/** The password of the user trying to log in. */
	public void setPassword(String password) {
		this.password = password;
	}

	/** The password of the user trying to log in. */
	public String getPassword() {
		return password;
	}

	/** The URL the user was trying to access (null if the login page was accessed directly). */
	public String getTargetUrl() {
		return targetUrl;
	}

	/** The URL the user was trying to access (null if the login page was accessed directly). */
	public void setTargetUrl(String targetUrl) {
		this.targetUrl = targetUrl;
	}

	/*
	 public Resolution getSourcePageResolution() {
	 return new JavaScriptResolution("");
	 }
	 */
	@DefaultHandler
	public Resolution login() throws IOException {
		logger.info("entering login");
		PersonManager pm = new PersonManager();
		Person person = pm.getPerson(this.username);
		logger.info("Logging in user " + this.username);
		ResponseWrapper response = new ResponseWrapper();
		PatientRecordResultManager pdqResults = getPatientRecordResultManager();
		
		RhioConfig[] rhios = pdqResults.getFilteredRhios();
		if (person == null) {
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage("No user matching " + this.username);
			//ValidationError error = new LocalizableError("usernameDoesNotExist");
			//getContext().getValidationErrors().add("username", error);
			if (getUseAtna()) {
				int eventOutcome = AtnaConstants.OUTCOME_MAJOR_FAILURE;
				atnaStatus(username, eventOutcome);
			}
			return new JavaScriptResolution(response);
		} else if (!person.getPassword().equals(password)) {
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage("Password not valid for user " + this.username);
			try {
				if (getUseAtna()) {
					int eventOutcome = AtnaConstants.OUTCOME_MAJOR_FAILURE;
					atnaStatus(username, eventOutcome);
				}
			} catch (Exception e) {
				logger.error("Atna login event failed:" + e.toString(), e);
			}

			return new JavaScriptResolution(response);
		} else {
			MdlActionContext ctx = (MdlActionContext) getContext();
			ctx.setUser(person);
			response.setStatus(ResponseWrapper.Status.OK);
			response.setContents(person);
			logger.info("About to return " + person);
			JavaScriptBuilder builder = new JavaScriptBuilder(person,
					Person.class);
			logger.info("Returned value:" + builder.toString());

			try {
				if (getUseAtna()) {
					int eventOutcome = AtnaConstants.OUTCOME_SUCCESS;
					atnaStatus(username, eventOutcome);
				}
			} catch (Exception e) {
				logger.error("Atna login event failed:" + e.toString(), e);
			}
			return new JavaScriptResolution(response);
		}
	}

	/**
	 * Reports error to ATNA server.
	 * 
	 * TODO: make this work with the right universal id - derive from the configuration.
	 * @param username
	 * @param eventOutcome
	 * @throws IOException
	 */
	private void atnaStatus(String username, int eventOutcome)
			throws IOException {
		//int eventOutcome = AtnaConstants.OUTCOME_SUCCESS;
		ResponseType response = null;
		String initiatingUserNodeIP = "DemoIdP";
		String authenticatingNodeIP = "10.242.0.70";
		PatientRecordResultManager pdqResults = getPatientRecordResultManager();
		RhioConfig[] rhios = pdqResults.getFilteredRhios();
		OhfBridgeSoapBindingStub binding = pdqResults.getOhfBindingStub();
		//String dataRecipientId = "doctor";
		//String exportedDataId = "urn:abcdefg";
		//String patientId = "95a1633c27e94eb";
		SessionContext sessionContext = createSessionContext(rhios[0], username);
		OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
		assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
		assigningAuthorityType.setUniversalIdType("ISO");

		response = binding.auditUserAuthenticationLoginEvent(sessionContext,
				eventOutcome, username, initiatingUserNodeIP,
				authenticatingNodeIP);

		if (response.isSuccess())
			logger.info("ATNA audit success  for LoginEvent");
		else {
			logger.info("ATNA Failed for LoginEvent");
			logger.info(response.getFailMessage());
		}
	}

}
