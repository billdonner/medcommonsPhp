<%@ page language="java"%>
<%
  response.setHeader("Cache-Control","no-cache"); // HTTP 1.1
  response.setHeader("Pragma","no-cache"); // HTTP 1.0
%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<%@ taglib uri="http://stripes.sourceforge.net/stripes.tld" prefix="s" %>
<%@ page isELIgnored="false" %> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>MedCommons Group Administration</title>
    <link rel="stylesheet" type="text/css" href="groups.css"/>
    <style type="text/css">
      table.groupsTable tr th {
        background-color: #111;
        color: white;
      }
      
      #addForm table input {
        width: 300px;
      }

      #addForm table tr th {
        text-align: right;
      }
    </style>
    <script type="text/javascript">
      function confirmDelete() {
        return confirm("Are you sure you want to delete this group?");
      }
    </script>
  </head>
  <body>

  <div id="head"><h1>MedCommons Group Administration</h1></div>

  <div class="content">
    <s:errors/>

    <s:form id="addForm" name="addForm" action="/Groups.action">
    <fieldset>
      <legend>Add a Group</legend>
      <table>
          <tr>
              <th>Group Name</th>
              <td><s:text name="group.name"/></td>
          </tr>
          <tr>
              <th>Admin Account Id</th>
              <td><s:text name="adminAccountId"/></td>
          </tr>
          <%--
          <tr>
              <th>Contact Email</th>
              <td><s:text name="group.user.email"/></td>
          </tr>
          <tr>
              <th>Contact First Name</th>
              <td><s:text name="group.user.firstName"/></td>
          </tr>
          <tr>
              <th>Contact Last Name</th>
              <td><s:text name="group.user.lastName"/></td>
          </tr>
          --%>
      </table>
      <div style="text-align: right"><s:submit name="add" value="Add"/></div>
      <%-- 
        Removed: default behavior is now to always create the practice.
      <p><s:checkbox name="createPractice"/>Associate this group with a Practice?</p>
      --%>
    </fieldset>
    </s:form>
    <br style="clear: both;"/>
    <br/>

    <p>There are ${fn:length(actionBean.groups)} existing Groups</p>

    <table cellspacing="10" cellpadding="0" class="groupsTable">
      <tr>
        <th>Id</th><th>Name</th><th>Acct Id</th><th>Created</th></tr>
      <c:forEach items="${actionBean.groups}" var="g" varStatus="status">
        <tr>
          <td>${g.id}</td><td><a href="Groups.action?edit&group.id=${g.id}"><c:out value="${g.name}"/></a></td>
          <td>${g.accid}</td>
          <td>${g.createDateTime}</td>
          <td><a href="Groups.action?delete&group.id=${g.id}" onclick="return confirmDelete();">delete</a></td>
        </tr>
      </c:forEach>
    </table>

  </div>

  </body>
</html>
