/* 
 * Derived from:
 * zxidhlo.java  -  Hellow World Java/Tomcat servlet script that calls libzxid using JNI
 * Copyright (c) 2007 Symlabs (symlabs@symlabs.com), All Rights Reserved.
 * Author: Sampo Kellomaki (sampo@iki.fi)
 * This is confidential unpublished proprietary source code of the author.
 * NO WARRANTY, not even implied warranties. Contains trade secrets.
 * Distribution prohibited unless authorized in writing.
 * Licensed under Apache License 2.0, see file COPYING.
 * $Id: zxidhlo.java,v 1.4 2007/02/21 06:33:12 sampo Exp $
 * 12.1.2007, created --Sampo
 *
 * See also: README-zxid section 10 "zxid_simple() API"
 */
package org.medcommons.modules.zxid;
// import zxidjava.*;
import java.io.IOException;
import java.util.Enumeration;
import java.util.Hashtable;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

/**
 * Example servlet which shows the user of the ZXIDSessionFilter
 * 
 * All context information about login is passed down in HttpServletRequest
 * 
 * @author mesozoic
 *
 */
public class filterHelloServlet extends HttpServlet {
   
    private static Logger logger = Logger.getLogger(filterHelloServlet.class);

    private String tempOutput_Start = "<html><body><h1>filterHelloServlet</h1>";
    private String tempOutput_Stop=	 "</body></html>";
    
    public void doGet(HttpServletRequest req, HttpServletResponse res)
	throws ServletException, IOException
    {
    	String qs = req.getQueryString();
	logger.info("Start GET. qs=" + qs);
	
	String user = formatUserInfo(req, "mcAuthenticatedUser");
	
	if (user != null){
		qs = qs + "\n" + user;
		}
		else{
			logger.info("No properties defined for mcAuthenticatedUser" );
		}
	
	//ZxidResult result = ZxidLibrary.do_zxid(req.getQueryString(), autoFlags);
	
	//handleZxidResult(req, res, result);
	res.setContentType("text/html");
	res.getOutputStream().print(generateOutput(qs));
	logger.info("End GET...\n");
    }

    public void doPost(HttpServletRequest req, HttpServletResponse res)
	throws ServletException, IOException
    {
	
	String qs;
	int len = req.getContentLength();
	byte[] b = new byte[len];
	// Need to make sure this isn't too large. Perhaps 
	// search through input to get input that zxid would care 
	// about?
	int got = req.getInputStream().read(b, 0, len);
	qs = new String(b, 0, got);
	logger.info("Start POST. qs="+qs);
	String user = formatUserInfo(req, "mcAuthenticatedUser");
	if (user != null){
		qs = qs + "\n" + user;
	}
	
	res.setContentType("text/html");
	res.getOutputStream().print(generateOutput(qs));
	
	
	
	logger.info("End POST...\n");
    }
   
    private String generateOutput(String queryString){
    	StringBuffer buff = new StringBuffer(tempOutput_Start);
    	buff.append(queryString);
    	buff.append(tempOutput_Stop);
    	return(buff.toString());
    }
    private String formatUserInfo(HttpServletRequest req, String name){
    	Object obj = req.getAttribute(name);
    	
    	if (obj == null) return(null);
    	else{
    		StringBuffer buff = new StringBuffer(name);
    		
    		if (obj instanceof Hashtable){
    			Hashtable<String,String> properties = (Hashtable)obj;
    			Enumeration<String> keys = properties.keys();
    			while (keys.hasMoreElements()){
    				String key = (String) keys.nextElement();
    				String value = (String) properties.get(key);
    				buff.append("\n<br>");
    				buff.append("key ='");buff.append(key); buff.append("', value='");buff.append(value); buff.append("'");
    				
    				
    			}
    			
				String sessionId = (String) properties.get("sesid");
				String zxidConf = (String) req.getAttribute("zxidConf");
				logger.info("sesid = " + sessionId);
				logger.info("zxidConf = " + zxidConf);
				if ((sessionId != null) &&(zxidConf != null)){
					int autoFlag = 0x1d54;
					String logout = ZxidLibrary.fedMgmt(zxidConf, sessionId, autoFlag);
					buff.append(logout);
				}
    			
    		}
    		return(buff.toString());
    	}
    }

  

}

/* EOF */
