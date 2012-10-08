package net.medcommons;

import java.io.IOException;

import java.util.Enumeration;
import java.util.Hashtable;
import java.util.Map;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.ServletException;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletRequestWrapper;

import net.medcommons.Session;

public class SessionFilter implements Filter {
    public void init(FilterConfig filterConfig) {
    }

    public void doFilter(ServletRequest req, ServletResponse resp,
			 FilterChain chain)
	throws IOException, ServletException {

	HttpServletRequest hreq = (HttpServletRequest) req;
	String queryString = hreq.getQueryString();

	if (queryString == null) {
	    chain.doFilter(req, resp);
	    return;
	}

	if (queryString.indexOf("ts=") >= 0) {
	    if (!Session.IsQueryStringCurrent(queryString))
		throw new ServletException("bad timestamp");

	    hreq.setAttribute("ts_verified", Boolean.TRUE);
	}
	else
	    hreq.setAttribute("ts_verified", Boolean.FALSE);

	if (queryString.indexOf("hmac=") >= 0) {
	    if (!Session.IsSignedQueryStringValid("secret", queryString))
		throw new ServletException("bad hmac signature");

	    hreq.setAttribute("hmac_verified", Boolean.TRUE);
	}
	else
	    hreq.setAttribute("hmac_verified", Boolean.FALSE);

	if (queryString.indexOf("enc=") >= 0) {
	    Hashtable parameters = new Hashtable();
	    String enc = Session.GetEncryptedQueryString(queryString,
							 "secret");

	    parameters.putAll(hreq.getParameterMap());
	    ParseParameters(enc, parameters);
	    System.out.println("enc=" + enc);

	    hreq.setAttribute("enc_verified", Boolean.TRUE);

	    chain.doFilter(new SessionRequestWrapper(hreq,
						     enc + '&' + queryString,
						     parameters), resp);
	}
	else {
	    hreq.setAttribute("enc_verified", Boolean.FALSE);

	    chain.doFilter(req, resp);
	}
    }

    static void ParseParameters(String queryString, Hashtable map) {
	int start = 0, i = queryString.indexOf('=');

	while (i >= 0) {
	    int j = queryString.indexOf('&', i);

	    String key = queryString.substring(start, i), value;

	    if (j < 0) {
		AddParameter(map, key, queryString.substring(i + 1));
		break;
	    }

	    AddParameter(map, key, queryString.substring(i + 1, j));

	    start = j + 1;
	    i = queryString.indexOf('=', start);
	}
    }

    static final void AddParameter(Hashtable map, String key,
				   String nextValue) {
	String[] values = (String[]) map.get(key);

	if (values == null) {
	    values = new String[1];
	    values[0] = nextValue;
	}
	else {
	    String[] newValues = new String[values.length + 1];

	    System.arraycopy(values, 0, newValues, 0, values.length);
	    newValues[values.length] = nextValue;

	    values = newValues;
	}

	map.put(key, values);
    }

    public void destroy() {
    }
}

class SessionRequestWrapper extends HttpServletRequestWrapper {
    private String queryString;
    private Hashtable parameters;

    SessionRequestWrapper(HttpServletRequest req, String queryString,
			  Hashtable parameters) {
	super(req);

	this.queryString = queryString;
	this.parameters = parameters;
    }

    public String getQueryString() {
	return this.queryString;
    }

    public String getParameter(String name) {
	String[] values = (String[]) this.parameters.get(name);

	return values != null ? values[0] : null;
    }

    public String[] getParameterValues(String name) {
	return (String[]) this.parameters.get(name);
    }

    public Enumeration getParameterNames() {
	return this.parameters.keys();
    }

    public Map getParameterMap() {
	return this.parameters;
    }

}
