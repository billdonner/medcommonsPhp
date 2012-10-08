package net.medcommons.mdl;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.Properties;

import org.apache.log4j.Logger;

/**
 * Very thin wrapper around a properties file. 
 * 
 * @author mesozoic
 *
 */
public class Configuration {
	static Properties props = null;
	private static Logger logger = Logger.getLogger(Configuration.class);

	private static void init() throws IOException {
		props = new Properties();
		File f = new File("conf/mdl.properties");
		if (!f.exists()) {
			f = new File("etc/resources/conf/mdl.properties");
			if (!f.exists()){
				throw new FileNotFoundException("Properties file does not exist:"
						+ f.getAbsolutePath());
			}
		}
		logger.info("Loading configurationf from file " + f.getAbsolutePath());
		FileInputStream in = new FileInputStream(f);
		props.load(in);
	}

	public Properties getConfiguration() throws IOException {
		if (props == null)
			init();

		if (props == null) {
			throw new NullPointerException("Properties not initialized");
		}
		return (props);
	}

	public static String getProperty(String name) throws IOException {
		if (props == null)
			init();
		String value = props.getProperty(name);
		return(value);
	}

}
