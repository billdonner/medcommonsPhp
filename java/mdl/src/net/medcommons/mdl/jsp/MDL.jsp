<%@ page language="java"%><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/DTD/strict.dtd">
<%@ include file="taglibs.jsp" %>
<html>
    <head>
        <title>MDL - <c:out value="${title}"/></title>
        <link rel="stylesheet"
              type="text/css"
              href="${pageContext.request.contextPath}/mdl.css"/>
         <script type="text/javascript"
        	src="MochiKit.js"></script>
        <script type="text/javascript"
                src="${pageContext.request.contextPath}/mdl.js"></script>
        <style type="text/css">
          #logo  {
            background-image: url('mc_logo.png');
          }
        </style>
        <!-- IE6 logo fix -->
        <!--[if lt IE 7]>
        <style type="text/css">
          #logo  {
            background-image: none;
            filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='mc_logo.png', sizingMethod='scale');
          }
        </style>
        <![endif]-->
    </head>
     <body onload="javascript:initialize();"> 	
 			<div id="applicationDescriptionFrame">
        <img style="float: right;" id="logo"
             width="246"
             height="60"
             src="blank.gif"
             style=""
             src="mc_logo.png"/>
				<h1>HIMSS DEMO Clinic  - HIPAA Records System</h1>
     </div>
     <div id="borderImg">&nbsp;</div>
       <table id="mainTable">
        <tr id="row1">
          <td class="xdsQuery">
            <fieldset class = "xdsQueryContents loginRequired">
                  <legend>
                    XDS Query
                  </legend>
              <div  id="xdsQueryFrame">
                
              </div>
            </fieldset>
          </td>
            <td class="userLoginContent" colspan="2" align="right">
              <fieldset>
                <legend>
                  User Login
                </legend>
              <div  id="loginFrame">
              
              </div>
            </fieldset>
          </td>
        </tr>
        <tr id="row2">
          <td class="currentPatientContent">
            <fieldset class="loginRequired">
                  <legend>
                    Current Patient
                  </legend>
                <div  id="currentPatientFrame">
                   
                </div>
            </fieldset>
          </td>
          <td class="sharingConsentContent" colspan="2" valign="top">
            <fieldset class = "sharingConsentFieldsetContents loginRequired">
                  <legend>
                    Patient Consent
                  </legend>
              <div  id="sharingConsentFrame">
              &nbsp;
                
              </div>
            </fieldset>
          </td>
        </tr>
        <tr>
          <td colspan="3" id="queryResultContents" valign="top"> 
            <fieldset class="loginRequired">
              <legend>
                Available
              </legend>
            
                <div  id="queryResultFrame">
                  Query result frame
              </div>
            </fieldset>
          </td>	
        </tr>
      </table>	
<div id="progressDisplay" class = "progressDisplayContents">
	<div id="progressDisplayFrame">
		Progress bar
	</div>
</div>
</div>
<div class="loginRequired" style="margin-left: 10px;">
  &nbsp;<a class="reference" href="javascript:MochiKit.Logging.logger.debuggingBookmarklet()">Client Log</a>
  <br/>
  <br/>
  <div id="rhioDisplay" class = "progressRhioContents">
    <div id="rhioDisplayFrame">
      
    </div>
  </div>
</div>
</body>
</html>
