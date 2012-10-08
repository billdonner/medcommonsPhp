/**
 * Copyright 2009, MedCommons Inc.
 */
import java.io.IOException;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;
import java.util.HashMap;
import java.util.Map;

import org.json.JSONObject;

import net.oauth.OAuthAccessor;
import net.oauth.OAuthConsumer;
import net.oauth.OAuthMessage;
import net.oauth.client.OAuthClient;
import net.oauth.client.URLConnectionClient;


/**
 * A simple example program to show how to get a CCR from 
 * MedCommons using the MedCommons Oauth API.
 * <p>
 * For more information on the MedCommons OAuth API, see the 
 * <a href='http://healthurl.medcommons.net/api/doc'>Online Documentation</a>.
 * 
 * @author ssadedin@medcommons.net
 */
public class GetCCR {
    
    // The consumer token details will be provided to you by MedCommons
    // or the operator of the appliance you wish to use.  You must
    // get these details as a one time operation via manual or 
    // out-of-band process in which you register your details with the
    // appliance operator.
    public static final String consumerToken = "270ebdf10b9bb9dd957a4a14833367183a196da7"; 
    public static final String consumerSecret = "72c25b142d4dd453213b586fc1278afc446b1d89"; 
    
    // The access token is specific to the patient that you wish
    // to call the APIs for.   You can get an access token for an
    // existing page by creating a request token and forwarding the
    // user to the OAuth authorization page (this process is defined by OAuth).
    //
    // If you are creating patients yourself using the MedCommons public
    // API then you will get an access token returned to you from the
    // patient creation service call itself.   You need to store that
    // token to use in future OAuth calls.  The token here is one
    // that is permanently rigged to enable access to the MedCommons 
    // demonstration data set.
    public static final String accessToken = "970efdf18b9bb9dd957a4a14833367283a116d37";
    public static final String tokenSecret = "32c25b142d4dd453213b586fc1278afc446b1d49";
    public static final String accountId = "1013062431111407";
    
    // The base URL of the MedCommons Appliance you wish to make service 
    // calls to
    public static final String applianceBaseUrl = "https://healthurl.medcommons.net/";
    
    public static void main(String[] args)  throws Exception {
        
        // First create the OAuth credentials
        OAuthConsumer consumer = new OAuthConsumer(null, consumerToken, consumerSecret, null);
        OAuthAccessor accessor = new OAuthAccessor(consumer);
        accessor.accessToken = accessToken;
        accessor.tokenSecret = tokenSecret;
        
        // The OAuthClient does the actual work of making calls for us
        OAuthClient client = new OAuthClient(new URLConnectionClient());
        
        // First find the storage location (gateway) by passing
        // the account id to the find_storage service
        Map<String, String> params = new HashMap<String, String>();
        params.put("accid",accountId);
        OAuthMessage response = client.invoke(accessor, applianceBaseUrl+"api/find_storage.php", params.entrySet());
        String text = readResponse(response);
        
        System.out.println("Got response to find_storage call : " + text);
        JSONObject obj = new JSONObject(text);
        String gwUrl = obj.getString("result");
        
        System.out.println("Storage URL for account " + accountId + " is " + gwUrl);

        // Now get the CCR from the gateway from the ccrs service on the gateway we found
        params.clear();
        params.put("fmt","xml"); // ask for it in XML format.  We could also ask for "json"
        response = client.invoke(accessor, gwUrl+"/ccrs/"+accountId, params.entrySet());
        System.out.println("Got CCR: " + readResponse(response));
    }
    
    /**
     * Utility method to read response stream into a string
     */
    private static String readResponse(OAuthMessage response) throws IOException, UnsupportedEncodingException {
        InputStream s = response.getBodyAsStream();
        StringBuilder b = new StringBuilder();
        byte[] buffer = new byte[1024];
        int read = 0;
        while((read = s.read(buffer)) > 0) {
           b.append(new String(buffer,0,read,"UTF-8"));
        }
        return b.toString();
    }
}
