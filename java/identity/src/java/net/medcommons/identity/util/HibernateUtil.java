/*
 * $Id$
 * Created on 13/06/2006
 */
package net.medcommons.identity.util;

import java.util.Properties;

import net.medcommons.identity.model.*;

import org.hibernate.Session;
import org.hibernate.SessionFactory;
import org.hibernate.cfg.AnnotationConfiguration;

public class HibernateUtil {

    private static SessionFactory sessionFactory;
    
    private static ThreadLocal<Session> session = new ThreadLocal<Session>();

    public static void init(Properties properties) {
        try {
            // Create the SessionFactory from hibernate.cfg.xml
            sessionFactory = new AnnotationConfiguration()
                .addAnnotatedClass(User.class)
                .addAnnotatedClass(AccountNotification.class)
                .addAnnotatedClass(AccountGroup.class)
                .addAnnotatedClass(Practice.class)
                .addAnnotatedClass(AccountRLS.class)
                .addAnnotatedClass(AuthenticationToken.class)
                .addAnnotatedClass(CCREvent.class)
                .addAnnotatedClass(MCProperty.class)
                .addResource("net/medcommons/identity/model/DICOMStatus.hbm.xml")                
                .setProperties(properties)
                .buildSessionFactory();
        } catch (Throwable ex) {
            // Make sure you log the exception, as it might be swallowed
            System.err.println("Initial SessionFactory creation failed." + ex);
            throw new ExceptionInInitializerError(ex);
        }
    }

    public static SessionFactory getSessionFactory() {
        return sessionFactory;
    }

    public static void closeSession() {
        if(session.get() != null) {
            session.get().close();
            session.set(null);
        }
    }
    
    /**
     * Caller is responsible for closing returned session.
     */
    public static Session getSession() {
       return sessionFactory.openSession();
    }
    
    /**
     * Designed to work with HibernateFilter, usually caller will not have to 
     * call closeSession() if the caller is in scope of HibernateFilter (eg. Stripes)
     * @return
     */
    public static Session getThreadSession() {
        if(session.get() == null) {
            session.set(sessionFactory.openSession());
        }
        return session.get();
    }
}
