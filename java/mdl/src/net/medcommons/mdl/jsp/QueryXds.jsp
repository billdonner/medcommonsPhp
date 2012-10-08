<%@ include file="taglibs.jsp" %>

<stripes:layout-render name="/standard.jsp" title="Provider Login">
    <stripes:layout-component name="contents">


<jsp:useBean id="patientManager" scope="session"
             class="net.medcommons.mdl.PatientRecordResultManager"/>
 <c:choose>
            <c:when test="${patientManager != null}">
                <c:set var="selectedPatient" value="${patientManager.currentPdqRecord}" scope="page"/>
            </c:when>
            <c:otherwise>
                <c:set var="list" value="<%= new Object[5] %>" scope="page"/>
            </c:otherwise>
  </c:choose>             
             
<div class="sectionTitle">MDL XDS Query for patient ${selectedPatient.patientId} </div>
 
<p>
Selected patient in Affinity Domain:
</p>
<table>
        
        <tr><td>Patient ID:</td><td>${selectedPatient.patientId} </td></tr>
		<tr><td>Patient Name:</td><td>${selectedPatient.givenName} ${selectedPatient.middleName} ${selectedPatient.familyName}</td></tr>
		<tr><td>Patient Address:</td><td>${selectedPatient.address} </td></tr>
		<tr><td>Patient Phone:</td><td>${selectedPatient.phone} </td></tr>
 </table> 

  </stripes:layout-component>
</stripes:layout-render>