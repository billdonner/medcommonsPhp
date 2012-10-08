<%@ include file="taglibs.jsp" %>
<stripes:layout-render name="/standard.jsp" title="MESA test results">
    <stripes:layout-component name="contents">

     <div class="sectionTitle">MESA TEST RESULTS</div>
          
	<jsp:useBean id="patientManager" scope="session"
	             class="net.medcommons.mdl.PatientRecordResultManager"/>

	<c:choose>
        <c:when test="${patientManager != null}">
        	<c:set var="mesaResults" value="${patientManager.mesaResultsManager}" scope="page"/>
	        <p> Test: ${mesaResults.testname}  ${patientManager.failureMessage}</p>
            <c:set var="patientList" value="${patientManager.allRecords}" scope="page"/>
            <c:set var="documentList" value="${patientManager.documents}" scope="page"/>
            ${patientManager.resultMessage}
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
			            </tr>
			        </c:forEach>
		       
	        </table>
	        </c:if>
	        <c:if test="${fn:length(documentList) >0}" >
	         <table class="display">
		        <tr>
		            <th>uuid</th>
		            <th>document title</th>
		            <th>mimetype</th>
		            <th>uri</th>
		        </tr>
		       
			        <c:forEach items="${documentList}" var="xdsDocumentType" varStatus="loop">
			            <tr>
			                <td>
			                    ${xdsDocumentType.uuid}
			                </td>
			                <td>
			                	${xdsDocumentType.documentTitle}
			                </td>
			                <td>
				                ${xdsDocumentType.mimeType}
				              
			                </td>
			                <td>
			                	${xdsDocumentType.uri}
			                </td>
			            </tr>
			        </c:forEach>
		       
	        </table>
	        </c:if>
        </c:when>
        <c:otherwise>
            <p>No patients selected.</p>
        </c:otherwise>
    </c:choose>
    
	
    
     <c:choose>
        <c:when test="${patientManager.bridgeLog != null}">
            <c:set var="log" value="${patientManager.bridgeLog}" scope="page"/>
            <verbatim>
            <c:forEach items="${log}" var="logEntry" varStatus="loop">
           		 ${logEntry}
            </c:forEach>
            </verbatim>
        </c:when>
    </c:choose>
    
    </stripes:layout-component>
    
</stripes:layout-render>