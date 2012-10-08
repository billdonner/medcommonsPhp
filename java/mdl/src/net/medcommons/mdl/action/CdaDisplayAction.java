package net.medcommons.mdl.action;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.rmi.RemoteException;
import java.util.List;

import net.medcommons.mdl.PatientRecordResultManager;
import net.medcommons.mdl.utils.Base64Coder;
import net.sourceforge.stripes.action.DefaultHandler;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;

import org.apache.axis.encoding.Base64;
import org.apache.log4j.Logger;
import org.eclipse.ohf.ihe.common.hl7v2.mllpclient.ClientException;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.Namespace;
import org.jdom.input.SAXBuilder;
import org.jdom.xpath.XPath;

/**
 * Handles display of CDA documents
 * 
 * Filters out the embedded contents.
 * 
 */
public class CdaDisplayAction extends MdlAction {
	private static Logger logger = Logger.getLogger(CdaDisplayAction.class);


	private String cdaFilename;
	private String cacheCdaFilename;


	public void setCdaFilename(String cdaFilename) {
		this.cdaFilename = cdaFilename;
	}

	public String getCdaFilename() {
		return (this.cdaFilename);
	}
	
	public void setCacheCdaFilename(String cacheCdaFilename){
		this.cacheCdaFilename = cacheCdaFilename;
	}
	public String getCacheCdaFilename(){
		return(this.cacheCdaFilename);
	}
	

	protected boolean initialized = false;
	@DefaultHandler
	public Resolution display() throws ClientException, RemoteException, IOException, JDOMException{
		logger.info("display " + this.cdaFilename );
		PatientRecordResultManager pdqResults = null;
		pdqResults = getPatientRecordResultManager();
		pdqResults.setCdaDocument(cdaFilename);
		File f = new File("webapps/mdl" + cdaFilename);
		
		if (!f.exists()){
			throw new FileNotFoundException(f.getAbsolutePath());
		}
		logger.info("About to save cache file:" + f.getAbsolutePath());
		FileInputStream in = new FileInputStream(f);
		
		Document cdaDocument;
		
		SAXBuilder builder = new SAXBuilder();
		cdaDocument = builder.build(in);
		Element rootElement = cdaDocument.getRootElement();
		String namespaceURI = cdaDocument.getRootElement().getNamespaceURI();
		
		logger.info("namespace is " + namespaceURI); // xmlns:n1="urn:hl7-org:v3" urn:hl7-org:v3
		Namespace ns = rootElement.getNamespace();
		XPath x   = XPath.newInstance("n1:component/n1:nonXMLBody");
		x.addNamespace("n1", namespaceURI);
		List<Element> list = x.selectNodes(rootElement);
		/*
		 * TODO
		 * Unencode the document to a binary file.
		 * Flush it to disk with the right extension.
		 * Add this to the pdqResults
		 * Have the xsl render this as a link.
		 */
		for (int i=0; i<list.size();i++){
			Element nonXMLBody = list.get(i);
			Element text = nonXMLBody.getChild("text", ns);
			logger.info(text.getName());
			String mediaType = text.getAttribute("mediaType").getValue();
			String representation = text.getAttribute("representation").getValue();
			String fileExtension = getFileExtension(mediaType);
			setCdaFilename(cdaFilename + "_embedded" + fileExtension);
			pdqResults.setCdaCacheDocument(getCdaFilename());
			File cacheFile = makeMDLCacheFile(getCdaFilename());
			cacheFile.getParentFile().mkdirs();
			String doc64 = text.getValue();
			if (doc64 != null)
				logger.info("Length of embeddedDocument:" + doc64.length() + ", " + mediaType);
			else
				logger.info("null embedded document"+ ", " + mediaType);
			byte[] doc = Base64.decode(doc64);
			FileOutputStream out = new FileOutputStream(cacheFile);
			out.write(doc);
			out.close();
			
		}
	
		
		return new RedirectResolution("/CdaDisplay.jsp");
		
	}

	private String getFileExtension(String mediaType){
		if (mediaType.equals("application/pdf")) return(".pdf");
		else if (mediaType.equals("text/plain")) return(".txt");
		else
			throw new IllegalArgumentException("Unknown media type:" + mediaType);
	}
	
 
	
}
