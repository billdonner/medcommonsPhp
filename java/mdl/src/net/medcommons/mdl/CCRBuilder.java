package net.medcommons.mdl;
/*
 * $Id$
 * Created on 4/07/2006
 */


import org.jdom.input.SAXBuilder;

public class CCRBuilder extends SAXBuilder {

    public CCRBuilder() {
        super();
        this.setFactory(new CCRElementFactory());
    }

    public CCRBuilder(boolean arg0) {
        super(arg0);
        this.setFactory(new CCRElementFactory());
    }

    public CCRBuilder(String arg0) {
        super(arg0);
        this.setFactory(new CCRElementFactory());
    }

    public CCRBuilder(String arg0, boolean arg1) {
        super(arg0, arg1);
        this.setFactory(new CCRElementFactory()); 
    }

}
