package net.medcommons.identity;

import java.io.IOException;
import java.util.Map;

import java.sql.SQLException;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * The Logout servlet clears all cookies, and presents the user
 * with the main login page.
 *
 * @see LoginServlet
 * @see RegisterServlet
 */
public class PasswordServlet extends IdentityServlet {

    protected static final String PASSWORD_PAGE = "/WEB-INF/jsp/password.jsp";

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
	throws ServletException, IOException {
	Map userInfo = getUsersSessionInfo(request);

	clearUserSessionInfo(request, response, userInfo);
	request.setAttribute("userInfo", userInfo);

	request.setAttribute("link", request.getParameter("link"));

	/* present user with the password change page */
	request.getRequestDispatcher(PASSWORD_PAGE).forward(request, response);
    }

    private static final String BAD_PASSWORD = "MCID, email, or password not correct";

    protected void doPost(HttpServletRequest request, HttpServletResponse response)
	throws ServletException, IOException {

	String mcid = request.getParameter("mcid");
	String link = request.getParameter("link");

	String pw0 = request.getParameter("password0");
	String pw1 = request.getParameter("password1");
	String pw2 = request.getParameter("password2");

	if (link != null)
	    request.setAttribute("link", link);

	if (mcid == null) {
	    request.setAttribute("mcidError", "To change your password, you must enter either your 16-digit MCID, or your email address.");
	    doGet(request, response);
	    return;
	}

	mcid = mcid.trim();
	request.setAttribute("mcid", mcid);

	if (mcid.length() == 0) {
	    request.setAttribute("mcidError", "To change your password, you must enter either your 16-digit MCID, or your email address.");
	    doGet(request, response);
	    return;
	}

	if (pw1 == null || pw1.length() < 6) {
	    request.setAttribute("pw1Error", "Passwords must be at least 6 characters.");
	    doGet(request, response);
	    return;
	}

	if (!pw1.equals(pw2)) {
	    request.setAttribute("pw2Error", "Passwords must match.");
	    doGet(request, response);
	    return;
	}

	try {
	    IdentityConnection c = getConnection();

	    try {
		if (mcid.indexOf('@') == -1) {
		    mcid = IdentityServlet.SmoothMCID(mcid);

		    if (mcid == null) {
			request.setAttribute("mcidError", "MCIDs have 16 digits");
			doGet(request, response);
			releaseConnection(c);
			return;
		    }
		}
		else {
		    mcid = c.getMCID(mcid);

		    if (mcid == null) {
			request.setAttribute("mcidError", BAD_PASSWORD);
			doGet(request, response);
			releaseConnection(c);
			return;
		    }
		}

		/*
		 * if password change successful, go to
		 * another page.
		 */
		if (c.changePassword(mcid, pw0, pw1)) {
		    response.sendRedirect(response.encodeRedirectURL(link));
		    releaseConnection(c);
		    return;
		}

		request.setAttribute("mcidError", BAD_PASSWORD);

		releaseConnection(c);
	    } catch (SQLException ex) {
		request.setAttribute("sqlError", ex.toString());
		ex.printStackTrace(System.err);
		releaseConnection(c);
	    }
	} catch (Exception ex) {
	    request.setAttribute("sqlError", ex.toString());
	    ex.printStackTrace(System.err);
	}

	doGet(request, response);
    }

}
