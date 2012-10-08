/*
 * $Id$
 * Created on 12/03/2007
 */
package net.medcommons.identity.util;

import java.io.IOException;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;

public class HibernateFilter implements Filter
{
    private FilterConfig    filterConfig    = null;


    public void init( FilterConfig filterConfig )
            throws ServletException
    {
        this.filterConfig = filterConfig;
    }


    public void destroy() {
        this.filterConfig = null;
    }

    public void doFilter( ServletRequest req, ServletResponse resp, FilterChain chain)
            throws IOException, ServletException
    {
        try {
            chain.doFilter( req, resp );
        }
        finally {
            HibernateUtil.closeSession();
        }
    }
}