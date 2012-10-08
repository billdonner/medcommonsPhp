<?
/**
 * Displays a form to allow a user to send imaging data to an arbitrary group
 */

require_once "utils.inc.php";
require_once "template.inc.php";
require_once "alib.inc.php";
require_once "login.inc.php";
require_once "JSON.php";
require_once "settings.php";

nocache();

$gwUrl = allocate_gateway(null);
$startDDLUrl =  $gwUrl."/ddl/start";

$errors = array();


$orderReference = req('callers_order_reference');

// Default case - display form
// Get all the groups
$groups = pdo_query("select * from groupinstances");

if($orderReference) {
    
    if(!isset($acDefaultDODXProvider))
        throw new Exception("Bad configuration - a default DODX provider must be configured to use this page.");
    
    $order = pdo_first_row("select * from dicom_order where callers_order_reference = ?",array($orderReference));
    if(!$order) 
        throw new Exception("Unknown order: ".$orderReference);
        
    $json = new Services_JSON();
	$t = template("dodx.tpl.php")
	        ->set("startDDLUrl",$startDDLUrl)
	        ->set("groups",$groups)
	        ->set("groupAccountId",$acDefaultDODXProvider)
	        ->set("order",$order)
	        ->set("orderJSON",$json->encode($order));
}
else {
	$t = template("dod.tpl.php")
	        ->set("startDDLUrl",$startDDLUrl)
	        ->set("groups",$groups);
}

echo template("base.tpl.php")
         ->set("content",$t)
         ->set("title","Dicom On Demand Upload")
         ->fetch();
?>
