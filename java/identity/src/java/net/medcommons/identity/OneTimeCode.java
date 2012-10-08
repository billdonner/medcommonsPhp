package net.medcommons.identity;

import java.io.UnsupportedEncodingException;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 * Medcommons SMS password handling.
 */
class OneTimeCode {

    private static final String ALGORITHM = "SHA1";
    private static final String ENCODING = "UTF-8";
    private static final String PREFIX = "medcommons.net";
    private static final char[] DIGITS = "0123456789ABCDEF".toCharArray();

    private static final long MIN5 = 5 * 60 * 1000;

    public static void main(String args[]) {
	System.out.print("Next hash = ");
	System.out.println(NextHash(args[0]));

	System.out.println("Previous hashes =");
	String hashes[] = HashList(args[0], 3);

	for (int i = 0; i < hashes.length; i++)
	    System.out.println(hashes[i]);
    }

    public static String NextHash(String mcid) {
	return HashTime(mcid, System.currentTimeMillis() / MIN5);
    }

    public static String[] HashList(String mcid, int n) {
	String[] result = new String[n];

	long time = System.currentTimeMillis() / MIN5;

	for (int i = 0; i < n; i++) {
	    result[i] = HashTime(mcid, time);
	    time--;
	}

	return result;
    }

    static String HashTime(String mcid, long m) {
	try {
	    MessageDigest md = MessageDigest.getInstance(ALGORITHM);
	    String time5m = "" + m;

	    md.update(mcid.getBytes(ENCODING));
	    md.update(time5m.getBytes(ENCODING));

	    /* collect 6 digits of data */
	    byte[] digest = md.digest();

	    return EncodeHex(digest, 0, 3);
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + " not supported?  Give me a break!");
	} catch (NoSuchAlgorithmException ex) {
	    throw new RuntimeException(ALGORITHM + " not supported?  Give me a break!");
	}
    }

    /**
     * Convert byte data into 'XX' hexadecimal.
     */
    static String EncodeHex(byte[] data, int start, int end) {
	StringBuffer sb = new StringBuffer((end - start) * 2);

	for (int i = start; i < end; i++) {
	    byte b = data[i];
	    sb.append(DIGITS[(b & 0xF0) >> 4]);
	    sb.append(DIGITS[b & 0x0F]);
	}

	return sb.toString();
    }

}
