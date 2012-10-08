<?php

require_once "appinclude.php";  // required of all facebook apps put this last

$facebook = new Facebook($appapikey, $appsecret);
$facebook->require_frame();
$user = $facebook->require_login();

if (isset($_POST['xfbml']))
$fbml = $_POST['xfbml'];
else $fbml = "";
$markup = <<<FBML
<fb:fbml><fb:title>Facebook Fbml and Javascript Tester</fb:title>

<fb:explanation>
<fb:message>Put your Fbml and Javascript in the box and hit OK</fb:message>
    <fb:editor action="h.php" labelwidth="30">
    <fb:editor-textarea rows=10 name=xfbml label="fbml" >overwrite and put your fbml here</fb:editor-textarea>
       <fb:editor-buttonset>
       <fb:editor-button value="OK"/>
     </fb:editor-buttonset>
  </fb:editor>
  </fb:explanation>
  
<fb:success>
<fb:message>Your Results</fb:message>
<div style='margin:10px; padding:20px; border:1px solid blue; background-color:white'>
	$fbml
</div
</fb:success>

<fb:explanation>
<fb:message>Your Raw HTML and Javascript code</fb:message>
<div style='margin:10px; padding:20px; border:1px solid blue; background-color:white'>
    <textarea rows=10 cols=90 label="html" >$fbml</textarea>
</div>
</fb:explanation>
  
<fb:explanation>
<fb:message>Your Transformed HTML code</fb:message>
<fb:editor labelwidth="30">
    <fb:editor-textarea rows=10 label="html" >$fbml</fb:editor-textarea>
  </fb:editor>
  </fb:explanation>
</fb:fbml>
FBML;
echo $markup;

?>

<script>
function test2(context) {
  var dialog = new Dialog(Dialog.DIALOG_CONTEXTUAL).setContext(context).showChoice('Disconnect MedCommons Account From Facebook', 'Do you really want to Disconnect from your MedCommons Account', 'Yes', 'No'); 
dialog.onconfirm = function() {
  context.setTextValue(' You  are  disconnected ');
return true;;
  };
dialog.oncancel = function() {
  context.setTextValue(' You were not disconnected ');return false;
  };
}
</script>
Press to disconnect from your MedCommons Account 
<a href="" onclick=" test2(document.getElementById('dialog_test_span2')); return false;"  >
<BUTTON>Disconnect</Button></a> 
<small> <span id="dialog_test_span2"></span></small><br />