package net.medcommons.mdl;

import java.io.IOException;
import java.util.Collections;
import java.util.Iterator;
import java.util.List;



import org.apache.log4j.Logger;
import org.jdom.Attribute;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;

public class XPathUtils {
	/**
	 * Logger to use with this class
	 */
	private static Logger log = Logger.getLogger(XPathUtils.class);

	public static String getSingleValue(Document document, String pathName)
			throws JDOMException, IOException {
		Object pathResult = XPathCache.getXPathResult(document, pathName,
				Collections.EMPTY_MAP, false);
		if (pathResult == null)
			return null;
		// throw new JDOMException("Can't get value on field referenced by path
		// " + pathName + ". Field not found.");

		if (pathResult instanceof List) {
			List paths = (List) pathResult;
			StringBuffer buff = new StringBuffer(
					"Multiple return values not supported in current implementation for  ");
			buff.append(pathName);
			buff.append(". Returned values are:");
			Iterator iter = paths.iterator();
			while (iter.hasNext()) {
				String value = getAtomicValue(pathName, iter.next());
				log.info("multiple value:" + value);
				buff.append(" '");
				buff.append(value);
				buff.append("' ");
			}
			throw new RuntimeException("Multiple matches to " + pathName  + " " + buff.toString());
		}

		return (getAtomicValue(pathName, pathResult));
	}

	/**
	 * Returns the given expression evaluated as a List. A list is always
	 * returned even if there is only 1 or no results.
	 * 
	 * @param pathName -
	 *            the pathName from the xpaths file, or a literal XPath
	 * @return
	 * @throws JDOMException
	 * @throws IOException
	 */
	public static List getValues(Document document, String pathName)
			throws JDOMException, IOException {
		return (List) XPathCache.getXPathResult(document, pathName,
				Collections.EMPTY_MAP, true);
	}

	private static String getAtomicValue(String pathName, Object pathResult)
			throws JDOMException {

		if (pathResult instanceof Attribute) {
			return ((Attribute) pathResult).getValue();
		} else if (pathResult instanceof Element) {
			return ((Element) pathResult).getTextTrim();
		} else if (pathResult instanceof String) {
			return (String) pathResult;
		} else
			throw new JDOMException(
					"Can't get value on field referenced by path " + pathName
							+ ".  Path returned unknown element type "
							+ pathResult.getClass().getName());
	}

	/**
	 * Perhaps we should deprecate getValue() in favor of getSingleValue() and
	 * getValues()? If a multiple path result is an error then we should have
	 * some mechanism for control/reporting here. There are some other issues
	 * too - Adrian wants there to be a dictionary/config file with error
	 * messages in them that may differ depending on what the pathname is (e.g.,
	 * we might return a different error for multiple occurances of <Email> than
	 * for something else.). This probably means creating some new exception
	 * types.
	 * 
	 * @param pathName
	 * @return
	 * @throws JDOMException
	 * @throws IOException
	 */
	public static String getValue(Document document, String pathName)
			throws JDOMException, IOException {
		Object pathResult = XPathCache.getXPathResult(document, pathName,
				Collections.EMPTY_MAP, false);
		if (pathResult == null){
			log.info("PathResult for " + pathName + " is null");
			return null;
		}
		// throw new JDOMException("Can't get value on field referenced by path
		// " + pathName + ". Field not found.");

		if (pathResult instanceof List) {
			List paths = (List) pathResult;
			StringBuffer buff = new StringBuffer();
			Iterator iter = paths.iterator();
			boolean first = true;
			while (iter.hasNext()) {
				Object next = iter.next();
				String value = getAtomicValue(pathName, next);

				buff.append(value);
				if (first) {
					buff.append(",");
					first = false;
				}

			}
			log.info("Multiple email addresses for path " + pathName + ":"
					+ buff.toString());
			return (buff.toString());

		}
		/*
		 * throw new JDOMException( "Can't get value on field referenced by path " +
		 * pathName + ". Path returned multiple results.");
		 */
		String returnedValue = getAtomicValue(pathName, pathResult);
		//log.info("result for pathname: " + pathName + "=" + returnedValue);
		return (returnedValue);
	}

	public static void setValue(Document document, String pathName, String value)
			throws JDOMException, IOException {
		Object pathResult = XPathCache.getXPathResult(document, pathName,
				Collections.EMPTY_MAP, false);

		if (pathResult == null)
			throw new JDOMException(
					"Can't set value on field referenced by path " + pathName
							+ ".  Field not found.");

		if (pathResult instanceof List)
			throw new JDOMException(
					"Can't set value on field referenced by path " + pathName
							+ ".  Path returned multiple results.");

		if (pathResult instanceof Attribute) {
			((Attribute) pathResult).setValue(value);
		} else if (pathResult instanceof Element) {
			((Element) pathResult).setText(value);
		}
	}

}

