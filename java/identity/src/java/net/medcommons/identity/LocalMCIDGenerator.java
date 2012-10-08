/*
 * $Id: LocalMCIDGenerator.java 3004 2007-03-12 06:57:25Z ssadedin $
 * Created on 04/09/2006
 */
package net.medcommons.identity;

import java.io.IOException;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;
import java.util.TreeMap;

import net.medcommons.identity.util.HibernateUtil;

import org.apache.log4j.Logger;
import org.hibernate.Session;

/**
 * A simple dummy version of the MCID Generator useful for development purposes. Should
 * not be used in production.  Uses a simple algorithm to return unpredictable yet
 * complete range of ids. 
 * 
 * @author ssadedin
 */
public class LocalMCIDGenerator extends MCIDGenerator {
    /**
     * Size of mcid allocation block
     */
    private static final int RANGE_SIZE = 1024;

    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(LocalMCIDGenerator.class);
    
    /**
     * Lower offset for ids
     */
    private long lo = 0;
    
    /**
     * Iterates over mcids as they are allocated.
     */
    private Iterator<Long> position = null;
    
    /**
     * Set of mcids to be allocated
     */
    private TreeMap<Double, Long> mcids = new TreeMap<Double, Long>();
    
    public LocalMCIDGenerator() {
        log.info("Created new LocalMCIDGenerator");
    }
    
    public String nextMCIDString() throws IOException {
        return String.valueOf(nextMCID());
    }

    @Override
    public long nextMCID() throws IOException {
        if(position == null || !position.hasNext()) {
            init();
        }
        return lo+position.next();
    }

    /**
     * Finds the next range of mcids to allocate
     */
    synchronized private void init() {
        Session s = HibernateUtil.getSession();
        try {
            s.beginTransaction();
            // Find highest mcid
            List<Long> highest = s.createQuery("select max(u.id) from User u").list(); 
            lo = (highest == null || highest.isEmpty()) ? 1000000000000000L :  highest.get(0)+1;
            log.info("mcid range initialized at " + lo);
            long timeMs = System.currentTimeMillis();
            for(long i=0;i<RANGE_SIZE; ++i) {
                mcids.put(Math.random(), i); // insert to map to get "random" order
            }
            position = mcids.values().iterator();
            log.info("Initialized mcids in " + (System.currentTimeMillis() - timeMs) + " ms");
            s.getTransaction().commit();
        }
        catch(Exception e) {
            log.error("Unable to initialize local mcid generator",e);
            if(s.getTransaction()!=null)
                s.getTransaction().rollback();
            throw new RuntimeException("unable to initialize mcid range",e); // hack, but we need to confirm to parent interface
        }
        finally {
            s.close();
        }
    }
}
