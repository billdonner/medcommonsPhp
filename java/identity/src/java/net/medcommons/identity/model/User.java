/*
 * $Id$
 * Created on 13/06/2006
 */
package net.medcommons.identity.model;

import java.io.Serializable;
import java.sql.Timestamp;
import java.util.HashSet;
import java.util.Set;

import javax.persistence.CascadeType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToMany;
import javax.persistence.OneToMany;
import javax.persistence.Table;

@Entity
@Table(name="users")
public class User implements Serializable {
    
    @Id
    public Long mcid;    
    public String email;
    public String sha1;    
    public Long server_id;
    public Timestamp since;
    public String first_name;
    public String last_name;
    public String photoUrl;
    public String mobile;
    public Integer smslogin;
    public Integer updatetime;
    public long ccrlogupdatetime;
    
    @Column(name="enable_simtrak")
    private Integer enableSimtrak;
    
    @Column(name="enable_vouchers")
    private Integer enableVouchers;
    
    public Integer getEnableVouchers() {
        return enableVouchers;
    }

    public void setEnableVouchers(Integer enableVouchers) {
        this.enableVouchers = enableVouchers;
    }

    @Column(name="acctype")
    public String accountType;
    
    @ManyToMany(mappedBy="users")
    public Set<AccountGroup> groups;
    
    @OneToMany(cascade=CascadeType.ALL)
    @JoinColumn(name="ar_accid")
    private Set<AccountRLS> rlsSet = new HashSet<AccountRLS>();
    
    @Column(name="amazon_user_token")
    private String amazonUserToken;
    
    @Column(name="amazon_product_token")
    private String amazonProductToken;
    
    @Column(name="amazon_pid")
    private String amazonPID;

    public String getAmazonPID() {
        return amazonPID;
    }

    public void setAmazonPID(String amazonPID) {
        this.amazonPID = amazonPID;
    }

    public Long getMcid() {
        return mcid;
    }

    public void setMcid(Long mcid) {
        this.mcid = mcid;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getFirstName() {
        return first_name;
    }

    public void setFirstName(String first_name) {
        this.first_name = first_name;
    }

    public String getLastName() {
        return last_name;
    }

    public void setLastName(String last_name) {
        this.last_name = last_name;
    }

    public Timestamp getSince() {
        return since;
    }

    public void setSince(Timestamp since) {
        this.since = since;
    }

    public Set<AccountGroup> getGroups() {
        return groups;
    }

    public void setGroups(Set<AccountGroup> groups) {
        this.groups = groups;
    }

    public String getAccountType() {
        return accountType;
    }

    public void setAccountType(String accountType) {
        this.accountType = accountType;
    }

    public Set<AccountRLS> getRlsSet() {
        return rlsSet;
    }

    public void setRlsSet(Set<AccountRLS> rlsSet) {
        this.rlsSet = rlsSet;
    }

    public String getMobile() {
        return mobile;
    }

    public void setMobile(String mobile) {
        this.mobile = mobile;
    }

    public String getPhotoUrl() {
        return photoUrl;
    }

    public void setPhotoUrl(String photoUrl) {
        this.photoUrl = photoUrl;
    }

    public String getAmazonUserToken() {
        return amazonUserToken;
    }

    public void setAmazonUserToken(String amazon_user_token) {
        this.amazonUserToken = amazon_user_token;
    }

    public String getAmazonProductToken() {
        return amazonProductToken;
    }

    public void setAmazonProductToken(String amazon_pid) {
        this.amazonProductToken = amazon_pid;
    }

    public String getSha1() {
        return sha1;
    }

    public void setSha1(String sha1) {
        this.sha1 = sha1;
    }

    public Integer getEnableSimtrak() {
        return enableSimtrak;
    }

    public void setEnableSimtrak(Integer enableSimtrak) {
        this.enableSimtrak = enableSimtrak;
    }
}
