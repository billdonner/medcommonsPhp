package net.medcommons.mdl;

/**
 * Represents a person. Persons can be patients, clerks, or doctors.
 * Unclear if the email/password belongs at this level or not.
 * @author mesozoic
 *
 */
public class Person {
	private Integer id; // Should disappear? Is this an internal table index or a real identifier?
	   private Identity identity;
	    private String username;
	    private String firstName;
	    private String lastName;
	    private String email;
	    private String password;
	    private String title;

	    /** Default constructor. */
	    public Person() { }

	    /** Constructs a well formed person. */
	    public Person(String username, String password, String first, String last, String email, String title) {
	        this.username = username;
	        this.password = password;
	        this.firstName = first;
	        this.lastName = last;
	        this.email = email;
	        this.title = title;
	    }
	    public Person(String username, String password, String first, String last, String email, String title, Identity identity) {
	        this(username, password,first,last,email, title);
	        this.identity = identity;
	    }
	    

	    /** Gets the Identity of the person. */
	    public Identity getIdentity() { return this.identity; }

	    /** Sets the ID of the person. */
	    public void setId(Identity identity) { this.identity = identity; }

	    /** Gets the username of the person. */
	    public String getUsername() { return username; }

	    /** Sets the username of the user. */
	    public void setUsername(String username) { this.username = username; }

	    /** Gets the first name of the person. */
	    public String getFirstName() { return firstName;  }

	    /** Sets the first name of the user. */
	    public void setFirstName(String firstName) { this.firstName = firstName; }

	    /** Gets the last name of the person. */
	    public String getLastName() { return lastName; }

	    /** Sets the last name of the user. */
	    public void setLastName(String lastName) { this.lastName = lastName; }

	    /** Gets the person's email address. */
	    public String getEmail() { return email; }

	    /** Sets the person's email address. */
	    public void setEmail(String email) { this.email = email; }

	    /** Gets the person's unencrypted password. */
	    public String getPassword() {
	        return password;
	    }

	    /** Sets the person's unencrypted password. */
	    public void setPassword(String password) {
	        this.password = password;
	    }
	    public void setTitle(String title){
	    	this.title = title;
	    }

	    public String getTitle(){
	    	return(this.title);
	    }
	    /** Equality is determined to be when the ID numbers match. */
	    public boolean equals(Object obj) {
	        return (obj instanceof Person) && this.id == ((Person) obj).id;
	    }
	    /** Gets the ID of the person. */
	    public Integer getId() { return id; }

	    /** Sets the ID of the person. */
	    public void setId(Integer id) { this.id = id; }

}
