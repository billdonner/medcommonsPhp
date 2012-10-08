<%@ include file="taglibs.jsp" %>

<stripes:layout-render name="/standard.jsp" title="MDL Patient Login">
    <stripes:layout-component name="contents">

<jsp:useBean id="pdqManager" scope="session"
             class="net.medcommons.mdl.PdqRecordResultManager"/>
             

  <c:choose>
            <c:when test="${pdqManager != null}">
                <c:set var="selectedPatient" value="${pdqManager.currentPdqRecord}" scope="page"/>
            </c:when>
            <c:otherwise>
                <c:set var="list" value="<%= new Object[5] %>" scope="page"/>
            </c:otherwise>
        </c:choose>
<p>
Selected patient in Affinity Domain
</p>

        <table style="vertical-align: top;">
            <tr>
            <td>
				<table>
				      
				        <tr><td>Patient ID:</td><td>${selectedPatient.patientId} </td></tr>
						<tr><td>Patient Name:</td><td>${selectedPatient.givenName} ${selectedPatient.middleName} ${selectedPatient.familyName}</td></tr>
						<tr><td>Patient Address:</td><td>${selectedPatient.address} </td></tr>
						<tr><td>Patient Phone:</td><td>${selectedPatient.phone} </td></tr>
				 </table> 
		    </td>
		    <td>
		    At this point the patient's identity in the affinity domain is complete. Next steps
		    <ul>
		       <li> The patient needs to be given the terminal and registery/log into their IdP. At this point
		       we have 'consent' for transferring documents. </li>
		       <li> 
		       <li> 
		       The user can now navigate to the 'documents' page which contains all of the documents for the 
		       patient's id in the XDS registry. The document list will be displayed in a table with a checkbox 
		       for each row. All documents are initially selected but the user can unselect any of them.
		       </li>
		       <li> 
		       The user then selects the 'export to MedCommons' button which does the following:
		       <ul>
		       <li> Each document is read out of XDS and saved in a local cache. </li>
		       <li> Some documents are transformed into PDFs </li>
		       <li> A CCR document is created containing the patient's demographic information; references to each of the documents is 
		        embedded in the CCR </li>
		       <li> CXP is then used to transfer the data to MedCommons using the patient's federated identity (retrieved from a local database)
		       and the logged-in user's credentials from their IdP (I assume this is just SAML stuff in the SOAP header)
		       </ul>
		       </li>
		       <li> At this point we can use a different browser to log in as the patient into MedCommons to look at the documents. </li>
		    </ul>
		    
		    </td>
         </table>

    </stripes:layout-component>
</stripes:layout-render>