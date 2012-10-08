<?php

require_once "is.inc.php";
require_once "stview.inc.php";

function load_table($table)
{
	global $simtrakid_;
	$vals = array();

$q = "Select * from $table where personid='$simtrakid_' ";
$result = dosql ($q);
while ($r=mysql_fetch_array($result))  
$vals []=$r;
mysql_free_result($result);
return $vals;
}

function onetab_horizontal ($rows,$viewer,$tabkey, $tablename,$tablabel,$markup, $sortkey,$extra)
{ 
global $labels_,$fields_,$viewer_,$simtrakid_,$frontab_;
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
foreach ($fields_[$tablename] as $name) if (isset($viewer_[$viewer][$tablename][$name])) 
{
	$top.="<th title='{$labels_[$tablename][$name]}' >{$fields_[$tablename][$name]}</th>";
	if (!$first) $jstop.=','; $first=false;
	$jstop .="{".'key:"'.$fields_[$tablename][$name].'",label:"'.$labels_[$tablename][$name].'", sortable:true}';
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
foreach ($fields_[$tablename] as $name) if (isset($viewer_[$viewer][$tablename][$name])) 
{	if (!$first) $jsmid.=',';
    $first=false;
	$jsmid .="{".'key:"'.$fields_[$tablename][$name].'"}'; 
}
/* generate body as rows by reading actual data tables

*/
foreach ($rows as $row)
{  $mid .= '
	<tr>
';	
	foreach ($fields_[$tablename] as $name) 	
	if (isset($viewer_[$viewer][$tablename][$name])) $mid .= '<td>'.$row[$name].'</td>';

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

if ("tab_$tablename" == $frontab_) $extra = "class=selected"; // highlight chosen tab
   $nav=" <li $extra ><a href='#tab_$tablename'><em>$tablabel</em></a></li> ";
return array($top.$mid.$bottom,$jstop.$jsmid.$jsbottom,$nav);
}

function onetab_vertical ($rows, $viewer, $tabkey, $tablename,$tablabel,$markup, $sortkey,$extra)
{ 
	
/*  read all the data to find out all of our columns
 * 
 *  <a href="?view=viewB&id=$playerind_" target="_new">more detail</a> 

*/
global $labels_,$fields_,$viewer_,$simtrakid_,$playerind_,$frontab_;
	


$jstop =<<<XXX
<script type="text/javascript">
YAHOO.util.Event.addListener(window, "load", function() {
YAHOO.example.EnhanceFromMarkup = function() {
var myColumnDefs = [

XXX;

$top = <<<XXX
<div id="tab_$tabkey">

  <div id="$markup">
    <table id="table_$tabkey">
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
	
for ($jj=0; $jj<count($rows); $jj++)

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
        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("table_$tabkey"));
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        myDataSource.responseSchema = {
         fields: [ $jsmidfields
XXX;
        
$first=true;


foreach ($fields_[$tabkey] as $name)

{	
	$mid.= "
	<tr>
	 <td>{$labels_[$tabkey][$name]}</td>
	";
	
	//foreach ($fields_[$tabkey] as $name) 	if (isset($viewer_[$viewer][$tabkey][$name])) $mid .= '<td>'.$r[$name].'</td>';
	for ($jj=0; $jj<count($rows); $jj++)		
	$mid.="<td >{$rows[$jj][$name]}</td>";
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
        {caption:"vertical dump of $tabkey for simtrak id $simtrakid_ filtered by $viewer sorted by $sortkey", sortedBy:{key:"DataElement"}}
        );
        return {oDS: myDataSource, oDT: myDataTable};
    }();
});
</script>
XXX;
if ("tab_$tabkey" == $frontab_) $extra = "class=selected"; // highlight chosen tab
   $nav=" <li $extra ><a href='#tab_$tabkey'><em>$tablabel</em></a></li> ";
//return array($top.$mid.$bottom,'',$nav);

return array($top.$mid.$bottom,$jstop.$jsmid.$jsbottom,$nav);
}

function page_top ($player,$team,$hurlink,$simtrakid_)
{
	return <<<XXX
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8">	<title>SimTrak Viewer</title>
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
<h3>MedCommons SimTrak Viewer Prototype -- $player on  $team</h3>
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
}//main 
$mcid_  = $_GET['accid']; $time = strftime('%D');
if (!isset($_GET['tab'])) $frontab_='tab_m';else
$frontab_ = $_GET['tab'];
$result = dosql("Select * from players where mcid='$mcid_' ");
$r=isdb_fetch_object($result);
if ($r===false) die ("Cant find player with mcid $mcid_");
$team = $r->team;
$simtrakid_ = $r->simtrakid;
$playerind_ = $r->playerind;
$hurlink = "<p><a href=$r->healthurl title='open health records in another window'>view health records for $r->name</a></p>";

$pagetop = page_top($r->name,$r->team,$hurlink,$simtrakid_); $pagefoot = page_foot();

// bring up a bunch of tables data
$popview_='';

// now use the data
$main = make_section_tabs(); // auto generated in stview.inc.php
$body = <<<XXX
$pagetop
$main
$pagefoot
XXX;

echo $body;

?>