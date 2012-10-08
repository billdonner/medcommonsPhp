<%@ include file="taglibs.jsp" %>

<stripes:layout-render name="/standard.jsp" title="MESA tests">
    <stripes:layout-component name="contents">

        <table style="vertical-align: top;">
        	<tr>
        	<th> PDQ Document Consumer </th><th>XDS Document Consumer </th> <th>Secure Node</th> <th> Content Consumer </th>
        	</tr>
            <tr>
	            <td>
		             <stripes:link href="/MesaPDC11311.action" event="runTest">
			                                Run Test PDC 11311
			         </stripes:link>
	            </td>
	            <td>
		             <stripes:link href="/MesaXDS11734.action" event="runTest">
			                                Run Test XDS 11734
			         </stripes:link>
	            </td>
	             <td>
		             <stripes:link href="/MesaSN11101.action" event="runTest">
			                                Run Test SN 11101
			         </stripes:link>
	            </td>
	             <td>
	                  Run Test CC 14123 
		             <stripes:link href="/CdaDisplay.action" event="display">
		             capmed 
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/14123/capmed_14101.xml"/>
			         </stripes:link>
			         <stripes:link href="/CdaDisplay.action" event="display">
		             adt 
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/14123/adt_sms_14101.xml"/>
			         </stripes:link>
			          <stripes:link href="/CdaDisplay.action" event="display">
		             ibm
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/14123/info_src_ibm_14101.xml"/>
			         </stripes:link>
			          <stripes:link href="/CdaDisplay.action" event="display">
		             cgi
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/14123/CGI_CONTENT_CONSUMER_14101.xml"/>
			         </stripes:link>
	            </td>
            </tr>
             <tr>
	            <td>
		             <stripes:link href="/MesaPDC11312.action" event="runTest">
			                                Run Test PDC 11312
			         </stripes:link>
	            </td>
	             <td>
		             <stripes:link href="/MesaXDS11739.action" event="runTest">
			                                Run Test XDS 11739
			         </stripes:link>
	            </td>
	            <td>
		             <stripes:link href="/MesaSN11103.action" event="runTest">
			                                Run Test SN 11103
			         </stripes:link>
	            </td>
	            <td>
	            Run Test CC 40805 
	             <stripes:link href="/CdaDisplay.action" event="display">
		             ad-discharge.xml
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/40805/ad-discharge.xml"/>
			    </stripes:link>     
	            </td>
            </tr>
              <tr>
	            <td>
		             <stripes:link href="/MesaPDC11315.action" event="runTest">
			                                Run Test PDC 11315
			         </stripes:link>
	            </td>
	            <td>
		           
			                                Run Test XDS 11741
			      
	            </td>
	            <td>
		             <stripes:link href="/MesaSN11104.action" event="runTest">
			                                Run Test SN 11104
			         </stripes:link>
	            </td>
	            <td>
	                  Run Test CC 40811 
	             <stripes:link href="/CdaDisplay.action" event="display">
		             cardref-referral.xml
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/40811/cardref-referral.xml"/>
			    </stripes:link>  
			         
			         
	            </td>
	            
            </tr>
             <tr>
	            <td>
		             <stripes:link href="/MesaPDC11320.action" event="runTest">
			                                Run Test PDC 11320
			         </stripes:link>
	            </td>
	            
	            <td>
	              <stripes:link href="/MesaXDS11954.action" event="runTest">
			                                Run Test XDS 11954
			         </stripes:link>
	            
	            </td>
	            <td>
		             <stripes:link href="/MesaSN11121.action" event="runTest">
			                                Run Test SN 11121
			         </stripes:link>
	            </td>
	            <td>
	            
	             Run Test CC 40815 
	             <stripes:link href="/CdaDisplay.action" event="display">
		             cardref-discharge.xml
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/40815/cardref-discharge.xml"/>
			    </stripes:link>  
	            </td>
            </tr>
              <tr>
	            <td>
		             <stripes:link href="/MesaPDC11325.action" event="runTest">
			                                Run Test PDC 11325
			         </stripes:link>
	            </td>
	             <td>
	            </td>
	            <td>
		             <stripes:link href="/MesaSN11182.action" event="runTest">
			                                Run Test SN 11182
			         </stripes:link>
	            </td>
	             <td>
	              Run Test CC 40820
		             <stripes:link href="/CdaDisplay.action" event="display">
		             allscripts
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/40820/MSReferral_ehr_allscripts.xml"/>
			         </stripes:link>
			         <stripes:link href="/CdaDisplay.action" event="display">
		             bell
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/40820/MSReferral_BELL_CONTENT_CONSUMER.xml"/>
			         </stripes:link>
			         <stripes:link href="/CdaDisplay.action" event="display">
		             mysis
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/40820/MSReferral_EHR_MISYS.xml"/>
			         </stripes:link>
			         .xml
			     </td>
            </tr>
              <tr>
	            <td>
		             <stripes:link href="/MesaPDC11335.action" event="runTest">
			                                Run Test PDC 11335
			         </stripes:link>
	            </td>
	            <td>
	            </td>
	            <td>
		             <stripes:link href="/MesaSN11196.action" event="runTest">
			                                Run Test SN 11196
			         </stripes:link>
	            </td>
	           
	             <td>
	             Run Test CC 40821
		             <stripes:link href="/CdaDisplay.action" event="display">
		             	GE
			          	<stripes:param name="cdaFilename" value="/mesa_rendering_tests/40821/MSDischarge_EHR_GEHEALTHCARE_ENTERPRISE.xml"/>
			         </stripes:link>
			         
			         <stripes:link href="/CdaDisplay.action" event="display">
		             	medquist
			          	<stripes:param name="cdaFilename" value="/mesa_rendering_tests/40821/MSDischarge_XDS_REP_MEDQUIST.xml"/>
			         </stripes:link>
	            </td>
	           
	           
            </tr>
            <tr>
	            <td>
		             <stripes:link href="/MesaPDC11350.action" event="runTest">
			                                Run Test PDC 11350
			         </stripes:link>
	            </td>
	            <td>
	            </td>
	            
	            <td>
	             Run Test CC 14121
		             <stripes:link href="/CdaDisplay.action" event="display">
		             example1
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/14121/example1.xml"/>
			         </stripes:link>
			         <stripes:link href="/CdaDisplay.action" event="display">
		             example2
			          <stripes:param name="cdaFilename" value="/mesa_rendering_tests/14121/example2.xml"/>
			         </stripes:link>
	            </td>
            </tr>
            
        </table>

    </stripes:layout-component>
</stripes:layout-render>