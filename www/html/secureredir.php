<?
/*
 * This file is a wrapper that acts as a gateway to secure
 * services.  These *may* reside on a different server. By referencing
 * this file static content on the WWW site can refer to these services
 * using relative path and thus be transportable across servers.
 */

  include("dbparams.inc.php");

 $p = $_REQUEST['p']; 
 header("Location: ".$GLOBALS['Commons_Url']."/$p.php?".$_SERVER['QUERY_STRING'] );
 //$q = $_SERVER['QUERY_STRING'];
 //echo "<html><body>$q</body></html>";
?>
