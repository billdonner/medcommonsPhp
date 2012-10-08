import java.io.InputStream;
import java.io.IOException;

import java.net.URL;

public class get_mcid {

    public static void main(String[] args) {
	try {
	    System.out.println(get_mcid());
	} catch (IOException ex) {
	    ex.printStackTrace(System.err);
	}
    }

    public static long get_mcid() throws IOException {
	return Long.parseLong(get_mcid_str());
    }

    public static String get_mcid_str() throws IOException {
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
}
