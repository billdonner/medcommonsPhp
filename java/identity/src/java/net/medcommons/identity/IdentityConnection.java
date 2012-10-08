package net.medcommons.identity;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.Map;
import java.util.Properties;
import java.util.logging.Logger;

import net.medcommons.identity.util.HibernateUtil;

import org.hibernate.Session;

/**
 * This class maintains a JDBC connection to an identity
 * database, linking SAML-provided users to their MCIDs and medcommons 
 * servers.
 */
public class IdentityConnection {
    
    private static Logger log = Logger.getLogger(IdentityConnection.class.getName());
    
    private static final String INCOMING_USER_QUERY = "SELECT servers.url, users.mcid, users.email, identity_providers.name, users.first_name, users.last_name, users.mobile, users.smslogin "
        + " FROM external_users, users, identity_providers, servers "
        + " WHERE identity_providers.source_id = ? AND "
        + " external_users.username = ? AND "
        + " external_users.provider_id = identity_providers.id AND "
        + " external_users.mcid = users.mcid AND " + " users.server_id = servers.id";

    private static final String SERVER_QUERY = "SELECT servers.url, users.mcid, users.email, users.first_name, users.last_name, users.mobile, users.smslogin "
	+ " FROM users, servers "
	+ " WHERE users.mcid = ? AND users.server_id = servers.id";

    private static final String LOGIN_QUERY = "SELECT servers.url, users.mcid, users.email, users.first_name, users.last_name, users.mobile, users.smslogin "
        + " FROM users, servers "
        + " WHERE users.mcid = ? AND "
        + " users.sha1 = ? AND "
        + " users.server_id = servers.id AND " +
            "users.acctype = 'USER'";
    
    private static final String SMS_QUERY = "SELECT servers.url, users.mcid, users.email, users.first_name, users.last_name "
        + " FROM users, servers " + " WHERE users.mcid = ? AND " + " users.server_id = servers.id";
    
    private static final String SOURCE_QUERY = "SELECT identity_providers.name " + " FROM identity_providers "
    + " WHERE identity_providers.source_id = ?";
    
    private static final String MCID_QUERY = "SELECT users.mcid FROM users WHERE users.email = ?";
    
    private static final String NEW_USER_UPDATE = "INSERT INTO users(mcid, email, sha1, server_id, "
        + "                  first_name, last_name, mobile, smslogin, acctype) " + " VALUES(?, ?, ?, ?, ?, ?, ?, ?, 'USER')";
    
    private static final String LINK_USER_UPDATE = "INSERT INTO external_users(mcid, username, provider_id) "
        + " SELECT ?, ?, identity_providers.id " + " FROM identity_providers WHERE source_id = ?";
    
    /**
     * Connection used by this object
     */
    private Connection connection = null;
    
    /**
     * Hibernate session used by this object
     */
    private Session session = null;
    
    /**
     * Prepares a common set of SQL queries.
     */
    public IdentityConnection() throws SQLException {
        this.connection = this.getConnection();
    }
    
    private static final String INIT_MCID_QUERY = "SELECT mcid, since FROM users WHERE ? <= mcid AND mcid < ? "
    + " ORDER BY since DESC " + " LIMIT 1";
    
    /**
     * Retrieves the last MCID of a given type. This can then be used as a seed
     * to retrieve the next batch of MCIDs.
     * 
     * <pre>
     * pre: 0 &lt;= type &lt; 10
     * </pre>
     */
    public long getMCIDSeed(int type) throws SQLException {
        PreparedStatement stmt =  null; 
        long result = -1L;
        Timestamp since = null;
        
        try {
            stmt = connection.prepareStatement(INIT_MCID_QUERY);
            
            stmt.setLong(1, type * 1000000000000000L);
            stmt.setLong(2, (type + 1) * 1000000000000000L);            
            ResultSet r = stmt.executeQuery();
            
            try {
                if (r.next()) {
                    result = r.getLong(1);
                    since = r.getTimestamp(2);
                }
            }
            finally {
                r.close();
            }
        }
        finally {
            if(stmt != null)
                stmt.close();
        }

        // ssadedin: problem if no entries in database we end
        // up with no result here and "0" as the seed, which is
        // outside the prescribed range for type. Hence
        // force into type range.
        if(result < 0) {
            result = (type * 1000000000000000L + 1);
        }
        
        return result;
    }
    
    String getServerUrl(String mcid, Map userInfo) throws SQLException {
        PreparedStatement serverQuery = null;
        try {
            serverQuery = connection.prepareStatement(SERVER_QUERY);
            serverQuery.clearParameters();
            serverQuery.setString(1, mcid);

            ResultSet r = serverQuery.executeQuery();

            try {
                if (r.next()) {
                    userInfo.put("mcid", r.getString(2));
                    userInfo.put("email", r.getString(3));
                    userInfo.put("source_name", r.getString(4));
                    userInfo.put("first_name", r.getString(5));
                    userInfo.put("last_name", r.getString(6));
                    userInfo.put("mobile", r.getString(7));

                    return Expand.expand(r.getString(1), userInfo);
                }
                else
                    return null;
            }
            finally {
                r.close();
            }
        }
        finally {
            try { serverQuery.close(); } catch(Exception ex) {};
        }
    }

    /**
     * Retrieves a URL to the MedCommons server that hosts a particular user's
     * desktop.
     * 
     * <p>
     * The input is the SAML-provided source_id and username. These two fields
     * uniquely identify a user. Since the username is provided by an identity
     * provider, there may be duplicates: 'jim' may come in from St. Mungo's,
     * and be different from the 'jim' coming in from Boston General.
     * 
     * The output is a string URL of the form:
     * 'http://something.somewhere/desktop?mcid=XXXXXXXXXXXXXXXX' Tack
     * additional query parameters at the end, like hashpw or whatever.
     * 
     * <p>
     * If the user/source_id combination is not found, returns null.
     */
    String getServerUrl(String source_id, String user_name, Map userInfo) throws SQLException {
        
        PreparedStatement incomingUserQuery = null;
        try {
            incomingUserQuery = connection.prepareStatement(INCOMING_USER_QUERY);
            incomingUserQuery.clearParameters();
            incomingUserQuery.setString(1, source_id);
            incomingUserQuery.setString(2, user_name);
            
            ResultSet r = incomingUserQuery.executeQuery();
            
            try {
                if (r.next()) {
                    userInfo.put("mcid", r.getString(2));
                    userInfo.put("email", r.getString(3));
                    userInfo.put("source_name", r.getString(4));
                    userInfo.put("first_name", r.getString(5));
                    userInfo.put("last_name", r.getString(6));
                    userInfo.put("mobile", r.getString(7));
                    
                    if (r.getInt(8) > 0)
                        return SMS;
                    
                    return Expand.expand(r.getString(1), userInfo);
                }
                else
                    return null;
            }
            finally {
                r.close();
            }
        }
        finally {
            try { incomingUserQuery.close(); } 
            catch(Exception ex) {log.info(ex.getLocalizedMessage());};
        }
    }
    
    public static final String SMS = "SMS";
    
    /**
     * Retrieves a URL to the MedCommons server that hosts a particular user's
     * desktop.
     * 
     * <p>
     * The input is a form-provided MCID and raw password. The MCID should be a
     * 16-digit number, stripped of any user-provided spaces or dashes.
     * 
     * <p>
     * The output is a string URL of the form:
     * 'http://something.somewhere/desktop?mcid=XXXXXXXXXXXXXXXX' Tack
     * additional query parameters at the end, like hashpw or whatever.
     * 
     * <pre>
     * pre: mcid.length() == 16
     * </pre>
     * 
     * <p>
     * If the result is null, the login failed. No information about whether the
     * MCID was bad or the password was bad should be given to the user.
     * 
     * <p>
     * If the result is "SMS", send out an SMS code, and display a page waiting
     * for the SMS code to track back to real user.
     * @throws DisabledAccountException 
     */
    public String login(String mcid, String password, Map userInfo) throws SQLException, DisabledAccountException {
        return login0(mcid, password, userInfo, SMS);
    }
    
    private String login0(String mcid, String password, Map userInfo, String smsValue) throws SQLException, DisabledAccountException {
        
        PreparedStatement loginQuery = null;
        try {
            loginQuery = connection.prepareStatement(LOGIN_QUERY);
            loginQuery.clearParameters();
            loginQuery.setString(1, mcid);
            loginQuery.setString(2, Password.hash(mcid, password));

            ResultSet r = loginQuery.executeQuery();

            try {
                if (r.next()) {
                    userInfo.put("mcid", r.getString(2));
                    userInfo.put("email", r.getString(3));
                    userInfo.put("first_name", r.getString(4));
                    userInfo.put("last_name", r.getString(5));
                    userInfo.put("mobile", r.getString(6));

                    if (smsValue != null && r.getInt(7) > 0)
                        return smsValue;

                    return Expand.expand(r.getString(1), userInfo);
                }
                else
                    return null;
            }
            finally {
                r.close();
            }
        }
        finally {
            try { loginQuery.close(); } catch(Exception ex) {};
        }
    }
    
    public String sms(String mcid, Map userInfo) throws SQLException {
        
        
        PreparedStatement smsQuery = null;
        try {
            smsQuery = connection.prepareStatement(SMS_QUERY);
            smsQuery.clearParameters();
            smsQuery.setString(1, mcid);
            
            ResultSet r = smsQuery.executeQuery();
            
            try {
                if (r.next()) {
                    userInfo.put("mcid", r.getString(2));
                    userInfo.put("email", r.getString(3));
                    userInfo.put("first_name", r.getString(4));
                    userInfo.put("last_name", r.getString(5));
                    
                    return Expand.expand(r.getString(1), userInfo);
                }
                else
                    return null;
            }
            finally {
                r.close();
            }
        }
        finally {
            try { smsQuery.close(); } catch(Exception ex) {};
        }
    }
    
    /**
     * Given a source_id, passed in by cookies from our SAML Relying Party
     * server (ex: 'StMungos') return the display string (ex: "Saint Mungo's")
     */
    public String getSourceName(String source) throws SQLException {
        if (source != null) {
            
            PreparedStatement sourceQuery = null;
            try {
                sourceQuery = connection.prepareStatement(SOURCE_QUERY);
                sourceQuery.setString(1, source);
                ResultSet r = sourceQuery.executeQuery();
                
                try {
                    if (r.next())
                        return r.getString(1);
                }
                finally {
                    r.close();
                }
            }
            finally {
                try { sourceQuery.close(); } catch(Exception ex) {};
            }
        }
        return null;
    }
    
    /**
     * Retrieves the MCID given an email address.
     */
    public String getMCID(String email) throws SQLException {
        
        PreparedStatement mcidQuery = null;
        try {
            mcidQuery = connection.prepareStatement(MCID_QUERY);
            mcidQuery.clearParameters();
            mcidQuery.setString(1, email);
            
            ResultSet r = mcidQuery.executeQuery();
            
            try {
                if (r.next())
                    return r.getString(1);
                else
                    return null;
            }
            finally {
                r.close();
            }
        }
        finally {
            try { mcidQuery.close(); } catch(Exception ex) {};
        }
    }
    
    /**
     * Creates a new user in the database, given an MCID, an email address, and
     * the un-hashed raw password.
     * 
     * @param mcid
     *            16-character raw MCID, stripped of user-provided spaces or
     *            dashes
     * @param email
     *            E-mail address of user
     * @param password
     *            raw user-provided password
     */
    public String newUser(String mcid, String email, String password, Map info) throws SQLException {
        
        PreparedStatement newUserUpdate = null;
        try {
            newUserUpdate = connection.prepareStatement(NEW_USER_UPDATE);
            newUserUpdate.clearParameters();
            newUserUpdate.setString(1, mcid);
            newUserUpdate.setString(2, email);
            
            // Note that we explicitly allow null password.  This is
            // a special case which creates an innaccessible account that can 
            // only be accessed by support.
            if((password == null) || (password.length()==0)) {
                log.info("Creating disabled account " + mcid);
                newUserUpdate.setString(3, "");                
            }
            else                
                newUserUpdate.setString(3, Password.hash(mcid, password));
            
            newUserUpdate.setInt(4, 1);
            newUserUpdate.setString(5, (String) info.get("first_name"));
            newUserUpdate.setString(6, (String) info.get("last_name"));
            newUserUpdate.setString(7, (String) info.get("mobile"));
            newUserUpdate.setString(8, (String) info.get("smslogin"));            
            newUserUpdate.executeUpdate();            
            return login0(mcid, password, info, null);
        }
        catch (DisabledAccountException e) {
            // Expected exception - if disabled account is created
            return null;
        }
        finally {
            try { newUserUpdate.close(); } catch(Exception ex) {};
        }
    }

    private static final String ADD_ADDRESS_UPDATE =
	"INSERT INTO addresses(mcid, comment, address1, address2, " + 
        "                      city, state, postcode, country, telephone)" + 
        " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";

    public void addAddress(String mcid, Map info) throws SQLException {
        
        PreparedStatement addAddressUpdate = null;
        try {
            addAddressUpdate = connection.prepareStatement(ADD_ADDRESS_UPDATE);
    
            addAddressUpdate.clearParameters();
            addAddressUpdate.setString(1, mcid);
            addAddressUpdate.setString(2, (String) info.get("comment"));
            addAddressUpdate.setString(3, (String) info.get("address1"));
            addAddressUpdate.setString(4, (String) info.get("address2"));
            addAddressUpdate.setString(5, (String) info.get("city"));
            addAddressUpdate.setString(6, (String) info.get("state"));
            addAddressUpdate.setString(7, (String) info.get("postcode"));
            addAddressUpdate.setString(8, (String) info.get("country"));
            addAddressUpdate.setString(9, (String) info.get("telephone"));
            
            addAddressUpdate.executeUpdate();
        }
        finally {
            try { addAddressUpdate.close(); } catch(Exception ex) {};
        }
    }
    
    /**
     * Link a particular MCID to a SAML-provided incoming user.
     * 
     * @param mcid
     *            MCID of user
     * @param source_id
     *            identity provider as given via cookies by PingFederate
     * @param username
     *            external user as given via cookies by PingFederate
     */
    public void linkUser(String mcid, String source_id, String username) throws SQLException {
        
        PreparedStatement linkUserUpdate = null;
        try {
            linkUserUpdate = connection.prepareStatement(LINK_USER_UPDATE);
            linkUserUpdate.clearParameters();
            linkUserUpdate.setString(1, mcid);
            linkUserUpdate.setString(2, username);
            linkUserUpdate.setString(3, source_id);
            linkUserUpdate.executeUpdate();
        }
        finally {
            try { linkUserUpdate.close(); } catch(Exception ex) {};
        }
    }

    private static final String PASSWORD_UPDATE =
	"UPDATE users SET sha1 = ? WHERE users.mcid = ? AND users.sha1 = ? ";
    
    public boolean changePassword(String mcid, String oldPassword, String newPassword) throws SQLException {
        
        PreparedStatement passwordUpdate = null;
        try {
            passwordUpdate = connection.prepareStatement(PASSWORD_UPDATE);
            passwordUpdate.clearParameters();
            passwordUpdate.setString(1, Password.hash(mcid, newPassword));
            passwordUpdate.setString(2, mcid);
            passwordUpdate.setString(3, Password.hash(mcid, oldPassword));
            return passwordUpdate.executeUpdate() > 0;
        }
        finally {
            try { passwordUpdate.close(); } catch(Exception ex) {};
        }
    }

    private static final String LOG_UPDATE_0 =
	"INSERT INTO account_log (mcid, operation) VALUES (?, ?)";

    private static final String LOG_UPDATE_1 =
	"INSERT INTO account_log (mcid, username, provider_id, operation)" +
	" VALUES(?, ?, ?, ?)";

    private static final String PROVIDER_QUERY =
	"SELECT id FROM identity_providers WHERE source_id = ?";

    public void log(String mcid, String username, String providerName,
		    String operation) throws SQLException {
	PreparedStatement stmt;
	int provider_id = -1;

	if (username != null && providerName != null) {
            stmt = connection.prepareStatement(PROVIDER_QUERY);

	    try {
		stmt.clearParameters();
		stmt.setString(1, providerName);
		ResultSet r = stmt.executeQuery();

		if (r.next())
		    provider_id = r.getInt(1);
	    } finally {
		try { stmt.close(); } catch (Exception ex) {}
	    }
	}

	if (provider_id != -1) {
	    stmt = connection.prepareStatement(LOG_UPDATE_1);

	    try {
		stmt.clearParameters();
		stmt.setString(1, mcid);
		stmt.setString(2, username);
		stmt.setString(3, providerName);
		stmt.setString(4, operation);
		stmt.execute();
	    } finally {
		try { stmt.close(); } catch (Exception ex) {}
	    }
	}
	else {
	    stmt = connection.prepareStatement(LOG_UPDATE_0);

	    try {
		stmt.clearParameters();
		stmt.setString(1, mcid);
		stmt.setString(2, operation);
		stmt.execute();
	    } finally {
		try { stmt.close(); } catch (Exception ex) {}
	    }
	}

    }

    public synchronized Connection getConnection() throws SQLException {
      if(this.connection != null)
          return this.connection;
      this.session = HibernateUtil.getSession();      
      this.connection = this.session.connection();
      
      return this.connection;
    }
    
    public void close() {        
        if(this.connection != null) {
            this.session.close();
            this.session = null;
            this.connection = null;
        }
    }
    
}
