<? 

require_once 'dbparams.inc.php';


function eu($prompt,$dbfield,$howset)
{
	$name='ac'.$dbfield;
	$query="Select value from mcproperties where property='$name'";
	$result = mysql_query($query);
	$value = mysql_fetch_row($result); 
	echo "$value[0]\r\n";
}
function applianceinfo()
{
	eu('Logo','Logo','registration (set by medcommons)');
	eu('Alt','Alt','registration');
	eu('Message','Message','registration');
	eu('Appliance Name','ApplianceName','registration');
	eu('Owner','Owner','registration');
	eu('Home Page','HomePage','registration');
	eu('Domain','Domain','registration');
	eu('Account Status','AccountStatus','registration');
	eu('Privacy Configuration File','PrivacyConfigurationFile','registration');
	eu('Privacy Policy File','PrivacyPolicyFile','registration');
	eu('Patient Brouchure File','PatientBrochureFile','each change');
	eu('Email Template Folder','EmailTemplateFolder','each change');
	eu('Printable Template Folder','PrintableTemplateFolder','each change');
	eu("Temporary Account Retention Time",'TemporaryAccountRetentionTime',
	'fff');
	
}



// start
header("Content-type: text/plain");

$db=$GLOBALS['DB_Database'];

mysql_connect($GLOBALS['DB_Connection'],
$GLOBALS['DB_User'],
$GLOBALS['DB_Password']
) or die ("can not connect to mysql");
$db = $GLOBALS['DB_Database'];
mysql_select_db($db) or die ("can not connect to database $db");
applianceinfo();

?>

