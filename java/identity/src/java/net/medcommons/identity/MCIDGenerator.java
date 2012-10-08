package net.medcommons.identity;

import java.io.InputStream;
import java.io.IOException;
import java.net.URL;

/**
 * Generates unique MCIDs.  The default implementation does this
 * by calling the MCID SOAP/REST service, but this can be overridden
 * by calling the <code>setInstance</code> method to provide another
 * implementation.
 */
public class MCIDGenerator {
    
    /**
     * One and only singleton instance used to generate mcid values
     */
    private static MCIDGenerator instance;
    
    /** Return the next MCID as a string. */
    public String nextMCIDString() throws IOException {
	URL url = new URL("http://mcid.internal:1080/mcid");
	InputStream in = url.openStream();

	try {
	    byte[] buffer = new byte[16];
	    in.read(buffer);
	    return new String(buffer);
	} finally {
	    in.close();
	}
    }

    /** Return the next MCID */
    public long nextMCID() throws IOException {
        return Long.parseLong(nextMCIDString());
    }
    
    public static void setInstance(MCIDGenerator mcidGenerator) {
        assert instance == null : "mcid generator should never be reset";
        instance = mcidGenerator;
    }
    
    public static MCIDGenerator getInstance() {
        if(instance == null) {
            instance = new MCIDGenerator();
        }
       return instance; 
    }
}
