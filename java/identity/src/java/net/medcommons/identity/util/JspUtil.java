/*
 * $Id$
 * Created on 12/03/2007
 */
package net.medcommons.identity.util;

import java.util.Set;

/**
 * Functions to make JSPs life easier
 * 
 * @author ssadedin
 */
public class JspUtil {
    public static boolean contains(Set s, Object o) {
        return s.contains(o);
    }
}
