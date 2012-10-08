/*
 * SMSAuthServlet.java
 * Copyright(c) 2006, Terence Way
 */
package net.medcommons.identity;

import java.io.IOException;

import java.sql.SQLException;

import java.util.Map;
import java.util.HashMap;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import net.medcommons.modules.services.utils.RESTException;

/**
 * Class documentation
 *
 * @author Terence Way
 */
public class SMSAuthServlet extends IdentityServlet {
    protected static final String SMS_JSP_PAGE = "/WEB-INF/jsp/sms.jsp";

    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
	throws ServletException, IOException {

	String mcid = req.getParameter("mcid");
	String email = req.getParameter("email");
	String code = req.getParameter("code");

	if (mcid != null && code != null) {
	    mcid = mcid.trim();
	    code = code.trim().toUpperCase();

	    String codes[] = OneTimeCode.HashList(mcid, 3);

	    for (int i = 0; i < codes.length; i++) {
		if (code.equals(codes[i])) {

		    try {
			IdentityConnection c = getConnection();

			try {
			    Map userInfo = new HashMap();
			    String url = c.sms(mcid, userInfo);

			    if (url != null) {
				/* known user */
				releaseConnection(c);
				login(resp, url, userInfo);
				return;
			    }

			    releaseConnection(c);
			} catch (SQLException ex) {
			    req.setAttribute("sqlError", ex.toString());
			    ex.printStackTrace(System.err);
			    c.close();
			}
            catch (RESTException ex) {
			    req.setAttribute("sqlError", ex.toString());
			    ex.printStackTrace(System.err);
			    c.close();
            }
		    } catch (SQLException ex) {
			req.setAttribute("sqlError", ex.toString());
			ex.printStackTrace(System.err);
		    } catch (InterruptedException ex) {
			req.setAttribute("sqlError", ex.toString());
			ex.printStackTrace(System.err);
		    }
		}
	    }

	    /* invalid login */
	}

	req.setAttribute("mcid", mcid);
	req.setAttribute("email", email);
	req.getRequestDispatcher(SMS_JSP_PAGE).forward(req, resp);
    }
}
