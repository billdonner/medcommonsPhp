package net.medcommons.identity.model;

import java.util.Date;

import javax.persistence.*;

@Entity
@Table(name="authentication_token")
public class AuthenticationToken {
    
    @Id
    @GeneratedValue(strategy=GenerationType.AUTO) 
    @Column(name="at_id")
    Long id;
    
    @Column(name="at_token")
    String token;
    
    @Column(name="at_account_id")
    String accountId;
    
    @Column(name="at_create_date_time")
    Date createDateTime;
    
    @Column(name="at_es_id")
    Long esId;
    
    @Column(name="at_parent_id")
    Long parentId;
    
    @Column(name="at_secret")
    String secret;
    
    @Column(name="at_priority")
    String priority;
    
    public Long getId() {
        return id;
    }
    public void setId(Long id) {
        this.id = id;
    }
    public String getToken() {
        return token;
    }
    public void setToken(String token) {
        this.token = token;
    }
    public String getAccountId() {
        return accountId;
    }
    public void setAccountId(String accountId) {
        this.accountId = accountId;
    }
    public Date getCreateDateTime() {
        return createDateTime;
    }
    public void setCreateDateTime(Date createDateTime) {
        this.createDateTime = createDateTime;
    }
    public Long getEsId() {
        return esId;
    }
    public void setEsId(Long esId) {
        this.esId = esId;
    }
    public Long getParentId() {
        return parentId;
    }
    public void setParentId(Long parentId) {
        this.parentId = parentId;
    }
    public String getSecret() {
        return secret;
    }
    public void setSecret(String secret) {
        this.secret = secret;
    }
    public String getPriority() {
        return priority;
    }
    public void setPriority(String priority) {
        this.priority = priority;
    }
}
