package org.medcommons.modules.zxid;





/*
 * net/medcommons/modules/zxid/ZXIDSessionFilter.java
 * Copyright(c) 2007, Medcommons, Inc.
 * 
 * Uncertain about some aspects of design:
 * <ul>
 * <li> Should attributes be set before the chain continues?
 * <li> If so - should there be any relationship with the 
 *      existing attributes like hmac_verified, ts_verified?
 * </ul>
 */


import java.io.IOException;

import java.util.Enumeration;
import java.util.Hashtable;
import java.util.Map;
import java.util.StringTokenizer;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.ServletException;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletRequestWrapper;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;



/**
 *
 */
public class ZXIDSessionFilter implements Filter {
	 private static Logger logger = Logger.getLogger("ZXIDSessionFilter");
	 FilterConfig filterConfig = null;
	 String zxidConf;
	 Hashtable<String, String> filterParameters = new Hashtable<String,String>();
	 
    public void init(FilterConfig filterConfig) {
    	this.filterConfig = filterConfig;
    	this.zxidConf = filterConfig.getInitParameter("zxidConf");
    	logger.info("ZXIDSessionFilter:init: zxidConf=" + zxidConf);
    
    	filterParameters.put("o", "");
    	filterParameters.put("e", "");
    	filterParameters.put("SAMLart", "");
    	filterParameters.put("fn", "");
    	filterParameters.put("fq", "");
    	filterParameters.put("fa", "");
    	filterParameters.put("fm", "");
    	filterParameters.put("fp", "");
    	filterParameters.put("ff", "");
    	filterParameters.put("Signature", "");
    	filterParameters.put("SigAlg", "");
    }

    public void doFilter(ServletRequest req, ServletResponse resp,
			 FilterChain chain)
	throws IOException, ServletException {
    	logger.info("doFilter");
	HttpServletRequest hreq = (HttpServletRequest) req;
	String queryString = getQueryString(hreq);
	

	if (queryString == null) {
	    chain.doFilter(req, resp);
	    return;
	}
	int autoFlags = 0x1d54;
	String dynamicConf = getDynamicConf(hreq);
	String conf = dynamicConf;
	logger.info("Dynamic conf = " + dynamicConf);
	logger.info("zxid    conf = " + zxidConf);
	ZxidResult result = ZxidLibrary.do_zxid(conf,queryString, autoFlags);
	HttpServletResponse response = (HttpServletResponse) resp;
	hreq.setAttribute("zxidConf", conf);
	switch (result.getResultType()) {
	case REDIRECT:
		 logger.info("REDIRECT to " + result.getContents());
		 if (result.getContents() != null)
			 response.sendRedirect(result.getContents());
		 else
			 throw new NullPointerException("No redirection URL returned (probably automation flags set incorrectly)");
		 break;
	case OUTPUT:
		 logger.info("OUTPUT: ");
		 response.setContentType("text/html");
		 response.getOutputStream().print(result.getContents());
		 break;
	case ERROR:
		logger.info("ERROR: " + result.getContents());
		response.setStatus(500);
		response.setContentType("text/html");
		response.getOutputStream().print("<html><body>ERROR</body></html>");
		break;
	case LOGGED_IN:
		logger.info("LOGGED IN:");
		String ldif = result.getContents();
		hreq.setAttribute("mcAuthenticatedUser", parse(ldif));
		chain.doFilter(req, resp);
		break;
	default:
		logger.info("Other (unhandled) response: " + result.getResultType());
		response.setStatus(501);
		response.setContentType("text/html");
		response.getOutputStream().print(result.getContents());
		break;
	}

    }
    
    /**
     * Creates a  hashtable from the ldif.
     * This is fairly generic - other authentication mechanisms
     * could also put hashtables in the http request.
     * If they have a common name structure we can standardize
     * the back end.
     */
    public Hashtable<String,String> parse(String ldif){
    	Hashtable<String, String> properties = new Hashtable<String, String>();
		StringTokenizer st = new StringTokenizer(ldif, "\n");
		while (st.hasMoreElements()){
			String line = st.nextToken();
			int delim = line.indexOf(":");
			if (delim == -1){
				throw new IndexOutOfBoundsException("No token ':' on line " + line);
			}
			String key = line.substring(0,delim);
			String value = line.substring(delim+1).trim();
			logger.info("key='" + key + "', value='" + value + "'");
			properties.put(key, value);
			
		}
		return(properties);
	}

    //PATH=/var/zxid/&URL=http://medcommons2:8091/zxidservlet/filter/hello

    private String getDynamicConf(HttpServletRequest hreq) throws IOException{
    	
    	String scheme = hreq.getScheme();             // http
        String serverName = "medcommons2";//hreq.getServerName()();     // hostname.com
        int serverPort = hreq.getServerPort();        // 80
        String contextPath = hreq.getContextPath();   // /mywebapp
        String servletPath = hreq.getServletPath();   // /servlet/MyServlet
        String pathInfo = hreq.getPathInfo();         // /a/b;c=123
        String queryString = getFilteredParameters(hreq);        // d=789
        String originalQueryString = getQueryString(hreq);
        logger.info("filtered query string:" + queryString);
        logger.info("original query string:" + originalQueryString);
    
        // Reconstruct original requesting URL
        String url = scheme+"://"+serverName+":"+serverPort+contextPath+servletPath;
        if (pathInfo != null) {
            url += pathInfo;
        }
        /*
        if (queryString != null) {
            url += "?"+queryString;
        }
        */
        String conf = "PATH=/var/zxid/&URL=" + url;
    	return(conf);
    }
    private String getFilteredParameters(HttpServletRequest hreq){
    	Hashtable<String,Object> parameters = new Hashtable<String,Object>();
    	parameters.putAll(hreq.getParameterMap());
    	Enumeration keys = parameters.keys();
    	while(keys.hasMoreElements()){
    		// If any of the keys are in the filter
    		// list, remove them.
    		String key = (String)keys.nextElement();
    		String avoidVal = filterParameters.get(key);
    		if (avoidVal != null)
    			parameters.remove(key);
    	}
    	keys = parameters.keys();
    	boolean first = true;
    	StringBuffer buff = new StringBuffer();
    	while(keys.hasMoreElements()){
    		// If any of the keys are in the filter
    		// list, remove them.
    		String key = (String)keys.nextElement();
    		String value = hreq.getParameter(key);
    		if (first){
    			first = false;
    		}
    		else{
    			buff.append("&");
    		}
    		buff.append(key);
    		buff.append("=");
    		buff.append(value);
    		
    		
    	}
    	if (buff.length() == 0) 
    		return(null);
    	else
    		return(buff.toString());
    }
    private String getQueryString(HttpServletRequest hreq) throws IOException{
    	String queryString = null;
    	if (hreq.getMethod().equalsIgnoreCase("get"))
    		queryString = hreq.getQueryString();
    	else if (hreq.getMethod().equalsIgnoreCase("post")){
    		int len = hreq.getContentLength();
    		byte[] b = new byte[len];
    		int got = hreq.getInputStream().read(b, 0, len);
    		if (got<0)
    			return(null);
    		else
    			queryString = new String(b, 0, got);
    	}
    	else{
    		throw new RuntimeException("Unsupported HTTP method:" + hreq.getMethod());
    	}
    	return(queryString);
    }

    public void destroy() {
    }
}




