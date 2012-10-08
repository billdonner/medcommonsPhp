<%@ include file="taglibs.jsp" %>
<stripes:layout-render name="/standard.jsp" title="Query Responses from Affinity Domain">
    <stripes:layout-component name="contents">

<div class="sectionTitle">Patient Demographic Query Results</div>
          
<jsp:useBean id="patientManager" scope="session"
	             class="net.medcommons.mdl.PatientRecordResultManager"/>


<stripes:form action="/PdqQueryResponse.action">
    <stripes:errors/>
	<c:choose>
            <c:when test="${patientManager != null}">
                <c:set var="patientList" value="${patientManager.allRecords}" scope="page"/>
            </c:when>
            <c:otherwise>
                <c:set var="patientList" value="<%= new Object[5] %>" scope="page"/>
            </c:otherwise>
        </c:choose>
        Number of patients matched  is ${fn:length(patientList)}
        <c:if test="${fn:length(patientList) >0}" >
    <table class="display">
        <tr>
            <th></th>
          
            <th>PatientID</th>
            <th>Patient Name</th>
            <th>Address:</th>
        </tr>

        
		
        <c:forEach items="${patientList}" var="patientRecord" varStatus="loop">
            <tr>

               
            
  
                <td>
                     ${patientRecord.id}
                    <stripes:hidden name="patientRecord[${loop.index}].id"/>
                </td>
                 <td>
                    ${patientRecord.id}
                </td>
                <td>
                	${patientRecord.patientIdentifier.idNumber}
                </td>
                <td>
	                ${patientRecord.patientName.familyName}
	                ,
	                ${patientRecord.patientName.givenName} 
                </td>
                <td>
                	${patientRecord.patientAddress.streetAddress}
                	${patientRecord.patientAddress.city}
                	${patientRecord.patientAddress.stateOrProvince}
                	${patientRecord.patientAddress.zipOrPostalCode}
                	
                </td>
                <td>
                 <stripes:link href="/PdqQueryResponse.action" event="selectPatient">
                                Select Patient
                                <stripes:param name="selectedRecordId" value="${patientRecord.id}"/>
                            </stripes:link>
                </td>
            </tr>
        </c:forEach>
      
    </table>
	</c:if>
   
</stripes:form>
 </stripes:layout-component>
</stripes:layout-render>