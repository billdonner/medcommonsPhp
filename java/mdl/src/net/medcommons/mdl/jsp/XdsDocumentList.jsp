<%@ include file="taglibs.jsp" %>
<stripes:layout-render name="/standard.jsp" title="MESA test results">
    <stripes:layout-component name="contents">

     <div class="sectionTitle">MESA TEST RESULTS</div>
          
	<jsp:useBean id="patientManager" scope="session"
	             class="net.medcommons.mdl.PatientRecordResultManager"/>

	<c:choose>
        <c:when test="${patientManager != null}">
        	
            <c:set var="documentList" value="${patientManager.documents}" scope="page"/>
            <c:set var="currentPatientRecord" value ="${patientManager.currentPatientRecord}" scope="page"/>
            ${patientManager.resultMessage}
            
          	<c:if test="${currentPatientRecord != null}">
          	 	Current patient is ${currentPatientRecord.patientName.familyName},  ${currentPatientRecord.patientName.givenName}
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