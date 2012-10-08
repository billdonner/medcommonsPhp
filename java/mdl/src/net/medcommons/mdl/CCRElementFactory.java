package net.medcommons.mdl;


import org.jdom.DefaultJDOMFactory;
import org.jdom.Element;
import org.jdom.Namespace;

public class CCRElementFactory extends DefaultJDOMFactory {

    public CCRElementFactory() {
        super();
    }

    @Override
    public Element element(String arg0, Namespace arg1) {
        return new CCRElement(arg0, arg1);
    }

    @Override
    public Element element(String arg0, String arg1, String arg2) {
        return new CCRElement(arg0, arg1, arg2);
    }

    @Override
    public Element element(String arg0, String arg1) {
        return new CCRElement(arg0, arg1);
    }

    @Override
    public Element element(String arg0) {
        return new CCRElement(arg0);
    }

    
    
}

