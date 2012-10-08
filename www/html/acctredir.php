<?
/*
 * This file is a wrapper that acts as a gateway to the accounts server.
 *
 * Provide a parameter 'p' for the path you want to execute on the id server
 * (example - 'register')
 */

  include("dbparams.inc.php");

 $p = $_REQUEST['p']; 
 header("Location: ".$GLOBALS['Accounts_Url']."/$p?".$_SERVER['QUERY_STRING'] );
?>
