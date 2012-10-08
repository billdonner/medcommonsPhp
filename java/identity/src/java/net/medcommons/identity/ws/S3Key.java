/*
 * $Id: S3Key.java 5131 2008-04-08 12:00:43Z ssadedin $
 * Created on 08/04/2008
 */
package net.medcommons.identity.ws;

public class S3Key {
    
    String key;
    
    String secret;

    public S3Key(String key, String secret) {
        this.key = key;
        this.secret = secret;
    }

    public String getKey() {
        return key;
    }

    public void setKey(String key) {
        this.key = key;
    }

    public String getSecret() {
        return secret;
    }

    public void setSecret(String secret) {
        this.secret = secret;
    }

}
