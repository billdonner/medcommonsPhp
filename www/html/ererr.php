<?php

  include("dbparams.inc.php");
// needs work - eliminate absolute urls
// build better page frame
?>
        <div id="supportingText" title="Emergency CCR Access Service - enter 16 digit ID">
                <div id="preamble">
                    <h3><span><font color="red">Emergency CCR Access</font></span>
                    </h3>
                    <p class="p1">We cannot find a CCR with that code, please re-enter</p>
                <!-- ssadedin: get necessary for redirect -->
                <form method="get" action="acctredir.php" target="ccrdisplay">
                  <input type="hidden" name="p" value="redccr.php"/> 
                  <input type="hidden" name="redirurl" value="<?echo $GLOBALS['Accounts_Url'];?>/eccrredir.php"/> 
                  <input type="hidden" name="returnurl" value="<?echo $GLOBALS['BASE_WWW_URL'];?>/ererr.php"/> 
                  <input type="hidden" name="accid" value="" size="18" maxlength="16"/>
                  <input type="submit" value="Go"
                          onclick="window.open('about:blank','ccrdisplay','toolbar=1,location=1,directories=1,status=1,menubar=1,scrollbars=1,resizable=1');"/>
                </form>
       
                </div>
         </div>

