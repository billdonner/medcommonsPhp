<?php
$url = $_GET['url']; $time = strftime('%D');
$row = 0; $num=0;
$handle = fopen($url, "r");
if ($handle===false) die("Cant open url $url");
$top=<<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Progressive Enhancement</title>

<style type="text/css">
/*margin and padding on body element
  can introduce errors in determining
  element position and are not recommended;
  we turn them off as a foundation for YUI
  CSS treatments. */
body {
	margin:0;
	padding:0;
}
</style>

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/tabview/assets/skins/sam/tabview.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/datatable/assets/skins/sam/datatable.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/element/element-beta-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/tabview/tabview-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/datasource/datasource-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.6.0/build/datatable/datatable-min.js"></script>

<!--there is no custom header content for this example-->

</head>

<body class=" yui-skin-sam">


<h1>CSV YUI TABLE DISPLAY</h1>

<!--BEGIN SOURCE CODE FOR EXAMPLE =============================== -->
 	<div id="demo" class="yui-navset"> 
   <ul class="yui-nav"> 
       <li><a href="#tab1"><em>Tab One Label</em></a></li> 
        <li class="selected"><a href="#tab2"><em>Tab Two Label</em></a></li> 
        
        <li ><a href="#tab3"><em>Tab Three Label</em></a></li> 		
           </ul>  
           <div class="yui-content"> 
      <div id="tab1">
<div id="markup">
    <table id="accounts">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Attrs</th>
            </tr>
        </thead>
        <tbody>
            <tr>

                <td>1/23/1999</td>
                <td>29e8548592d8c82</td>
                <td>12</td>
 
            </tr>
            <tr>
                <td>5/19/1999</td>

                <td>83849</td>
                <td>8</td>
            </tr>
            <tr>
                <td>8/9/1999</td>
                <td>11348</td>

                <td>1</td>
            </tr>
            <tr>
                <td>1/23/2000</td>
                <td>29e8548592d8c82</td>
                <td>10</td>

            </tr>
            <tr>
                <td>4/28/2000</td>
                <td>37892857482836437378273</td>
                <td>123</td>

            </tr>
            <tr>
                <td>1/23/2001</td>
                <td>83849</td>
                <td>5</td>
            </tr>

            <tr>
                <td>9/30/2001</td>
                <td>224747</td>
                <td>14</td>
            </tr>
        </tbody>

    </table>
</div>
</div> 
<div id="tab2">
<div id="markup2">
    <table id="accounts2">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Attrs</th>
            </tr>
        </thead>
        <tbody>
            <tr>

                <td>aaaaaaa</td>
                <td>29e8548592d8c82</td>
                <td>12</td>
 
            </tr>
            <tr>
                <td>bbbbbbbb</td>

                <td>83849</td>
                <td>8</td>
            </tr>
            <tr>
                <td>8/9/1999</td>
                <td>11348</td>

                <td>1</td>
            </tr>
            <tr>
                <td>1/23/2000</td>
                <td>29e8548592d8c82</td>
                <td>10</td>

            </tr>
            <tr>
                <td>4/28/2000</td>
                <td>37892857482836437378273</td>
                <td>123</td>

            </tr>
            <tr>
                <td>1/23/2001</td>
                <td>83849</td>
                <td>5</td>
            </tr>

            <tr>
                <td>9/30/2001</td>
                <td>224747</td>
                <td>14</td>
            </tr>
        </tbody>

    </table>
</div>
</div> 
<div id='tab3'>
Third tab goes here
</div>
</div> 

XXX;

echo $top;


$x = array (
array ('key'=>'name','label'=>'Name','sortable'=>'true'),
array ('key'=>'type','label'=>'Type','sortable'=>'true'),
array ('key'=>'attrs','label'=>'Attrs','sortable'=>'true')
);

$y = array ( 'datasource'=>"accounts",'table'=>'markup');



$foot=<<<XXX



<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
YAHOO.example.EnhanceFromMarkup = function() {
var myColumnDefs = [
{key:"name",label:"Name",formatter:YAHOO.widget.DataTable.formatDate,sortable:true},
{key:"type",label:"Type", sortable:true},
{key:"attrs",label:"Attrs",formatter:YAHOO.widget.DataTable.formatNumber,sortable:true}
];

var parseNumberFromCurrency = function(sString) {
// Remove dollar sign and make it a float
return parseFloat(sString.substring(1));
        };

        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("accounts"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
        fields: [{key:"name"},
        {key:"type"},
        {key:"attrs"} // point to a custom parser
        ]
        };

        var myDataTable = new YAHOO.widget.DataTable("markup", myColumnDefs, myDataSource,
        {caption:"Example: CSV $url rendered via YUI",
        sortedBy:{key:"name"}}
        );

        return {
        oDS: myDataSource,
        oDT: myDataTable
        };
    }();
});
</script>

<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
YAHOO.example.EnhanceFromMarkup = function() {
var myColumnDefs = [
{key:"name",label:"Name",formatter:YAHOO.widget.DataTable.formatDate,sortable:true},
{key:"type",label:"Type", sortable:true},
{key:"attrs",label:"Attrs",formatter:YAHOO.widget.DataTable.formatNumber,sortable:true}
];

var parseNumberFromCurrency = function(sString) {
// Remove dollar sign and make it a float
return parseFloat(sString.substring(1));
        };

        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("accounts2"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
        fields: [{key:"name"},
        {key:"type"},
        {key:"attrs"} // point to a custom parser
        ]
        };

        var myDataTable = new YAHOO.widget.DataTable("markup2", myColumnDefs, myDataSource,
        {caption:"Example: CSV $url rendered via YUI",
                sortedBy:{key:"name"}}
        );
        
        return {
            oDS: myDataSource,
            oDT: myDataTable
        };
    }();
});
</script>

<script type="text/javascript"> 
   var tabView = new YAHOO.widget.TabView('demo'); 
</script> 

<!--END SOURCE CODE FOR EXAMPLE =============================== -->


<!--MyBlogLog instrumentation-->
<script type="text/javascript" src="http://track2.mybloglog.com/js/jsserv.php?mblID=2007020704011645"></script>

</body>
</html>

<script type="text/javascript" src="http://us.js2.yimg.com/us.js.yimg.com/lib/rt/rto1_78.js"></script><script>var rt_page="792404008:FRTMA"; var rt_ip="72.89.255.17"; if ("function" == typeof(rt_AddVar) ){ rt_AddVar("ys", escape("F14C9345"));}</script><noscript><img src="http://rtb.pclick.yahoo.com/images/nojs.gif?p=792404008:FRTMA"></noscript><script language=javascript>
if(window.yzq_d==null)window.yzq_d=new Object();
window.yzq_d['ztTzZkLEYrM-']='&U=13esmj1e8%2fN%3dztTzZkLEYrM-%2fC%3d289534.9603437.10326224.9298098%2fD%3dFOOT%2fB%3d4123617%2fV%3d1';
</script><noscript><img width=1 height=1 alt="" src="http://us.bc.yahoo.com/b?P=jKiLskWTTNJXr_8pSKtM7wR2SFn_EUkKLNEABoBX&T=142mb4513%2fX%3d1225403601%2fE%3d792404008%2fR%3ddev_net%2fK%3d5%2fV%3d2.1%2fW%3dH%2fY%3dYAHOO%2fF%3d1646153475%2fQ%3d-1%2fS%3d1%2fJ%3dF14C9345&U=13esmj1e8%2fN%3dztTzZkLEYrM-%2fC%3d289534.9603437.10326224.9298098%2fD%3dFOOT%2fB%3d4123617%2fV%3d1"></noscript>
<!-- VER-548 -->
<script language=javascript>
if(window.yzq_p==null)document.write("<scr"+"ipt language=javascript src=http://l.yimg.com/us.js.yimg.com/lib/bc/bc_2.0.4.js></scr"+"ipt>");
</script><script language=javascript>
if(window.yzq_p)yzq_p('P=jKiLskWTTNJXr_8pSKtM7wR2SFn_EUkKLNEABoBX&T=13tqoqrct%2fX%3d1225403601%2fE%3d792404008%2fR%3ddev_net%2fK%3d5%2fV%3d1.1%2fW%3dJ%2fY%3dYAHOO%2fF%3d3173136303%2fS%3d1%2fJ%3dF14C9345');
if(window.yzq_s)yzq_s();
</script><noscript><img width=1 height=1 alt="" src="http://us.bc.yahoo.com/b?P=jKiLskWTTNJXr_8pSKtM7wR2SFn_EUkKLNEABoBX&T=142h28obv%2fX%3d1225403601%2fE%3d792404008%2fR%3ddev_net%2fK%3d5%2fV%3d3.1%2fW%3dJ%2fY%3dYAHOO%2fF%3d1257063833%2fQ%3d-1%2fS%3d1%2fJ%3dF14C9345"></noscript>
<!-- p2.ydn.re1.yahoo.com compressed/chunked Thu Oct 30 14:53:21 PDT 2008 -->

XXX;

        echo $foot;

        fclose($handle);
        ?>