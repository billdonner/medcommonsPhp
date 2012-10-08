<?header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Pragma: no-cache");
  $msg = $_REQUEST['msg'];
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <style type="text/css" media="all"> 
      @import "acctstyle.css"; 
    </style>
  </head>
  <body style='background: transparent;'>
  <h3>Error Occurred!</h3>
  <p>A problem occurred with your last operation.</p>
    <pre>
    <? echo $msg ?>
    </pre>
  </body>
</html>

