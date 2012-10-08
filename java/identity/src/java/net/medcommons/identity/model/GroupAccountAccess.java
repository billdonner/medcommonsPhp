/*
 * $Id$
 * Created on 22/07/2006
 */
package net.medcommons.identity.model;

import javax.persistence.Entity;
import javax.persistence.Table;

//@Entity
//@Table(name="group_account_access")
public class GroupAccountAccess {
    
    
    private Long accountId;
    
    private Long groupId;
    
    public GroupAccountAccess() {
        super();
    }
}
