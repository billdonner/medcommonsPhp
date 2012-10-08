<%@ include file="taglibs.jsp" %>

<stripes:layout-definition>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html>
        <head>
            <title>MDL - ${title}</title>
            <link rel="stylesheet"
                  type="text/css"
                  href="${pageContext.request.contextPath}/mdl.css"/>
             <script type="text/javascript"
            	src="MochiKit.js"></script>
            <script type="text/javascript"
                    src="${pageContext.request.contextPath}/mdl.js"></script>
           
            <stripes:layout-component name="html-head"/>
        </head>
        <body>
        	
            <div id="contentPanel">
                <stripes:layout-component name="header">
                    <jsp:include page="header.jsp"/>
                </stripes:layout-component>

                <div id="pageContent">
                    <div class="sectionTitle">${title}</div>
                    <stripes:messages/>
                    <stripes:layout-component name="contents"/>
                </div>

                <div id="footer">

                    View Log files
                    <stripes:useActionBean binding="/ViewResource.action" var="bean"/>
                    <select style="width: 350px;" onchange="document.location = this.value;">
                        <c:forEach items="${bean.availableResources}" var="file">
                            <stripes:url value="/mdl/ViewResource.action" var="url">
                                <stripes:param name="resource" value="${file}"/>
                            </stripes:url>
                            <option value="${url}">${file}</option>
                        </c:forEach>
                    </select>
                    | Built on <a href="http://stripes.mc4j.org">Stripes and OHF</a>
                </div>
            </div>
            <div id="errorPanel">
            Error messages here.
            </div>
        </body>
    </html>
</stripes:layout-definition>