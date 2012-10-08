<? 

require_once 'dbparams.inc.php';

define ('INFOLEVEL',1);
define ('USERLEVEL',0);
function emit($s)
{
	$GLOBALS['uinfo'].=$s;
}
function z($level,$prompt,$value,$dbfield,$db,$edit,$howset)
{
	if ($level<=$GLOBALS['glevel'])
	{
		//    table|field|accid|id
		$ed = ($edit?'edit':' ');
		if ($edit){
			if ($value=='')$value = '&nbsp;&nbsp;';
			$value = "<span id='$db|$dbfield|m|' class='editText'>$value</span>";
		}
		//	echo "db $db field $dbfield prompt $prompt value $value $ed<br>";
		emit ("<tr><td>$prompt</td><td class=inputfield>$value</td></tr>\r\n");
	}
}
function fu($level,$prompt,$dbfield,$howset)
{
	$name='ac'.$dbfield;
	$query="Select value from mcproperties where property='$name'";
	$result = mysql_query($query);
	$value = mysql_fetch_row($result);
	z($level,$prompt,$value[0],$dbfield,'wierd',false,$howset);
}
function eu($level,$prompt,$dbfield,$howset)
{
	$name='ac'.$dbfield;
	$query="Select value from mcproperties where property='$name'";
	$result = mysql_query($query);
	$value = mysql_fetch_row($result); 
	z($level,$prompt,$value[0],$dbfield,'wierd',true,$howset);
}
function applianceinfo($glevel)
{
	$GLOBALS['glevel']=$glevel;
	$GLOBALS['uinfo']=''; // someday I'll figure out how global variables work in php
	// now lay this into tables
	emit("<table class=trackertable>\r\n");

	eu(USERLEVEL,'Logo','Logo','registration (set by medcommons)');
	eu(USERLEVEL,'Alt','Alt','registration');
	eu(USERLEVEL,'Message','Message','registration');
	eu(USERLEVEL,'Appliance Name','ApplianceName','registration');
	eu(USERLEVEL,'Owner','Owner','registration');
	eu(USERLEVEL,'Home Page','HomePage','registration');
	eu(USERLEVEL,'Domain','Domain','registration');
	fu(USERLEVEL,'Account Status','AccountStatus','registration');
	eu(USERLEVEL,'Privacy Configuration File','PrivacyConfigurationFile','registration');
	eu(USERLEVEL,'Privacy Policy File','PrivacyPolicyFile','registration');
	eu(USERLEVEL,'Patient Brouchure File','PatientBrochureFile','each change');
	eu(USERLEVEL,'Email Template Folder','EmailTemplateFolder','each change');
	eu(USERLEVEL,'Printable Template Folder','PrintableTemplateFolder','each change');
	eu(USERLEVEL,"Temporary Account Retention Time",'TemporaryAccountRetentionTime',
	'fff');
	emit("</table>\r\n");

	return $GLOBALS['uinfo'];
}

function opspage ($desc,$title,$customhead,$onload,$custombody)
{
	if ($onload===false) $onload = "'initApplianceConfigurator()'";
	$html = <<<XXX
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
        <meta name="author" content="MedCommons"/>
        <meta name="keywords" content="ccr, phr, privacy, patient, health, records, medical, w3c,
            web standards"/>
        <meta name="description" content="$desc"/>
        <meta name="robots" content="noindex,nofollow"/>
        <title>$title</title>
        <link rel="shortcut icon" href="images/favicon.gif" type="image/gif"/>
        <link rel="stylesheet" type="text/css" media="print" href="print.css"/>
        <link rel="stylesheet" type="text/css" href="tabs.css"/>
        <link rel="stylesheet" type="text/css" href="rls.css"/>
        <link rel="stylesheet" type="text/css" href="autoComplete.css"/>
        <style type="text/css" media="all"> @import "main.css";</style> 
        <!-- <style type="text/css" media="all"> @import "acctstyle.css";</style> -->
        <style type="text/css" media="all"> @import "theme.css"; </style>
        <style type="text/css" media="all"> @import "theme.css.php"; </style>
        <script src="MochiKit.js" type="text/javascript"></script>
        <script src="tabs.js" type="text/javascript"></script>
        <script src="blender.js" type="text/javascript"></script>
        <script src="utils.js" type="text/javascript"></script>
        <script src="autoComplete.js" type="text/javascript"></script>
$customhead
   </head>
    <body id="css-zen-garden" onload=$onload>
    <div id="container">
 $custombody
    </div>
    </body>
    </html>
XXX;
	return $html;
}

// start
$db=$GLOBALS['DB_Database'];

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");

echo opspage('appliance configurator','Configure MedCommons Appliances',
"<p>",false,applianceinfo(0));
?>

