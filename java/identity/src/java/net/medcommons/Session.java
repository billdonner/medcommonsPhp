package net.medcommons;

import java.io.FileInputStream;
import java.io.IOException;
import java.io.UnsupportedEncodingException;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.GeneralSecurityException;
import java.security.InvalidKeyException;

import java.util.Arrays;

import javax.crypto.Cipher;
import javax.crypto.Mac;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

import net.medcommons.Base64Coder;
import net.medcommons.identity.IdentityServlet;

/**
 * @author <a href='email:tway@medcommons.net'>Terence Way</a>
 */
public class Session {

    private static final String DIGEST_ALGORITHM = "SHA1";
    private static final String CRYPTO_ALGORITHM = "AES";
    private static final String TRANSFORMATION = "AES/CBC/PKCS5Padding";
    private static final String MAC_ALGORITHM = "HmacSHA1";

    private static final String ENCODING = "UTF-8";
    private static final String URANDOM = "/dev/urandom";
    private static final int DEFAULT_SECONDS = 30;

    private static final String ERR_DESC = " not supported?  Give me a break!";

    static FileInputStream urandom;

    public static final String ENCRYPTED_QUERY = "enc";
    public static final String TIMESTAMP_QUERY = "ts";
    public static final String HMAC_QUERY = "hmac";
    
    /**
     * Flag to allow systems without support for hardware random device to work
     */
    static boolean softUrandom = false;
    
    static {
	try {
        if((IdentityServlet.config != null) && "true".equals(IdentityServlet.config.getProperty("softUrandom"))) {
            softUrandom = true;
        }
        else {
            urandom = new FileInputStream(URANDOM);
        }
	} catch (IOException ex) {
	    throw new RuntimeException(URANDOM + ERR_DESC);
	}
    }

    /**
     * Add a timestamp to a URL's query string
     *
     * @param url  URL to add to
     *
     * @return new URL
     *
     * @see #TimestampUrl(java.lang.String, long)
     */
    public static String TimestampUrl(String url) {
	return TimestampUrl(url, System.currentTimeMillis());
    }

    /**
     * Add a timestamp to a URL's query string using a specific time.
     *
     * @param url  URL to add to
     * @param timestamp  milliseconds since the epoch
     *
     * @return new URL
     *
     * @see #TimestampUrl(java.lang.String)
     */
    public static String TimestampUrl(String url, long timestamp) {
	return AddToUrl(url, TIMESTAMP_QUERY, Long.toString(timestamp / 1000));
    }

    /**
     * Tests if a query string has a timestamp generated in the last
     * 30 seconds.
     *
     * @param queryString query string portion of URL to test
     *
     * @return true if timestamp exists and is within the range, false otherwise
     *
     * @see #TimestampUrl(java.lang.String)
     */
    public static boolean IsQueryStringCurrent(String queryString) {
	return IsQueryStringCurrent(queryString, DEFAULT_SECONDS,
				    System.currentTimeMillis());
    }

    /**
     * Tests if a query string has a timestamp generated within a
     * specified number of seconds.
     *
     * @param queryString query string portion of URL to test
     * @param seconds the range of seconds to accept (defaults to 30)
     *
     * @return true if timestamp exists and is within the range, false otherwise
     *
     * @see #TimestampUrl(java.lang.String)
     */
    public static boolean IsQueryStringCurrent(String queryString,
					       int seconds) {
	return IsQueryStringCurrent(queryString, seconds,
				    System.currentTimeMillis());
    }

    /**
     * Tests if a query string has a timestamp generated within a
     * specified number of seconds.
     *
     * @param queryString query string portion of URL to test
     * @param seconds the range of seconds to accept (defaults to 30)
     * @param timestamp milliseconds since the epoch
     *
     * @return true if timestamp exists and is within the range, false otherwise
     *
     * @see #TimestampUrl(java.lang.String, long)
     */
    public static boolean IsQueryStringCurrent(String queryString, int seconds,
					       long timestamp) {
	String ts = GetQueryParameter(queryString, TIMESTAMP_QUERY);

	if (ts != null) {
	    try {
		long l = Long.parseLong(ts);
		long diff = timestamp / 1000 - l;

		if (-seconds <= diff && diff <= seconds)
		    return true;
	    } catch (NumberFormatException ex) {
	    }
	}

	return false;
    }

    /**
     * Encrypt critical portions of a URL.
     *
     * Pulls 16 bytes from the system's hard random number generator to
     * use as the initialization vector (IV).
     *
     * @param url URL to append query parameters to
     * @param query query string to encrypt
     * @param key secret encryption key
     *
     * @return new URL
     *
     * @see #AddEncryptedQueryString(String, String, String, byte[])
     */
    public static String AddEncryptedQueryString(String url, String query,
						 String key) {
	return AddEncryptedQueryString(url, query, key, GetIV());
    }

    /**
     * Encrypt critical portions of a URL.
     *
     * @param url URL to append query parameters to
     * @param query query string to encrypt
     * @param key secret encryption key
     * @param iv  16-byte initialization vector
     *
     * @return new URL
     *
     * @see #AddEncryptedQueryString(String, String, String)
     */
    public static String AddEncryptedQueryString(String url, String query,
						 String key, byte[] iv) {
	return AddToUrl(url, ENCRYPTED_QUERY,
			new String(Base64Coder.urlsafe_encode(Encrypt(query,
								      key,
								      iv))));
    }

    /**
     * Encrypt <em>data</em> using AES (Rijndael-128) in CBC mode.
     *
     * @param data  String to encrypt
     * @param key   Secret encryption key
     * @param iv    16-byte initialization vector, or null to use system
     *              hard random number generator
     * @return the IV appended with the encryupted data as a binary string.
     */
    public static byte[] Encrypt(String data, String key, byte[] iv) {
	if (iv == null) iv = GetIV();

	IvParameterSpec ivSpec = new IvParameterSpec(iv);

	try {
	    MessageDigest md = MessageDigest.getInstance(DIGEST_ALGORITHM);

	    byte[] key_data = md.digest(key.getBytes(ENCODING));

	    SecretKeySpec k = new SecretKeySpec(key_data, 0, 16,
						CRYPTO_ALGORITHM);

	    try {
		Cipher c = Cipher.getInstance(TRANSFORMATION);
		c.init(Cipher.ENCRYPT_MODE, k, ivSpec);

		byte[] bytes = c.doFinal(data.getBytes(ENCODING));
		byte[] result = new byte[iv.length + bytes.length];

		System.arraycopy(iv, 0, result, 0, iv.length);
		System.arraycopy(bytes, 0, result, iv.length, bytes.length);

		return result;
	    } catch (GeneralSecurityException ex) {
		throw new RuntimeException(TRANSFORMATION + ERR_DESC);
	    }
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + ERR_DESC);
	} catch (NoSuchAlgorithmException ex) {
	    throw new RuntimeException(DIGEST_ALGORITHM + ERR_DESC);
	}
    }

    private static final byte[] GetIV() {
	byte[] iv = new byte[16];

	try {
        if(softUrandom)  {
            for (int i = 0; i < iv.length; i++) {
                iv[i] = (byte)Math.floor(Math.random()*256);            
            }
        }
        else
		    urandom.read(iv);
	} catch (IOException ex) {
	    throw new RuntimeException("IO error on a system device?  " +
				       "Give me a break!");
	}

	return iv;
    }

    public static String GetEncryptedQueryString(String queryString,
						 String key) {
	String v = GetQueryParameter(queryString, ENCRYPTED_QUERY);

	if (v == null)
	    return null;

	try {
	    return new String(Decrypt(Base64Coder.urlsafe_decode(v.toCharArray()),
				      key),
			      ENCODING);
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + ERR_DESC);
	}
    }

    public static byte[] Decrypt(byte[] data, String key) {
	IvParameterSpec ivSpec = new IvParameterSpec(data, 0, 16);

	try {
	    MessageDigest md = MessageDigest.getInstance(DIGEST_ALGORITHM);

	    byte[] key_data = md.digest(key.getBytes(ENCODING));

	    SecretKeySpec k = new SecretKeySpec(key_data, 0, 16,
						CRYPTO_ALGORITHM);

	    try {
		Cipher c = Cipher.getInstance(TRANSFORMATION);
		c.init(Cipher.DECRYPT_MODE, k, ivSpec);

		return c.doFinal(data, 16, data.length - 16);
	    } catch (GeneralSecurityException ex) {
		throw new RuntimeException(TRANSFORMATION + ERR_DESC);
	    }
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + ERR_DESC);
	} catch (NoSuchAlgorithmException ex) {
	    throw new RuntimeException(DIGEST_ALGORITHM + ERR_DESC);
	}
    }

    public static String SignQueryString(String secret, String url) {
	int i = url.indexOf('?');
	String qs = i < 0 ? "" : url.substring(i + 1);

	try {
	    SecretKeySpec key = new SecretKeySpec(secret.getBytes(ENCODING),
						  MAC_ALGORITHM);
	    Mac mac = Mac.getInstance(MAC_ALGORITHM);

	    mac.init(key);

	    return AddToUrl(url, HMAC_QUERY,
			    EncodeHex(mac.doFinal(qs.getBytes(ENCODING))));
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + ERR_DESC);
	} catch (NoSuchAlgorithmException ex) {
	    throw new RuntimeException(DIGEST_ALGORITHM + ERR_DESC);
	} catch (InvalidKeyException ex) {
	    throw new RuntimeException();
	}
    }

    public static boolean IsSignedQueryStringValid(String secret,
						   String query_string) {
	int i = query_string.lastIndexOf(HMAC_QUERY + '=');
	String qs;
	byte[] sig;

	if (i > 0)
	    qs = query_string.substring(0, i - 1);
	else if (i == 0)
	    qs = "";
	else
	    return false;

	sig = DecodeHex(query_string.substring(i + 5));
	if (sig == null) return false;

	try {
	    SecretKeySpec key = new SecretKeySpec(secret.getBytes(ENCODING),
						  MAC_ALGORITHM);
	    Mac mac = Mac.getInstance(MAC_ALGORITHM);

	    mac.init(key);

	    return Arrays.equals(mac.doFinal(qs.getBytes(ENCODING)), sig);
	} catch (UnsupportedEncodingException ex) {
	    throw new RuntimeException(ENCODING + ERR_DESC);
	} catch (NoSuchAlgorithmException ex) {
	    throw new RuntimeException(DIGEST_ALGORITHM + ERR_DESC);
	} catch (InvalidKeyException ex) {
	    throw new RuntimeException();
	}
    }

    private static final char[] DIGITS = { '0', '1', '2', '3',
					   '4', '5', '6', '7',
					   '8', '9', 'a', 'b',
					   'c', 'd', 'e', 'f' };

    static String EncodeHex(byte[] data) {
	StringBuffer sb = new StringBuffer(data.length * 2);

	for (int i = 0; i < data.length; i++) {
	    int ch = data[i] & 0xFF;

	    sb.append(DIGITS[ch >> 4]);
	    sb.append(DIGITS[ch & 0x0F]);
	}

	return sb.toString();
    }

    static byte[] DecodeHex(String s) {
	int l = s.length();

	if (l % 2 != 0) return null;

	byte[] result = new byte[l / 2];

	for (int i = 0; i < l; i += 2) {
	    int d1 = Character.digit(s.charAt(i), 16);
	    int d2 = Character.digit(s.charAt(i + 1), 16);

	    if (d1 == -1 || d2 == -1)
		return null;

	    result[i / 2] = (byte) (d1 * 16 + d2);
	}

	return result;
    }

    private static final String AddToUrl(String url, String name,
					 String value) {
	return url + (url.indexOf('?') >= 0 ? '&' : '?') + name + '=' + value;
    }

    public static String GetQueryParameter(String queryString, String key) {
	int qlen = queryString.length();
	int i = queryString.indexOf('?') + 1, len = key.length();

	do {
	    if (i + len < qlen && queryString.charAt(i + len) == '=' &&
		key.equals(queryString.substring(i, i + len))) {
		int j = queryString.indexOf('&', i + len);

		if (j >= 0)
		    return queryString.substring(i + len + 1, j);
		else
		    return queryString.substring(i + len + 1);
	    }

	    i = queryString.indexOf('&', i) + 1;
	} while (i > 0);

	return null;
    }

}
