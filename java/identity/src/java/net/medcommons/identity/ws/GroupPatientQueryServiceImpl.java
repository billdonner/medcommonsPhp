package net.medcommons.identity.ws;

import static net.medcommons.modules.utils.Str.blank;

import java.text.SimpleDateFormat;
import java.util.List;

import net.medcommons.identity.model.CCREvent;
import net.medcommons.identity.util.HibernateUtil;
import net.medcommons.modules.services.interfaces.GroupPatientQueryService;
import net.medcommons.modules.services.interfaces.PatientDemographics;
import net.medcommons.modules.services.interfaces.ServiceException;
import net.medcommons.modules.utils.Str;

import org.apache.log4j.Logger;
import org.hibernate.Criteria;
import org.hibernate.Query;
import org.hibernate.Session;
import org.hibernate.criterion.Restrictions;

public class GroupPatientQueryServiceImpl implements GroupPatientQueryService {
    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(GroupPatientQueryServiceImpl.class);

    @Override
    public PatientDemographics[] query(String firstName, String lastName, String sex, String auth) throws ServiceException {
        
        log.info("Patient Query: firstName=" + firstName + " lastName="+lastName + "sex=" + sex + " auth="+auth);
        
        Session s = HibernateUtil.getSession();
        try {
            
            StringBuffer sb = 
                new StringBuffer("select e from CCREvent e, AuthenticationToken at " +
					             "where e.practice.groupAccountId = at.accountId ")
					     .append("and at.token = :auth ")
                         .append("and e.viewStatus is not null ");            
            
            if(firstName != null)
                sb.append("and e.patientGivenName = :firstName ");
            
            if(lastName != null)
                sb.append("and e.patientFamilyName = :lastName ");
            
            if(sex != null)
                sb.append("and e.patientSex = :sex ");
            
            String querySql = sb.toString();
            if(log.isInfoEnabled()) 
                log.info("Querying for patients using " + querySql);
            
            Query query = s.createQuery(querySql)
                           .setString("auth", auth);
            
            if(firstName != null)
                query.setString("firstName", firstName);
            
            if(lastName != null)
                query.setString("lastName", lastName);
            
            if(sex != null)
                query.setString("sex", sex);
                    
             List<CCREvent> events = query.list();
                             
            
            log.info("Found " + events.size() + " patients from search");
            SimpleDateFormat fmt = new SimpleDateFormat("dd MMM yyyy H:m:s z");
            
            PatientDemographics [] results = new PatientDemographics[events.size()];
            int i=0;
            for(CCREvent evt : events) {
                PatientDemographics result = new PatientDemographics();
                result.setAccountId(evt.getPatientIdentifier());
                result.setGivenName(evt.getPatientGivenName());
                result.setFamilyName(evt.getPatientFamilyName());
                result.setSex(evt.getPatientSex());
                
                if(!blank(evt.getDob()))
	                result.setDateOfBirth(fmt.parse(evt.getDob()));
                results[i++] = result;
            }
            return results;
        }
        catch (Exception e) {
            throw new ServiceException("Failed to query patients using [givenName:"+firstName+",familyName:"+lastName+",sex:"+sex+"] for auth="+auth,e);
        }
        finally {
            HibernateUtil.closeSession();
        }
    }
}
