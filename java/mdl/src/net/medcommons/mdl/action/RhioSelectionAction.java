package net.medcommons.mdl.action;

import java.io.IOException;

import org.apache.log4j.Logger;

import OHFBridgeStub.RhioConfig;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;
import net.medcommons.mdl.PersonManager;
import net.medcommons.mdl.ResponseWrapper;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;
import net.sourceforge.stripes.validation.LocalizableError;
import net.sourceforge.stripes.validation.Validate;
import net.sourceforge.stripes.validation.ValidationError;

public class RhioSelectionAction extends MdlAction {
	 
	private static Logger logger = Logger.getLogger(RhioSelectionAction.class);
	    private String rhioFilter;

	    public void setRhioFilter(String rhioFilter){
	    	this.rhioFilter = rhioFilter;
	    }
	    public String getRhioFilter(){
	    	return(this.rhioFilter);
	    }
	    @DefaultHandler
	    public Resolution setRhioFilter(){
	    	ResponseWrapper response = new ResponseWrapper();
	    	try{
	    	setDefaultRhioFilter(this.rhioFilter);
	    	PatientRecordResultManager resultManager = getPatientRecordResultManager();
	    	
	    	
	    	//RhioConfig[] rhios = resultManager.getAllRhios();
	    	resultManager.setFilteredRhios(filterRhios(rhioFilter,
					resultManager.getAllRhios()));
	    
	    	RhioConfig[] filteredRhios = resultManager.getFilteredRhios();
	    	
	    	logger.info("Number or rhios selected:" + filteredRhios.length);
	    	for (int i=0;i<filteredRhios.length; i++){
	    		logger.info("Rhio[" + i + "] " + filteredRhios[i]);
	    		//RhioConfig r = filteredRhios[i];
	    	
	    	}
	        if (filteredRhios.length == 0) {
	        	response.setStatus(ResponseWrapper.Status.ERROR);
	        	response.setContents(null);
	        	response.setMessage("No Rhios were selected");
	            return new JavaScriptResolution(response);
	        }

	        else {
	        	setPDQAttributes(filteredRhios[0].getName());
	        	response.setStatus(ResponseWrapper.Status.OK);
	        	response.setContents(filteredRhios[0]);
	        	return(new JavaScriptResolution(response));
	        }
	    	}
	    	catch(Exception e){
	    		response.setStatus(ResponseWrapper.Status.ERROR);
				response.setMessage(e.getLocalizedMessage());
				response.setContents(ResponseWrapper.throwableToString(e));
				return (new JavaScriptResolution(response));
	    	}
	       
	    }
	    /*
	     * # IHE Assigning Authority Configs for HXTI
DefaultUniversalId=1.3.6.1.4.1.21367.2005.1.1
DefaultUniversalIdType=ISO
DefaultNamespaceId=

# IHE Assigning Authority Configs for Spirit
#DefaultUniversalId=1.3.6.1.4.1.21367.2007.1.2.132
#DefaultUniversalId=1.3.6.1.4.1.21367.2005.1.1
#DefaultUniversalIdType=ISO
#DefaultNamespaceId=HIMSS2005
#Spirit-101^^^&1.3.6.1.4.1.21367.2007.1.2.132&ISO^PI

# IHE Assigning Authority Configs for AXOLOTL1
#DefaultUniversalId=1.3.6.1.4.1.21367.2005.1.1
#DefaultUniversalIdType=ISO
#DefaultNamespaceId=
#DefaultNamespaceId=HIMSS2005

#defaultRhioFilter=HXTI2_HXTI2_HXTI2_IBM1
#defaultRhioFilter=SPIRIT1_IBM6_AGFA13_IBM1

# Spirit w/ATNA
defaultRhioFilter=SPIRIT1_IBM6_AGFA13_SPIRIT5

# HXTI with/SPIRIT ATNA
#defaultRhioFilter=HXTI2_IBM6_SPIRIT1_SPIRIT5
# HXTI with/AGFA ATNA
#defaultRhioFilter=HXTI2_IBM6_SPIRIT1_AGFA13
HXTI with IBM registry/repository
defaultRhioFilter=HXTI2_IBM6_IBM6_AGFA13

# IBM with IBM
# defaultRhioFilter=IBM5_IBM6_NDMA1_AGFA13 -- No docs in this config
#defaultRhioFilter=IBM5_IBM6_IBM6_AGFA13
#defaultRhioFilter=IBM5_SPIRIT1_NDMA1_SPIRIT5
bridgeendpoint=http://medcommons2:8090/bridge/services/ohf-bridge
# need to put connectathon server configs here for rhio

# XDS/MS Retrival for EHR_GEHEALTHCARE_EMR
#defaultRhioFilter=AXOLOTL1-PDQ_SPIRIT1_SPIRIT1_AGFA13

	     */
	    private void setPDQAttributes(String rhioName) throws IOException{
	    	logger.info("Setting PDQ attributes for RHIO:'" + rhioName + "'");
	    	//String pdqServer = rhioName.substring(0, rhioName.indexOf("_"));
	    	//logger.info("PDQ Server name:" + rhioName);
	    	PatientRecordResultManager resultManager = getPatientRecordResultManager();
	    	String namespaceId = "";//"HIMSS2005";
	    	String universalId = "1.3.6.1.4.1.21367.2007.1.2.300";
	    	String universalIdType = "ISO";
	    	String application = "OHF_DEFAULT";
	    	String facility = "OHF_DEFAULT";
	    	
	    	if(rhioName.indexOf("HIMSS_BPPC") ==0){
	    		application = "XDSDemo_ADT";
	    		facility = "XDSDemo";
	    		namespaceId = "";
		    	 universalId = "1.3.6.1.4.1.21367.2007.1.2.200";
		    	 universalIdType = "ISO";
	    	}
	    	else if(rhioName.indexOf("HIMSS_NON_BPPC1") ==0){
	    		application = "PAT_IDENTITY_X_REF_MGR_QUADRAMED";
	    		facility = "HIMSS";
	    		namespaceId = "";
		    	 universalId = "1.3.6.1.4.1.21367.2007.1.2.300";
		    	 universalIdType = "ISO";
	    	}
	    	else if (rhioName.indexOf("HIMSS_NON_BPPC2-PIXPDQ")==0){
	    		application = "PAT_IDENTITY_X_REF_MGR_INITIATE";
	    		facility = "HIMSS";
	    		namespaceId = "";
		    	 universalId = "1.3.6.1.4.1.21367.2007.1.2.400";
		    	 universalIdType = "ISO";
	    	}
	    	else if (rhioName.indexOf("HIMSS_NON_BPPC2-ADT")==0){
	    		namespaceId = "";
		    	 universalId = "1.3.6.1.4.1.21367.2005.1.1";
		    	 universalIdType = "ISO";
	    	}
	    	else if(rhioName.indexOf("SPIRIT") == 0){
	    		logger.info("SPIRIT");
	    		 namespaceId = "HIMSS2005";
		    	 universalId = "1.3.6.1.4.1.21367.2005.1.1";
		    	 universalIdType = "ISO";
	    	}
	    	else if(rhioName.indexOf("HXTI") == 0){
	    		logger.info("HXTI");
	    		universalIdType = "ISO";
	    		namespaceId = "";
	    	}	
	    	else if(rhioName.indexOf("IBM") == 0){
	    		logger.info("IBM");
	    		namespaceId = "";
	    		universalIdType = "";
	    		universalId = "";
	    	}
	    	else if(rhioName.indexOf("AXOLOTL") == 0){
	    		logger.info("AXOLOTL");
	    		namespaceId = "HIMSS2005";
	    		universalIdType = "ISO";

	    	}
	    	else{
	    		logger.info("Just using defaults");
	    	}
	    	resultManager.setNamespaceid(namespaceId);
	    	resultManager.setUniversalId(universalId);
	    	resultManager.setUniversalIdType(universalIdType);
	    	resultManager.setFacility(facility);
	    	resultManager.setApplication(application);
	    	logger.info("PDQ settings: namespace " + namespaceId + " universalId = " + universalId +
	    			" univeralIdType= " + universalIdType + " facility = " + facility + " application = " + application);
	    	
	    }
}
