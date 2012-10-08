<%@ include file="taglibs.jsp" %>

<stripes:layout-render name="/standard.jsp" title="Demographic Query/RHIO XYZQ">
    <stripes:layout-component name="contents">

        <table style="vertical-align: top;">
            <tr>
                <td style="width: 25%; vertical-align: top;">
                    <!-- Somewhat contrived example of using the errors tag 'action' attribute. -->
                    <stripes:errors action="/PdqQuery.action"/>

                    <stripes:form action="/PdqQuery.action" focus="">
                        <table>
                           
                            <tr>
                                <td style="font-weight: bold;"><stripes:label for="patient.patientId"/>:</td>
                            </tr>
                            <tr>
                                <td><stripes:text name="queryid" value="${queryid}"/></td>
                            </tr>
                            
                             <tr>
                                <td style="font-weight: bold;"><stripes:label for="patient.firstName"/>:</td>
                            </tr>
                             <tr>
                                <td><stripes:text name="firstname" value="${firstname}"/></td>
                            </tr>
                                   <tr>
                                <td style="font-weight: bold;"><stripes:label for="patient.middleName"/>:</td>
                            </tr>
                             <tr>
                                <td><stripes:text name="middlename" value="${middlename}"/></td>
                            </tr>
                            
                            <tr>
                                <td style="font-weight: bold;"><stripes:label for="patient.lastName"/>:</td>
                            </tr>
                            <tr>
                                <td><stripes:text name="lastname" value="${lastname}"/></td>
                            </tr>
                            
                            <tr>
                                <td style="font-weight: bold;"><stripes:label for="patient.suffix"/>:</td>
                            </tr>
                           <tr>
                                <td><stripes:text name="suffix" value="${suffix}"/></td>
                            </tr>
                            <tr>
                              <td> <stripes:submit name="query" value="Query"/></td>
                            </tr>
                        </table>
                    </stripes:form>
                </td>
                <td style="vertical-align: top;">
                	<p>
                	This form is used to query for patient identifiers using other demographic fields.
                	
                   Currently only 'lastname' is hooked up. Some names that are currently in the IBM database: Murphy, Renly, Smith.
                   </p>
                </td>
            </tr>
        </table>

    </stripes:layout-component>
</stripes:layout-render>