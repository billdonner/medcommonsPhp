package net.medcommons.mdl;


/**
 * Superclass of tasks that are monitored by users. Instances of this class and subclasses
 * are serialized via JSON for consumption in browses.
 * 
 * @author mesozoic
 *
 */
public class TaskStatus {
	final static String STATUS_UNINITIALIZED = "UNINITIALIZED";
	final static String STATUS_COMPLETE = "COMPLETE";
	final static String STATUS_ERROR = "ERROR";
	
	public enum STATUS  {Uninitialized, InProcess, Failed, Complete};
	
	long startTime;
	long endTime;
	String name;
	String displayStatus =STATUS_UNINITIALIZED;
	STATUS status = STATUS.Uninitialized;
	int percentComplete = 0;
	
	public void setName(String name){
		this.name = name;
	}
	public String getName(){
		return(this.name);
	}
	public void setStartTime(long startTime){
		this.startTime = startTime;
	}
	public long getStartTime(){
		return(this.startTime);
	}
	
	public void setEndTime(long endTime){
		this.endTime = endTime;
	}
	public long getEndTime(){
		return(this.endTime);
	}
	public void setDisplayStatus(String displayStatus){
		this.displayStatus = displayStatus;
	}
	public String getDisplayStatus(){
		return(this.displayStatus);
	}
	public void setPercentComplete(int percentComplete){
		int p = percentComplete;
		if (p>100) p=100;
		if (p<0) p = 0;
		this.percentComplete = p;
	}
	public int getPercentComplete(){
		return(this.percentComplete);
	}
	public void setStatus(STATUS status){
		this.status = status;
	}
	public STATUS getStatus(){
		return(this.status);
	}
}
