/*
 * $Id$
 * Created on 21/07/2006
 */
package net.medcommons.identity.action;

import java.sql.Timestamp;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

import javax.servlet.ServletException;

import org.apache.log4j.Logger;
import org.hibernate.Hibernate;
import org.hibernate.HibernateException;
import org.hibernate.ObjectNotFoundException;
import org.hibernate.Session;

import net.medcommons.identity.IdentityServlet;
import net.medcommons.identity.MCIDGenerator;
import net.medcommons.identity.model.AccountGroup;
import net.medcommons.identity.model.Practice;
import net.medcommons.identity.model.User;
import net.medcommons.identity.util.HibernateUtil;
import net.medcommons.modules.services.interfaces.AccountType;
import net.sourceforge.stripes.action.ActionBean;
import net.sourceforge.stripes.action.ActionBeanContext;
import net.sourceforge.stripes.action.After;
import net.sourceforge.stripes.action.ForwardResolution;
import net.sourceforge.stripes.action.RedirectResolution;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.controller.LifecycleStage;
import net.sourceforge.stripes.validation.EmailTypeConverter;
import net.sourceforge.stripes.validation.LocalizableError;
import net.sourceforge.stripes.validation.SimpleError;
import net.sourceforge.stripes.validation.Validate;
import net.sourceforge.stripes.validation.ValidateNestedProperties;
import net.sourceforge.stripes.validation.ValidationError;
import net.sourceforge.stripes.validation.ValidationErrors;

/**
 * Various actions for administering groups.
 * 
 * @author ssadedin
 */
public class GroupsAction implements ActionBean {
    
    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(GroupsAction.class);
    
    public static final String AccountIdFormat = "[0-9]{4} *[0-9]{4} *[0-9]{4} *[0-9]{4} *"; 

    private ActionBeanContext ctx; 
    
    /**
     * List of all groups
     */
    private List<AccountGroup> groups;
    
    /**
     * New group when we are adding one
     */
    @ValidateNestedProperties( { 
        @Validate(on={"add"}, field="name", required=true)
        /* @Validate(on={"add"}, field="user.email", required=true, converter=EmailTypeConverter.class)  */
        })
    private AccountGroup group = null;
    
    @Validate(on={"add"}, required=true,  mask=AccountIdFormat)
    private String adminAccountId = null;
    
    /**
     * Whether to create a practice when adding a group
     */
    private boolean createPractice;
    
    /**
     * User when adding users to groups
     */
    @Validate(on={"addUser"}, required=true,  mask=AccountIdFormat)
    private String userId;
    
    /**
     * Hibernate session
     */
    private Session s;
    
    public GroupsAction() {
        super();
    }
    
    public Resolution add() throws Exception {
        try {
            s.beginTransaction();
            
            // Load the admin user for the group
            User admin = (User) s.get(User.class, Long.parseLong(adminAccountId.replaceAll(" ", "")));
            if(admin == null) {
                log.error("Invalid account id " + adminAccountId + " specified for new group");
                this.ctx.getValidationErrors().add("adminAccountId",new SimpleError("Account {1} is not a valid user account", adminAccountId));
                return this.ctx.getSourcePageResolution();
            }
            
            Long accid = MCIDGenerator.getInstance().nextMCID();
            group.setId(null); // autoalloc by db
            group.setCreateDateTime(new Timestamp(System.currentTimeMillis()));
            group.setUser(new User());
            group.getUser().setMcid(accid);
            group.getUser().server_id = 0L;
            group.getUser().updatetime = new Integer((int)System.currentTimeMillis());
            group.getUser().ccrlogupdatetime = 0L;
            group.getUser().setAccountType(AccountType.GROUP.name());
            group.setUsers(new HashSet<User>());
            group.getUser().setGroups(new HashSet<AccountGroup>());
            group.getUser().setFirstName(admin.getFirstName());
            group.getUser().setLastName(admin.getLastName());
            group.getUser().setEmail(admin.getEmail());
            
            // Add the admin as member and admin of the group
            group.getUsers().add(admin);
            group.setAdmins(new HashSet<User>());
            group.getAdmins().add(admin); 
            
            s.save(group.getUser());
            s.save(group);
            
            Practice p = new Practice();
            p.setGroup(group);
            p.setName(group.getName());
            p.setGroupAccountId(accid);
            s.save(p);

            // Now update the practice with an appropriate RLS url
            String defaultRlsHost = IdentityServlet.config.getProperty("defaultRlsHost");
            if(defaultRlsHost == null) {
                log.warn("No default RLS Host configured:  practice created without RLS");
            }
            else {
                p.setRlsUrl(defaultRlsHost + "/acct/ws/R.php?pid="+p.getId());
                s.update(p);
            }
            
            s.getTransaction().commit();
            return new RedirectResolution(GroupsAction.class, "list");
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }
    
    public Resolution list() throws Exception {
        try {
            s.beginTransaction();
            groups = s.createQuery("from AccountGroup").list();
            return new ForwardResolution("/WEB-INF/jsp/groups.jsp");
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }
    
    public Resolution edit() throws HibernateException {
        try {
            s.beginTransaction();
            
            this.group = (AccountGroup)s.load(AccountGroup.class, this.group.getId());
            
            Set<User> users = this.group.getUsers();
            Hibernate.initialize(users);
            Hibernate.initialize(this.group.getAdmins());
            
            return new ForwardResolution("/WEB-INF/jsp/groupEdit.jsp");
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }
    
    public Resolution delete() throws HibernateException {
        try {
            s.beginTransaction();

            this.group = (AccountGroup)s.load(AccountGroup.class, this.group.getId());
            
            s.delete(this.group);
            
            s.getTransaction().commit();
            
            this.group = new AccountGroup();
            
            return new RedirectResolution(this.getClass(), "list");
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }    
    
    public Resolution saveGroupDetails() {
        try {
            s.beginTransaction();
            s.update(this.group);
            s.getTransaction().commit();
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
        return edit();
    }

    public Resolution addUser() throws Exception {
        try {
            s.beginTransaction();
            User u = (User) s.get(User.class, Long.parseLong(userId.replaceAll("[ \t-]","")));
            // this.group = (AccountGroup)s.load(AccountGroup.class, this.group.getId());
            for (User user : this.group.getUsers()) {
                Hibernate.initialize(user);
            }
            ValidationErrors errors = new ValidationErrors();
            if(u==null) {
                errors.add( "userId", new LocalizableError("addGroupUser.invalidAccountId") );
                getContext().setValidationErrors(errors);
                return getContext().getSourcePageResolution();                            
            }
            if(!this.group.getUsers().contains(u)) {
                this.group.getUsers().add(u);
                s.update(this.group);
                s.getTransaction().commit(); 
            }
            else {
                errors.add( "userId", new LocalizableError("addGroupUser.alreadyMember") );
                getContext().setValidationErrors(errors);
                return getContext().getSourcePageResolution();                                            
            }
            return new ForwardResolution("/WEB-INF/jsp/groupEdit.jsp"); 
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }

    public Resolution removeUser() throws Exception {
        try {
            s.beginTransaction();
            User u = (User) s.load(User.class, Long.parseLong(userId.replaceAll("[ \t-]","")));
            this.group = (AccountGroup)s.load(AccountGroup.class, this.group.getId());
            this.group.getUsers().remove(u);
            
            s.update(this.group);
            s.getTransaction().commit();
            
            for (User user : this.group.getUsers()) {
                Hibernate.initialize(user);
            }
            return new ForwardResolution("/WEB-INF/jsp/groupEdit.jsp");
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
    }
    
    public ActionBeanContext getContext() {
        return ctx;
    }       

    public void setContext(ActionBeanContext ctx) {
        this.s = HibernateUtil.getThreadSession();
        String groupId = ctx.getRequest().getParameter("group.id");
        
        AccountGroup g = (AccountGroup) ctx.getRequest().getSession().getAttribute("group");
        if(g != null && g.getId() != null) { // Use id from session if it is available
            groupId = g.getId().toString();
        }
        
        if(groupId == null) {
            ctx.getRequest().getSession().setAttribute("group", new AccountGroup());
            this.group = (AccountGroup) ctx.getRequest().getSession().getAttribute("group");
        }
        else {
            this.group = (AccountGroup) s.get(AccountGroup.class, Long.parseLong(groupId));
            if(this.group == null)
                this.group = new AccountGroup();
            ctx.getRequest().getSession().setAttribute("group", this.group); // hack: not sure what depends on this
        }
        this.ctx = ctx;
    }

    public List<AccountGroup> getGroups() {
        return groups;
    }

    public void setGroups(List<AccountGroup> groups) {
        this.groups = groups;
    }

    public AccountGroup getGroup() {
        return group;
    }

    public void setGroup(AccountGroup group) {
        this.group = group;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public boolean isCreatePractice() {
        return createPractice;
    }

    public void setCreatePractice(boolean createPractice) {
        this.createPractice = createPractice;
    }

    public String getAdminAccountId() {
        return adminAccountId;
    }

    public void setAdminAccountId(String adminAccountId) {
        this.adminAccountId = adminAccountId;
    }
}
