package net.medcommons.mdl;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.rmi.RemoteException;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import localhost.bridge.services.ohf_bridge.OhfBridgeSoapBindingStub;
import net.medcommons.mdl.cxp.Upload;
import net.medcommons.mdl.ohf.AtnaConstants;
import net.medcommons.mdl.utils.MetadataFormat;

import org.apache.axis.encoding.Base64;
import org.apache.log4j.Logger;
import org.cxp2.Parameter;
import org.cxp2.PutResponse;
import org.cxp2.RegistryParameters;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.Namespace;
import org.jdom.input.SAXBuilder;
import org.jdom.output.XMLOutputter;
import org.jdom.transform.XSLTransformException;
import org.jdom.transform.XSLTransformer;
import org.jdom.xpath.XPath;

import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.ResponseType;
import OHFBridgeStub.RetrieveDocumentResponseType;
import OHFBridgeStub.RhioConfig;
import OHFBridgeStub.SessionContext;
import OHFBridgeStub.XdsDocType;

public class XdsDownloadTask extends Task {
	private static Logger logger = Logger.getLogger(XdsDownloadTask.class);

	List<XdsDocType> documentList;
	private PatientRecordResultManager resultsManager = null;
	private RhioConfig[] rhios = null;
	private OhfBridgeSoapBindingStub binding = null;
	private boolean inititialized = false;
	private Person person;
	
	String storageId = null;
	
	// Retrieve XDS docs by URL or UUID. 
	private boolean getXDSDocumentByURL = true; // True is faster
	
	List<cacheFiles> filesToUpload = new ArrayList<cacheFiles>();
	
	boolean useAtna = true;
	
	boolean useSecureIfAvailable = true;
	
	
	public XdsDownloadTask(Person person, PatientRecordResultManager resultsManager, TaskStatus taskStatus){
		super(taskStatus);
		this.resultsManager = resultsManager;
		this.person = person;
		
	}
	
	
	/**
	 * Initializes SOAP bindings.
	 *
	 */
	private void initialize(){
		rhios = resultsManager.getFilteredRhios();
		binding = resultsManager.getOhfBindingStub();
		inititialized = true;
	}
	
	/**
	 * Sets the document list for the transfer.
	 * 
	 * Sets the totalBytes as a side effect using the XDS metadata.
	 * @param documentList
	 */
	public void setDocumentList(String storageId, List<XdsDocType> documentList){
		this.storageId = storageId;
		this.documentList = documentList;
		long byteCount = 0;
		for (int i=0;i<documentList.size();i++){
			byteCount += getDocumentSize(documentList.get(i));
		}
		((XdsDownloadTaskStatus) getTaskStatus()).setTotalBytes(byteCount);
		logger.info("Set total number of bytes to be " + byteCount);
	
	}
	
	public void setUseAtna(boolean useAtna){
		this.useAtna = useAtna;
	}
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
	public List<XdsDocType> getDocumentList(){
		return(this.documentList);
	}
	
	private long getDocumentSize(XdsDocType document){
		Long docSize = new Long(0);
		String size = document.getDocumentSize();
		if ((size != null) && (!size.equals(""))){
			docSize = Long.parseLong(size.trim());
		}
		return(docSize.longValue());
	}
		
	/**
	 * Iterates over all of the XDS documents and performs the following tasks:
	 * <ol>
	 * <li> Retrieves document from XDS repository </li>
	 * <li> Places document in cache; if there is an embedded document create two documents. Create HTML out of CDA documents.</li>
	 * <li> Upload to PHR service via CXP </li>
	 * </ol>
	 */
	public void run() {
		if (!inititialized){
			initialize();
		}
		XdsDocType doc = null;
		XdsDownloadTaskStatus status = (XdsDownloadTaskStatus) getTaskStatus();
		status.setStatus(TaskStatus.STATUS.InProcess);
		status.setStartTime(System.currentTimeMillis());
		try{
			PatientInfoType patientInfoType = resultsManager.getCurrentPatientRecord();
			// Retrieve the document from XDS repository and place in local cache
			for (int i=0;i<documentList.size();i++){
				try{
					doc = documentList.get(i);
					downloadToCache(i, doc);
					try{
						if (useAtna){
							logger.info("PHI Export for patient:" + patientInfoType.getPatientIdentifier().getIdNumber());
							atnaLogPhiExport(doc.getUuid(),patientInfoType.getPatientIdentifier().getIdNumber());
						}
					}
					catch(Exception e){
						logger.error("Error in ATNA PHI export ", e);
					}
				}
				catch(Exception e){
					// If there is an error - then just return after setting
					// the result status to failure.
					String docInfo =  MetadataFormat.toString(doc);
					status.setDisplayStatus("Error retrieving document:" + docInfo);
					status.setStatus(TaskStatus.STATUS.Failed);
					status.setEndTime(System.currentTimeMillis());
					logger.error("Error downloading document " + docInfo, e);
					return;
					
				}
			}
			
			// Upload documents to MedCommons via CXP
			String endpoint = Configuration.getProperty("cxpendpoint");
			status.setDisplayStatus("CXP Upload In Progress");
		
			PutResponse cxpAttachmentResponse = uploadDocsToCXP(endpoint, storageId, "IdentityToken",filesToUpload);
			
			File CCRFile = generateCCR(storageId, patientInfoType,cxpAttachmentResponse );
			
			cacheFiles ccrCacheFile = new cacheFiles();
			ccrCacheFile.f = CCRFile;
			ccrCacheFile.mimeType = "application/x-ccr+xml";
			ArrayList<cacheFiles> ccrFileToUpload = new ArrayList<cacheFiles>();
			ccrFileToUpload.add(ccrCacheFile);
			
			PutResponse ccrCxpResponse = uploadDocsToCXP(endpoint, storageId, "identityToken", ccrFileToUpload);
			String url = getResultUrl(ccrCxpResponse);
			
			// TODO: Delete from cache here?
			
			status.setUrl(url);
			status.setEndTime(System.currentTimeMillis());
			status.setDisplayStatus(TaskStatus.STATUS_COMPLETE);
			status.setStatus(TaskStatus.STATUS.Complete);
		}
		catch(Exception e){
			logger.error("Error migrating documents", e);
			status.setDisplayStatus(TaskStatus.STATUS_ERROR);
			status.setStatus(TaskStatus.STATUS.Failed);
		}
		logger.info("Task completed in " + getElapsedTime()  +  " seconds ");
	}
	
	private double getElapsedTime(){
		XdsDownloadTaskStatus status = (XdsDownloadTaskStatus) getTaskStatus();
		long msec = status.getEndTime() - status.getStartTime();
		return( msec/ 1000.0);
	}
	
	private void atnaLogPhiExport(String exportedDataId, String patientId) throws RemoteException{
	   	int eventOutcome = AtnaConstants.OUTCOME_SUCCESS;
	    ResponseType response = null;
      	//String dataRecipientId = "doctor";
      	//String exportedDataId = "urn:abcdefg";
      	//String patientId = "95a1633c27e94eb";
      	SessionContext sessionContext = createSessionContext(rhios[0], person.getUsername());
      	OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = resultsManager.getAssigningAuthorityType();
      	/*
      	OHFBridgeStub.AssigningAuthorityType assigningAuthorityType = new OHFBridgeStub.AssigningAuthorityType();
        assigningAuthorityType.setUniversalId("1.3.6.1.4.1.21367.2005.1.1");
        assigningAuthorityType.setUniversalIdType("ISO");
        */
        PatientIdType idType = new PatientIdType();
	      	idType.setIdNumber(patientId);
	      	idType.setAssigningAuthorityType(assigningAuthorityType);
      	response = binding.auditPhiExport(sessionContext, eventOutcome, person.getUsername(), exportedDataId, idType);
      	if (response.isSuccess())
      		logger.info("ATNA phi export success " + exportedDataId + ", " + patientId);
      	else{
      		logger.info("ATNA Failed");
      		logger.info(response.getFailMessage());
      	}
	}
	private void downloadToCache(int docCounter, XdsDocType doc) throws RemoteException, Exception{
		String uuid = doc.getUuid();
		String uri = doc.getUri();
		logger.info("download to cache:" + docCounter + " " + uuid + 
				" " + uri);
		int hostPos = uri.indexOf("208.67.120.29");
		// https://
		if (hostPos != -1){
			uri = "https://ndma1" + uri.substring(13 + 8);
			logger.info("New url:" + uri);
		}
		else{
			//https://lswin10.dfw.ibm.com
			hostPos = uri.indexOf("https://lswin10.dfw.ibm.com");
			if (hostPos != -1){
				uri = "https://ibm6" + uri.substring(27);
				logger.info("New url:" + uri);
			}
		}
		XdsDownloadTaskStatus status = (XdsDownloadTaskStatus) getTaskStatus();
		String displayName = "";
		if (doc.getTypeCode() != null){
			displayName = doc.getTypeCode().getDisplayName();
		}
		status.setDisplayStatus(docCounter + " " + displayName);

		
		for (int i=0;i<rhios.length;i++){
			SessionContext sessionContext = createSessionContext(rhios[i], person.getUsername());
			RetrieveDocumentResponseType response;
			
			if (getXDSDocumentByURL){
				logger.info("retrieving XDS document by URI:" + uri);
				response = binding.retrieveDocumentByUrl(sessionContext, uri);
				
			}
			else{
				logger.info("retrieving XDS document by UUID:" + uuid);
				response = binding.retrieveDocumentByUUID(sessionContext, uuid);
			}
			
			
			saveFile(doc, rhios[i], response);
		}
		
		
		
		long transferredBytes = status.getTransferredBytes();
		transferredBytes += getDocumentSize(doc);
		status.setTransferredBytes(transferredBytes);
		
		
		status.setPercentComplete((int) ((100.0 * transferredBytes)/status.getTotalBytes()));
		logger.info("Status set to:" + status.getDisplayStatus() + " " + transferredBytes + " of total " + status.getTotalBytes() + " percent complete=" + status.getPercentComplete());
	}
	
	/**
	 * Saves the CDA file from XDS to disk
	 * @param doc
	 * @param rhio
	 * @param response
	 * @throws Exception
	 */
	private void saveFile(XdsDocType doc, RhioConfig rhio, RetrieveDocumentResponseType response) throws Exception{
		String rhioName = rhio.getName();
		//File xsltFile = new File()
		if(!response.isSuccess()){
			logger.error("Retrieve failed: " + response.getFailMessage());
			throw new Exception("Retrieve FAILED: " + response.getFailMessage());
		}
		else{
    		if (response.getDocument() != null){
    			
        		logger.info("Retrieve Success for " + doc.getUuid());
        		logger.info(MetadataFormat.toString(doc));
        		
        		String doc64 = response.getDocument().getBase64EncodedDocument();
        	
        		if (doc64 != null)
        		{
        			logger.info("Document is not null");
        			
        			//try {
        				byte[] encapsulatedDoc = Base64.decode(doc64);
        				// Need to put into cache - based on user name?
        				String fileFragment = doc.getUuid();
        				fileFragment = fileFragment.replace(':', '_');
        				File f = makeMDLCacheFile(rhioName + "_" +fileFragment );
        				FileOutputStream out = new FileOutputStream(f);
        				out.write(encapsulatedDoc);
        				out.close();
        				logger.info("Document written to " + f.getAbsolutePath());
        				cacheFiles files = new cacheFiles();
        				
        				files.mimeType = "text/html";
        				files.displayName = doc.getTypeCode().getDisplayName();
        				files.f = processEmbeddedFile(f, files.displayName);
        				filesToUpload.add(files);
        				
        				
        				
        				
        		//	}
        		//	catch(Exception e){
        		//		logger.error("Error decoding document " + doc.getUuid(), e);
        		//	}
        		}

    		else{
    			logger.info("Retrieved document is null");
    		}
		}
	}
	}
	public File processEmbeddedFile(File cdaFile, String parentDisplayName) throws XSLTransformException,IOException,JDOMException{
		
			String cdaFilename = cdaFile.getAbsolutePath();
			String processedFileDisplayName = parentDisplayName;
			logger.info("Processing file " + cdaFilename);
			String htmlFilename = cdaFilename + ".html";
		 		File xsl = new File("webapps/mdl/mesa_xsl/CDA.xsl");
		 		if (!xsl.exists()){
		 			throw new FileNotFoundException(xsl.getAbsolutePath());
		 		}
		 		XSLTransformer trans=new XSLTransformer(xsl);
		 		Document htmlDoc=null;
		 		
		 		SAXBuilder builder = new SAXBuilder();
		 		Document cdaDocument = builder.build(cdaFile);
		 			 		
		 		// Next step - get the embedded PDFs out.
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
					String cdaFilenameEmbedded = cdaFilename + "_embedded" + fileExtension;
					//pdqResults.setCdaCacheDocument(getCdaFilename());
					File cacheFile = new File(cdaFilenameEmbedded);
					cacheFile.getParentFile().mkdirs();
					String doc64 = text.getValue();
					if (doc64 != null){
						logger.info("Length of embeddedDocument:" + doc64.length() + ", with media type " + mediaType);
						logger.info("Saving file " + cdaFilenameEmbedded + ", representation  " + representation);
						String embeddedMimeType = "application/pdf";
						processedFileDisplayName = parentDisplayName + "(PDF)";
						if (mediaType.equals("text/plain")){
							embeddedMimeType = "text/plain";
							processedFileDisplayName = parentDisplayName + "(TXT)";
						}
						byte[] doc = Base64.decode(doc64);
						FileOutputStream out = new FileOutputStream(cacheFile);
						out.write(doc);
						out.close();
						cacheFiles files = new cacheFiles();
        				files.f = cacheFile;
        				files.mimeType = embeddedMimeType;
        				files.displayName = processedFileDisplayName;
        				filesToUpload.add(files);
					}
					else
						logger.error("null embedded document"+ ", " + mediaType);
					
					
				}
				FileOutputStream outHTML = new FileOutputStream(htmlFilename);
		 		
		 		htmlDoc=trans.transform(cdaDocument);
		 		XMLOutputter xmlOutputter = new XMLOutputter();
		 		xmlOutputter.output(htmlDoc, outHTML);
		 		
		 		logger.info("Saved html file to " + htmlFilename);
		 		File htmlFile = new File(htmlFilename);
		 		return(htmlFile);
		
	}

	
	private String getFileExtension(String mediaType){
		if (mediaType.equals("application/pdf")) return(".pdf");
		else if (mediaType.equals("text/plain")) return(".txt");
		else
			throw new IllegalArgumentException("Unknown media type:" + mediaType);
	}
	
	File mdlCacheDirectory = null;
	/*
	 * Makes a file in the MDL cache
	 */
	public File makeMDLCacheFile(String filename) {
		logger.info("makeMDLCacheFile:" + filename);
		if (mdlCacheDirectory == null) {
			File dataDirectory = new File("data");
			if (!dataDirectory.exists())
				dataDirectory.mkdir();
			File mdl = new File(dataDirectory, "mdl");
			if (!mdl.exists())
				mdl.mkdir();
			mdlCacheDirectory = new File(mdl, "mdlCache");
			if (!mdlCacheDirectory.exists())
				mdlCacheDirectory.mkdir();
		}

		File f = new File(mdlCacheDirectory, filename);
		if (f.exists())
			f.delete();

		return (f);
	}
	
	private File generateCCR(String storageId, PatientInfoType patientInfoType, PutResponse cxpResponse) throws IOException, JDOMException, InvalidCCRException{
		CCRGenerator ccrGenerator = new CCRGenerator();
		File ccrFile = makeMDLCacheFile("CCR" + System.currentTimeMillis() + ".xml");
		ccrGenerator.loadTemplateCCR();
		if (storageId == null){
			logger.error("Kludge for XDS query - null storageId");
			storageId = "1013062431111407";
		}
		if (patientInfoType == null){
			patientInfoType = new PatientInfoType();
			PatientNameType patientNameType = new PatientNameType();
			patientNameType.setFamilyName("Demo");
			patientNameType.setGivenName("XDS");
			patientInfoType.setPatientName(patientNameType);
		}
		ccrGenerator.setDemographics(storageId, patientInfoType);
		List<org.cxp2.Document> documents = cxpResponse.getDocinfo();
			
		for (int i=0;i<documents.size();i++){
			
			org.cxp2.Document doc = documents.get(i);
			logger.info("doc desc = " + doc.getDescription() + ", " + doc.getDocumentName());
			ccrGenerator.addReference(doc.getGuid(), doc.getDocumentName(), doc.getContentType());
		}
		Document ccrDoc =ccrGenerator.getCCRDocument();
		XMLOutputter outputter = new XMLOutputter();
		FileOutputStream out = new FileOutputStream(ccrFile);
		outputter.output(ccrDoc, out);
		return(ccrFile);
		
	}
	
	public PutResponse uploadDocsToCXP(String endPoint, String storageId, String identityToken, List<cacheFiles>files) throws Exception{
		Upload upload = new Upload(endPoint, storageId, identityToken);
		
		
		for (int i=0;i<files.size();i++){
			cacheFiles uploadFile = files.get(i);
			String displayName = uploadFile.displayName;
			if ((displayName == null) || ("".equals(displayName)))
				displayName = uploadFile.f.getName();
			upload.addDocument(uploadFile.f, uploadFile.mimeType, displayName);
		}
		PutResponse response = upload.upload();
		displayResponseInfo(response);
		return(response);
	}
	
	public String getResultUrl(PutResponse resp) throws IOException{
		String viewerBaseURL = Configuration.getProperty("viewerBaseURL");
		String guid = null;
		
		List<org.cxp2.Document> responseDocs = resp.getDocinfo();
		Iterator<org.cxp2.Document> iter = responseDocs.iterator();
		
		while (iter.hasNext()){
			org.cxp2.Document doc = iter.next();
			guid = doc.getGuid();
			
			//http://caudipteryx:9080/router/access?g=54cd536c501370445fffb740513d0c4fb72f655b&t=&a=1013062431111407&m=&c=
		}
		String url = viewerBaseURL + "router/access?g=" + guid + "&a=" + storageId;
		logger.info("URL to CCR is " + url);
		return(url);
	}
	public void displayResponseInfo(PutResponse resp){
		
		List<org.cxp2.Document> responseDocs = resp.getDocinfo();
		
		Iterator<org.cxp2.Document> iter = responseDocs.iterator();
		logger.info("Number of files successfully stored:" + responseDocs.size());
		while (iter.hasNext()){
			org.cxp2.Document doc = iter.next();
			logger.info(doc.getContentType() + " " + doc.getGuid() + " " + doc.getDocumentName());
		}
		
		List registryParameters= resp.getRegistryParameters();
		for (int i=0;i<registryParameters.size(); i++){
			RegistryParameters r = (RegistryParameters) registryParameters.get(i);
			logger.info("Registry Parameters:" + r.getRegistryId() + "," + r.getRegistryName());
			List<Parameter> params = r.getParameters();
			if(params.size() >0){
				logger.info(" Parameters:");
				for (int k=0;k<params.size();k++){
					Parameter p = params.get(k);
					logger.info("  Parameter name=" + p.getName() + ", value=" + p.getValue());
				}
			}
			else{
				logger.info("   Parameter list empty");
			}
			
		}
		
		
		logger.info("Response: " + resp.getStatus() + ", "
				+ resp.getReason());
	}
	
	class cacheFiles{
		File f;
		String mimeType;
		String displayName;
	}
}
