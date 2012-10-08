<%@ include file="taglibs.jsp" %>
<stripes:layout-render name="/standard.jsp" title="CDA Document Display">
    <stripes:layout-component name="contents">

<jsp:useBean id="patientManager" scope="session"
	             class="net.medcommons.mdl.PatientRecordResultManager"/>
<c:choose>
	<c:when test="${patientManager != null}">
	        	<c:set var="cdaDocument" value="${patientManager.cdaDocument}" scope="page"/>
	        	<c:set var="cdaCacheDocument" value="${patientManager.cdaCacheDocument}" scope="page"/>
	        	<c:import var="document" url="${cdaDocument}"/>
				<c:import var="renderXSL" url="/mesa_xsl/CDA.xsl"/>
				<stripes:link href="/mdlCache${cdaCacheDocument}" event="display">
			     	${cdaCacheDocument}
			     </stripes:link>
				<x:transform xml="${document}" xslt="${renderXSL}"/>  
			    
	</c:when>	

	 
	         
	
</c:choose> 


 </stripes:layout-component>
</stripes:layout-render>
