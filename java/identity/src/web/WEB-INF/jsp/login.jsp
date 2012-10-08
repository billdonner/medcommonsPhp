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

td {
    vertical-align: top;
    padding: 10px;
    border: 1px;
    border-style: solid;
    border-color: #fff #fff #ccc #ccc;
}

td p, td a {
    font-size: x-small;
    padding-bottom: 0px;
    margin-bottom: 0px;
}

#forgotten {
    padding-top: 0px;
    margin-top: 0px;
}

.label {
    font-size: x-small;
}

.error {
	background-color: #c00;
	color: #fff;
}
 
h4 {
    background-color: #ccc;
}

// --></style>
    </head>
    <body <c:if test='${not empty cookieDomain}'>onload="document.domain='${cookieDomain}';"</c:if> >
        <div id="container">
            <div id="intro">
            </div>
            <div id="supportingText">
	        <h3>
                    <span>Sign In</span>
                </h3>
<c:if test="${not empty requestScope.sourceName}">
		<p>
		The next time you access MedCommons from <c:out value="${requestScope.sourceName}" />,
		you'll bypass this page.
                </p>
</c:if>

		<div id='login'>
		  <table><tr><td>
		    <form method='post' action='login'>
		        <h4>Existing Account</h4>
		  <a class='label' href='register'>Create a New Account</a>
			<p>Your MCID or E-Mail Address:</p>
			<input name='mcid' size='19' value='<c:out value="${requestScope.mcid}" />' />
<c:if test='${not empty requestScope.loginError}'>
			<p class='error'>
			 <c:out value='${requestScope.loginError}' />
			</p>
</c:if>
			<p>Your Password:</p>
			<input name='password' type='password' />
			<p id='forgotten'>
			    <a href='forgotten'>Forgotten Password?</a>
			</p>
			<input type='hidden' name='userId' value='<c:out value="${requestScope.userId}" />' />
			<input type='hidden' name='sourceId' value='<c:out value="${requestScope.sourceId}" />' />
			<input type='hidden' name='next' value='<c:out value="${requestScope.next}" />' />
			<input type='submit' value='Sign On>>' />

<c:if test='${not empty requestScope.next}'>
	<input type='hidden' name='next' value='<c:out value="${requestScope.next}" />' />
</c:if>

		    </form>
		    </td></tr></table>
		</div>

            </div>
        </div>
        <div id="footer">
            <a href="http://validator.w3.org/check/referer" title="Check the validity of this
                site&#8217;s XHTML">xhtml</a> &nbsp; <a
                href="http://jigsaw.w3.org/css-validator/check/referer" title="Check the validity of
                this site&#8217;s CSS">css</a> &nbsp; <a
                href="http://creativecommons.org/licenses/by-nc-sa/1.0/" title="View details of the
                license of this site, courtesy of Creative Commons.">cc</a> &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=sec508&amp;test="
                title="Check the accessibility of this site according to U.S. Section 508">508</a>
            &nbsp; <a
                href="http://bobby.watchfire.com/bobby/bobbyServlet?URL=http%3A%2F%2Fwww.mezzoblue.com%2Fzengarden%2F&amp;output=Submit&amp;gl=wcag1-aaa&amp;test="
                title="Check the accessibility of this site according to Web Content Accessibility
                Guidelines 1.0">aaa</a>
            <p class="p1">&#169; MedCommons 2006</p>
        </div>
    </body>
</html>
