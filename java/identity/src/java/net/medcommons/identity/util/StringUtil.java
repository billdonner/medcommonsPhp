/*
 * $Id: StringUtil.java 1577 2007-02-08 06:57:08Z ssadedin $
 * Created on 22/03/2005
 */
package net.medcommons.identity.util;

import java.io.IOException;
import java.io.StringWriter;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.jdom.Content;
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
     * Pattern used to find newlines when counting rows
     */
    private static Pattern lineEndPattern = Pattern.compile("\\r{0,1}\\n");
    
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
     * Normalizing is intended to allow for comparisons between strings 
     * while allowing for differences in white space formatting and
     * other factors. 
     * 
     * @TODO make normalization algorithm more efficient
     * @param value
     * @return normalized value
     */
    public static String normalize(String value) {
       return value.trim().replace("\r\n","\n").trim(); 
    }
    
    public static boolean equalNormalized(String value1, String value2) {
        if(equals(value1,value2)) {
            return true;
        }
        
        if(value1==null && value2!=null)
            return false;
        
        if(value2==null && value1!=null)
            return false;
        
        // Both not null, both not equal
        return normalize(value1).equals(normalize(value2));
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
    
    /**
     * Return the value if it is not blank, otherwise the given default instead.
     */
    public static String bvl(String value, String ifBlank) {
        return blank(value) ? ifBlank : value;
    }
    
    /**
     * Return the value if it is not null, otherwise the given default instead.
     */
    public static String nvl(String value, String ifBlank) {
        return value == null ? ifBlank : value;
    }
    
    /**
     * Equals method safe for null values
     * <lineEndPattern/>
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
     * @return
     */
    public static int countRows(String value, int cols) {
        Matcher m = lineEndPattern.matcher(value);
        int rows = 0;
        int lastEnd = 0;
        while(m.find()) {
            ++rows;
            int len = m.end() - m.start();
            rows += len / cols;
            lastEnd = m.end();
        }
        
        // wrap last row, if any
        rows += Math.ceil( ((double)(value.length() - lastEnd)) / cols );
        
        return rows;
    }
    
    /**
     * Inverse of split() operation  
     * 
     * @param values
     * @param separator
     * @return
     */
    public static String join(Object [] values, String separator) {
        StringBuilder result = new StringBuilder();
        for (Object object : values) {
            if(result.length()>0)
                result.append(separator);
            result.append(object.toString());
        }
        return result.toString();
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
