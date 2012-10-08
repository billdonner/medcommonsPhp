/*
 * $Id$
 * Created on 12/03/2007
 */
package net.medcommons.identity.action;

import java.util.Collections;

import org.apache.log4j.Logger;
import org.hibernate.Hibernate;
import org.hibernate.HibernateException;
import org.hibernate.Session;

import net.medcommons.identity.model.AccountGroup;
import net.medcommons.identity.model.User;
import net.medcommons.identity.util.HibernateUtil;
import net.sourceforge.stripes.action.ActionBean;
import net.sourceforge.stripes.action.ActionBeanContext;
import net.sourceforge.stripes.action.After;
import net.sourceforge.stripes.action.ForwardResolution;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.ajax.JavaScriptResolution;
import net.sourceforge.stripes.controller.LifecycleStage;

public class UserAction implements ActionBean {
    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(UserAction.class);
    
    private ActionBeanContext ctx; 
    
    private User user;
    
    private Long groupId;
    
    private boolean admin = false;
    
    /**
     * Hibernate session
     */
    private Session s;
    
    /**
     * Sets the user to admin, returns JSON Object with status = "success" if successful. 
     * 
     * @return
     */
    public Resolution setAdmin() {        
        // If user is admin, remove them from admins of group, otherwise add them
        try {
            s.beginTransaction();
            
            // Find the group that we want to remove them from
            for (AccountGroup g : this.user.getGroups()) {
                if(g.getId().equals(this.groupId)) {
                    if(admin) { // Add as admin
                        g.getAdmins().add(this.user);
                    }
                    else {
                        g.getAdmins().remove(this.user);
                    }
                    s.update(g);
                    break;
                }
            }
            
            s.getTransaction().commit();
        }
        catch (HibernateException e) {
            try { s.getTransaction().rollback();}catch (Throwable t) {};
            throw e;
        }
        return new JavaScriptResolution( Collections.singletonMap("status", "ok"));
    }
    
    public ActionBeanContext getContext() {
        return this.ctx;
    }

    public void setContext(ActionBeanContext ctx) {
        this.ctx = ctx;

        // Load the user specified
        this.s = HibernateUtil.getThreadSession();
        try {
            s.beginTransaction();
            this.user = (User) s.load(User.class, Long.parseLong(ctx.getRequest().getParameter("user.mcid")));
            s.getTransaction().commit();
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }

    public boolean getAdmin() {
        return admin;
    }

    public void setAdmin(boolean admin) {
        this.admin = admin;
    }

    public Long getGroupId() {
        return groupId;
    }

    public void setGroupId(Long groupId) {
        this.groupId = groupId;
    }

}
