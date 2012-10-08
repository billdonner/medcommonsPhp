<?
  /**
   * An error page that displays details about errors, uncaught exceptions etc.
   * See error_page() in utils.inc.php
   */
  if(!headers_sent()) {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
  }
  $msg = $_REQUEST['msg'];
?>
  <br/>
  <h3>Error Occurred!</h3>
  <br/>

  <p>A problem occurred with your last action.  The following message may help to resolve the issue:</p>
    <pre>

    <? echo htmlspecialchars($msg) ?>

    </pre>
  
  <p>This error has been logged.   It may help to retry what you were doing again
     at a later time.  If the problem persists, please visit the support link at the bottom
     of the page to request assistance and quote the following error #<?=$_REQUEST['errorid']?> .</p>
  </body>
</html>

