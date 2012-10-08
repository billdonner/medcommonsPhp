package net.medcommons.mdl;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.StringReader;
import java.io.StringWriter;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;



import net.medcommons.mdl.utils.MetadataFormat;




import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.Namespace;
import org.jdom.filter.ElementFilter;
import org.jdom.input.SAXBuilder;
import org.jdom.output.Format;
import org.jdom.output.XMLOutputter;

import OHFBridgeStub.AddressType;
import OHFBridgeStub.AssigningAuthorityType;
import OHFBridgeStub.PatientIdType;
import OHFBridgeStub.PatientInfoType;
import OHFBridgeStub.PatientNameType;

public class CCRGenerator {

	private static Logger logger = Logger.getLogger(CCRGenerator.class);
	static File confDir = null;
	private Format utfOutputFormat = null;
	static{
		confDir = new File("conf");
		if (!confDir.exists())
			confDir = new File("etc/resources/conf");
	}
	
	private Document ccrDocument = null;
	
	public CCRGenerator(){
		this.utfOutputFormat = Format.getPrettyFormat();
		utfOutputFormat.setEncoding("UTF-8");
	}
	/*
	 public static Document loadTemplate(String templatePath) throws JDOMException, IOException {
		 	File templateFile = new File(templatePath);
		 	if (!templateFile.exists())
		 		throw new FileNotFoundException(templateFile.getAbsolutePath());
	        // Open the file as a stream and read it
	        FileInputStream inputStream = new FileInputStream(templatePath);
	        StringBuffer xml = new StringBuffer();
	        byte[] buffer = new byte[4096];
	        int read = -1; 
	        while ((read = inputStream.read(buffer)) >= 0) {
	            xml.append(new String(buffer,0,read));
	        }
	        inputStream.close();                 
	        Document jdomDocument = new CCRBuilder().build(new StringReader(xml.toString()));
	        return jdomDocument;        
	    }
	 */
	public Document loadTemplateCCR() throws IOException,JDOMException{
 		ccrDocument = loadTemplate("CCRTemplate.xml");
 		return(ccrDocument);
	}
	public Document loadReferenceCCR() throws IOException,JDOMException{
 		Document doc = loadTemplate("CCRReferenceTemplate.xml");
 		return(doc);
	}
	public Document loadActorCCR() throws IOException,JDOMException{
 		Document doc = loadTemplate("CCRActorTemplate.xml");
 		return(doc);
	}
	
	
	private Document loadTemplate(String filename)throws IOException, JDOMException{
		File template = new File(confDir, filename);
		
		if (!template.exists()){
			throw new FileNotFoundException(template.getAbsolutePath());
		}
		//SAXBuilder builder = new SAXBuilder();
 		Document doc = new CCRBuilder().build(template);
 		return(doc);
	}
	
	
	/**
	 * <IDs>
     * <Type>
	 *   <Text>MedCommons Account Id</Text>
	 * </Type>
	 * <ID>1012576340589251</ID>
	 * <Source>
	 *   <Actor>
	 *     <ActorID>ID-8eb1fd10-1f7a-4014-906c-fdfe5092ca09</ActorID>
	 *   </Actor>
	 * </Source>
	 * </IDs>
	 * @param medCommonsId
	 */
	
	public void setDemographics(String medCommonsId, PatientInfoType patientInfoType)throws IOException, JDOMException, InvalidCCRException{
		logger.info("setting Demographics:" + medCommonsId + " " + MetadataFormat.toString(patientInfoType));
		//### 
		addPatientId(medCommonsId, CCRDocumentTypes.MEDCOMMONS_PATIENT_ID_TYPE);
		//CCRElement mcidElement=	XPathUtils.getValue(ccrDocument,"patientMedCommonsId");
		
		
		PatientNameType patientName = patientInfoType.getPatientName();
		if(patientName != null){
			XPathUtils.setValue(ccrDocument,"patientGivenName", patientName.getGivenName()); 
			CCRElement patientElement = getPatientActor();
			
			if (patientName.getOtherName() != null){
				createPath("patientMiddleName");
				XPathUtils.setValue(ccrDocument,"patientMiddleName", patientName.getOtherName()); 
			}
			XPathUtils.setValue(ccrDocument,"patientFamilyName", patientName.getFamilyName()); 
		}
		String sex = patientInfoType.getPatientSex();
		if (sex != null){
			createPath("patientGender");
			XPathUtils.setValue(ccrDocument,"patientGender", patientName.getFamilyName());
		}
		String dob = patientInfoType.getPatientDateOfBirth();
		logger.info("Unhandled: patient dob:" + dob);
		AddressType address = patientInfoType.getPatientAddress();
		logger.info(MetadataFormat.toString(address));
		
		PatientIdType patientIdType = patientInfoType.getPatientIdentifier();
		if (patientIdType != null){
			String idNumber = patientIdType.getIdNumber();
			
			String idType = "HIMSS";
			AssigningAuthorityType authorityType = patientIdType.getAssigningAuthorityType();
			if (authorityType != null){
				idType = authorityType.getNamespaceId();
			}
			
			logger.info(MetadataFormat.toString(authorityType));
			addPatientId(idNumber, idType);
		}
	}
	public void addReference(String guid, String displayName, String contentType) throws IOException, JDOMException{
		logger.info("Adding document " + guid + ", " + displayName + ", " + contentType);
		//Document reference = loadReferenceCCR();
		Namespace namespace = namespace = ccrDocument.getRootElement().getNamespace();;
		Document referenceDoc = loadTemplate("CCRReferenceTemplate.xml");
        CCRElement reference = (CCRElement) referenceDoc.getRootElement();
        reference.getChild("ReferenceObjectID").setText(generateObjectID());
        Element references = getOrCreate((CCRElement)ccrDocument.getRootElement(), "References");        
        XPathCache.getElement(reference, "referenceType").setText(contentType);
        XPathCache.getElement(reference, "referenceURL").setText("mcid://" + guid);
        
        XPathCache.getElement(reference, "referenceDisplayName").setText(displayName);        
        referenceDoc.removeContent(reference);
        reference.setNamespace(namespace);
        references.addContent(reference);
		
	}
	public Document getCCRDocument(){
		return(this.ccrDocument);
	}
	public static void main (String [] args){
		try{
			CCRGenerator generator = new CCRGenerator();
			generator.loadTemplateCCR();
			
			PatientInfoType patient = new PatientInfoType();
			PatientNameType patientName = new PatientNameType();
			patientName.setFamilyName("Hernandez");
			patientName.setGivenName("Joan");
			patientName.setPrefix("Ms.");
			patient.setPatientName(patientName);
			
			PatientIdType patientIdType=new PatientIdType();
			patientIdType.setIdNumber("ABCDEFG");
			patient.setPatientIdentifier(patientIdType);
			
			
			generator.setDemographics("1012576340589251", patient);
			
			
			
			generator.addReference("6882d38b08e231f4ffe2d1a9dfd856c0b3abb92b", "HTML file", "text/html" );
			XMLOutputter outputter = new XMLOutputter();
			outputter.output(generator.ccrDocument, System.out);
			
			File saveFile = new File("CCRGeneratorOutput.xml");
			FileOutputStream out = new FileOutputStream(saveFile);
			outputter.output(generator.ccrDocument, out);
			
			
		}
		catch(Exception e){
			e.printStackTrace();
		}
		
	}
	
	public static CCRElement getOrCreate(CCRElement parent, String name, String after) {
	       return getOrCreate(parent,name, new String[] { after }); 
	    }
	    
		/**
		 * Attempts to retrieve a child element of the given name from the parent.
		 * If the child is not found, creates it.
		 * 
		 * @param parent -
		 *            element to retrieve/create child from/in
		 * @param name -
		 *            name of child element (assumed to be in CCR namespace)
		 *            ###### Assumed to be in the namespace of the parent?
		 * @return
		 */
		public static CCRElement getOrCreate(CCRElement parent, String name) {
			CCRElement child = parent.getChild(name);
			if (child == null) {
				child = new CCRElement(name, parent.getNamespace());
	            
	            // If there is a defined order for children of this parent then 
	            // use that order.
	            if(CCRElement.CCR_ELEMENT_ORDER.get(parent.getName()) != null)
	                return getOrCreate(parent,name,CCRElement.CCR_ELEMENT_ORDER.get(parent.getName()));
	            else
	                parent.addContent(child);
			}
			return child;
		}    
		
		public static Element create(Element parent, String name){
			Element child  = new Element(name, parent.getNamespace());
			parent.addContent(child);
			return(child);
		}
	    
	    /**
	     * Retrieves the given field, creating it if it does not already exist, while
	     * keeping order consistent with the given array of element names.  Any elements 
	     * prior in the array are ensured to be prior in the result, any elements 
	     * after are ensured to be after in the result.
	     * 
	     * @param parent
	     * @param name
	     * @param afterName
	     * @return
	     */
		public static Element createAfter(Element parent, String name, String afterName[]){
			Element child = null;
			
			int index = -1;
			int i = 0;
			
			child = new Element(name, parent.getNamespace());
			logger.debug("createAfter:New child created for " + name);
			
			List parentContents = parent.getContent();
			Iterator iter =parentContents.iterator();
			while (iter.hasNext()){
				Object obj = iter.next();
				if (obj instanceof Element){
					Element element = (Element) obj;
					logger.debug("createAfter:Found element " + element.getName());
				}
				else
					logger.debug("createAfter:Non-element value:" + obj.getClass().getCanonicalName());
			}
			
			Element insertAfter = null;
			for (i=0;i<afterName.length;i++){
				insertAfter = parent.getChild(afterName[i], parent.getNamespace());
				
	            logger.debug("createAfter:Insert after is " + insertAfter);
	            
	            // If we found the name of element to be inserted, break here
	            if(afterName[i].equals(name))
	                break;
	            
				if (insertAfter != null){
					index = parent.indexOf(insertAfter);
					logger.debug("createAfter: found match with " + insertAfter.getName());
				}
			}
			
			if (index == -1){
				//logger.error("Appending " + name + " at end of " + parent.getName());
				parent.addContent(child);
			}
			else{
				//logger.error("Inserting " + name + " after index :" + index + "(" + afterName[i] + ")");
				parent.addContent(index+1, child);
			}
			return(child);
		}
		
		public static CCRElement getOrCreate(CCRElement parent, String name, String afterName[]) {
			CCRElement child = parent.getChild(name);
			int index = -1;
			if (child == null) {
				child = new CCRElement(name, parent.getNamespace());
				logger.debug("New child created for " + name);
				
				List parentContents = parent.getContent();
				Iterator iter =parentContents.iterator();
	            if(logger.isDebugEnabled()){
	                while (iter.hasNext()){
	                    Object obj = iter.next();
	                    if (obj instanceof Element){
	                        Element element = (Element) obj;
	                        logger.debug("Found element " + element.getName());
	                    }
	                    else
	                        logger.debug("Non-element value:" + obj.getClass().getCanonicalName());
	                }
	            }
				
				Element insertAfter = null;
				for (int i=0;i<afterName.length;i++){
	                if(afterName[i].equals(name))
	                    break;
					insertAfter = parent.getChild(afterName[i], parent.getNamespace());
					//logger.debug("Insert fter is " + insertAfter);
					if (insertAfter != null){
						int insertIndex = parent.indexOf(insertAfter);
						//logger.debug("found match with " + insertAfter.getName());
	                    if(insertIndex >= index)
	                        index = insertIndex;
					}
				}
	            
	            if (index == -1)
	                parent.addContent(0,child);
	            else
	                parent.addContent(index+1, child);
			}
			else {
				logger.debug("child " + name + " already exists..");
			}
			return(child);
		}
			
			

		/**
		 * Creates a default MedCommons actor. If the Actors node does not exist it
		 * will be created.
		 * 
		 * @param type - one of To or From
		 * @throws JDOMException
		 * @throws IOException
	     * @return the Actor element created. (note, not the link, the actual Actor)
		 */
		public CCRElement createDefaultActor(String type) throws JDOMException,
				IOException {
			Namespace namespace = ccrDocument.getRootElement().getNamespace();
	        CCRElement root = (CCRElement)ccrDocument.getRootElement();

	        CCRElement actor = root.getChild(type);
			if (actor == null) {
				Element patient = root.getChild("Patient", namespace);
				int patientIndex = 0;
				if (patient != null)
					patientIndex = root.indexOf(patient);

				int pos = patientIndex > 0 ? patientIndex + 1 : 0;

				if ("To".equals(type)) {
					if (root.getChild("From", namespace) != null)
						pos = root.indexOf(root.getChild("From", namespace)) + 1;
				}

				actor = new CCRElement(type, namespace);

				// TODO: will produce invalid content for From node if there is
				// already a To (order will be wrong).
				root.addContent(pos, actor);
			}
			return this.createActorForLink(actor);
		}

		/**
		 * Creates an Actor initialized with default values corresponding to a
		 * MedCommons actor.
		 * 
		 * @param actorLinkParent
		 * @throws JDOMException
		 * @throws IOException
		 */
		private CCRElement createActorForLink(CCRElement actorLinkParent) throws JDOMException,
				IOException {
	        CCRElement root = (CCRElement)ccrDocument.getRootElement();
	        Namespace namespace = root.getNamespace();
	        CCRElement actor = this.createActor();
	        
			getOrCreate(getOrCreate(actorLinkParent, "ActorLink"), "ActorID")
					.setText(actor.getChildText("ActorObjectID",namespace));

	        /*
			// Now create actual actor
			Document actorDoc = XPathCache.loadTemplate(TEMPLATE_PATH
					+ "/actorTemplate.xml");
			Element actor = actorDoc.getRootElement();
			actor.setNamespace(actorLinkParent.getNamespace());
			actorDoc.removeContent(actor);
			
			Element actors = getOrCreate(root, "Actors");
			
			actors.addContent(actor);
			actor.getChild("ActorObjectID", namespace).setText(actorId);
			actor.getChild("Source", namespace).getChild("Actor", namespace).getChild(
					"ActorID", namespace).setText(actorId);
	                */
	        
	        return actor;
			//logger.error("Actors are:" + dumpXML(actors));
		}
	    
	    public CCRElement createActor() throws JDOMException, IOException {
	        // Allocate an actor ID
	        String actorId = generateObjectID();
	        Namespace namespace = ccrDocument.getRootElement().getNamespace();
	        CCRElement root = (CCRElement)ccrDocument.getRootElement();
	        Document actorDoc = loadTemplate("/actorTemplate.xml");
	        CCRElement actor = (CCRElement) actorDoc.getRootElement();
	        actor.setNamespace(namespace);
	        actorDoc.removeContent(actor);
	        
	        CCRElement actors = getOrCreate(root, "Actors");
	        
	        actors.addContent(actor);
	        actor.getChild("ActorObjectID", namespace).setText(actorId);
	        actor.getChild("Source", namespace).getChild("Actor", namespace).getChild(
	                "ActorID", namespace).setText(actorId);
	        
	        return actor;        
	    }

		/**
		 * Adds a new ActorLink to the existing actorLinkParent passed as a
		 * parameter.
		 * 
		 * @param actorLinkParent
		 * @param actorId
		 */
		public void addActorLink(Element actorLinkParent, String actorId) {
			Namespace namespace = ccrDocument.getRootElement().getNamespace();
			Element actorLink = new CCRElement("ActorLink");
			actorLinkParent.addContent(actorLink.addContent(new CCRElement("ActorID",
					namespace).setText(actorId)));
		}

		/**
		 * Searches for the first From actorLink that links to an actor with an
		 * email address and returns the ActorLink element.
		 * 
		 * @return
		 * @throws JDOMException
		 * @throws IOException
		 */
		public CCRElement getPrimaryFromActor() throws JDOMException, IOException {
			Namespace namespace = ccrDocument.getRootElement().getNamespace();
	        CCRElement root = (CCRElement)ccrDocument.getRootElement();

			// Get the from node, if it exists
			Element fromNode = root.getChild("From", namespace);

			if (fromNode == null)
				return null;

			// For each actorlink
			Iterator actorLinks = fromNode.getDescendants(new ElementFilter(
					"ActorLink", namespace));
			Map params = new HashMap();
			for (Iterator iter = fromNode.getDescendants(new ElementFilter(
					"ActorLink", namespace)); iter.hasNext();) {
	            CCRElement actorLink = (CCRElement) iter.next();
				// Does this actor have an email address
				String actorObjId = actorLink.getChildText("ActorID", namespace);
				params.put("actorId", actorObjId);
				if (actorObjId != null) {
					Element emailElement = XPathCache.getElement(ccrDocument, "emailFromActorID", params);
					if (emailElement != null) {
						if (!StringUtil.blank(emailElement.getText())) {
							return actorLink;
						}
					}
				}
			}

			return null;
		}
		
	    /**
	     * Adds an ID of given Type and Value to the Patient Actor of this CCR
	     * 
	     * @param patientId
	     * @param patientIdType
	     * @throws JDOMException
	     * @throws IOException
	     */
	    public void addPatientId(String patientId, String patientIdType) throws JDOMException, IOException {
	        Element patientActor = XPathCache.getElement(ccrDocument, "patientActor");
	        if(patientActor == null) {
	            throw new RuntimeException("Unable to locate Patient Actor.  CCR appears to be invalid.");
	        }
	        Namespace ns = ccrDocument.getRootElement().getNamespace();
	        
	        Element IDs = new CCRElement("IDs", ns);
	        IDs.addContent(new CCRElement("Type", ns).addContent(new CCRElement("Text",ns).setText(patientIdType)));
	        IDs.addContent(new CCRElement("ID", ns).setText(patientId));
	        IDs.addContent(
	            new CCRElement("Source", ns).addContent(
	                            new CCRElement("Actor",ns).addContent(
	                                            new CCRElement("ActorID",ns).setText(XPathUtils.getValue(ccrDocument,"patientActorID")))));
	        
	        // HACK:  must be before Address and Source
	        int index = -1;
	        if(patientActor.getChild("Address", ns)!=null) {
	            index = patientActor.indexOf(patientActor.getChild("Address", ns));
	        }
	        else
	        if(patientActor.getChild("Source", ns)!=null) {
	            index = patientActor.indexOf(patientActor.getChild("Source", ns));
	        }
	        if(index == -1) {
	            patientActor.addContent(IDs);
	        }
	        else {
	            patientActor.addContent(index,IDs);
	        }               
	    }
		/**
		 * Finds and returns the patient Actor for this CCR
		 */
		private CCRElement getPatientActor() throws JDOMException, IOException {
			CCRElement patientActor = (CCRElement)XPathCache.getElement(ccrDocument,
					"patientActor");
			return patientActor;
		}
	    
		private CCRElement createPatientDateOfBirth() throws JDOMException,
				IOException {
			// Get Patient element
			CCRElement patientActor = getPatientActor();
			return getOrCreate(getOrCreate(patientActor, "Person"), "DateOfBirth");
		}
		/**
		 * Finds or creates the Patient "Person" element and returns it.
		 * @throws InvalidCCRException 
		 */
		private CCRElement getPatientPerson() throws JDOMException, IOException, InvalidCCRException {
	        CCRElement patientActor = this.getPatientActor();
	        if(patientActor == null) {
	            throw new InvalidCCRException("Unable to locate Patient Actor.  Patient Actor is required by CCR Standard.");
	        }
	        CCRElement patientPerson = patientActor.getOrCreate("Person"); 
	        //getOrCreate(patientActor, "Person", new String[]{ "Source" });
			return patientPerson;
		}

	    /**
	     * Returns a To actor for the encapsulated CCR document.  If no To actor
	     * exists then one will be created based on a default template.
	     * 
	     * @throws JDOMException
	     * @throws IOException
	     */
	    private CCRElement getToActor() throws JDOMException, IOException {
	        // Is there a To actor yet?
	        CCRElement toActorId = (CCRElement)XPathCache.getElement(ccrDocument,"toActorId");
	        if(toActorId == null) { // no To Actor
	            this.createDefaultActor("To"); 
	        }

	        CCRElement toActor = (CCRElement)XPathCache.getElement(ccrDocument, "toActor");
	        return toActor;
	    }

	    public Element createPath(String path) throws JDOMException, IOException, InvalidCCRException {
			//Element root = this.ccrDocument.getRootElement();
	    	Namespace namespace = ccrDocument.getRootElement().getNamespace();
			if ("patientAge".equals(path)) {
				CCRElement patientDob = this.createPatientDateOfBirth();

				// Add age
				CCRElement age = getOrCreate(patientDob, "Age");
				CCRElement value = getOrCreate(age, "Value");
				//getOrCreate(age, "Units").setText("Years");
				return value;
			}

			if ("patientGender".equals(path)) {
	            CCRElement person = this.getPatientPerson();
				return getOrCreate(getOrCreate(person, "Gender"), "Text");
			}

			if ("patientExactDateOfBirth".equals(path)) {
	            CCRElement patientDob = this.createPatientDateOfBirth();
				return getOrCreate(patientDob, "ExactDateTime");
			}

			// TODO: possibly should NOT allow this to be created
			// - a user creating this doesn't really make sense.
			// Logic should be:
			// If a DICOMPatient identifier exists - test for equality.
			// If it is the same - don't change anything.
			// If it's new - then append a new one.
			// If there isn't one -add a new <IDs>.
			// Never delete/pave over old one.
			/*
			
		*/
			if ("patientDICOMId".equals(path)) {
				Element patient = getPatientActor();
				Element dicomID = XPathCache.getElement(this.ccrDocument,
					"patientDICOMId");
				if(dicomID != null){
					StringWriter sw = new StringWriter();

					new XMLOutputter(utfOutputFormat).output(dicomID, sw);
					//log.error("DICOM ID element is " + sw.toString());
					return(dicomID);
				}
				else{
					// Insert new IDs after Person.
					// This is way too ugly.
					String afterList[] = new String[1];
					afterList[0] = "Person";
					Element ids = createAfter(patient, "IDs", afterList);
					create(create(ids, "Type"), "Text").setText(
						"DICOM Patient Id");
				
					String fromActor = XPathUtils.getValue(this.ccrDocument,"fromActorId");
					if (fromActor == null) {
						this.createDefaultActor("From");
						fromActor = XPathUtils.getValue(this.ccrDocument,"fromActorId");
					}
					Element id = new Element("ID", namespace);
					ids.addContent(id);
					ids.addContent(new Element("Source", namespace)
							.addContent(new Element("Actor", namespace)
							.addContent(new Element("ActorID", namespace)
									.setText(fromActor))));

					logger.debug("Adding new 'DICOM Patient Id' IDs");
					return(id);
					
				}
				/*	
				// There may be multiple ids here. Be careful to only append
				// the DICOM id to the existing IDs.
				Element ids = getOrCreate(patient, "IDs");
				
				getOrCreate(getOrCreate(ids, "Type"), "Text").setText(
						"DICOM Patient Id");
				return getOrCreate(ids, "ID");
				*/
			}

			if ("patientAddress1".equals(path)) {
	            CCRElement patient = this.getPatientActor();
				return getOrCreate(getOrCreate(patient, "Address"), "Line1");
			}

			if ("patientCity".equals(path)) {
	            CCRElement patient = this.getPatientActor();
				return getOrCreate(getOrCreate(patient, "Address"), "City");
			}
	        
	        if ("patientState".equals(path)) {
	            CCRElement patient = this.getPatientActor();
	            return getOrCreate(getOrCreate(patient, "Address"), "State");
	        }
	        
	        if ("patientPostalCode".equals(path)) {
	            CCRElement patient = this.getPatientActor();
	            return getOrCreate(getOrCreate(patient, "Address"), "PostalCode");
	        }
	        
	        if ("patientCountry".equals(path)) {
	            CCRElement patient = this.getPatientActor();
	            return getOrCreate(getOrCreate(patient, "Address"), "Country");
	        }
	        
			if ("purposeText".equals(path)) {
	            return this.createPurposeText("User Comment");
			}
	 
	        if ("objectiveText".equals(path)) {
	            return this.createPurposeText("Objective");
	        }
	        
	        if ("assessmentText".equals(path)) {
	            return this.createPurposeText("Assessment");
	        }
	        
	        if ("planText".equals(path)) {
	            return this.createPurposeText("Plan");
	        }
	        
			if ("toEmail".equals(path)) {            
	            CCRElement toActor = this.getToActor();            
	            return getOrCreate(getOrCreate(toActor,"EMail"),"Value");
			}
	        
	        if ("toEmail".equals(path)) {            
	            CCRElement toActor = this.getToActor();            
	            return getOrCreate(getOrCreate(toActor,"EMail"),"Value");
	        }
	        
	        if ("patientEmail".equals(path)) {            
	            CCRElement patient = this.getPatientActor();
	            return getOrCreate(getOrCreate(patient,"EMail"),"Value");
	        }
	        
	        if ("sourceEmail".equals(path)) {             
	            CCRElement from = this.getPrimaryFromActor();
	            if(from == null) {
	                from = this.createDefaultActor("From");                
	            }
	            return getOrCreate(getOrCreate(from,"EMail"),"Value");
	        }
	        
	        if ("patientGivenName".equals(path)) {            
	            CCRElement patient = this.getPatientPerson();
	            return getOrCreate(getOrCreate(getOrCreate(patient,"Name"),"CurrentName"),"Given");
	        }
	        
	         if ("patientFamilyName".equals(path)) {            
	             CCRElement patient = this.getPatientPerson();
	            return getOrCreate(getOrCreate(getOrCreate(patient,"Name"),"CurrentName"),"Family");
	        }
	        
	        if ("patientMiddleName".equals(path)) {            
	            CCRElement patient = this.getPatientPerson();
	            return getOrCreate(getOrCreate(getOrCreate(patient,"Name"),"CurrentName"),"Middle");
	        }
	        
			String afterList[] = new String[1];
			afterList[0] = "Source";
			if ("patientPhoneNumber".equals(path)) {
	            CCRElement patient = this.getPatientActor();
				return getOrCreate(getOrCreate(patient, "Telephone", afterList), "Value");
			}
			return null;
		}
	    
	    
	    
	    public CCRElement getRoot() throws JDOMException, IOException {
	        return (CCRElement) ccrDocument.getRootElement();
	    }
	    /**
	     * Creates a comment field in the CCR for the MedCommons Comment.
	     * @param type TODO
	     */
	    private CCRElement createPurposeText(String type) throws JDOMException, IOException {
	        CCRElement purposeText;
	        Namespace namespace = getRoot().getNamespace();
	        // Create purpose text
	        CCRElement comment = getRoot().getOrCreate("Comments").addChild(new CCRElement("Comment"));

	        // Add an object id
	        comment.createPath("CommentObjectID", generateObjectID());
	        
	        String dateToSet = CCRElement.getCurrentTime();
	        comment.addContent(new CCRElement("DateTime", namespace)
	        		.addContent(new CCRElement("ExactDateTime", namespace)
	        				.setText(dateToSet)));

	        // Add the comment type
	        comment.addContent(new CCRElement("Type", namespace)
	        		.addContent(new CCRElement("Text", namespace)
	        				.setText("MedCommons " + type)));

	        // create the comment node itself
	        comment.addContent(new CCRElement("Description", namespace)
	        		.addContent(purposeText = new CCRElement("Text", namespace)));

	        // Set the From actor as the source
	        String fromActor = XPathUtils.getValue(ccrDocument,"fromActorId");
	        if (fromActor == null) {
	        	this.createDefaultActor("From");
	        	fromActor = XPathUtils.getValue(ccrDocument,"fromActorId");
	        }
	        comment.addContent(new CCRElement("Source", namespace)
	        		.addContent(new CCRElement("ActorID", namespace)
	        				.setText(fromActor)));
	        return purposeText;
	    }
		/**
		 * A utility method for generating ObjectIDs inside the CCR. This just
		 * generates a random 16 digit number and prefixes it with AA (this last is
		 * only done to make it look consistent with other object ids used by other
		 * vendors).
		 * 
		 * @return - a new, unique object id
		 */
		public static String generateObjectID() {
			// Generate a PIN
			String objId = "";
			int cksum = 0;
			for (int i = 0; i < 16; ++i) {
				int digit = (int) Math.floor(Math.abs(Math.random() * 10));
				objId += digit;
			}
			return objId;
		}


}
