/*
 * $Id: TransferStatusServiceImpl.java 6939 2009-01-12 03:39:03Z ssadedin $
 * Created on 12/12/2008
 */
package net.medcommons.identity.ws;

import java.util.Date;
import java.util.List;

import net.medcommons.identity.util.HibernateUtil;
import net.medcommons.modules.services.interfaces.OutOfDateException;
import net.medcommons.modules.services.interfaces.ServiceException;
import net.medcommons.modules.services.interfaces.TransferMessage;
import net.medcommons.modules.services.interfaces.TransferState;
import net.medcommons.modules.services.interfaces.TransferStatusService;

import org.apache.log4j.Logger;
import org.hibernate.Session;

/**
 * SOAP implementation of TransferStatusService
 * 
 * @author ssadedin
 */
public class TransferStatusServiceImpl implements TransferStatusService {
    
    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(TransferStatusServiceImpl.class);

    public TransferState get(String accountId, String key) throws ServiceException {
        log.info(String.format("get: accountId %s, key %s", accountId,key));
        
        Session s = HibernateUtil.getSession();
        try { 
            return (TransferState) s.createQuery("from TransferState d where d.key = :key and d.accountId = :accid")
                     .setParameter("accid", accountId)
                     .setParameter("key", key).uniqueResult();
        }
        catch(Exception e) {
            throw new ServiceException("Unable to query for Transfer Status account Id = " + accountId + " key = " + key,e);
        }
        finally {
            s.close();
        }
    }

    @SuppressWarnings("unchecked")
    public List<TransferState> list(String accountId) throws ServiceException {
        log.info(String.format("list: accountId %s", accountId));
        
        Session s = HibernateUtil.getSession();
        try {
            return s.createQuery("from TransferStatus d where d.key = ?")
                     .setParameter(1, accountId)
                     .list();
        }
        catch(Exception e) {
            throw new ServiceException("Unable to query Transfer Status entries for account Id = " + accountId + "("+e.getMessage()+")",e);
        }
        finally {
            s.close(); 
        }
    }

    public TransferState put(TransferState status) throws ServiceException, OutOfDateException {
        log.info(String.format("put: " + status.toString()));
        Session s = HibernateUtil.getSession();
        try {
            s.beginTransaction();
            TransferState old = (TransferState) s.get(TransferState.class, status.getKey());
            if(old != null) {
                old.setStatus(status.getStatus());
                old.setProgress(status.getProgress());
                if(status.getVersion() != old.getVersion())
                    throw new OutOfDateException("Stale data - version mismatch: " + old.getVersion() + " != " + status.getVersion());
                    
                old.setModified(new Date());
                old.setVersion(old.getVersion()+1);
                s.update(old);
            }
            else {
                status.setModified(new Date());
                s.save(status);
                old = status;
            }
            s.getTransaction().commit();
            old = (TransferState) s.load(TransferState.class, status.getKey());
            return old;
        }
        catch(ServiceException e) {
            throw e;
        }
        catch(Exception e) {
            if(s.getTransaction()!=null && s.getTransaction().isActive())
                s.getTransaction().rollback(); 
            
            throw new ServiceException("Unable to update or create Transfer Status " + status.toString(), e);
        }
        finally {
            s.close();
        }
        
    }

    /**
     * Save the given message 
     */
    public void addMessage(TransferMessage message) throws ServiceException {
        log.info(String.format("message: " + message.toString()));
        Session s = HibernateUtil.getSession();
        try {
            s.beginTransaction();
            
            // Validate that there is a corresponding transfer
            if(message.getTransferKey() != null) {
                TransferState state = 
                    (TransferState) s.createQuery("from TransferState s where s.key = :key")
                                     .setString("key", message.getTransferKey())
                                     .uniqueResult();
                if(state == null) 
                    throw new ServiceException("Unknown transfer state " + message.getTransferKey() + " specified");
            }
            
            s.save(message);
            s.getTransaction().commit();
        }
        catch(ServiceException e) {
            throw e;
        }
        catch(Exception e) {
            if(s.getTransaction()!=null && s.getTransaction().isActive())
                s.getTransaction().rollback(); 
            throw new ServiceException("Unable to save transfer message " + message.toString(), e);
        }
        finally {
            s.close();
        }
     }

}
