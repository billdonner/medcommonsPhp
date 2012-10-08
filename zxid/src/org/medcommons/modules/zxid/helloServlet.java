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

import javax.servlet.ServletConfig;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

public class helloServlet extends HttpServlet {

	private static Logger logger = Logger.getLogger(helloServlet.class);
	private String zxidConf;
	
	public void init(){
		zxidConf = getServletConfig().getInitParameter("zxidConf");
		logger.info("init:zxidConf=" + zxidConf);
	}
	public void doGet(HttpServletRequest req, HttpServletResponse res)
			throws ServletException, IOException {
		logger.info("Start GET...\n");
		// LECP/ECP PAOS header checks
		int autoFlags = 0x1d54;
		ZxidResult result = ZxidLibrary
				.do_zxid(zxidConf,req.getQueryString(), autoFlags);

		handleZxidResult(req, res, result);
		logger.info("End GET...\n");
	}

	public void doPost(HttpServletRequest req, HttpServletResponse res)
			throws ServletException, IOException {
		logger.info("Start POST...\n");
		String qs;
		int len = req.getContentLength();
		byte[] b = new byte[len];
		// Need to make sure this isn't too large. Perhaps 
		// search through input to get input that zxid would care 
		// about?
		int got = req.getInputStream().read(b, 0, len);
		qs = new String(b, 0, got);
		int autoFlags = 0x1d54;
		ZxidResult result = ZxidLibrary.do_zxid(zxidConf, qs, autoFlags);
		handleZxidResult(req, res, result);
		logger.info("End POST...\n");
	}

	String generateLoggedInContent(HttpServletRequest req, ZxidResult result){
		
		String content = "";
		String conf = result.getConf();
		String sessionId = result.getSessionId();
		if ((conf != null) && (sessionId != null)){
			int autoFlags = 0x1d54;
			// This is basically a logout screen.
			content= ZxidLibrary.fedMgmt(result.getConf(), result.getSessionId(), autoFlags);
		}
		else{
			content = "<html><body>This page intentionally left blank</body></html>";
		}
		return(content);
	}
	private void handleZxidResult(HttpServletRequest req,
			HttpServletResponse res, ZxidResult result) throws IOException {
		logger.info("ZxidResult:" + result);
		logger.info("ZxidResult: resultType " + result.getResultType());
		logger.info("ZxidResult: contents " + result.getContents());

		switch (result.getResultType()) {
		case REDIRECT:
			res.sendRedirect(result.getContents());
		case OUTPUT:
			res.setContentType(result.getContentType());
			res.setContentLength(result.getContents().length());
			res.getOutputStream().print(result.getContents());
		case ERROR:
			res.setStatus(500);
			res.setContentType("text/html");
			res.getOutputStream().print(result.getContents());
		case LOGGED_IN:
			res.setContentType(result.getContentType());
			String content = generateLoggedInContent(req, result);
			res.setContentLength(content.length());
			res.getOutputStream().print(content);
		default:
			res.setStatus(501);
			res.setContentType("text/html");
			res.getOutputStream().print(result.getContents());
		}
	}
}
/* EOF */
