<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ page isELIgnored="false" %>
<?xml version='1.0' encoding='US-ASCII' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>

<html xmlns='http://www.w3.org/1999/xhtml'>
 <head>
  <meta http-equiv='Content-Type' content='text/html; charset=US-ASCII' />
  <title>Enter SMS Code To Continue Logging In</title>
  <style type="text/css" media="all"><!--
@import "main.css";

label, .label {
    font-size: x-small;
    padding-bottom: 0px;
    margin-bottom: 0px;
}
// --></style>

 </head>
 <body <c:if test='${not empty cookieDomain}'>onload="document.domain='${cookieDomain}';"</c:if> >
  <h1>Enter SMS Code To Continue Logging In</h1>

  <p>Wait for your 6-digit code to arrive on your mobile device</p>

  <form action='sms' method='post'>
    <label>mcid:<br />
      <input type='text' name='mcid' readonly='readonly'
	     value='<c:out value="${requestScope.mcid}" />' /><br />
    </label>

    <label>email:<br />
      <input type='text' name='email' readonly='readonly'
	     value='<c:out value="${requestScope.email}" />' /><br />
    </label>

    <label>6 digit code:<br />
      <input type='text' name='code' /><br />
    </label>

    <input type='submit' value="Log In" />
  </form>
 </body>
</html>
