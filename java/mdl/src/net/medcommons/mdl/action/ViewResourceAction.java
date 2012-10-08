package net.medcommons.mdl.action;



import net.medcommons.mdl.PatientRecordResultManager;
import net.sourceforge.stripes.action.Resolution;
import net.sourceforge.stripes.util.HtmlUtil;
import net.sourceforge.stripes.validation.SimpleError;
import net.sourceforge.stripes.validation.Validate;
import net.sourceforge.stripes.validation.ValidationErrors;
import net.sourceforge.stripes.validation.ValidationMethod;

import javax.servlet.ServletContext;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.log4j.Logger;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.IOException;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.Iterator;
import java.util.List;
import java.util.SortedSet;
import java.util.TreeSet;

/**
 * ActionBean that is used to display source files from the bugzooky web application
 * to the user.
 * Should change this to read the log files.
 *
 * @author Tim Fennell
 */
public class ViewResourceAction extends MdlAction {
	
    @Validate(required=true)
    private String resource;
    
	private static Logger log = Logger.getLogger(ViewResourceAction.class);

    /** Sets the name resource to be viewed. */
    public void setResource(String resource) { this.resource = resource; }

    /** Gets the name of the resource to be viewed. */
    public String getResource() { return resource; }

    /** Validates that only resources in the allowed places are asked for. */
    @ValidationMethod
    public void validate(ValidationErrors errors) {
        if (resource.startsWith("/WEB-INF") && !resource.startsWith("/WEB-INF/src")) {
            errors.add("resource",
                       new SimpleError("Naughty, naughty. We mustn't hack the URL now."));
        }
    }

    /**
     * Handler method which will handle a request for a resource in the web application
     * and stream it back to the client inside of an HTML preformatted section.
     */
    public Resolution view() throws IOException{
    	final File logDir = new File("logs");
    	final File inputFile = new File(logDir, this.resource);
    	
        final InputStream stream = new FileInputStream(inputFile);
        final BufferedReader reader = new BufferedReader( new InputStreamReader(stream) );

        return new Resolution() {
            public void execute(HttpServletRequest request, HttpServletResponse response) throws Exception {
                PrintWriter writer = response.getWriter();
                writer.write("<html><head><title>");
                writer.write(resource);
                writer.write("</title></head><body><pre>");

                String line;
                while ( (line = reader.readLine()) != null ) {
                    writer.write(HtmlUtil.encode(line));
                    writer.write("\n");
                }

                writer.write("</pre></body></html>");
            }
        };
    }

    /**
     * Method used when this ActionBean is used as a view helper. Returns a listing of all the
     * JSPs and ActionBeans available for viewing.
     */
    public Collection getAvailableResources() {
       // ServletContext ctx = getContext().getRequest().getSession().getServletContext();
        SortedSet<String> resources = new TreeSet<String>();
        File logDir = new File("logs");
        if (logDir.exists()){
        	
        	String[] files = logDir.list();
        	
        	List<String> mdlLogs = Arrays.asList(files);
        	
	       // Collection mdlResources = ctx.getResourcePaths("/mdl");
	        if (mdlLogs != null){
		        resources.addAll( mdlLogs);
		      
		
		        Iterator<String> iterator = resources.iterator();
		        while (iterator.hasNext()) {
		            String file = iterator.next();
		            if (!file.endsWith(".log")) {
		                iterator.remove();
		            }
		        }
	        }
        }
        else{
        	log.info("Log directory does not exist:" + logDir.getAbsolutePath());
        }

        return resources;
    }
}
