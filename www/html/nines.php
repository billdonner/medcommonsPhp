<?

  include("dbparams.inc.php");
?>
<div id="supportingText" title="a brief journey into Janes CCR">
          <div id="preamble"> 
              <h3>CCR Demonstration</h3> 
              <p class="p1">If you would like to access a CCR, please enter the tracking number in the field provided in the upper right corner.</p> 
              <p class="p1">To view a demonstration CCR, please click the button below </p>
              <p class="p1">
                  Demo images courtesy of Gordon J. Harris, PhD - MGH Radiology 3D Imaging Service</p> 
              <div >
                <!-- ssadedin: get necessary for redirect -->
                <form method="get" action="acctredir.php" target="ccrdisplay">
                  <input type="hidden" name="p" value="redccr.php"/> 
                  <input type="hidden" name="redirurl" value="<?echo $GLOBALS['Accounts_Url'];?>/eccrredir.php"/> 
                  <input type="hidden" name="returnurl" value="<?echo $GLOBALS['BASE_WWW_URL'];?>/ererr.php"/> 
                  <input type="hidden" name="accid" value="9999999999999999" size="18" maxlength="16"/>
                  <input type="submit" value="Go"
                          onclick="window.open('about:blank','ccrdisplay','toolbar=1,location=1,directories=1,status=1,menubar=1,scrollbars=1,resizable=1');"/>
                </form>
              </div> 
          </div> 
      </div>
