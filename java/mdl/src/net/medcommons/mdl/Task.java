package net.medcommons.mdl;

import java.util.Date;
import java.util.List;

import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;

/**
 * Superclass for tasks that are monitored by users. It's basically a thread that contains properties
 * useful for display via Ajax.
 * 
 * @author mesozoic
 *
 */
public class Task extends Thread{
	private TaskStatus taskStatus = null;
	
	public Task(TaskStatus taskStatus){
		super(taskStatus.getName());
		setTaskStatus(taskStatus);
		
	}
	public TaskStatus getTaskStatus(){
		return(this.taskStatus);
	}
	public void setTaskStatus(TaskStatus taskStatus){
		this.taskStatus = taskStatus;
	}
	
	public SessionContext createSessionContext(RhioConfig pRhioConfig,
			String username) {
		SessionContext context = new SessionContext();
		context.setUser(username);
		context.setUserApplicationName("CONTENT_CONSUMER_MEDCOMMONS");
		context.setUserFacilityName("A General Hospital");
		context.setRhioName(pRhioConfig.getName());
		context.setReturnLogLevel("DEBUG");
		context.setSessionID("MC" + System.currentTimeMillis());
		context.setUseSecuredConnectionWhenAvaliable(true);
		return context;
	}
}
