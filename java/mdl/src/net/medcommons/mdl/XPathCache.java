package net.medcommons.mdl;






import java.io.File;
import java.io.IOException;
import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

//import net.medcommons.modules.configuration.Configuration;
//import net.medcommons.modules.utils.StringUtil;

import org.apache.log4j.Logger;
import org.jdom.Attribute;
import org.jdom.Content;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.filter.ElementFilter;
import org.jdom.input.SAXBuilder;
import org.jdom.xpath.XPath;

/**
 * A utility that slightly eases executing XPath statements against
 * JDOM documents as well as caching them.
 *
 * Usage:   XPathCache.getElement(jdomDoc, "<your xpath expression>");
 * 
 * NOTE: the default namespace of the jdomDoc will be added to the expression as "x",
 * so you can put references into the expression as "x:<element> without
 * doing anything else.
 * 
 * Example:  for a CCR document
 * 
 * Element patient = XPathCache.getElement(jdomDoc, "/x:ContinuityOfCareRecord/x:Patient");
 * 
 * @author ssadedin
 */
public class XPathCache {
    
    
    /**
     * Default path from which XML XPaths will be read if no value is provided in the 
     * config file for XPathMappingsConfig.
     */
    private static String DATA_XPATHS_XML = "conf/xpaths-ccr-1.0.xml";
    private static String DATA_XPATHS_XML_JUNIT = "etc/resources/conf/xpaths-ccr-1.0.xml";
  
    
    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(XPathCache.class);
    
    /**
     * Internal class used to hold cache entries
     */
    private static class XPathCacheEntry {
        public String expression;
        public XPath path;
        public long loadTimeMs;
    }

    /**
     * Cache of XPath instances - one per thread so we don't have to synchronize
     */
    private static ThreadLocal cachedPaths = new ThreadLocal();
    
    /**
     * If the cache gets older than this then we reload
     */
    private static long maxCacheAge = 60000;
    
    static {
    	try{
	    	String value = Configuration.getProperty("MaxXPathCacheAgeMs");
	    	if((value != null) && (!"".equals(value)))
	    		maxCacheAge = Long.parseLong(value);
	    	
	        DATA_XPATHS_XML = Configuration.getProperty("XPathMappingsConfig");   
	        if (DATA_XPATHS_XML == null)
	        	throw new NullPointerException("XPathMappingsConfig not defined in configuration");
	        File f = new File(DATA_XPATHS_XML);
	        if (!f.exists()){
	        	log.error("File doesn't exist:" + f.getAbsolutePath());
	        	DATA_XPATHS_XML = DATA_XPATHS_XML_JUNIT;
	        	f = new File(DATA_XPATHS_XML);
	        	if (!f.exists()){
	        		log.error("Alternative config file doesn't exist:" + f.getAbsolutePath());
	        	}
	        	else{
	        		log.info("Using config file:" + f.getAbsolutePath());
	        	}
	        }
    	}
    	catch(IOException e){
    		log.error("Error loading configs", e);
    		throw new RuntimeException(e);
    	}
    }
    
    /**
     * Do not construct me.
     */
    private XPathCache() {
        super();
    }

    /**
     * Executes the given named XPath expression against the given context object.
     * The expression can be the name of an expression from the xpaths.xml file,
     * or if it is not found then it will be attempted as a literal expression.
     * 
     * @return - the result object, any JDOM type depending on the expression.
     */    
    public static Element getElement(Object contextObj, String expression) throws JDOMException {        
        return getElement(contextObj, expression, Collections.EMPTY_MAP);
    }
    
    public static Element getElement(Object contextObj, String expression, Map variables) throws JDOMException {        
       Object result = getXPathResult(contextObj,expression,variables,false);
       if(result == null)
           return null;
       
       // Defensive handling: the user may send us data we don't expect so 
       // look for incorrect types here
       if(result instanceof List) {
           log.warn("Element expression returned List: " + expression);
           List resultList = (List)result;
           if(resultList.size() > 0)
               result =resultList.get(0);
           else
               return null;
       }
       if(! (result instanceof Element)) {
           log.warn("Element expression '" + expression + " returned unexpected result type " + result.getClass().getName());
       }
           
       return (Element)result; 
    }
    
    public static Attribute getAttribute(Object contextObj, String expression) throws JDOMException {        
       return (Attribute)getXPathResult(contextObj,expression,Collections.EMPTY_MAP,false); 
    }
    
     public static Attribute getAttribute(Object contextObj, String expression, Map variables) throws JDOMException {        
       return (Attribute)getXPathResult(contextObj,expression,variables,false); 
    }
    
     /**
     * Executes the given named XPath expression against the given context object.
     * The expression can be the name of an expression from the xpaths.xml file,
     * or if it is not found then it will be attempted as a literal expression.
     * 
     * The context object must be of type Document or Element or another class
     * extending the JDOM Content class.
     * 
     * Note that invocation of this method may cause the xpaths data file to be
     * read and parsed in its entirety if it is stale or has not yet been loaded.
     * 
     * You can change the refresh interval by setting the configuration parameter
     * "xpathCache.lastLoadTime"
     * 
     * @param contextObj - context (should be JDOM Document or Element)
     * @param expression - expression to evaluate.
     * @param variables - variables to add to the expression.
     * @return - the result object, may be any JDOM node type depending on expression.
     */
    public static Object getXPathResult(Object contextObj, String expression, Map variables, boolean alwaysList) throws JDOMException {        
        HashMap cache = (HashMap) cachedPaths.get();        
        
        if (contextObj == null)
        	throw new NullPointerException("Null contextObj");
        Document doc = null;
        if(contextObj instanceof Document) {
            doc = (Document)contextObj;
        }
        else 
        if(contextObj instanceof Content) {
            doc = ((Content)contextObj).getDocument();
        }
        else {
            throw new JDOMException("Context object must be a Document or Element.  Object type " 
                            + contextObj.getClass().getName() + " was passed.");
        }
        
        String namespaceURI = doc.getRootElement().getNamespaceURI();
        
        if(cache == null) {
            loadCache();
            cache = (HashMap) cachedPaths.get();
            assert cache != null : "Cache is null after load";
        }
       
        XPathCacheEntry entry = (XPathCacheEntry) cache.get(expression);
        Long lastLoadTime = (Long) cache.get("xpathCache.lastLoadTime");  
        if(System.currentTimeMillis() - lastLoadTime.longValue() > maxCacheAge) { // Older than 2 seconds, check file mod
           cachedPaths.set(null);
           loadCache();
           entry = (XPathCacheEntry) cache.get(expression);
        }
	            
        if(entry == null) { // Unregistered XPath statement => name = expr
            entry = new XPathCacheEntry();
            entry.expression = expression;
            cache.put(expression, entry);            
        }
        
       XPath xpath = entry.path;
        if(entry.path== null) {
            entry.path = XPath.newInstance(entry.expression);
            if(!StringUtil.blank(namespaceURI)) {
                entry.path.addNamespace("x", namespaceURI);                    
            }
        }
        
       for (Iterator iter = variables.entrySet().iterator(); iter.hasNext();) {
            Map.Entry variable = (Map.Entry) iter.next();
            entry.path.setVariable((String) variable.getKey(), variable.getValue());
        }
       
       log.debug("Executing xpath " + entry.expression);

       List resultList = entry.path.selectNodes(contextObj);       
       Object resultObj = null;            
       
       if(!resultList.isEmpty()) {
           if((resultList.size() == 1) && (!alwaysList)) {
               resultObj = resultList.get(0);
           }
           else {
               resultObj = resultList;
           }
       }    
       else
       if(alwaysList) {
           resultObj = Collections.EMPTY_LIST;
       }
        
        // Unset the variables so they do not get used when this path
        // is taken again from the cache.
       for (Iterator iter = variables.entrySet().iterator(); iter.hasNext();) {
            Map.Entry variable = (Map.Entry) iter.next();
            entry.path.setVariable((String) variable.getKey(), "");
        }
       
       return resultObj;

    }
    
    public static String getValue(Object obj, String pathName) throws JDOMException, IOException {     
        Object pathResult = XPathCache.getXPathResult(obj, pathName, Collections.EMPTY_MAP, false);
        if(pathResult == null)
            return null;

        if(pathResult instanceof List)
            throw new JDOMException("Can't get value on field referenced by path " + pathName + ".  Path returned multiple results.");
        
        if(pathResult instanceof Attribute) {
           return ((Attribute)pathResult).getValue();
        }
        else 
        if(pathResult instanceof Element) {
            return ((Element)pathResult).getTextTrim();           
        }        
        else
          return pathResult.toString();  
    }    
    
    public static void loadCache() throws JDOMException {
        HashMap cache = new HashMap();        
        try {
            Document pathDoc =  new SAXBuilder().build(new File(DATA_XPATHS_XML));
            Iterator iter = pathDoc.getDescendants(new ElementFilter("path"));
            while(iter.hasNext()) { 
                Element path = (Element) iter.next();
                XPathCacheEntry entry = new XPathCacheEntry();
                String name = path.getAttributeValue("name");                
                entry.expression = path.getTextTrim();
                if(path.getAttributeValue("noAddNs")==null) {
                    entry.expression = entry.expression.replaceAll("([\\[/])([A-Za-z])","$1x:$2");
                    if((!entry.expression.startsWith("/")) && (!entry.expression.startsWith(".")) ) {
                        if(!entry.expression.matches("^[\\w]*\\(.*")) { // function - don't append namespace
                            entry.expression = "x:" + entry.expression;
                        }
                    }
                    
                }                                
                //log.debug("Translated xpath entry [" + name + "] as " + entry.expression );
                cache.put(name, entry);
	            }            
        }
        catch (IOException e) {
            throw new JDOMException("IOException while loading xpaths file", e);
        }        
        cache.put("xpathCache.lastLoadTime", new Long(System.currentTimeMillis()));
        cachedPaths.set(cache);
    }
    
   
    

    /**
     * ONLY for use by unit tests to control directly where the xpaths are loaded from.
     * 
     * @param path
     */
    public static void setXPathMappingFile(String path) {
        DATA_XPATHS_XML = path;
    }
}
