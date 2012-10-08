package net.medcommons.identity;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.io.Writer;
import java.io.UnsupportedEncodingException;

import java.net.URLEncoder;
import java.net.URLDecoder;

import java.util.Map;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Enumeration;
import java.util.Properties;

import java.sql.SQLException;

import javax.servlet.ServletConfig;
import javax.servlet.ServletContext;
import javax.servlet.ServletException;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;
import org.hibernate.HibernateException;
import org.hibernate.Session;
import org.json.JSONObject;

import net.medcommons.identity.model.AccountGroup;
import net.medcommons.identity.model.User;
import net.medcommons.identity.util.HibernateUtil;
import net.medcommons.modules.services.utils.RESTConfiguration;
import net.medcommons.modules.services.utils.RESTConfigurationException;
import net.medcommons.modules.services.utils.RESTException;
import net.medcommons.modules.services.utils.RESTUtil;

import com.pingidentity.adapters.pftoken.sp.PFTokenSpAgent;
import com.pingidentity.adapters.pftoken.core.PFTokenException;

/**
 * Abstract Base Class for the identity webapp's servlets.
 *
 * <p>
 * This class handles the cookies provided by the PingFederate server,
 * maintains the pool of IdentityConnections, and handles the default
 * case of displaying the login/registration page.
 */
public class IdentityServlet extends HttpServlet {
    
    /**
     * Default URL to document service, used if not configured
     */
    private static final String DOCUMENT_SERVICE_URL = "http://localhost/secure";

    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(IdentityServlet.class);

    /** Location in web archive of login/register page. */
    protected static final String CHOICE_JSP_PAGE = "/WEB-INF/jsp/choice.jsp";

    /** Prefix of cookies we need to see. */
    protected static final String COOKIE_PREFIX = "_RP:";

    /** Configuration properties */
    public static Properties config = new Properties();

    /** map hibernate properties to configuration web.xml properties */
    private static final String[][] HIBERNATE_PROPERTIES = {
	{ "hibernate.connection.username",      "db.user" },
	{ "hibernate.connection.password",      "db.password" },
	{ "hibernate.connection.url",           "database" },
	{ "hibernate.connection.driver_class",  "driver"}
    };

    /**
     * Initialize this servlet by getting the properties for
     * the database connection, connecting to the database, and
     * creating a pool of IdentityConnection objects.
     */
    public void init(ServletConfig servletConfig) throws ServletException {
	ServletContext ctx = servletConfig.getServletContext();

        super.init(servletConfig);

        synchronized(config) {
            if(config.isEmpty()) {
		Enumeration e = ctx.getInitParameterNames();

		while (e.hasMoreElements()) {
		    String name = (String) e.nextElement();
		    String value = ctx.getInitParameter(name);

		    config.setProperty(name, value);
		}

		// Load config properties, if there are any
		String propertyFileName = config.getProperty("properties");
		log.info("Property file name is " + propertyFileName);
		if (propertyFileName != null) {
		    File propertyFile = new File(System.getProperty("catalina.base"),
						 propertyFileName);
		    log.info("Property file:" + propertyFile.getAbsolutePath());
		    // Load local customizations if they exist
		    try {
			InputStream f = new FileInputStream(propertyFile);

			try {
			    config.load(f);
			} finally {
			    f.close();
			}
		    }
		    catch (FileNotFoundException ex) {
		    	
			log.warn("No local properties '" + propertyFile.getAbsolutePath() + "' found");
		    }
		    catch (IOException ex) {
		    	log.warn("Error while reading from '" + propertyFile.getAbsolutePath() + "'");
			ex.printStackTrace(System.err);
		    }
		}
		
		// A hack that makes the DocumentService available on localhost if not found elsewhere
		if(config.getProperty("DocumentService.createAuthenticationToken.url")==null) {
		    String createAuthTokenServiceUrl = DOCUMENT_SERVICE_URL + "/ws/createAuthenticationToken.php";
		    log.info("Using default document service location: "+createAuthTokenServiceUrl);
		    config.setProperty("DocumentService.createAuthenticationToken.url", createAuthTokenServiceUrl);
		}
        
        RESTUtil.init(new RESTConfiguration() {
            public String getProperty(String key) throws RESTConfigurationException {
                return config.getProperty(key);
            }
            public String getProperty(String key, String arg1) {
                String value = config.getProperty(key);
                return value == null ? arg1 : value;
            }
            public int getProperty(String key, int arg1) {
                String value = config.getProperty(key);
                return value == null ? arg1 : Integer.parseInt(value);
            }
            public boolean getProperty(String key, boolean arg1) {
                String value = config.getProperty(key);
                return value == null ? arg1 : Boolean.parseBoolean(value);
            }
        }); 

		Properties hibernateConfig = new Properties();

		for (int i = 0; i < HIBERNATE_PROPERTIES.length; i++) {
		    String name = HIBERNATE_PROPERTIES[i][0];
		    String tokey = HIBERNATE_PROPERTIES[i][1];
		    String value = config.getProperty(tokey);
            
            // SS: somewhat hacky.  If not specified in config, use blank instead of null
            // Allows password value to be left out of web config altogether when it is blank
            if(value == null) {
                value = "";
            }
            log.info("Setting hibernate property " + name + " with value " + value + " corresponding to key " + tokey);

		    hibernateConfig.setProperty(name, value);
		}

		e = config.propertyNames();
		while (e.hasMoreElements()) {
		    String name = (String) e.nextElement();

		    if (name.startsWith("hibernate."))
			hibernateConfig.setProperty(name,
						    config.getProperty(name));
		}

		HibernateUtil.init(hibernateConfig);
	    }
        }

        // Give JSP pages access to this
        servletConfig.getServletContext().setAttribute("cookieDomain", config.getProperty("cookieDomain"));
        
        // Note: used to initialize connection pool here, now it is done automatically because
        // we share hibernate's connection pool (c3p0)
        
        // see if a custom mcid generator was configured
        String mcidGenerateClass = config.getProperty("mcidgenerator.class");
        if(mcidGenerateClass!=null) { // found custom mcid generator
            try {
                log.info("Using mcid generator class = " + mcidGenerateClass);
                // Attempt to create
                Object mcidGenerator = Thread.currentThread().getContextClassLoader().loadClass(mcidGenerateClass).newInstance();
                MCIDGenerator.setInstance((MCIDGenerator) mcidGenerator);
            }
            catch (InstantiationException e) {
                log.error("Unable to initialize mcid generator", e);
            }
            catch (IllegalAccessException e) {
                log.error("Unable to initialize mcid generator", e);
            }
            catch (ClassNotFoundException e) {
                log.error("Unable to initialize mcid generator", e);
            }
        }
    }

    /**
     * The default HTTP request handler displays the login/registration
     * page.
     */
    protected void doGet(HttpServletRequest request,
			 HttpServletResponse response)
	throws ServletException, IOException  {
	request.getRequestDispatcher(CHOICE_JSP_PAGE).forward(request,
							      response);
    }

    private static final String COOKIE_FORMAT
	= "mcid=$(mcid),"
	+ "from=$(source_name),"
	+ "fn=$(first_name),"
	+ "ln=$(last_name),"
	+ "email=$(email),"
	+ "auth=$(auth)";

    protected static final String COOKIE_NAME = "mc";

    protected static final String ENCODING = "UTF-8";

    protected void login(HttpServletResponse response, String url, Map userInfo) 
    throws IOException, RESTException {
        
        // Create an authentication token on the secure server
        String accounts = (String)userInfo.get("mcid");
        Session s = HibernateUtil.getSession();
        try {
            User user = new User();
            s.load(user, Long.parseLong((String)userInfo.get("mcid")));
            for (AccountGroup g : user.groups) {
              accounts += "," + g.getAccid();
            }
            JSONObject result = RESTUtil.callJSON("", "DocumentService.createAuthenticationToken","accountIds", accounts);
            String token = result.getString("result");
            log.info("Received token " + token + " for login to accounts " + accounts);
            userInfo.put("auth",token);

            String value = Expand.expand(COOKIE_FORMAT, userInfo);

            Cookie c = new Cookie(COOKIE_NAME, URLEncoder.encode(value, ENCODING));

            String domain = config.getProperty("cookieDomain");
            if (domain != null && !domain.equals(""))
                c.setDomain(domain);

            c.setPath("/");
            c.setMaxAge(-1);

            response.addCookie(c);
            response.sendRedirect(response.encodeRedirectURL(url));
        }
        catch(HibernateException e) {
            try {s.getTransaction().rollback(); } catch(Throwable t){};
            throw e;
        }
        finally {
            s.close();
        }
    }

    private String buildCookieName(String baseName) {
	return COOKIE_PREFIX + baseName;
    }

    /**
     * Clear all login-related cookies.
     */
    protected void clearUserSessionInfo(HttpServletRequest request,
					HttpServletResponse response,
					Map userInfo) throws IOException {
	for (Iterator i = userInfo.entrySet().iterator(); i.hasNext(); ) {
	    Map.Entry entry = (Map.Entry) i.next();
	    String key = (String)entry.getKey();
	    String kookiekey = buildCookieName(key);
	    Cookie cookie = new Cookie(kookiekey, null);

	    cookie.setMaxAge(0);
	    cookie.setPath("/");

	    String domain = config.getProperty("cookieDomain");
	    if (domain != null && !domain.equals(""))
		cookie.setDomain(domain);

	    response.addCookie(cookie);
	}

	userInfo.clear();
    }

    protected Map getMedcommonsCookie(HttpServletRequest request) {
	Map m = new HashMap();
	Cookie cookies[] = request.getCookies();
	int i;

	for (i = 0; i < cookies.length; i++)
	    if (COOKIE_NAME.equals(cookies[i].getName()))
		break;

	if (i < cookies.length) {
	    Cookie cookie = cookies[i];

	    try {
		String value = URLDecoder.decode(cookie.getValue(), ENCODING);
		String values[] = value.split(",");

		for (i = 0; i < values.length; i++) {
		    String[] eq = values[i].split("=", 2);

		    if (eq.length == 2)
			m.put(eq[0], eq[1]);
		}
	    } catch (UnsupportedEncodingException ex) {
		// no UTF-8?  huh
	    }
	}

	return m;
    }

    /**
     * Get all login-related cookies.
     */
    protected Map getUsersSessionInfo(HttpServletRequest request) {
	String serverName = request.getServerName();

	try {
	    Map m;
	    PFTokenSpAgent pf = new PFTokenSpAgent();
	    pf.extractFromQuery(request);
	    m = pf.getAttributes();

	    if (!m.isEmpty()) {
		m.put("sourceId", "IdpSample");
		m.put("serverName", serverName);
		return m;
	    }
	} catch (PFTokenException ex) {
	    ex.printStackTrace(System.err);
	} catch (IOException ex) {
	    ex.printStackTrace(System.err);
	}

	Map userProps = new HashMap();
	Cookie[] cookies = request.getCookies();

	if (cookies != null)
	    for (int i = 0; i < cookies.length; i++) {
		Cookie cookie = cookies[i];
		String name = cookie.getName();

		if (name.startsWith(COOKIE_PREFIX))
		    userProps.put(name.substring(COOKIE_PREFIX.length()),
				  cookie.getValue());
	    }

	userProps.put("serverName", serverName);
        return userProps;
    }

    public static final String SmoothMCID(String mcid) {
	if (mcid != null) {
	    StringBuffer sb = new StringBuffer(16);

	    int l = mcid.length();

	    for (int i = 0; i < l; i++) {
		char ch = mcid.charAt(i);

		if ('0' <= ch && ch <= '9')
		    sb.append(ch);
	    }

	    if (sb.length() == 16)
		return sb.toString();
	}

	return null;
    }

    /**
     * Get an old IdentityConnection or create a new one.
     *
     * @see IdentityConnectionPool
     */
    protected IdentityConnection getConnection()
	throws SQLException, InterruptedException {
        return new IdentityConnection();
    }

    /**
     * Return an IdentityConnection to the pool, closing it if the pool is full.
     *
     * @see IdentityConnectionPool
     */
    protected void releaseConnection(IdentityConnection c) {
        c.close();
    }
}
