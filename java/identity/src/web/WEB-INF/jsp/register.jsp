<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ page isELIgnored="false" %>
<?xml version='1.0' encoding='US-ASCII' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=US-ASCII" />
    <meta name="author" content="MedCommons"/>
    <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
				   web standards"/>
    <meta name="description" content="MedCommons Home Page"/>
    <meta name="robots" content="all"/>
    <title>MedCommons - Interoperable and Private Personal Health Records</title>
    <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
    <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
    <style type="text/css" media="all"><!--
@import "main.css";

.error {
	background-color: #c00;
	color: #fff;
}

label, .label {
	font-size: x-small;
	padding-bottom: 0px;
	margin-bottom: 0px;
}

td {
	vertical-align: top;
}

.comment {
	font-size: x-small;
	padding-top: 0px;
	margin-top: 0px;
}

#addressFields {
  display: none;
  width: 500px;
}

#addressFieldLink:link,
#addressFieldLink:visited,
#addressFieldLink:hover,
#addressFieldLink:active {
  color: blue;
  font-size: 12px;
}

// --></style>
  </head>
  <body <c:if test='${not empty cookieDomain}'>onload="document.domain='${cookieDomain}';"</c:if> >
  <div id="container">
  <div id="supportingText">
    <h3> <span>Register a New Account</span></h3>
    <p>
      This creates a MedCommons ID (MCID) for your exclusive use.
    </p>
    <c:if test="${not empty requestScope.sourceName}">
	  <p>
	    The next time you access MedCommons from <c:out value="${requestScope.sourceName}" />,
	    you'll bypass this page.
    </p>
    </c:if>
    <p>
      The information collected here is used for MedCommons internal use only.
      MedCommons will not disclose any personal information about you, including
      your email address, to any third party without your consent.
    </p>
  </div>
  <form method='post' action='register'>
	<table border='0'>
	  <tr>
	    <td>
       
	      <fieldset>
		<legend>Account</legend>

		<table border='0'>
		  <tr>
		    <td colspan='2'>
		      <label>Email:<br />
			<input name='email' size='36' value='<c:out value="${requestScope.email}" />'/>
		      </label>

		      <c:if test='${not empty requestScope.emailError}'>
			<p class='error'>
			  <c:out value="${requestScope.emailError}" />
			</p>
		      </c:if>
		    </td>
		  </tr>
		  <tr>
		    <td>
		      <label>First name:<br />
			<input name='first_name' size='16' value='<c:out value="${requestScope.first_name}" />' />
		      </label>
		    </td>
		    <td>
		      <label>Last name:<br />
			<input name='last_name' size='16' value='<c:out value="${requestScope.last_name}" />' />
		      </label>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='2'>
		      <label>Password:<br />
			<input name='password1' type='password' />
		      </label>

		      <c:if test="${not empty requestScope.pw1Error}">
			<p class='error'><c:out value="${requestScope.pw1Error}" /></p>
		      </c:if>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='2'>
		      <label>To verify your password, type in the same password again:<br />
			<input name='password2' type='password' />
		      </label>

		      <c:if test="${not empty requestScope.pw2Error}">
			<p class='error'><c:out value="${requestScope.pw2Error}" /></p>
		      </c:if>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='2'>
		      <p class='label'>Mobile Phone/SMS:</p>
		      <select name='smsprovider'>
			<option selected='selected'
				value='other'>Other...</option>
			<option value='@message.alltel.com'>Alltel</option>
			<option value='@mobile.mycingular.com'>Cingular</option>
			<option value='@messaging.nextel.com'>Nextel</option>
			<option value='@messaging.sprintpcs.com'>Sprint</option>
			<option value='@tms.suncom.com'>SunCom</option>
			<option value='@tmomail.net'>T-Mobile</option>
			<option value='@voicestream.net'>VoiceStream</option>
			<option value='@vtext.com'>Verizon</option>
		      </select>

		      <input name='smsnumber' size='16'
			     value='<c:out value="${requestScope.smsnumber}" />' /><br />
		      <input name='smslogin' type='checkbox' value='1' />
		      Require mobile phone for log-in.

		      <c:if test="${not empty requestScope.smsError}">
			<p class='error'><c:out value="${requestScope.smsError}" /></p>
		      </c:if>
		    </td>
		  </tr>
		</table>
    </fieldset>
    </td>
    </tr>
    </table>

    <br/>
    <a id="addressFieldLink" href="#" onclick="document.getElementById('addressFields').style.display='block'; if(window.parent && window.parent.sizecontent) window.parent.eval('sizecontent()');">Show Address Fields</a>
    <br/>
    <div id="addressFields">
    <fieldset>
		<legend>Address</legend>
		<table border='0'>
		  <tr>
		    <td colspan='3'>
		      <select name='comment'>
			<option>None</option>
			<option>Home</option>
			<option>Office</option>
		      </select>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='3'>
		      <p class='label'>Address:</p>
		      <input name='address1' size='24' value='<c:out value="${requestScope.address1}" />'/><br />
		      <input name='address2' size='24' value='<c:out value="${requestScope.address2}" />'/>
		    </td>
		  </tr>

		  <tr>
		    <td>
		      <label>City:<br />
			<input name='city' size='16' value='<c:out value="${requestScope.city}" />'/>
		      </label>
		    </td>
		    <td>
		      <label>State:<br />
			<input name='state' size='2' value='<c:out value="${requestScope.state}" />'/>
		      </label>
		    </td>
		    <td>
		      <label>Postcode:<br />
			<input name='postcode' size='10' value='<c:out value="${requestScope.postcode}" />'/>
		      </label>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='3'>
		      <label>Country:<br />
			<input name='country' value='<c:out value="${requestScope.country}" />'/>
		      </label>
		    </td>
		  </tr>
		  <tr>
		    <td colspan='3'>
		      <label>Phone number at this address:<br />
			<input name='telephone' value='<c:out value="${requestScope.telephone}" />'/>
		      </label>
		    </td>
		  </tr>
		</table>
    </fieldset>
  </div> <%-- /addressFields --%>

	<input type='hidden' name='userId' value='<c:out value="${requestScope.userId}" />' />
	<input type='hidden' name='sourceId' value='<c:out value="${requestScope.sourceId}" />' />
  <br/>
	<input type='submit' value='Register' />
      </form>

      <c:if test="${not empty requestScope.sqlError}">
	<p class='hidden'>
	  <c:out value="${requestScope.sqlError}" />
	</p>
      </c:if>

      <%-- ssadedin:  footer is not needed any longer because this page is displayed
           inside an iframe of another page which has the footer.  It is left here
           for convenience in case there is a need to display this page independently.
       --%>
      <div style="display: none;" id="footer">
        <a href="http://validator.w3.org/check/referer"
	   title="Check the validity of this site&#8217;s XHTML">xhtml</a> &nbsp;
	<a href="http://jigsaw.w3.org/css-validator/check/referer"
	   title="Check the validity of this site&#8217;s CSS">css</a> &nbsp;
	<a href="http://creativecommons.org/licenses/by-nc-sa/1.0/"
	   title="View details of the license of this site, courtesy of Creative Commons.">cc</a> &nbsp;
	<a href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
           title="Check the accessibility of this site according to U.S. Section 508">508</a> &nbsp;
	<a href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
	   title="Check the accessibility of this site according to Web Content Accessibility Guidelines 1.0">aaa</a>
        <p class="p1">&copy; MedCommons 2006</p>
      </div>
    </div>
  </body>
</html>
