package net.medcommons.identity.model;

import javax.persistence.*;

/**
 * Domain class for practice CCR events.
 * <p>
 * Note that this class is not valid for inserts because 
 * the primary key is not mapped correctly.
 * 
 * @author ssadedin
 */
@Entity
@Table(name="practiceccrevents")
public class CCREvent {
    
    @ManyToOne(cascade={CascadeType.PERSIST})
    @JoinColumn(name="practiceid")
    Practice practice;
    
    String patientGivenName;
    String patientFamilyName;
    String patientIdentifier;
    String patientIdentifierSource;
    
    /**
     * We specify a primary key for hibernate's sake *even though* this column
     * is not actually a primary key of the table.  We CANNOT insert this class.
     */
    @Id
    String guid;
    
    String purpose;
    String senderProviderId;
    String receiverProviderId;
    String dob;
    String cxpServerURL;
    String cxpServerVendor;
    String viewerURL;
    String comment;
    Long creationDateTime;
    String confirmationCode;
    String registrySecret;
    String patientSex;
    String patientAge;
    String status;
    String viewStatus;
    
    public String getPatientGivenName() {
        return patientGivenName;
    }
    public void setPatientGivenName(String patientGivenName) {
        this.patientGivenName = patientGivenName;
    }
    public String getPatientFamilyName() {
        return patientFamilyName;
    }
    public void setPatientFamilyName(String patientFamilyName) {
        this.patientFamilyName = patientFamilyName;
    }
    public String getPatientIdentifier() {
        return patientIdentifier;
    }
    public void setPatientIdentifier(String patientIdentifier) {
        this.patientIdentifier = patientIdentifier;
    }
    public String getPatientIdentifierSource() {
        return patientIdentifierSource;
    }
    public void setPatientIdentifierSource(String patientIdentifierSource) {
        this.patientIdentifierSource = patientIdentifierSource;
    }
    public String getGuid() {
        return guid;
    }
    public void setGuid(String guid) {
        this.guid = guid;
    }
    public String getPurpose() {
        return purpose;
    }
    public void setPurpose(String purpose) {
        this.purpose = purpose;
    }
    public String getSenderProviderId() {
        return senderProviderId;
    }
    public void setSenderProviderId(String senderProviderId) {
        this.senderProviderId = senderProviderId;
    }
    public String getReceiverProviderId() {
        return receiverProviderId;
    }
    public void setReceiverProviderId(String receiverProviderId) {
        this.receiverProviderId = receiverProviderId;
    }
    public String getDob() {
        return dob;
    }
    public void setDob(String dob) {
        this.dob = dob;
    }
    public String getCxpServerURL() {
        return cxpServerURL;
    }
    public void setCxpServerURL(String cxpServerURL) {
        this.cxpServerURL = cxpServerURL;
    }
    public String getCxpServerVendor() {
        return cxpServerVendor;
    }
    public void setCxpServerVendor(String cxpServerVendor) {
        this.cxpServerVendor = cxpServerVendor;
    }
    public String getViewerURL() {
        return viewerURL;
    }
    public void setViewerURL(String viewerURL) {
        this.viewerURL = viewerURL;
    }
    public String getComment() {
        return comment;
    }
    public void setComment(String comment) {
        this.comment = comment;
    }
    public Long getCreationDateTime() {
        return creationDateTime;
    }
    public void setCreationDateTime(Long creationDateTime) {
        this.creationDateTime = creationDateTime;
    }
    public String getConfirmationCode() {
        return confirmationCode;
    }
    public void setConfirmationCode(String confirmationCode) {
        this.confirmationCode = confirmationCode;
    }
    public String getRegistrySecret() {
        return registrySecret;
    }
    public void setRegistrySecret(String registrySecret) {
        this.registrySecret = registrySecret;
    }
    public String getPatientSex() {
        return patientSex;
    }
    public void setPatientSex(String patientSex) {
        this.patientSex = patientSex;
    }
    public String getPatientAge() {
        return patientAge;
    }
    public void setPatientAge(String patientAge) {
        this.patientAge = patientAge;
    }
    public String getStatus() {
        return status;
    }
    public void setStatus(String status) {
        this.status = status;
    }
    public String getViewStatus() {
        return viewStatus;
    }
    public void setViewStatus(String viewStatus) {
        this.viewStatus = viewStatus;
    }
    public Practice getPractice() {
        return practice;
    }
    public void setPractice(Practice practice) {
        this.practice = practice;
    }
}
