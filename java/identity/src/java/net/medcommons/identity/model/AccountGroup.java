/*
 * $Id$
 * Created on 21/07/2006
 */
package net.medcommons.identity.model;

import java.sql.Timestamp;
import java.util.Set;

import javax.persistence.CascadeType;
import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.JoinTable;
import javax.persistence.ManyToMany;
import javax.persistence.OneToOne;
import javax.persistence.Table;

@Entity
@Table(name="groupinstances")
public class AccountGroup {

    @Id
    @GeneratedValue(strategy=GenerationType.AUTO) 
    @Column(name="groupinstanceid")
    private Long id;
    
    private String name;
    
    // public Long accid;
    
    @Column(name="createdatetime")
    private Timestamp createDateTime;
    
    @ManyToMany(cascade = {CascadeType.PERSIST})
    @JoinTable(
        name="groupmembers",
        joinColumns={@JoinColumn(name="groupinstanceid")}
        ,inverseJoinColumns={@JoinColumn(name="memberaccid")}
    )
    private Set<User> users;    
    
    
    @ManyToMany(cascade = {CascadeType.PERSIST})
    @JoinTable(
        name="groupadmins",
        joinColumns={@JoinColumn(name="groupinstanceid")}
        ,inverseJoinColumns={@JoinColumn(name="adminaccid")}
    )
    private Set<User> admins;    
    
    /**
     * Each group has a user record associated with it.  This will probably contain
     * details of the group in the future, for now it is something of a placeholder.
     */
    @OneToOne(cascade={CascadeType.ALL})
    @JoinColumn(name="accid")
    private User user;

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public Timestamp getCreateDateTime() {
        return createDateTime;
    }

    public void setCreateDateTime(Timestamp createDateTime) {
        this.createDateTime = createDateTime;
    }

    public Set<User> getUsers() {
        return users;
    }

    public void setUsers(Set<User> users) {
        this.users = users;
    }

    public Long getAccid() {
        return this.user.getMcid();
    }

    public User getUser() {
        return user;
    }

    public void setUser(User user) {
        this.user = user;
    }

    public Set<User> getAdmins() {
        return admins;
    }

    public void setAdmins(Set<User> admins) {
        this.admins = admins;
    }
    
}
