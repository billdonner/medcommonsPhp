package net.medcommons.mdl.action;

import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumer;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerQuery;

import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;

import net.sourceforge.stripes.action.ActionBeanContext;
import net.sourceforge.stripes.action.Resolution;
import net.medcommons.mdl.PatientRecord;
import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.Person;

public class MdlActionContext extends ActionBeanContext {
	
	/**
     * Override for the source page resolution.  Used in some special cases
     * to force validation failures to specific pages without having the
     * annoying _sourcePage parameter in the request.
     */
    private Resolution sourceResolution = null;
    
    private SessionContext ohfSessionContext = null;

    public void setOhfSessionContext(SessionContext ohfSessionContext){
    	this.ohfSessionContext = ohfSessionContext;
    }
    public SessionContext getOhfSessionContext(){
    	return(this.ohfSessionContext);
    }
	String rhioFilter = null;

    /** Gets the currently logged in user, or null if no-one is logged in. */
    public Person getUser() {
        return (Person) getRequest().getSession().getAttribute("user");
    }

    /** Sets the currently logged in user. */
    public void setUser(Person currentUser) {
        getRequest().getSession().setAttribute("user", currentUser);
    }

    /** Logs the user out by invalidating the session. */
    public void logout() {
        getRequest().getSession().invalidate();
    }
    

    /** Gets the current PDQ query */
    public PatientInfoType getPdqQuery() {
        return (PatientInfoType) getRequest().getSession().getAttribute("pdqQuery");
    }

    /** Sets the current PDQ query. */
    public void setPdqQuery(PatientInfoType currentPdqQuery) {
        getRequest().getSession().setAttribute("pdqQuery", currentPdqQuery);
    }
    /*
    public PdqConsumer getPdqConsumer(){
    	return (PdqConsumer) getRequest().getSession().getAttribute("pdqConsumer");
    }
    public void setPdqConsumer(PdqConsumer pdqConsumer){
    	 getRequest().getSession().setAttribute("pdqConsumer", pdqConsumer);
    }
*/
    public void setCurrentPdqRecord(PatientRecord currentPdqRecord){
    	getRequest().getSession().setAttribute("currentPdqRecord", currentPdqRecord);
    	
    }

    public PatientRecord getCurrentPdqRecord(){
    	return (PatientRecord) getRequest().getSession().getAttribute("currentPdqRecord");
    }
    public void setPdqResultsManager(PatientRecordResultManager pm){
    	getRequest().getSession().setAttribute("pdqResultsManager", pm);
    }
    public PatientRecordResultManager getPdqResultsManager(){
    	return (PatientRecordResultManager) getRequest().getSession().getAttribute("pdqResultsManager");
    }
    
    public void setRhioFilter(String rhioFilter){
    	this.rhioFilter = rhioFilter;
    }
    
    public String getRhioFilter(){
    	return(this.rhioFilter);
    }
    
    public void setSourcePageResolution(Resolution r) {
        this.sourceResolution = r;
    }
    
    @Override
    public Resolution getSourcePageResolution() {
        if(sourceResolution==null)            
            return super.getSourcePageResolution();
        else
            return this.sourceResolution;
    }
}
