/*
 * $Id$
 * Created on 20/06/2006
 */
package net.medcommons.identity;

public class DisabledAccountException extends Exception {

    public DisabledAccountException() {
        super();
    }

    public DisabledAccountException(String message) {
        super(message);
    }

    public DisabledAccountException(String message, Throwable cause) {
        super(message, cause);
    }

    public DisabledAccountException(Throwable cause) {
        super(cause);
    }

}
