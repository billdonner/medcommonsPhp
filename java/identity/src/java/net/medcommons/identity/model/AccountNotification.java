/*
 * $Id$
 * Created on 14/06/2006
 */
package net.medcommons.identity.model;
import javax.persistence.*;

@Entity
@Table(name="account_notifications")
public class AccountNotification {
    @Id
    @GeneratedValue(strategy=GenerationType.AUTO)
    public Long id = null;
    
    @ManyToOne()
    @JoinColumn(name="mcid")
    public User user;
    public String recipient;
    public String status;
}
