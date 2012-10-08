<?
global $is_test;
$is_test = true;
require_once("UpdateAccessTest.php");
require_once("utils.inc.php");

nocache();


class ShowPasses extends HtmlReporter {

     function paintCaseStart($test_name) {
        print "<p><h4>$test_name</h4> <ul>";
     }

     function paintCaseEnd($test_name) {
       print "</ul>";
       $this->_progress++;
     }
  
     function paintMethodStart($test_name) {
        print "<li>$test_name: ";
     }
    
    function paintPass($message) {
      $this->_passes++;
      print "<span class='pass'>Pass</span>";
    }
    function paintError($message) {
      $this->_exceptions++;
      print "<div class='details'>$message</div>";
    }
    function paintException($message) {
      $this->_exceptions++;
      print "<div class='details'>$message</div>";
    }
    function paintFail($message) {
      $this->_fails++;
      print "<span class='fail'>Fail<br><div class='details'>$message</div></span>";
    }
    
    function _getCss() {
        return parent::_getCss() . ' body { font-family: arial; } h1 { font-size: 18px;} .pass { color: green; } .fail { color: red; } .details { border: solid 1px black; background-color: #f4f4d6; color: black; padding: 8px; margin: 8px 4px;} h4 { color: #227;}';
    }
}

$test = new TestSuite('MedCommons Secure Services Tests');
$test->addTestCase(new UpdateAccessTest());
$test->run(new ShowPasses());
?>
