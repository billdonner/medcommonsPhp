/*
 * $Id$
 * Created on 24/04/2007
 */
package net.medcommons.identity.model;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.Table;

@Entity
@Table(name="account_rls")
public class AccountRLS {
    @Id
    @Column(name="ar_accid")
    private String accountId;
    
    @Column(name="ar_rls_url")
    private String rlsUrl;

    public String getAccountId() {
        return accountId;
    }

    public void setAccountId(String accountId) {
        this.accountId = accountId;
    }

    public String getRlsUrl() {
        return rlsUrl;
    }

    public void setRlsUrl(String rlsUrl) {
        this.rlsUrl = rlsUrl;
    }
}
