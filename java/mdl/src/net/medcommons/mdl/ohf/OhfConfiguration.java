package net.medcommons.mdl.ohf;

/*******************************************************************************
 * Copyright (c) 2000, 2005 Jiva Medical and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *     Jiva Medical - initial API and implementation
 *******************************************************************************/


import java.net.URI;
import java.net.URISyntaxException;

import com.sun.net.ssl.SSLContext;




/**
 * This is a consolidated configuration file for running the sample code. You
 * may need to edit this class to get the examples working on your own system.
 * Minimally, you will likely need to change the workspace DATA_PATH. 
 * 
 * If you have your own HL7Definitions file, the location of the file should 
 * also be specified here and HAS_ACCESSFILE changed to 'true'.
 * 
 * The IBM Dallas Server is available for public testing under the use terms
 * provided on the website given below.
 * 
 * @author <a href="mailto:srrenly@us.ibm.com">Sondra Renly</a>
 */

public class OhfConfiguration {

	//basics
	public static final String DATA_PATH = "webapps/mdl/WEB-INF/conf/";
	public static final String LOG4J_PATH = DATA_PATH  + "/pdqquery_log4j.xml";
	
	//HL7PdqQuery - run from file
	public static final String HL7FILE_PATH = DATA_PATH + "/QBP-Q22(find candidates).hl7";
	
	//Enable examples using the Access DB File 
	//This file is licensed through HL7 and not provided as part of this plugin
	public static final boolean HAS_ACCESSFILE = false;
	public static final String ACCESS_DATABASE_PATH = DATA_PATH + "/hl7_58.mdb";
	
	//Enable examples using the free javaStream file
	//This file is free and provided as part of the org.eclipse.ohf.ihe.common.hl7v2.client plugin
	public static final boolean HAS_JAVASTREAM = true;
    public static final String SERIALISED_PATH = DATA_PATH + "/hl7Definitions.stream";
	
    //Conformance profile for second level HL7 verification used with either Access or javaStream File
    public static final String CPROFILE_PATH = DATA_PATH +  "/QBP-Q22(find candidates).XML";
    public static final String PQQ_PATIENT_NAME = DATA_PATH +  "/QBP-11311.XML";
	
    //ATNA logging
    public static final boolean doAudit = false;
    
    //MLLP Connectivity
	//Default IBM Dallas IHII Server - more connection info available at:
	//http://ibmod235.dal-ebis.ihost.com:9080/IBMIHII/serverInfoOHF.htm
    public static final boolean USE_ATNA_MLLP = false;
	//public static final String MLLP_HOST = "ibmod235.dal-ebis.ihost.com";
    public static final String MLLP_HOST = "mesozoic.homeip.net";
	//Unsecure connection port
	//public static final int MLLP_PORT = 3600;
    public static final int MLLP_PORT = 3700;
	public static URI MLLP_URI;
	static {
		try {
			//MLLP_URI = new URI("mllp", null, "ibmod235.dal-ebis.ihost.com", 3600, null, null, null);
			MLLP_URI = new URI("mllp", null, "mesozoic.homeip.net", 3700, null, null, null);
		} catch (URISyntaxException e) {
			//nothing
		}
	}
	
	public static URI MLLPS_URI;
	static {
		try {
			//MLLPS_URI = new URI("mllps", null, "ibmod235.dal-ebis.ihost.com", 3700, null, null, null);
			MLLPS_URI = new URI("mllps", null, "stegosaurus", 3700, null, null, null);
		} catch (URISyntaxException e) {
			//nothing
		}
	}
	
	//TLS: IBM JRE Secure connection parameters
	public static final int MLLP_SECUREPORT = 3700;
	public static final String MLLP_KEYSTORE_NAME = DATA_PATH + "/org.eclipse.ohf.ihe.common.mllp/resources/security/ibm0.jks";
	public static final String MLLP_KEYSTORE_PASSWORD = "ibm0";
	public static final String MLLP_TRUSTSTORE_NAME = DATA_PATH + "/org.eclipse.ohf.ihe.common.mllp/resources/security/ihiissltrusts.jks";
	public static final String MLLP_TRUSTSTORE_PASSWORD = "ihiissltrusts";
	public static final boolean MLLP_NEEDS_CLIENT_AUTH = true;
	public static final String MLLP_PROTOCOL = "TLS";
	public static final String MLLP_SSLPROVIDER_NAME = "IBMJSSE2";
	public static final String MLLP_SSLPROVIDER_CLASS = "com.ibm.jsse2.IBMJSSEProvider2";

	//TLS: SUN JRE Secure connection parameters
	//public static final int MLLP_SECUREPORT = 3700;
	//public static final String MLLP_KEYSTORE_NAME = DATA_PATH + "/org.eclipse.ohf.ihe.common.mllp/resources/security/ibm0.jks";
	//public static final String MLLP_KEYSTORE_PASSWORD = "ibm0";
	//public static final String MLLP_TRUSTSTORE_NAME = DATA_PATH + "/org.eclipse.ohf.ihe.common.mllp/resources/security/ihiissltrusts.jks";
	//public static final String MLLP_TRUSTSTORE_PASSWORD = "ihiissltrusts";
	//public static final boolean MLLP_NEEDS_CLIENT_AUTH = true;
	//public static final String MLLP_PROTOCOL = "TLS";
	
	//public static String MLLP_SSLPROVIDER_NAME;
	//public static String MLLP_SSLPROVIDER_CLASS;
	
	//static {
		//try {
			//MLLP_SSLPROVIDER_NAME = SSLContext.getInstance("SSLv3").getProvider().toString();
			//MLLP_SSLPROVIDER_CLASS = SSLContext.getInstance("SSLv3").getClass().toString();
		//} catch (Exception e) {
			//nothing
		//}
	//}
	
}

