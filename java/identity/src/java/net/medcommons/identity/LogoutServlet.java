package net.medcommons.identity;

import java.io.IOException;
import java.util.Map;

import javax.servlet.ServletException;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.sql.SQLException;

/**
 * The Logout servlet clears all cookies, and presents the user
 * with the main login page.
 *
 * @see LoginServlet
 * @see RegisterServlet
 */
public class LogoutServlet extends IdentityServlet {

    protected void doGet(HttpServletRequest request, HttpServletResponse response)
	throws ServletException, IOException {
	Cookie c = new Cookie(COOKIE_NAME, "deleted");

	Map userInfo = getUsersSessionInfo(request);
	Map cookieInfo = getMedcommonsCookie(request);

	String domain = config.getProperty("cookieDomain");

	if (domain != null && !domain.equals(""))
	    c.setDomain(domain);

	c.setPath("/");
	c.setMaxAge(0);

	response.addCookie(c);

	/*
	 * What to do?  Redirect back to referer?  Have ?url=...
	 * parameter?  Or just redirect to 'main' page, the server
	 * specified in the db?
	 */
	String url = request.getParameter("next");

	String mcid = (String) cookieInfo.get("mcid");

	try {
	    IdentityConnection conn = getConnection();

	    try {
		conn.log(mcid, null, null, "logout");

		if (url == null)
		    url = conn.getServerUrl(mcid, userInfo);

		clearUserSessionInfo(request, response, userInfo);
		request.setAttribute("userInfo", userInfo);

		response.sendRedirect(response.encodeRedirectURL(url));

		releaseConnection(conn);
		return;
	    } catch (SQLException ex) {
		request.setAttribute("sqlError", ex.toString());
		ex.printStackTrace(System.err);
		conn.close();
	    }
	} catch (SQLException ex) {
	    request.setAttribute("sqlError", ex.toString());
	    ex.printStackTrace(System.err);
	} catch (InterruptedException ex) {
	    request.setAttribute("sqlError", ex.toString());
	    ex.printStackTrace(System.err);
	}

	clearUserSessionInfo(request, response, userInfo);

	/* present user with the main login page */
	super.doGet(request, response);
    }

}
