<?php

require_once "is.inc.php";
require_once "stdump.inc.php";
function onetab ($viewer,$orientation, $tablename,$label,$markup, $sortkey,$extra)
{ 
	if ($orientation=='vertical') $code = onetab_vertical ($viewer, $tablename,$label,$markup, $sortkey,$extra);
	else $code = onetab_horizontal($viewer, $tablename,$label,$markup, $sortkey,$extra);
	return $code;
}
function onetab_horizontal ($viewer, $tablename,$tablabel,$markup, $sortkey,$extra)
{ 
global $labels_,$names_,$viewer_,$simtrakid_;
$jstop =<<<XXX
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
YAHOO.example.EnhanceFromMarkup = function() {
var myColumnDefs = [

XXX;

$top = <<<XXX

<div id="tab_$tablename">
  <div id="$markup">
    <table id="table_$tablename">
      <thead>
        <tr>
XXX;
/* generate headers by reading the metadata table for the basic

 */
$first = true;
foreach ($names_[$tablename] as $name) if (isset($viewer_[$viewer][$tablename][$name])) 
{
	$top.="<th title='{$labels_[$tablename][$name]}' >{$names_[$tablename][$name]}</th>";
	if (!$first) $jstop.=','; $first=false;
	$jstop .="{".'key:"'.$names_[$tablename][$name].'",label:"'.$labels_[$tablename][$name].'", sortable:true}';
}

$mid = <<<XXX
	    </tr>
      </thead>
      <tbody>
XXX;
/* jsmid is different */
$jsmid = <<<XXX
];
        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("table_$tablename"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
        fields: [
XXX;
$first=true;
foreach ($names_[$tablename] as $name) if (isset($viewer_[$viewer][$tablename][$name])) 
{	if (!$first) $jsmid.=',';
    $first=false;
	$jsmid .="{".'key:"'.$names_[$tablename][$name].'"}'; 
}
/* generate body as rows by reading actual data tables

*/
$q = "Select * from $tablename where personid='$simtrakid_' ";
//echo $q;


$result = dosql ($q);
while ($r=mysql_fetch_array($result))
{  $mid .= '
	<tr>
';	
	foreach ($names_[$tablename] as $name) 	if (isset($viewer_[$viewer][$tablename][$name])) $mid .= '<td>'.$r[$name].'</td>';

$mid .='
	</tr>
	';
}

$bottom = <<<XXX
     </tbody>
    </table>
  </div>
</div> 
XXX;

$jsbottom = <<<XXX
        ] };

        var myDataTable = new YAHOO.widget.DataTable("$markup", myColumnDefs, myDataSource,
        {caption:"horizontal dump of $tablename for simtrak id $simtrakid_ filtered by $viewer sorted by $sortkey", sortedBy:{key:"$sortkey"}}
        );
       
        return {oDS: myDataSource, oDT: myDataTable};
    }();
});
</script>
XXX;
   $nav=" <li $extra ><a href='#tab_$tablename'><em>$tablabel</em></a></li> ";
return array($top.$mid.$bottom,$jstop.$jsmid.$jsbottom,$nav);
}

function onetab_vertical ($viewer, $tablename,$tablabel,$markup, $sortkey,$extra)
{ 
	
/*  read all the data to find out all of our columns

*/
global $labels_,$names_,$viewer_,$simtrakid_,$playerind_;
	
$vals = array();

$q = "Select * from $tablename where personid='$simtrakid_' ";
$result = dosql ($q);
while ($r=mysql_fetch_array($result))  
$vals []=$r;
mysql_free_result($result);


$jstop =<<<XXX
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
YAHOO.example.EnhanceFromMarkup = function() {
var myColumnDefs = [

XXX;

$top = <<<XXX
<div id="tab_$tablename">
 <a href="?view=viewB&id=$playerind_" target="_new">more detail</a> 
  <div id="$markup">
    <table id="table_$tablename">
      <thead>
        <tr>
XXX;
/* generate headers by reading the metadata table for the basic

 */
$first = true;

	$top.="<th title='Data Element' >DATAELEMENT</th>";
	if (!$first) $jstop.=','; $first=false;
	$jstop .="{".'key:"'.'DataElement'.'",label:"'.'Data Element'.'", sortable:true}';
	$jsmidfields= '{key:"DataElement"}';
	
for ($jj=0; $jj<count($vals); $jj++)

{
	$top.="<th title='Record $jj' >RECORD $jj</th>";
	
	$jstop .=",{".'key:"'."Record".$jj.'",label:"'."Record ".$jj.'", sortable:true}';
	$jsmidfields.= ", {".'key:"'."Record".$jj.'"}';
}

$mid = <<<XXX
	    </tr>
      </thead>
      <tbody>
XXX;
/* jsmid is different */
$jsmid = <<<XXX
];
        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("table_$tablename"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
         fields: [ $jsmidfields
XXX;
        
$first=true;


//foreach ($names_[$tablename] as $name) if (isset($viewer_[$viewer][$tablename][$name])) 


foreach ($viewer_[$viewer][$tablename] as $name)  

{	
	$mid.= "
	<tr>
	 <td>{$labels_[$tablename][$name]}</td>
	";
	
	//foreach ($names_[$tablename] as $name) 	if (isset($viewer_[$viewer][$tablename][$name])) $mid .= '<td>'.$r[$name].'</td>';
	for ($jj=0; $jj<count($vals); $jj++)		
	$mid.="<td >{$vals[$jj][$name]}</td>";
    $mid .='
	</tr>
	';
}
$bottom = <<<XXX
     </tbody>
    </table>
  </div>
</div> 
XXX;

$jsbottom = <<<XXX
        ] };
        var myDataTable = new YAHOO.widget.DataTable("$markup", myColumnDefs, myDataSource,
        {caption:"vertical dump of $tablename for simtrak id $simtrakid_ filtered by $viewer sorted by $sortkey", sortedBy:{key:"DataElement"}}
        );
        return {oDS: myDataSource, oDT: myDataTable};
    }();
});
</script>
XXX;
   $nav=" <li $extra ><a href='#tab_$tablename'><em>$tablabel</em></a></li> ";
//return array($top.$mid.$bottom,'',$nav);

return array($top.$mid.$bottom,$jstop.$jsmid.$jsbottom,$nav);
}
function standalone_vertical ($viewer, $tablename,$tablabel,$markup, $sortkey,$extra)
{ 
	
/*  read all the data to find out all of our columns

*/
global $labels_,$names_,$viewer_,$simtrakid_,$playerind_;
	
$vals = array();

$q = "Select * from $tablename where personid='$simtrakid_' ";
$result = dosql ($q);
while ($r=mysql_fetch_array($result))  
$vals []=$r;
mysql_free_result($result);


$jstop =<<<XXX
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
YAHOO.example.EnhanceFromMarkup = function() {
var myColumnDefs = [

XXX;

$top = <<<XXX
  <div id="$markup">
    <table id="table_$tablename">
      <thead>
        <tr>
XXX;
/* generate headers by reading the metadata table for the basic

 */
$first = true;

	$top.="<th title='Data Element' >DATAELEMENT</th>";
	if (!$first) $jstop.=','; $first=false;
	$jstop .="{".'key:"'.'DataElement'.'",label:"'.'Data Element'.'", sortable:true}';
	$jsmidfields= '{key:"DataElement"}';
	
for ($jj=0; $jj<count($vals); $jj++)

{
	$top.="<th title='Record $jj' >RECORD $jj</th>";
	
	$jstop .=",{".'key:"'."Record".$jj.'",label:"'."Record ".$jj.'", sortable:true}';
	$jsmidfields.= ", {".'key:"'."Record".$jj.'"}';
}

$mid = <<<XXX
	    </tr>
      </thead>
      <tbody>
XXX;
/* jsmid is different */
$jsmid = <<<XXX
];
        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("table_$tablename"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
         fields: [ $jsmidfields
XXX;
        
$first=true;


//foreach ($names_[$tablename] as $name) if (isset($viewer_[$viewer][$tablename][$name])) 


foreach ($viewer_[$viewer][$tablename] as $name)  

{	
	$mid.= "
	<tr>
	 <td>{$labels_[$tablename][$name]}</td>
	";
	
	//foreach ($names_[$tablename] as $name) 	if (isset($viewer_[$viewer][$tablename][$name])) $mid .= '<td>'.$r[$name].'</td>';
	for ($jj=0; $jj<count($vals); $jj++)		
	$mid.="<td >{$vals[$jj][$name]}</td>";
    $mid .='
	</tr>
	';
}
$bottom = <<<XXX
     </tbody>
    </table>
  </div>
XXX;

$jsbottom = <<<XXX
        ] };
        var myDataTable = new YAHOO.widget.DataTable("$markup", myColumnDefs, myDataSource,
        {caption:"vertical dump of $tablename for simtrak id $simtrakid_ filtered by $viewer sorted by $sortkey", sortedBy:{key:"DataElement"}}
        );
        return {oDS: myDataSource, oDT: myDataTable};
    }();
});
</script>
XXX;
   $nav=" <li $extra ><a href='#tab_$tablename'><em>$tablabel</em></a></li> ";
//return array($top.$mid.$bottom,'',$nav);

return array($top.$mid.$bottom,$jstop.$jsmid.$jsbottom,$nav);
}
function page_top ($player,$team,$hurlink,$simtrakid_)
{
	return <<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8">	<title>SimTrak Table Dumper</title>
<style type="text/css">
	/*	margin and padding on body element can introduce errors in determining element position and are not recommended;
	we turn them off as a foundation for YUI CSS treatments. */
	body {	margin:0;	padding:0; }
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
<h3>MedCommons SimTrak Table Dumper -- $player on  $team</h3>
$hurlink

<!-- =============================== -->
XXX;
}
function page_foot()
{
$foot = <<<XXX
</div>
</div>
<script type="text/javascript"> 
   var tabView = new YAHOO.widget.TabView('demo');
   var parseNumberFromCurrency = function(sString) {return parseFloat(sString.substring(1));}; 
</script> 

<!--=============================== -->


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
return $foot;
}
//main 
$playerind_  = $_GET['id']; $time = strftime('%D');
$result = dosql("Select * from players where playerind='$playerind_' ");
$r=isdb_fetch_object($result);
if ($r===false) die ("Cant find player with axio id $playerind_");
$team = $r->team;
$simtrakid_ = $r->simtrakid;
$hurlink = "<p><a href=$r->healthurl title='open health records in another window'>view health records for $r->name</a></p>";

$pagetop = page_top($r->name,$r->team,$hurlink,$simtrakid_); $pagefoot = page_foot();
if (isset($_GET['view']))
{
// bring up the standalone viewer on the person table without tabs
$popview_ = $_GET['view']; 
list ($m,$js,$nav) = standalone_vertical($popview_, 'PERSON','person tab','personMarkup','DataElement','class=selected');
$body = <<<XXX
$pagetop
$m
$js
XXX;
}

else {
// bring up a bunch
$popview_='';
$navsettop = <<<XXX
<div id="demo" class="yui-navset"> 
<ul class="yui-nav">
XXX;

$navsetend = <<<XXX
</ul>  
<div class="yui-content"> 
XXX;

$alltabs = $alljs = $navsetmid='';
list ($m,$js,$nav) = onetab('viewA', 'vertical','PERSON','person tab','personMarkup','LNAME','class=selected'); $alltabs.=$m; $alljs.=$js; $navsetmid .=$nav;
list ($m,$js,$nav) = onetab('viewA', 'horizontal','INJURY','injury tab','injuryMarkup','INJURYID','');$alltabs.=$m; $alljs.=$js;$navsetmid .=$nav;
list ($m,$js,$nav) = onetab('viewA', 'horizontal','WEIGHT','weights tab','weightMarkup','WEIGHTDATE','');$alltabs.=$m; $alljs.=$js;$navsetmid .=$nav;

$body = <<<XXX
$pagetop
$navsettop
$navsetmid
$navsetend
$alltabs
$alljs
$pagefoot
XXX;
}
echo $body;

?>