package org.medcommons.modules.zxid;

import java.io.IOException;

import javax.servlet.ServletException;

import org.apache.log4j.Logger;

import zxidjava.zxidjni;

/**
 * Simple wrapper around the zxid 'simple' API.
 * All native methods are acccessed via this API.This class is loaded once by the classloader
 * and may be shared between multiple webapps.
 * 
 * @author mesozoic
 *
 */
public class ZxidLibrary {
	 static { System.loadLibrary("zxidjni"); } 
	 private static Logger logger = Logger.getLogger(ZxidLibrary.class);
	 //static final String conf = "PATH=/var/zxid/&URL=http://medcommons2:8091/zxidservlet/hello";
	 
	 /**
	  * 
	  * @param qs
	  * @param autoFlags 0x1d54 returns a lot of debugging info
	  * @return
	  * @throws ServletException
	  * @throws IOException
	  */
	 public static ZxidResult do_zxid(String conf, String qs, int autoFlags)
		throws ServletException, IOException
	    {
		
		//String ret = zxidjni.simple(conf, qs, 0xd54);
		 logger.info("Query string \n" + qs );
		 ZxidResult result = new ZxidResult();
		 
		 result.setRawOutput(zxidjni.simple(conf, qs, autoFlags));
		
		logger.info("Query string \n" + qs + "\n results in zxid output \n" + result.getRawOutput());
		String rawOutput = result.getRawOutput();
		switch (rawOutput.charAt(0)) {
		case 'L':  /* Redirect: ret == "LOCATION: urlCRLF2" */
			result.setResultType(ZxidResult.ResultType.REDIRECT);
			result.setContents(rawOutput.substring(10, rawOutput.length() - 4));
		    
		    return (result);
		case '<':
			result.setResultType(ZxidResult.ResultType.OUTPUT);
		    switch (rawOutput.charAt(1)) {
		    case 's':  /* <se:  SOAP envelope */
		    case 'm':  /* <m20: metadata */
		    	result.setContentType("text/xml");
		    	break;
		    default:
		    	result.setContentType("text/html");
				break;
		    }
		    result.setContents(rawOutput);
		   // res.setContentLength(rawOutput.length());
		    //res.getOutputStream().print(ret);
		    break;
		case 'd': /* Logged in case */
		    //my_parse_ldif(res);
			  int x = rawOutput.indexOf("\nsesid: ");
	          int y = rawOutput.indexOf('\n', x + 8);
	          String sid = rawOutput.substring(x + 8, y);

			result.setResultType(ZxidResult.ResultType.LOGGED_IN);
			result.setContentType("text/html");
		    result.setContents(rawOutput);
		    result.setConf(conf);
		    result.setSessionId(sid);
		   
		    break;
		case 'e':
			result.setResultType(ZxidResult.ResultType.REDIRECT);
			result.setContents(null);
			
		default:
			result.setResultType(ZxidResult.ResultType.ERROR);
			result.setContents("Unknown zxid_simple() response:" + rawOutput);
		    
		    
		}
		return(result);
	    }

	 public static String fedMgmt(String conf, String sid, int autoFlags){
		 return(zxidjni.fed_mgmt(conf, sid, autoFlags));
	 
	 }
}
	 
