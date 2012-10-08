/*
 * $Id$
 * Created on 09/03/2007
 */
package net.medcommons.identity.model;

import javax.persistence.CascadeType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.OneToOne;
import javax.persistence.Table;

@Entity
@Table(name="practice")
public class Practice {
    @Id
    @GeneratedValue(strategy=GenerationType.AUTO) 
    @Column(name="practiceid")
    public Long id;
    
    @Column(name="practicename")
    public String name;
    
    @OneToOne(cascade={CascadeType.PERSIST})
    @JoinColumn(name="providergroupid")
    public AccountGroup group;
    
    @Column(name="practiceRlsUrl")
    public String rlsUrl = "";
    
    @Column(name="practiceLogoUrl")
    public String logoUrl = "";
    
    @Column(name="accid")
    public long groupAccountId;

    public AccountGroup getGroup() {
        return group;
    }

    public void setGroup(AccountGroup group) {
        this.group = group;
    }

    public long getGroupAccountId() {
        return groupAccountId;
    }

    public void setGroupAccountId(long groupAccountId) {
        this.groupAccountId = groupAccountId;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getLogoUrl() {
        return logoUrl;
    }

    public void setLogoUrl(String logoUrl) {
        this.logoUrl = logoUrl;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getRlsUrl() {
        return rlsUrl;
    }

    public void setRlsUrl(String rlsUrl) {
        this.rlsUrl = rlsUrl;
    }
}
