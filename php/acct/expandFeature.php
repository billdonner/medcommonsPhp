<?
/**
 * This page can be used to expand a feature box from an external script.   To use it,
 * a page loads this frame which will redirect back to it's referrer, after first 
 * expanding the requested widget that should be passed in the 'feature' parameter
 */

$feature = isset($_GET['feature']) ? stripslashes($_GET['feature']) : false;
if($feature) {
  if(!preg_match("/[a-z]*/i",$feature)) {
    error_log("Invalid feature passed: ".$feature);
    $feature = false;
  }
}
?>
<html>
<head>
  <script type="text/javascript">
    <?if($feature):?>
      window.parent.activate('<?=$feature?>');
    <?endif;?>
    window.location.href=document.referrer;
  </script>
</head>
<body>
</body>
</html>
