<?
require_once "../../alib.inc.php";
aconnect_db();
$info = testif_logged_in();
$eccr=false;
if($info) {
  $eccr = tryECCR($info[0]);
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Current CCR Updates</title>
	<link href="../../main.css" rel="stylesheet"/>
  <style type="text/css">
    body { background-image: none; }
  </style>
</head>
<body>
   <div style="padding-left: 30px;">
    <p style="vertical-align: middle;">
      <img style="vertical-align: middle;" src="../../images/RedCross_16.gif"/>&nbsp;
    <?if($eccr!=false):?>
      Your Emergency CCR is available. <a style="vertical-align: middle;" href="<?=$GLOBALS['Accounts_Url']?>/printeccr.php" onclick="alert('This function isn\'t implemented yet. Please try again soon!'); return false;" target="printeccr">Print Card</a> 
    <?else:?>
      You don't have an Emergency CCR set.  Open any CCR and choose the "Save as Emergency CCR" to create one.
    <?endif;?>
    </p>
  </div>
</body>
</html>
