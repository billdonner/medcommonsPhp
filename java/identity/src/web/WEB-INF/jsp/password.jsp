<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ page isELIgnored="false" %>
<?xml version='1.0' encoding='US-ASCII' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>

<html xmlns='http://www.w3.org/1999/xhtml'>
 <head>
  <meta http-equiv='Content-Type' content='text/html; charset=US-ASCII' />
  <link rel='StyleSheet' type='text/css' href='main.css' />
  <title></title>
 </head>
 <body <c:if test='${not empty cookieDomain}'>onload="document.domain='${cookieDomain}';"</c:if> >
  <h1>Change your MedCommons Password</h1>

  <form method='post' action='password'>
   <table>
    <tbody>

     <tr>
      <th>MCID or email:</th>
      <td><input name='mcid' size='19' value='<c:out value="${requestScope.mcid}" />'/></td>

      <c:if test='${not empty requestScope.mcidError}'>
       <td class='error' rowspan='2'>
        <c:out value="${requestScope.mcidError}" />
       </td>
      </c:if>
      <c:if test='${empty requestScope.mcidError}'>
       <td class='comment'><em>example: 0000-2222-4444-6666</em><br />
                           <em>example: janedoe@site.com</em></td>
      </c:if>
     </tr>

     <tr>
      <th>Old Password:</th>
      <td><input name='password0' type='password'/></td>
     </tr>

     <tr>
      <th>New Password:</th>
      <td><input name='password1' type='password'/></td>
      <c:if test="${not empty requestScope.pw1Error}">
       <td class='error'><c:out value="${requestScope.pw1Error}" /></td>
      </c:if>
      <c:if test="${empty requestScope.pw1Error}">
       <td class='comment'><em>A good password is easy for you to remember,
               yet hard for someone else to guess.
               If you forget this password,
                    we can email you a new one.</em></td>
      </c:if>
     </tr>

     <tr>
      <th>New Password (again):</th>
      <td><input name='password2' type='password'/></td>
      <c:if test="${not empty requestScope.pw2Error}">
       <td class='error'><c:out value="${requestScope.pw2Error}" /></td>
      </c:if>
      <c:if test="${empty requestScope.pw2Error}">
       <td class='comment'><em>To verify your password, type in the same password again</em></td>
      </c:if>

     </tr>

     <tr>
      <th></th>
      <td><input type='image' src='gobutton.gif' alt='Go' /></td>
     </tr>

    </tbody>
   </table>


   <input type='hidden' name='link'
          value="<c:out value='${requestScope.link}' />" />
  </form>

  <c:if test="${not empty requestScope.sqlError}">
   <p class='hidden'>
    <c:out value="${requestScope.sqlError}" />
   </p>
  </c:if>

 </body>
</html>
