package net.medcommons.mdl.ohf;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.List;

import org.apache.log4j.Logger;
import org.apache.log4j.xml.DOMConfigurator;
import org.eclipse.ohf.hl7v2.core.definitions.formats.PrivateFormat;
import org.eclipse.ohf.hl7v2.core.utilities.HL7V2Exception;
import org.eclipse.ohf.hl7v2.core.validators.CPValidator;
import org.eclipse.ohf.ihe.common.hl7v2.message.PixPdqMessageException;
import org.eclipse.ohf.ihe.common.hl7v2.message.PixPdqMessageUtilities;
import org.eclipse.ohf.ihe.common.hl7v2.mllpclient.ClientException;
import org.eclipse.ohf.ihe.common.mllp.MLLPDestination;
import org.eclipse.ohf.ihe.common.mllp.SecureTCPPort;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumer;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerException;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerQuery;
import org.eclipse.ohf.ihe.pdq.consumer.PdqConsumerResponse;

public class TestPDQ {

	private static Logger logger = Logger.getLogger(TestPDQ.class);
	
	public static List<PdqConsumerResponse> test() throws PdqConsumerException, ClientException {
		
		PdqConsumer pdqQuery = null;
		PdqConsumerQuery msg = null;
		PdqConsumerResponse response = null;
		String auditUser = "pdqQueryUser";
		List<PdqConsumerResponse> responses = new ArrayList<PdqConsumerResponse>();
				
		//logger set-up
		DOMConfigurator.configure(OhfConfiguration.LOG4J_PATH);
		logger.debug("ClientPdqQueryTest: main - Enter ");
			
		/*
		//pdqQuery set-up: access db setup example
		if (OhfConfiguration.HAS_ACCESSFILE == true) {
			try {
				String msAccessFile = OhfConfiguration.ACCESS_DATABASE_PATH;
				InputStream cpaStream = new FileInputStream(OhfConfiguration.CPROFILE_PATH);
				//pdqQuery = new PdqConsumer(msAccessFile);
				pdqQuery = new PdqConsumer(msAccessFile,cpaStream);
				pdqQuery.setDoAudit(OhfConfiguration.doAudit);
				pdqQuery.setMaxVerifyEvent(CPValidator.ITEM_TYPE_FATAL);
				pdqQuery.setMLLPDestination(createMLLP());
				//pdqQuery.setMLLPDestination(createSecureMLLP());
			} catch (FileNotFoundException e) {
				throw new PdqConsumerException(e);
			}

			msg = createMessage(pdqQuery);
			response = pdqQuery.sendQuery(msg, true, auditUser);
			readReturn(response);
			
			while (response.getContinuationPointer() != null) {
				msg.addOptionalContinuationPointer(response);
				response = pdqQuery.sendQuery(msg, true, auditUser);
				readReturn(response);
			}	
		}
		*/
		//pdqQuery set-up: javaStream setup example
		//if (OhfConfiguration.HAS_JAVASTREAM == true) {
			try {
				PrivateFormat javaStream = new PrivateFormat(new File(OhfConfiguration.SERIALISED_PATH));
				InputStream cpStream = new FileInputStream(OhfConfiguration.CPROFILE_PATH);
				//pdqQuery = new PdqConsumer(javaStream);
				pdqQuery = new PdqConsumer(javaStream, cpStream);
				pdqQuery.setDoAudit(OhfConfiguration.doAudit);
				pdqQuery.setMaxVerifyEvent(CPValidator.ITEM_TYPE_FATAL);
				pdqQuery.setMLLPDestination(createMLLP());
				//pdqQuery.setMLLPDestination(createSecureMLLP());
			} catch (FileNotFoundException e) {
				throw new PdqConsumerException(e);
			} catch (HL7V2Exception e) {
				throw new PdqConsumerException(e);
			}

			msg = createMessage(pdqQuery);
			response = pdqQuery.sendQuery(msg, true, auditUser);
			readReturn(response);
			responses.add(response);
			
			while (response.getContinuationPointer() != null) {
				msg.addOptionalContinuationPointer(response);
				response = pdqQuery.sendQuery(msg, true, auditUser);
				readReturn(response);
				responses.add(response);
			}
		//}
	
		logger.debug("ClientPdqQueryTest Main - Exit ");
		return(responses);
	}
	
	public static MLLPDestination createMLLP() {
		
		MLLPDestination mllp = new MLLPDestination(OhfConfiguration.MLLP_HOST, OhfConfiguration.MLLP_PORT);
		MLLPDestination.setUseATNA(OhfConfiguration.USE_ATNA_MLLP);
		return mllp;
	}
	
	public static MLLPDestination createSecureMLLP() {
		
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
	
	public static PdqConsumerQuery createMessage(PdqConsumer pdqQuery) throws PdqConsumerException {
		
		PdqConsumerQuery msg = pdqQuery.createQuery();
		
		//msg.addOptionalDemographicSearch("PID-8","F");
		msg.addQueryPatientSex("F");
		
		//msg.addQueryPatientNameFamilyName("SMITH");
		//msg.addQueryPatientNameFamilyName("TRENTON");
		//msg.addQueryPatientNameFamilyName("RENLY");
		//msg.addQueryPatientNameFamilyName("CAMPBELL");
		//msg.addQueryPatientNameFamilyName("MUNOZ");
		
		//msg.addQueryPatientNameGivenName("BARNEY");  //Barney Smith
		//msg.addQueryPatientNameGivenName("HALLEY");  //Halley Renly		
		
		//msg.addQueryPatientAddressCity("Oakbrook");
		msg.addQueryPatientAddressStateOrProvince("WI");

		msg.addOptionalQuantityLimit(5);
		
		return msg;
	}
	
	public static void readReturn(PdqConsumerResponse response) throws PdqConsumerException {
		
		try {
			logger.debug("ClientPdqQueryTest: main - QBP^Q22 \r" + PixPdqMessageUtilities.msgToString(response));
		} catch (PixPdqMessageException e) {
			logger.debug("ClientPdqQueryTest: main - QBP^Q22 \r");
		}
		
		//status
		logger.debug("Query Status: " + response.getQueryStatus(true));
		logger.debug("ResponseACK Code: " + response.getResponseAckCode(false));
		logger.debug("ResponseACK Desc: " + response.getResponseAckCode(true));
		
		//header echo
		try {
			logger.debug("Sending App: " + response.getSendingApplication()[0]);
			logger.debug("Sending Fac: " + response.getSendingFacility()[0]);
			logger.debug("Receiving App: " + response.getReceivingApplication()[0]);
			logger.debug("Receiving Fac: " + response.getReceivingFacility()[0]);
		} catch (PixPdqMessageException e) {
			throw new PdqConsumerException(e);
		}
			
		logger.debug("Control ID: " + response.getControlId());
		
		//check for error?
		if (response.hasError()) {
			
			//multiple errors returned either as segments or repeats (not both)
			int segCnt = response.getErrorCountbySegment();
			int rptCnt = response.getErrorCountbyRepeat();
			logger.debug("Errors returned: " + "seg-" + segCnt + " rpt-" + rptCnt);
			
			if (segCnt > 0) {
				for (int i=0; i < segCnt; i++) {
					String errLoc[] = response.getErrorLocation(i, 0);
					String errCode[] = response.getErrorCode(i);
					logger.debug("  Error location: " + errLoc[0] + "^" + errLoc[1] + "^" + errLoc[2] + "^" + errLoc[3] + "^" + errLoc[4] + "^" + errLoc[5]);
					logger.debug("  Error code: " + errCode[0]);
					logger.debug("  Error severity: " + response.getErrorSeverity(i, true));
				}
			}
			else if (rptCnt > 0) {
				for (int i=0; i < rptCnt; i++) {
					String errLoc[] = response.getErrorLocation(0, i);
					String errCode[] = response.getErrorCode(0);
					logger.debug("  Error location: " + errLoc[0] + "^" + errLoc[1] + "^" + errLoc[2] + "^" + errLoc[3] + "^" + errLoc[4] + "^" + errLoc[5]);
					logger.debug("  Error code: " + errCode[0]);
					logger.debug("  Error severity: " + response.getErrorSeverity(0, true));
				}
			}
		}
		
		//check for patients?
		logger.debug("Patients returned: " + response.getPatientCount());
		
		for (int i=0; i < response.getPatientCount(); i++) {
			String patID[] = response.getPatientIdentifier(i, 0);  //just retrieving first ID
			String patName[] = response.getPatientName(i, 0);
			logger.debug("  Patient: " + patID[0] + "-" + response.getPatientNameFamilyName(i, 0) + ", " + patName[1]);
			String patAddr[] = response.getPatientAddress(i, 0);
			logger.debug("    Address: " + patAddr[3]);
			logger.debug("      Phone: " + response.getPatientPhoneHomeUnformattedTelephoneNumber(i, 0));
		}
	}
}
