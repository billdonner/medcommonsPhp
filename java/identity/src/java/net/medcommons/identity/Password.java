package net.medcommons.identity;

import java.io.UnsupportedEncodingException;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 * Medcommons password handling.
 *
 * <p>
 * Passwords are not stored raw in any MedCommons databases.  Instead,
 * we save a SHA1 of the password with some salt.  This makes it hard
 * to recover the password, but easy to verify if a password is correct.
 *
 * <p>
 * To make it hard to see if two users have the same password, we SHA1
 * the MCID along with the password.  Then if two users pick 'welcome'
 * for their password, we won't be able to tell.
 *
 * <p>
 * Suppose another web service has usernames and passwords, and SHA1's
 * the combinations like we do.  A database administrator may have access
 * to both our database and this other database, could create a user on
 * the other system with the same MCID as a current MedCommons user, and
 * could start changing passwords until SHA1s match.  To prevent this, we
 * also SHA1 'medcommons.net'
 *
 * <p>
 * To support internationalization, we convert all strings to UTF8.
 *
 * <p>
 * So, the complete MedCommons password system is:
 *  <ol>
 *   <li>SHA1 the UTF-8/ascii string "medcommons.net"</li>
 *   <li>SHA1 the UTF-8/ascii string of the user's MCID, stripped of all
 *       spaces or dashes</li>
 *   <li>SHA1 the UTF-8 encoded password
 *  </ol>
 */
public class Password {

    private static final String ALGORITHM = "SHA1";
    private static final String ENCODING = "UTF-8";
    private static final String PREFIX = "medcommons.net";
    private static final char[] DIGITS = "0123456789ABCDEF".toCharArray();

    public static void main(String[] args) {
	if (args.length != 2)
	    System.err.println("Usage: java net.medcommons.identity.Password {mcid} {password}");
	else
	    System.out.println(hash(args[0], args[1]));
    }

    /**
     * Convert byte data into 'XX' hexadecimal.
     */
    static String EncodeHex(byte[] data) {
	StringBuffer sb = new StringBuffer(data.length * 2);

	for (int i = 0; i < data.length; i++) {
	    byte b = data[i];
	    sb.append(DIGITS[(b & 0xF0) >> 4]);
	    sb.append(DIGITS[b & 0x0F]);
	}

	return sb.toString();
    }

    /**
     * Return the hexadecimal SHA1 hash of a user's password.
     *
     * @param mcid  16-character MCID, stripped of all spaces or dashes.
     * @param password   user-supplied password string.
     */
    public static String hash(String mcid, String password) {
	try {
	    MessageDigest md = MessageDigest.getInstance(ALGORITHM);

	    md.update(PREFIX.getBytes(ENCODING));
	    md.update(mcid.getBytes(ENCODING));

	    if (password != null)
		md.update(password.getBytes(ENCODING));

	    return EncodeHex(md.digest());
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + " not supported?  Give me a break!");
	} catch (NoSuchAlgorithmException ex) {
	    throw new RuntimeException(ALGORITHM + " not supported?  Give me a break!");
	}
    }

}
