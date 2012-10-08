package net.medcommons.mdl;

/*
 * $Id$
 * Created on 4/07/2006
 */



import java.io.IOException;
import java.io.StringWriter;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.TimeZone;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import net.medcommons.router.services.xds.consumer.web.action.CCRDocument;
import net.medcommons.router.util.StringUtil;

import org.apache.log4j.Logger;
import org.jdom.Content;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.Namespace;
import org.jdom.output.XMLOutputter;

public class CCRElement extends Element {
    
    /**
     * Logger to use with this class
     */
    private static Logger log = Logger.getLogger(CCRElement.class);
    
    /**
     * Hashmap of rules governing CCR Element order.  
     * 
     * Keys of the hashmap are "parent" elements and the array values are an ordered list
     * of children.  Elements created with getOrCreate() are guaranteed to be created
     * consistent with order in the array associated with the key of corresponding 
     * parent element name. 
     */
    public static HashMap<String, String[]> CCR_ELEMENT_ORDER = new HashMap<String,String[]>();

    public static HashSet<String> CCR_DATA_OBJECTS = new HashSet<String>();
    
    final static List<String> CCR_DATA_OBJECT_ORDER = Arrays.asList( new String[]{"CCRDataObjectID","DateTime","Type","Status","Description","Source","CommentID" });
    
    static {
        // All the following elements inherit the object order from CCR_DATA_OBJECT_ORDER above.
        CCR_DATA_OBJECTS.addAll( Arrays.asList(new String[] {
                        "AdvanceDirective", 
                        "EnvironmentalAgent", 
                        "Alert", 
                        "Authorization", 
                        "Episode", 
                        "Encounter",
                        "Consent",
                        "Outcome",
                        "Insurance",
                        "Intervention",
                        "OrderRxHistory",
                        "Plan",
                        "Problem",
                        "SocialHistory",
                        "StructuredProduct", 
                        "Result", 
                        "Test",
                        "AdvanceDirective",
                        "Medication", 
                        "FamilyProblemHistory"
                        }));
                             
        CCR_ELEMENT_ORDER.put("ContinuityOfCareRecord", new String[] {"CCRDocumentObjectID","Patient","From","To","Purpose","Body","Actors","References","Comments","Signatures"});
        CCR_ELEMENT_ORDER.put("Address", new String[] { "Line1","Line2","City","State","Country","PostalCode" } );
        CCR_ELEMENT_ORDER.put("Actor", new String[] {"ActorObjectID","Person","IDs","Relation","Address","Telephone","EMail","Source" });
        CCR_ELEMENT_ORDER.put("Body", new String[] { "Insurance","Medications","Advance Directives","FunctionalStatus","Support","VitalSigns","Immunizations","Procedures","Problems","Encounters","FamilyHistory","PlanOfCare","SocialHistory","Alerts","HealthCareProviders"});
        CCR_ELEMENT_ORDER.put("FamilyProblemHistory", new String[] { "FamilyMember","Problem"});
        CCR_ELEMENT_ORDER.put("Result", new String[] { "Substance","Test"});
        CCR_ELEMENT_ORDER.put("Person", new String[] { "Name","DateOfBirth","Gender"});
        CCR_ELEMENT_ORDER.put("Strength", new String[] { "Value","Units"});
        CCR_ELEMENT_ORDER.put("Test", new String[] { "Description","TestResult"});
        CCR_ELEMENT_ORDER.put("TestResult", new String[] { "Value","Units"});
        CCR_ELEMENT_ORDER.put("Procedure", new String[] { "CCRDataObjectID","DateTime","IDs","Type","Description","Status", "Source", "InternalCCRLink","ReferenceID","CommentID","Signature","Locations","Indications"});
        CCR_ELEMENT_ORDER.put("Medication", new String[] { "Product","Quantity","Directions","PatientInstructions","Refills"});
        CCR_ELEMENT_ORDER.put("Product", new String[] { "ProductName","BrandName","Strength","Form"});
        CCR_ELEMENT_ORDER.put("Comment", new String[] { "CommentObjectID","DateTime","Type","Description","Source"});
        
        
        for (String name : CCR_DATA_OBJECTS) {
            if(CCR_ELEMENT_ORDER.containsKey(name)) {
                List<String> order = new ArrayList<String>();
                order.addAll(CCR_DATA_OBJECT_ORDER);
                order.addAll(Arrays.asList(CCR_ELEMENT_ORDER.get(name)));
                CCR_ELEMENT_ORDER.put(name, order.toArray(new String[order.size()]));
            }
        }
    }
    
    /**
     * Pattern used to parse javabean property style parameters
     */
    private static final Pattern INDEX_PATTERN = Pattern.compile(".*\\[([0-9]*)\\]$");
    
    private static final Pattern PROPERTY_PART_REGEX = Pattern.compile("\\.");
    
    private static final Pattern DATE_TIME_REGEX = Pattern.compile("datetime$", Pattern.CASE_INSENSITIVE);

    public static final String EXACT_DATE_TIME_FORMAT = "yyyy-MM-dd'T'HH:mm:ss'Z'";

    /**
     * @param arg0
     * @param arg1
     */
    public CCRElement(String arg0, Namespace arg1) {
        super(arg0, arg1);
    }

    /**
     * @param arg0
     * @param arg1
     * @param arg2
     */
    public CCRElement(String arg0, String arg1, String arg2) {
        super(arg0, arg1, arg2);
    }

    /**
     * @param arg0
     * @param arg1
     */
    public CCRElement(String arg0, String arg1) {
        super(arg0, arg1);
    }

    /**
     * @param arg0
     */
    public CCRElement(String arg0) {
        super(arg0, Namespace.getNamespace(CCRDocumentTypes.CCR_NAMESPACE_URN));
    }

    public CCRElement() {
        this.setNamespace(Namespace.getNamespace(CCRDocumentTypes.CCR_NAMESPACE_URN));
    }
    
    public CCRElement getChild(String name){
        return (CCRElement)super.getChild(name,this.namespace);
    }
    
    /**
     * Creates a list of elements based on the properties specified in the given
     * map.   Each property will only be used if it is prefixed by the given prefix,
     * which must be followed by an array index to specify where in the list the 
     * element should be created.  The remaining parts of the property will be
     * converted to XPath form and used to create the hierarchy implied.
     * 
     * For example:
     * 
     *   foo[3].bar.fubar=fu
     *   
     * Will create an array with 3 elements, the third having form "Name/Bar/Fubar" containing 
     * text "fu".
     * 
     * Some special handling is added based on naming conventions:
     * <ul>
     *   <li> Fields ending in DateTime will be treated as dates and will be probed to determine
     *     whether they are exact or approximate and the appropriate structure created.
     *     
     *   <li> Fields called "comment" will create a Comment section in the CCR Footer and instead a 
     *     reference by CommentID will be created rather than a literal "Comment" element
     * </ul>
     * 
     * @param params
     * @return
     * @throws IOException 
     * @throws JDOMException 
     */
    public static List<CCRElement> create(CCRDocument ccr, String prefix, String name, Map params) throws JDOMException, IOException {
        List result = new ArrayList();
        for (Iterator iter = params.keySet().iterator(); iter.hasNext();) {
            String property = (String) iter.next();
            String [] values = (String[]) params.get(property);
            
            if(!property.startsWith(prefix))
                continue;
            
            // check if the name matches expected pattern
            String [] parts = PROPERTY_PART_REGEX.split(property);                
            if((parts.length>0) && (parts[0].matches("^.*\\[[0-9]*\\]$"))) {
                Matcher m = INDEX_PATTERN.matcher(parts[0]);
                if(m.find()) {
                    int index = Integer.parseInt(m.group(1));
                    log.debug("Found param with " + parts.length + " pieces with index " + index);                    

                    while(index>=result.size())
                        result.add(null);
                    
                    CCRElement element = (CCRElement)result.get(index);
                    if(element==null) {
                        element = new CCRElement(name);
                        result.set(index, element);
                    }
                    log.info("Setting path " + property + " on element " + element.toXml());
                    
                    for(int i=1; i<parts.length;++i) {
                        String partName = Character.toUpperCase(parts[i].charAt(0))+parts[i].substring(1);                        
                        element = element.getOrCreate(partName);
                    }
                    
                    // Special handling for Comment fields - they have to be handled as references
                    if(element.getName().equals("Comment")) {
                        // HACK:  do not add a comment if it is "Type Here" - this is the default text placed in the field
                        CCRElement parent = (CCRElement)element.getParent();
                        parent.removeContent(element);
                        if(!StringUtil.blank(values[0]) && !"Type Here".equals(values[0])) {
                            parent.createPath("CommentID",
                                            ccr.addComment(values[0]).getChildText("CommentObjectID",element.getNamespace()));                        
                        }
                    }
                    else                    
                    if(DATE_TIME_REGEX.matcher(element.getName()).find(0)) {
                        element.setDate(values[0]);
                    }
                    else
                        element.setText(values[0]);
                }
            } 
            log.info("Created element " + ((CCRElement)result.get(result.size()-1)).toXml());
        }        
        return result;
    }
    
    /**
     * Return a CCR compatible format of the current system time
     * @return
     */
    public static String getCurrentTime() {        
        DateFormat df = new SimpleDateFormat(EXACT_DATE_TIME_FORMAT);
        df.setTimeZone(TimeZone.getTimeZone("GMT"));
        return df.format(new Date(System.currentTimeMillis()));
    }
    
    /**
     * Attempts to create the given xpath value by recursively retrieving and creating
     * each segment.
     * 
     * @param path - path to be created
     * @param value - value to set (if any)
     */
    public CCRElement createPath(String xpath, String value) {       
       CCRElement segmentElement = this;
       String [] segments = xpath.split("/");       
       for (String path: segments) {
          segmentElement = segmentElement.getOrCreate(path);
       }       
       segmentElement.setText(value);
       return segmentElement;
    }
    
    /**
     * Attempts to create the given xpath value by recursively retrieving and creating
     * each segment.
     * 
     * @param path - path to be created
     * @param value - value to set (if any)
     */
    public CCRElement createPath(String xpath, Content value) {       
       CCRElement segmentElement = this;
       String [] segments = xpath.split("/");       
       for (String path: segments) {
          segmentElement = segmentElement.getOrCreate(path);
       }       
       segmentElement.setContent(value);
       return segmentElement;
    }

    /**
     * Attempts to retrieve a child element of the given name from the parent.
     * If the child is not found, creates it.
     * 
     * @param parent -
     *            element to retrieve/create child from/in
     * @param name -
     *            name of child element (assumed to be in CCR namespace)
     *            ###### Assumed to be in the namespace of the parent?
     * @return
     */
    public CCRElement getOrCreate(String name) {
        CCRElement child = this.getChild(name);
        if (child == null) {
            child = new CCRElement(name);
            this.addChild(child);
            
        }
        return child;
    }    
    
    public CCRElement addChild(CCRElement child) {
        // If there is a defined order for children of this parent then 
        // use that order.
        if(CCRElement.CCR_ELEMENT_ORDER.get(this.getName()) != null)
            this.addChild(child, CCRElement.CCR_ELEMENT_ORDER.get(this.getName()));
        else
            super.addContent(child);
        
        return child;
    }

    /**
     * Attempts to create the given element consistent with the order
     * in the supplied array of element names.
     * @param name
     * @param afterName
     * @return
     */
    public CCRElement createChild(String name, String afterName[]) {

        CCRElement child = this.getChild(name);
        if (child == null) {
            child = new CCRElement(name);
            log.debug("New child created for " + name);
            this.addChild(child, afterName);
        }        
        return child;
    }
    
    public CCRElement addChild(CCRElement child, String afterName[]) {
        List thisContents = this.getContent();
        Iterator iter =thisContents.iterator();
        if(log.isDebugEnabled()){
            while (iter.hasNext()){
                Object obj = iter.next();
                if (obj instanceof Element){
                    Element element = (Element) obj;
                    log.debug("Found element " + element.getName());
                }
                else
                    log.debug("Non-element value:" + obj.getClass().getCanonicalName());
            }
        }

        Element insertAfter = null;
        int index = -1;
        for (int i=0;i<afterName.length;i++){
            if(afterName[i].equals(child.getName()))
                break;
            insertAfter = this.getChild(afterName[i], this.getNamespace());
            //log.debug("Insert fter is " + insertAfter);
            if (insertAfter != null){
                int insertIndex = this.indexOf(insertAfter);
                //log.debug("found match with " + insertAfter.getName());
                if(insertIndex >= index)
                    index = insertIndex;
            }
        }

        if (index == -1)
            this.addContent(0,child);
        else
            this.addContent(index+1, child);
        return(child);
    }
    

    /**
     * Tries to parse the given date using the given format.  If it parses,
     * creates an ApproximateDateTime for it and sets it as a chid of this element.
     * 
     * @param dob
     * @param dobElement
     * @return - true if the format is parsed.
     */
    private boolean trySetDateFormat(String dob, String format) {
        SimpleDateFormat df;
        Date d;
        df = new SimpleDateFormat(format);
        try {
            if((d = df.parse(dob))!=null) {
                this.removeChild("ApproximateDateTime", namespace);
                this.getOrCreate("ExactDateTime").setText(dob);
                return true;
            }
        }
        catch (ParseException e) {
            // didn't parse, try next format
        }
        return false;
    }

    public void setDate(String dob) {
        // Try and parse in some different formats that the user may have entered
        SimpleDateFormat df = null;
        df = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'");
        df.setTimeZone(TimeZone.getTimeZone("GMT"));
        df.setLenient(true);
        Date d;
        try {
            if(df.parse(dob)!=null) {
                this.removeChild("ApproximateDateTime", namespace);
                getOrCreate("ExactDateTime").setText(dob);
            } 
        }
        catch (ParseException e) {
            // didn't parse, try next format
        }
        
        if(trySetDateFormat(dob,"yyyy-MM-dd'T'HH:mm:ss"))
            return;
        
        // not part of CCR Spec, but standard US date format
        if(trySetDateFormat(dob,"MM-dd-yyyy"))
            return;
        
        df = new SimpleDateFormat("MM/dd/yyyy");
        df.setTimeZone(TimeZone.getTimeZone("GMT"));
        df.setLenient(true);
        try {
            if((d = df.parse(dob))!=null) {
                this.removeChild("ApproximateDateTime", namespace);
                df = new SimpleDateFormat("yyyy-MM-dd");
                getOrCreate("ExactDateTime").setText(df.format(d));
                return;
            }
        }
        catch (ParseException e) {
            // didn't parse, try next format
        }
        
        if(trySetDateFormat(dob,"yyyy-MM-dd"))
            return;
        
        if(trySetDateFormat(dob,"yyyy"))
            return;
         
        // Not a parseable format?  Just set it as approximate
        log.info("Date " + dob + " not parseable.  Setting as ApproximateDateTime");
        this.removeChild("ExactDateTime", namespace);
        this.createPath("ApproximateDateTime/Text",dob);
    }
    
    public String toXml() {
        try {
            StringWriter sw = new StringWriter();            
            new XMLOutputter().output(this, sw);
            return (sw.toString());
        } 
        catch (IOException e) {
            throw new RuntimeException(e);
        }
    }
}

