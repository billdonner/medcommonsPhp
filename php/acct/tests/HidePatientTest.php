<?
// Because simple test has warnings
error_reporting(E_ALL ^ E_WARNING);

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('../testdata_ids.inc.php');
require_once('login.inc.php');
require_once('template.inc.php');
require_once('../alib.inc.php');
require_once "JSON.php";
  
error_reporting(E_ALL);

class HidePatientTest extends UnitTestCase {
    
    function testHideUnhidePatient() {
      global $user1Id,$practiceId,$user1Auth;
      $_COOKIE['mc'] = " mcid=$user1Id,from=MedCommons,fn=,ln=,email=user1@medcommons.net,auth=$user1Auth,enc=";
      $json = new Services_JSON();
      $result = hide_patient($practiceId,$user1Id);
      $obj = $json->decode($result);
      $this->assertTrue($obj->status=="ok");

      $result = unhide_patient($practiceId,$user1Id);
      $obj = $json->decode($result);
      $this->assertTrue($obj->status=="ok");
    }
}

//$test = new AuthenticationTest();
//$test->run(new HtmlReporter());
?>
