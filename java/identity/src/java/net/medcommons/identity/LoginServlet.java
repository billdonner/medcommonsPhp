package net.medcommons.identity;

import java.io.IOException;
import java.util.Map;
import java.util.HashMap;
import org.apache.log4j.Logger;

import java.sql.SQLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import net.medcommons.Session;
import net.medcommons.modules.services.utils.RESTException;

/**
 * Handle logins from an external SAML-enabled customer, or from
 * existing users completing our login/registration form.
 *
 * @see LogoutServlet
 * @see RegisterServlet
 */
public class LoginServlet extends IdentityServlet {
	 private static Logger log = Logger.getLogger(LoginServlet.class.getName());
    private static final String ACCOUNT_DISABLED = "This account is currently disabled.  Please contact support for assistance.";
    protected static final String LOGIN_JSP_PAGE = "/WEB-INF/jsp/login.jsp";
    protected static final String SMS_JSP_PAGE = "/WEB-INF/jsp/sms.jsp";
    
    private static final String BAD_EMAIL = "Email or password not correct";
    private static final String BAD_MCID = "MCID or password not correct";
    
    /**
     * Entry point for external SAML users, wanting to use their user validation
     * with our service.
     *
     * The PingFederate server checks on the SAML assertions, then forwards the
     * users to this location, with cookies set up if the user is valid.
     */
    protected void doGet(HttpServletRequest request,
                    HttpServletResponse response)
    throws ServletException, IOException {
        Map userInfo = getUsersSessionInfo(request);
log.info("doGet");
	// if this is a redirect from a partner, it will have an 'enc'
	// so handle separately
	if (incomingMedcommonsPartner(userInfo, request, response))
	    return;

        String userId = (String) userInfo.get("userId");
        String sourceId = (String) userInfo.get("sourceId");
	String next = request.getParameter("next");

	request.setAttribute("next", next);

        if (userId != null && sourceId != null) {
	    incomingPartner(userInfo, request, response, userId, sourceId);
        }
        else {
            request.setAttribute("mcid", request.getParameter("email"));
            
            request.getRequestDispatcher(LOGIN_JSP_PAGE).forward(request,
                            response);
        }
    }

    private boolean incomingMedcommonsPartner(Map userInfo,
					      HttpServletRequest request,
					      HttpServletResponse response)
	throws ServletException, IOException {
	String queryString = request.getQueryString();

	if (queryString == null)
	    return false;

	if (!Session.IsQueryStringCurrent(queryString))
	    return false;

	if (!Session.IsSignedQueryStringValid("secret", queryString))
	    return false;

	queryString = Session.GetEncryptedQueryString(queryString, "secret");

	if (queryString == null)
	    return false;

	String userId = Session.GetQueryParameter(queryString, "userId");
	String sourceId = Session.GetQueryParameter(queryString, "sourceId");

	if (userId == null || sourceId == null)
	    return false;

	incomingPartner(userInfo, request, response, userId, sourceId);

	return true;
    }

    private void incomingPartner(Map userInfo, HttpServletRequest request,
				 HttpServletResponse response, String userId,
				 String sourceId) throws ServletException,
							 IOException {
	String next = request.getParameter("next");

	try {
	    IdentityConnection c = getConnection();

	    try {
		String mcid = (String) userInfo.get("mcid");
		String url = c.getServerUrl(sourceId, userId, userInfo);

		if (url != null)
		    c.log(mcid, userId, sourceId, "login");

		if (IdentityConnection.SMS.equals(url)) {
		    releaseConnection(c);
		    String code;

		    code = OneTimeCode.NextHash(mcid);
		    mail((String) userInfo.get("mobile"), code);

		    request.setAttribute("mcid", mcid);
		    request.setAttribute("email",
					 (String) userInfo.get("email"));
		    request.getRequestDispatcher(SMS_JSP_PAGE).forward(request,
								       response);
		    return;
		}

		else if (url != null) {
		    /* known user */
		    releaseConnection(c);

		    if (next != null) url = next;

		    login(response, url, userInfo);
		    return;
		}
                    
		request.setAttribute("sourceName",
				     c.getSourceName(sourceId));

		releaseConnection(c);
	    } catch (SQLException ex) {
		request.setAttribute("sqlError", ex.toString());
		log.error("sqlErr:" + ex.toString(), ex);
		ex.printStackTrace(System.err);
		c.close();
	    }
        catch (RESTException ex) {
		request.setAttribute("sqlError", ex.toString());
		log.error("sqlErr:" + ex.toString(), ex);
		ex.printStackTrace(System.err);
		c.close();
        }
	} catch (SQLException ex) {
	    request.setAttribute("sqlError", ex.toString());
	    log.error("sqlErr:" + ex.toString(), ex);
	    ex.printStackTrace(System.err);
	} catch (InterruptedException ex) {
	    request.setAttribute("sqlError", ex.toString());
	    log.error("sqlErr:" + ex.toString(), ex);
	    ex.printStackTrace(System.err);
	}
            
	request.setAttribute("userId", userId);
	request.setAttribute("sourceId", sourceId);
	request.setAttribute("next", next);
            
	super.doGet(request, response);
    }

    private static final String LOGIN_ERROR
    = "Enter either your email address, or your 16-digit MCID";
    
    private static final String[] INFO_KEYS = {
        "userId", "sourceId"
    };
    
    /**
     * Entry point for the 'login' page, for existing users.
     */
    protected void doPost(HttpServletRequest request,
                    HttpServletResponse response)
    throws ServletException, IOException {
    	log.info("doPost");
        Map userInfo = new HashMap();
        String mcid = request.getParameter("mcid");
        String userId = request.getParameter("userId");
        String sourceId = request.getParameter("sourceId");
        String errorMessage = BAD_MCID;
        
	String next = request.getParameter("next");
        log.info("next:" + next);
        for (int i = 0; i < INFO_KEYS.length; i++) {
            String value = request.getParameter(INFO_KEYS[i]);
            userInfo.put(INFO_KEYS[i], value);
            
        }
        userInfo.put("serverName", request.getServerName());

        if (mcid == null) {
        	log.info("mcid is null");
            request.setAttribute("userId", userId);
            request.setAttribute("sourceId", sourceId);
            request.setAttribute("loginError", LOGIN_ERROR);
	    request.setAttribute("next", next);
            super.doGet(request, response);
            return;
        }
        
        mcid = mcid.trim();
        
        request.setAttribute("mcid", mcid);
        
        if (mcid.length() == 0) {
        	log.info("mcid length is zero");
            request.setAttribute("userId", userId);
            request.setAttribute("sourceId", sourceId);
            request.setAttribute("loginError", LOGIN_ERROR);
	    request.setAttribute("next", next);
            super.doGet(request, response);
            return;
        }
        
        try {
            IdentityConnection c = getConnection();
            
            try {
                /* if not an email address */
                if (mcid.indexOf('@') == -1) {
                    mcid = IdentityServlet.SmoothMCID(mcid);
                    log.info("Smoothid is " + mcid);
                    if (mcid == null) {
                        request.setAttribute("userId", userId);
                        request.setAttribute("sourceId", sourceId);
                        request.setAttribute("loginError",
                        "MCIDs have 16 digits");
			request.setAttribute("next", next);
                        super.doGet(request, response);
                        releaseConnection(c);
                        log.info("MCIDs have 16 digits");
                        return;
                    }
                    
                    errorMessage = BAD_MCID;
                    log.info("errorMessage is " + errorMessage);
                }
                
                else {
		    String email = mcid;
                    mcid = c.getMCID(email);

                    /*
                     * do not differentiate between bad emails, can't find
                     * MCIDs, and bad passwords.  This prevents fishing for
                     * valid MCIDs or valid emails
                     */
                    if (mcid == null) {
                        request.setAttribute("userId", userId);
                        request.setAttribute("sourceId", sourceId);
                        request.setAttribute("loginError",
                                        BAD_EMAIL);
			request.setAttribute("next", next);
                        super.doGet(request, response);
                        releaseConnection(c);
                        log.info("mcid from email:" + email + " is null");
                        return;
                    }
                    
                    errorMessage = BAD_EMAIL;
                }
                
                /* valid MCID */
                String url = c.login(mcid, request.getParameter("password"), userInfo);
                
                if (IdentityConnection.SMS.equals(url)) {
                    String code = OneTimeCode.NextHash(mcid);
                    
                    releaseConnection(c);
                    
                    mail((String) userInfo.get("mobile"), code);
                    
                    request.setAttribute("mcid", mcid);
                    request.setAttribute("email", (String) userInfo.get("email"));
                    request.getRequestDispatcher(SMS_JSP_PAGE).forward(request,
                                    response);
                    log.info("Seems to be valid");
                    return;
                }
                else if (url != null) {
		    if (next != null && next.length() > 0)
			url = next;

                    /* successful login, link user if we have SAML cookies */
                    if (userId != null && sourceId != null)
                        c.linkUser(mcid, sourceId, userId);
                    
                    userInfo.put("source_name", c.getSourceName(sourceId));

                    c.log(mcid, userId, sourceId, "login");

                    releaseConnection(c);

                    login(response, url, userInfo);
                    return;
                }

                releaseConnection(c);
            } catch (SQLException ex) {
                request.setAttribute("sqlError", ex.toString());
                log.error("sqlErr:" + ex.toString(), ex);
                ex.printStackTrace(System.err);
                c.close();
            }
            catch (DisabledAccountException e) {
                errorMessage = ACCOUNT_DISABLED;
            }
            catch (RESTException ex) {
                request.setAttribute("sqlError", ex.toString());
                log.error("sqlErr:" + ex.toString(), ex);
                ex.printStackTrace(System.err);
                c.close();
            }
        } catch (SQLException ex) {
            request.setAttribute("sqlError", ex.toString());
            log.error("sqlErr:" + ex.toString(), ex);
            ex.printStackTrace(System.err);
        } catch (InterruptedException ex) {
            request.setAttribute("sqlError", ex.toString());
            log.error("sqlErr:" + ex.toString(), ex);
            ex.printStackTrace(System.err);
        }
        
        request.setAttribute("userId", userId);
        request.setAttribute("sourceId", sourceId);
        request.setAttribute("loginError", errorMessage);
	request.setAttribute("next", next);
        
        super.doGet(request, response);
    }
    
    private void mail(String email, String code) {
        Runtime r = Runtime.getRuntime();
        
        try {
            r.exec("/home/terry/bin/smsmail " + email + " " + code);
        } catch (Exception ex) {
            ex.printStackTrace(System.err);
            log.error("sqlErr:" + ex.toString(), ex);
        }
    }
    
}
