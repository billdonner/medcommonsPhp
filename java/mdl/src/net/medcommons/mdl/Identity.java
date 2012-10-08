package net.medcommons.mdl;

/**
 * A temporary class for holding the identity of a person.  The real class
 * has to be better structured.
 * @author mesozoic
 *
 */
public class Identity {

	/** 
	 * A little unclear what IdP is yet. Is it a string identifier? 
	 * Is it the hostname or some logical identifier?
	 * 
	 * Should SAML IdPs be lumped in here with the IHE AD and
	 * MedCommons CAF ones? Probably should subclass. 
	 */
	private String idP = null;
	
	/**
	 * Not sure what this is yet - it's an obvious subclass thing
	 * where there may be internal structure instead of string
	 * encoding. In the CAF case - it's just the MedCommonsId.
	 * In SAML - is it the federated identity or is it the federated
	 * identity plus other attributes? 
	 */
	private String identityToken = null;
	
	/**
	 * Not sure what this is yet - perhaps it is the SAML assertion
	 * which might contain a bunch of relevant info (? Time of assertion?)
	 */
	private String assertion = null;
	
	public Identity(String idP, String identityToken){
		this.idP = idP;
		this.identityToken = identityToken;
	}
	public String getIdP(){
		return(this.idP);
	}
	public void setIdP(String idP){
		this.idP = idP;
	}
	
	public String getIdentityToken(){
		return(this.identityToken);
	}
	
	public void setIdentityToken(String identityToken){
		this.identityToken = identityToken;
	}
}
