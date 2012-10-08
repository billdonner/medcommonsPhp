package net.medcommons.mdl.action;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.rmi.RemoteException;
import java.util.ArrayList;
import java.util.List;

import localhost.bridge.services.ohf_bridge.OHFBridgeServiceLocator;
import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.Configuration;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.ResponseWrapper;
import net.sourceforge.stripes.action.ActionBean;
import net.sourceforge.stripes.action.ActionBeanContext;
import net.sourceforge.stripes.action.Resolution;

import org.apache.log4j.Logger;

import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;

/**
 * Simple ActionBean implementation that all ActionBeans in the MDL
 * will extend.
 *
 * @author mesozoicx
 */
public abstract class MdlAction implements ActionBean {

	private static Logger logger = Logger.getLogger(MdlAction.class);

	private MdlActionContext context;

	private File mdlCacheDirectory = null;

	private File logFile = null;
	
	private boolean useAtna = true;
	
	private boolean useSecureIfAvailable = true;
	
	
    

	//private String defaultRhioFilter = "MC MESA consumer port 3700";

	 //private String defaultRhioFilter = "NIST";
	 
	 //private String defaultRhioFilter = "lswin10-xds";//"ibm";

	 private String defaultRhioFilter = null;

	 public void setDefaultRhioFilter(String defaultRhioFilter){
		 this.defaultRhioFilter = defaultRhioFilter;
	 }
	 public String getDefaultRhioFilter(){
		 return(this.defaultRhioFilter);
	 }
	/** Gets the ActionBeanContext set by Stripes during initialization. */
	public ActionBeanContext getContext() {
		return this.context;
	}

	

    public void setContext(net.sourceforge.stripes.action.ActionBeanContext ctx) {
        this.context = (MdlActionContext)ctx;
        this.context.setSourcePageResolution(this.getSourcePageResolution());
        context.getResponse().setHeader("Cache-Control","no-cache"); // HTTP 1.1
        context.getResponse().setHeader("Pragma","no-cache"); // HTTP 1.0
               
    }
	    
	public RhioConfig[] generateAllRhios(OhfBridgeSoapBindingStub binding)
			throws RemoteException {
		RhioConfig[] allRhios = binding.getRhios();
		return (allRhios);

	}

	public void setLogFile(File logFile) {
		this.logFile = logFile;
	}

	public File getLogFile() {
		return (this.logFile);
	}
	public void setUseAtna(boolean useAtna){
		this.useAtna = useAtna;
	}

	/**
	 * Returns a boolean denoting if the MDL should directly invoke ATNA or not.
	 * The default is true but if there is no ATNA server it should be set to false
	 * in the configuration.
	 * @return
	 */
	public boolean getUseAtna(){
		return(this.useAtna);
	}
	
	public void setUseSecureIfAvailable(boolean useSecureIfAvailable){
		this.useSecureIfAvailable = useSecureIfAvailable;
	}
	
	/**
	 * Uses secure (TLS) connections with PDQ, XDS servers if available.
	 * @return
	 */
	public boolean getUseSecureIfAvailable(){
		return(this.useSecureIfAvailable);
	}
	/**
	 * Returns the set of RHIO objects matching the filter criterion. The filter is simply a
	 * string that matches the start of the name of the specified rhio.
	 * 
	 * @param filter
	 * @param configs
	 * @return
	 */
	public RhioConfig[] filterRhios(String filter, RhioConfig[] configs) {
		if (isEmptyOrNull(filter)) {
			return configs;
		}
		List<RhioConfig> rhioTemp = new ArrayList<RhioConfig>();
		for (int i = 0; i < configs.length; i++) {
			logger.info("filterRhio: " + configs[i].getName() + " starts with " + filter + " ?");
			if (null != configs[i].getName()
					&& configs[i].getName().startsWith(filter)) {
				rhioTemp.add(configs[i]);
			}
		}
		if (rhioTemp.size() == 0)
			throw new RuntimeException(
					"There are no RHIOs matching filter criterion '" + filter
							+ "'");
		return (RhioConfig[]) rhioTemp.toArray(new RhioConfig[rhioTemp.size()]);
	}

	public static String trimNull(String pString) {
		if (null == pString) {
			return null;
		}
		pString = pString.trim();
		if (pString.length() == 0 || "".equals(pString)) {
			return null;
		}
		return pString;
	}

	public static boolean isEmpty(String pString) {
		pString = pString.trim();
		if (pString.length() == 0 || "".equals(pString)) {
			return true;
		}
		return false;
	}

	public static boolean isEmptyOrNull(String pString) {
		if (null == pString) {
			return true;
		}
		return isEmpty(pString);
	}

	public static boolean notEmpty(String pString) {
		return !isEmptyOrNull(pString);
	}

	public static String htmlEncode(String s) {

		if (s == null)
			return null;
		String v = s.replace("<", "&lt;");
		v = v.replace(">", "&gt;");
		return (v);
	}

	public SessionContext createSessionContext(RhioConfig pRhioConfig,
			String username) {
		SessionContext context = new SessionContext();
		context.setUser(username);
		context.setUserApplicationName("XDSDEMO_ADT");
		context.setUserFacilityName("XDSDEMO");
		context.setRhioName(pRhioConfig.getName());
		context.setReturnLogLevel("INFO");
		context.setSessionID("MC" + System.currentTimeMillis());
		context.setUseSecuredConnectionWhenAvaliable(true);
		return context;
	}

    
	
	public String sessionContextToString(SessionContext sessionContext) {
		StringBuffer buff = new StringBuffer();
		buff.append("SessionContext[");
		buff.append(", User = ");
		buff.append(sessionContext.getUser());
		buff.append(", UserApplicationName=");
		buff.append(sessionContext.getUserApplicationName());
		buff.append(", UserFacilityName=");
		buff.append(sessionContext.getUserFacilityName());
		buff.append(", RhioName=");
		buff.append(sessionContext.getRhioName());
		buff.append(", sessionId=");
		buff.append(sessionContext.getSessionID());
		buff.append(", ReturnLogLevel=");
		buff.append(sessionContext.getReturnLogLevel());
		buff.append(", UserSecuredConnectionWhenAvailable=");
		buff.append("]");

		return (buff.toString());
	}

	public OhfBridgeSoapBindingStub createBinding() throws MalformedURLException{
		OhfBridgeSoapBindingStub binding = null;
		try {
			String bridgeendpoint =  Configuration.getProperty("bridgeendpoint");
			URL url = new URL(bridgeendpoint);
			binding = (OhfBridgeSoapBindingStub) new OHFBridgeServiceLocator()
					.getOhfBridge(url);
			// ?? Is this where I set the endpoint?
		} catch (javax.xml.rpc.ServiceException jre) {
			if (jre.getLinkedCause() != null)
				jre.getLinkedCause().printStackTrace();
			jre.printStackTrace(System.out);
		}
		catch(IOException e){
			logger.error("Error reading configuration", e);
		}
		if (binding == null)
			throw new NullPointerException("Binding is null");
		// Time out after a minute
		binding.setTimeout(60000);
		return (binding);
	}

	/**
	 * Returns the PatientRecordResultsManager in the current context; creates
	 * one if none exists.
	 * @return
	 */
	public PatientRecordResultManager getPatientRecordResultManager()
			throws RemoteException, IOException {
		if (defaultRhioFilter == null){
			defaultRhioFilter = Configuration.getProperty("defaultRhioFilter");
			logger.info("Setting defaultRhioFilter to " + defaultRhioFilter);
		}
		MdlActionContext ctx = (MdlActionContext) getContext();
		PatientRecordResultManager resultManager = 
			ctx.getPdqResultsManager();
		if (resultManager == null) {
			resultManager = initializePatientRecordResultsManager(defaultRhioFilter);
			ctx.setPdqResultsManager(resultManager);
		} else {
			logger
					.info("getPatientRecordResultManager:Using existing resultManager");
		}
		return (resultManager);
	}

	public PatientRecordResultManager initializePatientRecordResultsManager(
			String rhioFilter) throws RemoteException {
		  System.setProperty("org.apache.commons.logging.Log",
          "org.apache.commons.logging.impl.SimpleLog");
      System.setProperty("org.apache.commons.logging.simplelog.showdatetime",
          "true");
      System.setProperty(
          "org.apache.commons.logging.simplelog.log.httpclient.wire", "warn");
      System
          .setProperty(
              "org.apache.commons.logging.simplelog.log.org.apache.commons.httpclient",
              "warn");
		PatientRecordResultManager resultManager = new PatientRecordResultManager();
		try {
			resultManager.setOhfBindingStub(createBinding());
		
			resultManager.setAllRhios(generateAllRhios(resultManager
					.getOhfBindingStub()));
			resultManager.setFilteredRhios(filterRhios(rhioFilter,
					resultManager.getAllRhios()));

			RhioConfig rhios[] = resultManager.getAllRhios();
			RhioConfig fRhios[] = resultManager.getFilteredRhios();
			
			logger.info("initializePatientRecordResultsManager: There are "
					+ rhios.length + " rhios on server:");
			for (int i = 0; i < rhios.length; i++) {
				logger.info(i + " '" + rhios[i].getName() + "' "
						+ rhios[i].getDescription());
			}
			logger.info("initializePatientRecordResultsManager: There are "
					+ fRhios.length + " filtered rhios:");
			for (int i = 0; i < fRhios.length; i++) {
				logger.info(i + " '" + fRhios[i].getName() + "' "
						+ fRhios[i].getDescription() + " pixURI =" + fRhios[i].getPixConfig().getDefaultPixUri());
			}
			 String configUseAtna = Configuration.getProperty("useAtna");
	         if ("false".equals(configUseAtna))
	        	useAtna = false;
	         
	         String configUseSecureIfAvailable = Configuration.getProperty("useSecureIfAvailable");
	         if ("false".equals(configUseSecureIfAvailable))
	        	 useSecureIfAvailable = false;

		} catch (Exception e) {
			logger.error("Error initializing PatientRecordResultManager", e);
		}
		return (resultManager);
	}

	/*
	 protected  MLLPDestination createMLLP() {
	 
	 MLLPDestination mllp = new MLLPDestination(OhfConfiguration.MLLP_HOST, OhfConfiguration.MLLP_PORT);
	 MLLPDestination.setUseATNA(OhfConfiguration.USE_ATNA_MLLP);
	 return mllp;
	 }
	 
	 protected  MLLPDestination createSecureMLLP() {
	 
	 SecureTCPPort tcpPort = new SecureTCPPort();
	 tcpPort.setTcpHost(OhfConfiguration.MLLP_HOST);
	 tcpPort.setTcpPort(OhfConfiguration.MLLP_SECUREPORT);
	 tcpPort.setKeyStoreName(OhfConfiguration.MLLP_KEYSTORE_NAME);
	 tcpPort.setKeyStorePassword(OhfConfiguration.MLLP_KEYSTORE_PASSWORD);
	 tcpPort.setTrustStoreName(OhfConfiguration.MLLP_TRUSTSTORE_NAME);
	 tcpPort.setTrustStorePassword(OhfConfiguration.MLLP_TRUSTSTORE_PASSWORD);
	 tcpPort.setClientAuthNeeded(OhfConfiguration.MLLP_NEEDS_CLIENT_AUTH);
	 tcpPort.setProtocol(OhfConfiguration.MLLP_PROTOCOL);
	 tcpPort.setSslProviderName(OhfConfiguration.MLLP_SSLPROVIDER_NAME);
	 System.out.println(OhfConfiguration.MLLP_SSLPROVIDER_NAME);
	 tcpPort.setSslProviderClass(OhfConfiguration.MLLP_SSLPROVIDER_CLASS);
	 System.out.println(OhfConfiguration.MLLP_SSLPROVIDER_CLASS);
	 MLLPDestination mllp = new MLLPDestination(tcpPort);
	 MLLPDestination.setUseATNA(OhfConfiguration.USE_ATNA_MLLP);
	 return mllp;
	 }
	 */

	/**
	 * Returns a PatientNameType object for use in a PDQ query.
	 * 
	 * If both givenName and familyName are blank or null this 
	 * method returns null.
	 */
	public PatientNameType makePdqPatientNameQuery(String givenName,
			String familyName) {
		if (isEmptyOrNull(givenName) && isEmptyOrNull(familyName))
			return (null);
		PatientNameType nameType = new PatientNameType();
		if (notEmpty(familyName))
			nameType.setFamilyName(familyName);
		if (notEmpty(givenName))
			nameType.setGivenName(givenName);
		return (nameType);
	}


	/**
	 * Returns a PatientIdType object for use in a PDQ query.
	 * 
	 * If the patientId is null then this method returns null;
	 * @param patientId
	 * @return
	 */
	public PatientIdType makePdqPatientIdQuery(String patientId) {
		if (isEmptyOrNull(patientId))
			return null;
		PatientIdType idType = new PatientIdType();
		idType.setIdNumber(patientId);
		return (idType);
	}

	public String saveLog(String bridgeLog[], File logFile) throws IOException {
		logger.info("Saving results to " + logFile.getAbsolutePath());
		FileWriter out = new FileWriter(logFile);
		StringBuffer buff = new StringBuffer();
		for (int k = 0; k < bridgeLog.length; k++) {
			buff.append(bridgeLog[k]);
			out.write(bridgeLog[k]);
			buff.append("\n");
			bridgeLog[k] = htmlEncode(bridgeLog[k]);
		}
		out.close();
		//pdqResults.setBridgeLog(bridgeLog);
		return (buff.toString());
	}

	/**
	 * Creates a File in the "logs" directory.
	 * @param filename
	 * @return
	 */
	public File makeLogFile(String filename) {
		File logDirectory = new File("logs");
		if (!logDirectory.exists()) {
			logDirectory.mkdir();
		}
		File f = new File(logDirectory, filename);
		if (f.exists())
			f.delete();

		return (f);
	}

	/*
	 * Makes a file in the MDL cache
	 */
	public File makeMDLCacheFile(String filename) {
		if (mdlCacheDirectory == null) {
			File dataDirectory = new File("webapps");
			if (!dataDirectory.exists())
				dataDirectory.mkdir();
			File mdl = new File(dataDirectory, "mdl");
			
			mdlCacheDirectory = new File(mdl, "mdlCache");
			if (!mdlCacheDirectory.exists())
				mdlCacheDirectory.mkdir();
		}

		File f = new File(mdlCacheDirectory, filename);
		if (f.exists())
			f.delete();

		return (f);
	}
	/**
     * Placeholder for child classes to override if they want to override
     * the resolution for validation failures.
     * 
     * @return
     */
    public Resolution getSourcePageResolution() {
        return null;
    }
    
    public boolean isLoggedIn(){
    	boolean response = false;
    	MdlActionContext ctx = (MdlActionContext) getContext();
    	Person person = ctx.getUser();
    	if(person != null)
    		response = true;
    	return(response);
    }
    public ResponseWrapper generateErrorResponse(String message, String content){
    	ResponseWrapper error = new ResponseWrapper();
    	error.setStatus(ResponseWrapper.Status.ERROR);
    	error.setMessage(message);
    	error.setContents(content);
    	return(error);
    }
    

}
