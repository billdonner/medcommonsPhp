package net.medcommons.identity;

import java.io.IOException;

import java.sql.SQLException;

import java.util.Map;
import java.util.HashMap;

import javax.servlet.ServletConfig;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import net.medcommons.modules.services.utils.RESTException;

/**
 * This servlet is the action for the register form.  New users
 * enter their email and verify a new password, and are given an
 * MCID.
 *
 * <p>
 * MCIDs are generated using the MCIDGenerator class.
 *
 * @see net.medcommons.identity.MCIDGenerator
 */
public class RegisterServlet extends IdentityServlet {
    
    /** Location in web archive of login/register page. */
    protected static final String REGISTER_JSP_PAGE = "/WEB-INF/jsp/register.jsp";

    /* fields to copy from form back out so form values stay the same */
    private static final String[] USER_FIELDS = {
	"first_name", "last_name", "smsnumber", "smslogin"
    };

    private static final String[] ADDRESS_FIELDS = {
	"comment",
	"address1",
	"address2",
	"city",
	"state",
	"postcode",
	"country",
	"telephone"
    };

    protected void doGet(HttpServletRequest request,
			 HttpServletResponse response)
	throws ServletException, IOException {

	request.getRequestDispatcher(REGISTER_JSP_PAGE).forward(request,
								response);
    }

    private static final String BLANK_MOBILE = "";

    private static String getMobile(HttpServletRequest request) {
	String provider = request.getParameter("smsprovider");
	String mobile = request.getParameter("smsnumber");
	int i, max;
	StringBuffer sb = new StringBuffer(10);

	if (mobile == null)
	    return BLANK_MOBILE;

	mobile = mobile.trim();
	if (mobile.equals(""))
	    return mobile;

	if (mobile.indexOf('@') > 0) {
	    /* email address directly in mobile, all good, ignore provider */
	    return mobile;
	}

	if ("other".equals(provider)) {
	    /* bad: other requires an email address */
	    request.setAttribute("smsError",
				 "Please enter an email address for SMS messages");
	    return null;
	}

	max = mobile.length();
	for (i = 0; i < max; i++) {
	    char ch = mobile.charAt(i);

	    if (Character.isDigit(ch))
		sb.append(ch);
	    else if (Character.isLetter(ch)) {
		request.setAttribute("smsError",
				     "Mobile number should be an email " +
				     "address, or a 10-digit phone number");
		return null;
	    }
	}

	/* remove leading 1 for numbers like '+1 (808) 443-6443' */
	if (sb.length() == 11 && sb.charAt(0) == '1')
	    sb.deleteCharAt(0);

	if (sb.length() != 10) {
	    request.setAttribute("smsError",
				 "Mobile number should be an email " +
				 "address, or a 10-digit phone number");
	    return null;
	}

	sb.append(provider);

	return sb.toString();
    }

    /**
     * The main entry point for people registering with MedCommons.
     */
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
	throws ServletException, IOException {

	String email = request.getParameter("email");
	String password1 = request.getParameter("password1");
	String password2 = request.getParameter("password2");
	String userId = request.getParameter("userId");
	String sourceId = request.getParameter("sourceId");

	Map userInfo = new HashMap();

	request.setAttribute("email", email);
	userInfo.put("email", email);
	userInfo.put("userId", userId);
	userInfo.put("sourceId", sourceId);

	for (int i = 0; i < USER_FIELDS.length; i++)
	    userInfo.put(USER_FIELDS[i],
			 request.getParameter(USER_FIELDS[i]));

	for (int i = 0; i < ADDRESS_FIELDS.length; i++)
	    userInfo.put(ADDRESS_FIELDS[i],
			 request.getParameter(ADDRESS_FIELDS[i]));

	if (email == null) {
	    request.setAttribute("emailError",
				 "You must enter an email address.");
	}

	else if (email.indexOf('@') == -1) {
	    /* all emails have a '@', no? */
	    request.setAttribute("emailError",
				 "You must enter a valid email address.");
	}

	else {
	    String mobile = getMobile(request);
	    email = email.trim();

	    if (password1 == null) {
		/* we've just been directed here by the login page...
		 * no error msg necessary */
	    }
	    else if (password1.length() < 6) {
		/* passwords need to be suitably complex */
		request.setAttribute("pw1Error",
				  "Passwords must be at least 6 characters.");
	    }

	    else if (!password1.equals(password2)) {
		/* passwords don't match */
		request.setAttribute("pw2Error", "Passwords must match.");
	    }

	    else if (mobile != null) {
		if (mobile == BLANK_MOBILE)
		    mobile = null;

		userInfo.put("mobile", mobile);

		try {
		    IdentityConnection c = getConnection();

		    try {
		        String mcid = MCIDGenerator.getInstance().nextMCIDString();
		        String url = c.newUser(mcid, email, password1,
		                        userInfo);

		        if (userId != null && sourceId != null)
		            c.linkUser(mcid, sourceId, userId);

		        if (!userInfo.get("comment").equals("None"))
		            c.addAddress(mcid, userInfo);

		        userInfo.put("source_name", c.getSourceName(sourceId));

		        c.log(mcid, userId, sourceId, "register");

		        releaseConnection(c);
		        login(response, url, userInfo);
		        return;
		    } catch (SQLException ex) {
		        request.setAttribute("sqlError", ex.toString());
		        ex.printStackTrace(System.err);
		        c.close();
		    }
		    catch (RESTException e) {
		        request.setAttribute("sqlError", e.toString());
		        e.printStackTrace(System.err);
		        c.close();
		    }
		} catch (InterruptedException ex) {
		    request.setAttribute("sqlError", ex.toString());
		    ex.printStackTrace(System.err);
		} catch (SQLException ex) {
		    request.setAttribute("sqlError", ex.toString());
		    ex.printStackTrace(System.err);
		}
	    }
	}

	request.setAttribute("userId", userId);
	request.setAttribute("sourceId", sourceId);

	for (int i = 0; i < USER_FIELDS.length; i++)
	    request.setAttribute(USER_FIELDS[i],
				 userInfo.get(USER_FIELDS[i]));

	for (int i = 0; i < ADDRESS_FIELDS.length; i++)
	    request.setAttribute(ADDRESS_FIELDS[i],
				 userInfo.get(ADDRESS_FIELDS[i]));

	request.getRequestDispatcher(REGISTER_JSP_PAGE).forward(request,
								response);
    }

}
