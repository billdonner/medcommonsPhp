/*
 * $Id: MCProperty.java 5261 2008-04-27 22:37:01Z ssadedin $
 * Created on 28/04/2008
 */
package net.medcommons.identity.model;

import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.Table;

@Entity
@Table(name="mcproperties")
public class MCProperty {
  
  @Id
  String property;
  String value;
  String infourl;
  String comment;
  
  public String getProperty() {
      return property;
  }
  public void setProperty(String property) {
      this.property = property;
  }
  public String getValue() {
      return value;
  }
  public void setValue(String value) {
      this.value = value;
  }
  public String getInfourl() {
      return infourl;
  }
  public void setInfourl(String infourl) {
      this.infourl = infourl;
  }
  public String getComment() {
      return comment;
  }
  public void setComment(String comment) {
      this.comment = comment;
  }
}
