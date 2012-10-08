package net.medcommons.mdl.action;

import org.apache.log4j.Logger;

import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.ResponseWrapper;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;

/**
 * Logs user out; returns a simple logout message.
 */
public class ADLogoutAction extends MdlAction {
	private static Logger logger = Logger.getLogger(ADLogoutAction.class);

	@DefaultHandler
	public Resolution logout() {
		logger.info("entering logout");
		MdlActionContext ctx = (MdlActionContext) getContext();
		ResponseWrapper response = new ResponseWrapper();
		Person currentPerson = ctx.getUser();

		try {
			//TODO Any other state to clear out?
			
			ctx.setUser(null);
			PatientRecordResultManager results = getPatientRecordResultManager();
			if (results != null) {
				results.reset();
			}
			
			/*
			 * Any reason for treating these cases differently? Calling logout when
			 * there is no current user shouldn't be an error. Should the logout
			 * reveal who was logged in? It might be nice to display a logout
			 * message - but the client should know who is logged in already.
			 * Therefore - revealing this information to a (potential) third party
			 * may expose a risk and thus only the 'Logout' message is returned.
			 */
			if (currentPerson == null) {
				response.setStatus(ResponseWrapper.Status.OK);
				response.setMessage("Logout");
				response.setContents("Logout");
				return new JavaScriptResolution(response);
			} else {
				logger.info("returning currentPerson:" + currentPerson);
				response.setStatus(ResponseWrapper.Status.OK);
				response.setMessage("Logout");
				response.setContents("Logout");
				return (new JavaScriptResolution(response));
			}
		} catch (Exception e) {
			response.setStatus(ResponseWrapper.Status.ERROR);
			response.setMessage(e.getLocalizedMessage());
			response.setContents(ResponseWrapper.throwableToString(e));
			return (new JavaScriptResolution(response));
		}

	}
}
