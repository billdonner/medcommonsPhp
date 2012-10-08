package net.medcommons.identity;

import java.util.Hashtable;
import java.util.Map;

public class Expand {

    public static void main(String[] args) {
	Hashtable ht = new Hashtable();

	for (int i = 1; i < args.length; i++) {
	    String a = args[i];

	    int j = a.indexOf('=');
	    if (j > 0) {
		ht.put(a.substring(0, j), a.substring(j + 1));
	    }
	}

	System.out.println(expand(args[0], ht));
    }

    public static String expand(String format, Map map) {
	int max = format.length();
	StringBuffer sb = new StringBuffer(max + 16);

	int start = 0, i = format.indexOf('$');

	while (i >= 0) {
	    if (i == max - 1)
		break;

	    if (format.charAt(i + 1) == '(') {
		int j = format.indexOf(')', i + 2);

		if (j > 0) {
		    String key = format.substring(i + 2, j);
		    Object value = map.get(key);

		    sb.append(format.substring(start, i));

		    if (value != null)
			sb.append(value);

		    i = j;
		    start = i + 1;
		}
	    }

	    i = format.indexOf('$', i + 1);
	}

	sb.append(format.substring(start));

	return sb.toString();
    }

}
