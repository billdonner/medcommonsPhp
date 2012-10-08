<%@ include file="taglibs.jsp" %>

<div id="imageHeader">
    <table style="padding: 5px; margin: 0px; width: 100%;">
        <tr>
        	<td> MDL </td>
            <td id="loginInfo">
                <c:if test="${not empty user}">
                    Welcome: ${user.firstName} ${user.lastName}
                    |
                    <stripes:link href="/ADLogout.action">Logout</stripes:link>
                </c:if>
            </td>
            <td id="medcommonsMain">
               |
              <stripes:link href="http://www.medcommons.net">MedCommons</stripes:link>
            </td>
        </tr>
    </table>
 </div>