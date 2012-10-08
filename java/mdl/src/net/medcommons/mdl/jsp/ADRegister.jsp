<%@ include file="taglibs.jsp" %>

<stripes:layout-render name="/standard.jsp" title="Provider Registration">
    <stripes:layout-component name="contents">

        <table style="vertical-align: top;">
            <tr>
                <td style="width: 25%; vertical-align: top;">
                    <!-- Somewhat contrived example of using the errors tag 'action' attribute. -->
                    <stripes:errors action="/ADRegister.action"/>

                    <stripes:form action="/ADRegister.action" focus="">
                        <table>
                            <tr>
                                <td style="font-weight: bold;"><stripes:label for="username"/>:</td>
                            </tr>
                            <tr>
                                <td><stripes:text name="username" value="${user.username}"/></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;"><stripes:label for="password"/>:</td>
                            </tr>
                            <tr>
                                <td><stripes:password name="password"/></td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    <%-- If the security servlet attached a targetUrl, carry that along. --%>
                                    <stripes:hidden name="targetUrl" value="${request.parameterMap['targetUrl']}"/>
                                    <stripes:submit name="login" value="Login"/>
                                </td>
                            </tr>
                        </table>
                    </stripes:form>
                </td>
                <td style="vertical-align: top;">
                    <c:choose>
                        <c:when test="${empty user}">
                            <div class="sectionTitle">Welcome</div>

                            <p>Welcome to the MedCommons Data Liberator. If you don't have an account on the Affinity Domain please
                            <stripes:link href="/ADRegister.jsp">register</stripes:link>
                            in order to log in.</p>

                            <div class="sectionTitle">Intuitive development</div>

                        

                            <p>
                            Need some text describing application.
                            </p>
                        </c:when>

                        <c:otherwise>
                            <p>You are already logged in as '${user.username}'.  Logging in again will cause
                            you to  be logged out, and then re-logged in with the username and password
                            supplied.</p>
                        </c:otherwise>
                    </c:choose>
                </td>
            </tr>
        </table>

    </stripes:layout-component>
</stripes:layout-render>