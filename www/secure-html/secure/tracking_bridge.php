<?PHP
  /*
   * A bridge frame that a user can put in their page to allow execution
   * of AJAX calls to this server.
   */
  require "dbparams.inc.php";
?>
<html>
  <head>
     <script src="MochiKit.js" type="text/javascript"></script>
     <script type="text/javascript">
       function success(obj) {
         window.parent.bridgeResult=obj;
         window.parent.setTimeout('track()',10);
       }

       function fail(e) {
         window.parent.bridgeResult=obj;
         window.parent.setTimeout('trackingError()',10);
       }

       function send(url) {
          alert(window.name + ' : ' + url);
          loadJSONDoc(url).addCallbacks(success, fail);
       }
     </script>
   </head>
   <body onload="var d='<? echo $GLOBALS['Script_Domain']; ?>'; if(d!='')document.domain=d;">
   </body>
</html>
