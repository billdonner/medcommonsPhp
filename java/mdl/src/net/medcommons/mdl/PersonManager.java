package net.medcommons.mdl;



import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Map;
import java.util.TreeMap;

import org.apache.log4j.Logger;

/**
 * Manager class that is used to access a "database" of people that is tracked in memory.
 */
public class PersonManager {
	
	private static Logger logger = Logger.getLogger(PersonManager.class);
	
    /** Sequence counter for ID generation. */
    private static int idSequence = 0;

    /** Stores the list of people in the system. */
    private static Map<Integer,Person> people = new TreeMap<Integer,Person>();

    static {
    	
    	Identity identity = new Identity("DemoHospitalIdP","asdfgh34567");
        Person person = new Person("doctor", "tester", "Demo" , "Doctor",  "demodoctor@medcommons.net", "MD", identity);
        saveOrUpdateInternal(person);

        identity = new Identity("DemoConsumerIdP","vbnm678i89");
        person =        new Person("clerk","tester", "Demo", "Clerk","democlerk@medcommons.net","", identity);
        saveOrUpdateInternal(person);

       
    }

    /** Returns the person with the specified ID, or null if no such person exists. */
    public Person getPerson(int id) {
        return people.get(id);
    }

    /** Returns a person with the specified username, if one exists. */
    public Person getPerson(String username) {
        for (Person person : PersonManager.people.values()) {
            if (person.getUsername().equalsIgnoreCase(username)) {
            	logger.info("matched username " + username + " with pass " + person.getPassword());
                return person;
            }
        }

        return null;
    }

    /** Gets a list of all the people in the system. */
    public List<Person> getAllPeople() {
        return Collections.unmodifiableList( new ArrayList<Person>(people.values()) );
    }

    /** Updates the person if the ID matches an existing person, otherwise saves a new person. */
    public void saveOrUpdate(Person person) {
        saveOrUpdateInternal(person);
    }

    /**
     * Deletes a person from the system...doesn't do anything fancy to clean up where the
     * person is used.
     */
    public void deletePerson(int id) {
        people.remove(id);
    }

    private static void saveOrUpdateInternal(Person person) {
        if (person.getId() == null) {
            person.setId(idSequence++);
        }

        people.put(person.getId(), person);
    }
}
