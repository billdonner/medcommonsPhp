package net.medcommons.mdl.action;

import java.rmi.RemoteException;

import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

import org.eclipse.ohf.ihe.common.hl7v2.mllpclient.ClientException;

public class MainAction extends MdlAction {
	@DefaultHandler
	public Resolution login() throws ClientException, RemoteException{
		
		return new RedirectResolution("/PdqQueryResponse.jsp");
		
	}
}
