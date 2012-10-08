<%@ page language="java"%>
<%
  response.setHeader("Cache-Control","no-cache"); // HTTP 1.1
  response.setHeader("Pragma","no-cache"); // HTTP 1.0
%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<%@ taglib uri="http://stripes.sourceforge.net/stripes.tld" prefix="s" %>
<%@ taglib uri="http://www.medcommons.net/medcommons-tld-1.0" prefix="mc" %>
<%@ page isELIgnored="false" %> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="groups.css"/>
    <title>MedCommons Edit Group <c:out value="${actionBean.group.name}"/></title>
    <style type="text/css">
      table.groupsTable tr th {
        background-color: #111;
        color: white;
        padding: 2px 4px;
      }

      #editForm table input {
        width: 300px;
      }
    </style>
    <script type="text/javascript" src="MochiKit.js"></script>
    <script type="text/javascript" src="utils.js"></script>
    <script type="text/javascript" src="groupEdit.js"></script>
  </head>
  <body>

    <div id="head"><h1>Details for Group <c:out value="${actionBean.group.name}"/> &nbsp;&nbsp;&nbsp;<a style="font-size:10px;" href="Groups.action?list">back to groups</a></h1></div>

  <div class="content">
    <s:errors/>

    <%-- float to get auto width --%>
    <fieldset style="float: left;">
      <legend>Group Details</legend>
      <s:form id="editForm" name="editForm" action="/Groups.action">
        <s:hidden name="group.id"/>
        <table>
          <tr><th>Name</th><td><s:text name="group.name"/></td></tr>
        </table>
        <br/>
        <div style="text-align: right;"><s:submit name="saveGroupDetails" value="Save"/></div>

      </s:form>
    </fieldset>

    <br style="clear: both;"/>
    <br/>

    <%-- float to get auto width --%>
    <fieldset style="float: left;">
      <legend>Add User to Group</legend>
      <s:form action="/Groups.action">
        <s:hidden name="group.id"/>
        <b>User Account ID:</b> <s:text name="userId"/> <s:submit name="addUser" value="Add User"/>                    
      </s:form>
    </fieldset>
    <br style="clear: both;"/>
    <br/>

    <p>There are ${fn:length(actionBean.group.users)} existing users in this group.</p>

    <table cellspacing="10" cellpadding="0" class="groupsTable">
      <tr><th>Id</th><th>Name</th><th>Admin?</th><th>&nbsp;</th></tr>
      <c:forEach items="${actionBean.group.users}" var="u" varStatus="status">
        <tr>
          <td>${u.mcid}</td>
          <td><c:out value="${u.firstName} ${u.lastName}"/></a></td>
          <td style="text-align: center;">
            <input type="checkbox" name="admin" value="${u.mcid}" 
              <c:if test='${mc:contains(actionBean.group.admins, u)}'>checked="true"</c:if>
              onclick="saveAdminState(${u.mcid},${group.id},this.checked);"/>
          </td>
          <td><a href="Groups.action?removeUser&userId=${u.mcid}&group.id=${actionBean.group.id}">remove</a></td>
        </tr>
      </c:forEach>
    </table>
    </div>
    <div id="msg">
      &nbsp;
    </div>
  </body>
</html>
