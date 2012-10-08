package net.medcommons.mdl;

/*
 * $Id: StringUtil.java 1342 2006-09-13 19:25:55Z sdoyle $
 * Created on 22/03/2005
 */

import java.io.IOException;
import java.io.StringWriter;

import org.jdom.Document;
import org.jdom.Element;
import org.jdom.output.Format;
import org.jdom.output.XMLOutputter;

/**
 * Simple useful methods for handling strings.
 * 
 * @author ssadedin
 */
public class StringUtil {

    /**
     * Do not construct me.
     */
    private StringUtil() {
        super();
    }
    
    /**
     * Returns true if the given string is either null or has no contents.
     * 
     * @param value
     * @return
     */
    public static boolean empty(String value) {
       return (value==null) || (value.length()==0);
    }

    /**
     * Returns true if the given string is either null or has contents that
     * are all whitespace or empty.
     * 
     * @param value
     * @return
     */
    public static boolean blank(String value) {
       return (value==null) || (value.length()==0) || (value.trim().length()==0);
    }
    
    /**
     * Returns an copy of the input string with the ', ", and \ characters
     * escaped.
     */
    public static String escapeForJavaScript(String value){
    	if (value == null) return null;
    	String v = value.replace("\\", "\\\\");
    	v = v.replace("'", "\\'");
    	v = v.replace("\"", "\\\"");
        v = v.replaceAll("\n", "\\\\n");    	
    	return(v);
    }
    
    public static String nvl(String value, String ifBlank) {
        return value == null ? ifBlank : value;
    }
    
    
    /**
     * Equals method safe for null values
     * <p/>
     * <ul>
     *  <li>equals(null, "foo") == false
     *  <li>equals(null, null) == true
     * </ul>
     *  
     * @return
     */
    public static boolean equals(String value1, String value2) {
       if(value1 == value2)
           return true;
       
       if(value1 != null)
           return value1.equals(value2);
       else
           return value2.equals(value1);
    }
    
    
    /**
     * Replaces HTML entities with their escaped equivalents, using 
     * character entities such as <tt>'&amp;'</tt> etc.
     */
     public static String escapeHTMLEntities(String value){
       StringBuffer result = null; 
       
       int len = value.length();
       for(int i=0; i<len; ++i) {
           char c = value.charAt(i);
           if (c == '<') {
               if(result == null)
                   result = new StringBuffer(value.substring(0,i)); 
               result.append("&lt;");
           }
           else if (c == '>') {
               if(result == null)
                   result = new StringBuffer(value.substring(0,i)); 
               result.append("&gt;");
           }
           else if (c == '\"') {
               if(result == null)
                   result = new StringBuffer(value.substring(0,i)); 
               result.append("&quot;");
           }
           else if (c == '\'') {
               if(result == null)
                   result = new StringBuffer(value.substring(0,i)); 
               result.append("&#039;");
           }
           else if (c == '\\') {
               if(result == null)
                   result = new StringBuffer(value.substring(0,i)); 
               result.append("&#092;");
           }
           else if (c == '&') {
               if(result == null)
                   result = new StringBuffer(value.substring(0,i)); 
               result.append("&amp;");
           }
           else {
               if(result != null)
                   result.append(c);
           }
       }
       if(result != null) { // one or more entities was found
	       return result.toString();
       }
       else
           return value; // no entities found, return the original value.
     }
     
	 private static Format outputFormat = Format.getPrettyFormat();
     
    /**
     * Utility to convert JDOM Document to string form
     */
     public static String toString(Document d) throws IOException {
            StringWriter sw = new StringWriter();
            new XMLOutputter(outputFormat).output(d, sw);
            return sw.toString();
     }
     
    /**
     * Utility to convert JDOM Element to string form
     */
     public static String toString(Element e) throws IOException {
            StringWriter sw = new StringWriter();
            new XMLOutputter(outputFormat).output(e, sw);
            return sw.toString();
     }
}
