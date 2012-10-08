package net.medcommons.mdl.utils;

import org.apache.axis.description.TypeDesc;

import OHFBridgeStub.AddressType;
import OHFBridgeStub.AssigningAuthorityType;
import OHFBridgeStub.CodedMetadataType;
import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;
import OHFBridgeStub.XdsDocType;

public class MetadataFormat {
	 public static String toString(AssigningAuthorityType assigningAuthorityType){
		  if (assigningAuthorityType == null) return("");
		  StringBuffer buf = new StringBuffer(" AssigningAuthority:");
		  buf.append("NamespaceId="); buf.append(assigningAuthorityType.getNamespaceId());
		  buf.append(",UniversalId="); buf.append(assigningAuthorityType.getUniversalId());
		  buf.append(",UniversalIdType="); buf.append(assigningAuthorityType.getUniversalIdType());
		  buf.append(",UniversalIdTypeCode="); buf.append(assigningAuthorityType.getUniversalIdTypeCode());
		
		  return(buf.toString());
	  }
	  
	  public static String toString(PatientNameType patientNameType){
		  if (patientNameType == null) return "";
		  StringBuffer buf = new StringBuffer(" PatientNameType:");
		  buf.append(" FamilyName="); buf.append(patientNameType.getFamilyName());
		  buf.append(" GivenName=");buf.append(patientNameType.getGivenName());
		  return(buf.toString());
	  }
	
	  public static String toString(AddressType address){
		  if (address == null){
			  return "";
		  }
		  StringBuffer buf = new StringBuffer("AddressType:");
		  buf.append(" City="); buf.append(address.getCity());
		  buf.append(" Country="); buf.append(address.getCountry());
		  buf.append(" StateOrProvince=");buf.append(address.getStateOrProvince());
		  buf.append(" StreetAddress="); buf.append(address.getStreetAddress());
		  buf.append(" ZipOrPostalCode="); buf.append(address.getZipOrPostalCode());
		  return(buf.toString());
		
	  }
	  public static String toString(PatientIdType patientIdType){
		  
		  if (patientIdType == null) return "";
		  StringBuffer buf = new StringBuffer(" PatientIdType:");
		  buf.append("IdNumber=");buf.append(patientIdType.getIdNumber());
		  buf.append(" AssigningAuthorityType=");buf.append(toString(patientIdType.getAssigningAuthorityType()));
		 
		  return(buf.toString());
	  }
	  public static String toString(PatientInfoType patientInfoType){
		  if (patientInfoType == null) return "";
		  StringBuffer buf = new StringBuffer("\nPatientInfoType:");
		  PatientNameType patientNameType = patientInfoType.getPatientName();
		  buf.append(toString(patientNameType));
		  
		  if (patientInfoType.getPatientIdentifier() != null){
			 
			  buf.append("\nPatientIdentifier:IdNumber=");buf.append( patientInfoType.getPatientIdentifier().getIdNumber());
			  		
			  AssigningAuthorityType assigningAuthorityType = patientInfoType.getPatientIdentifier().getAssigningAuthorityType();
			  buf.append(toString(assigningAuthorityType));
		  }
		  return(buf.toString());
		  
	  }
	  
	  public static String toString(CodedMetadataType codedMetadataType){
		  if (codedMetadataType == null)
			  return("");
		  StringBuffer buf = new StringBuffer(" CodedMetadataType:");
		  buf.append("Codename="); buf.append(codedMetadataType.getCodeName());
		  buf.append(",DisplayName="); buf.append(codedMetadataType.getDisplayName());
		  buf.append(",SchemeName="); buf.append(codedMetadataType.getSchemeName());
		  return(buf.toString());
	  }
	 
	  
	  public static String toString(XdsDocType document){
		  StringBuffer buf = new StringBuffer("XdsDocType:\n");
		  PatientInfoType[] authors = document.getAuthors();
		  if (authors != null){
			  buf.append("\n Authors:" + authors.length);
			  for (int i=0;i<authors.length;i++){
				  buf.append(toString(authors[i]));
			  }
		  }
		  String availabilityStatus = document.getAvailabilityStatus();
		  buf.append("\n AvailabilityStatus=");
		  buf.append(availabilityStatus);
		  
		  buf.append("\n Comments:");
		  buf.append(document.getComments());
		  CodedMetadataType classCode = document.getClassCode();
		  buf.append("\n ClassCode:"); buf.append(toString(classCode));
		  CodedMetadataType confidentialityCode = document.getConfidentialityCode();
		  buf.append("\n ConfidentialityCode:"); buf.append(toString(confidentialityCode));
		  
		  buf.append("\n DecodedDocument:"); buf.append(document.getDecodedDocument());
		  buf.append("\n CreationTime:"); buf.append(document.getCreationTime());
		  buf.append("\n EffectiveTime:"); buf.append(document.getEffectiveTime());
		  buf.append("\n DocumentTitle:"); buf.append(document.getDocumentTitle());
		  buf.append("\n DocumentSize:"); buf.append(document.getDocumentSize());
		  buf.append("\n EncodingType:"); buf.append(document.getEncodingType());
		  CodedMetadataType[] eventCodes = document.getEventCodes();
		  if (eventCodes != null){
			  buf.append("\n EventCodes:" + eventCodes.length);
			  for (int i=0;i<eventCodes.length;i++){
				  buf.append("\n   ");
				  buf.append(toString(eventCodes[i]));
			  }
		  }
		   
		  buf.append("\n FormatCode:"); buf.append(document.getFormatCode());
		  buf.append("\n HealthCareFacilityTypecode:");buf.append(toString(document.getHealthCareFacilityTypeCode()));
		  buf.append("\n LanguageCode:"); buf.append(document.getLanguageCode());
		  buf.append("\n MimeType:"); buf.append(document.getMimeType());
		  buf.append("\n ParentDocumentId:"); buf.append(document.getParentDocumentId());
		  buf.append("\n ParentDocumentRelationship:"); buf.append(document.getParentDocumentRelationship());
		  PatientInfoType patient = document.getPatientInfo();
		  buf.append("\n PatientInfo:"); buf.append(toString(patient));
		  buf.append("\n ServiceStartTime:"); buf.append(document.getServiceStartTime());
		  buf.append("\n ServiceStopTime:"); buf.append(document.getServiceStopTime());
		  CodedMetadataType practiceSettingCode = document.getPracticeSettingCode();
		  buf.append("\n SourcePatientId:"); buf.append(toString(document.getSourcePatientId()));
		  buf.append("\n PracticeSettingCode:"); buf.append(toString(practiceSettingCode));
		  
		  CodedMetadataType typeCode = document.getTypeCode();
		
		  if (document.getBase64EncodedDocument() != null){
			  buf.append("\n Base64EncapsulatedDocument:size=");
			  buf.append(document.getBase64EncodedDocument().length()); 
		  }
		  buf.append("\n TypeCode:"); buf.append(toString(typeCode));
		  return(buf.toString());
	  }
}
