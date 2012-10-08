package net.medcommons.mdl.cxp;


import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.security.NoSuchAlgorithmException;
import java.util.List;

import net.medcommons.modules.cxp.client.CXPClient;
import net.medcommons.modules.utils.FilenameFileFilter;
import net.medcommons.modules.utils.PerformanceMeasurement;
import net.medcommons.modules.utils.SpecialFileFilter;

import org.apache.log4j.Logger;
import org.cxp2.Document;
import org.cxp2.PutRequest;
import org.cxp2.PutResponse;

/**
 * Simple API for PUTing files on a server via CXP.
 * 
 * 
 *
 */
public class Upload{
	/**
	 * Logger to use with this class
	 */
	private static Logger log = Logger.getLogger(Upload.class);
	
	
	private String endpoint = null;
	
	private PutRequest request = null;
	private CXPClient client = null;
	String storageId = null;
	

	/**
	 * Initializes the transsfer
	 * 
	 * @param endpoint - the SOAP endpoint accepting the CXP transaction
	 * @param storageId - the MedCommons account where the documents are to be put.
	 * @param identityToken - some security token that has to be put into the SOAP header. TBD.
	 * @throws Exception
	 */
	public Upload(String endpoint, String storageId, String identityToken) throws Exception{
		this.endpoint = endpoint;
		
		this.storageId = storageId;
	
		if (!(storageId.length()==16))
			throw new IllegalArgumentException("StorageId '" + storageId + "' must be 16 digits in length, not " + storageId.length());
		client = new CXPClient(endpoint);
		request = new PutRequest();
		request.setStorageId(storageId);
	}
	
	
	/**
	 * Uploads all specified files to the specified CXP endpoint.
	 * <P>
	 * Note that the CCR documents should be uploaded last because they may have embedded references
	 * to other documents. This may throw an exception on the server if the referenced objects 
	 * don't exist. Thus - invoke addDocument() with the CCRs as the last set of documents.
	 *
	 * @throws Exception
	 */
	public PutResponse upload() throws Exception{
		long startTime = System.currentTimeMillis();
		
		
		PutResponse response = client.getService().put(request);
		client.displayResponseInfo(response); // Just for logging
		
		long endTime = System.currentTimeMillis();
		log.info(PerformanceMeasurement.throughputString("Upload to " + storageId, (endTime-startTime), client.getByteCount()));
		return (response);

	}
	
	public void addDocument(File f, String mimeType, String displayName) throws IOException, NoSuchAlgorithmException{
		log.info("addDocument " + mimeType + " " + displayName);
		Document doc = client.createSimpleDocument(f, mimeType,displayName);
		request.getDocinfo().add(doc);
	}




}
