<?
// Because simple test has warnings
error_reporting(E_ALL ^ E_WARNING);

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('../testdata_ids.inc.php');
require_once('login.inc.php');
require_once('template.inc.php');

error_reporting(E_ALL);

class AuthenticationTokenTest extends UnitTestCase {
    
    function testCreateOneAccountAuthToken() {
      global $doctorId;
      $t = new Template();
      $token = get_authentication_token($doctorId,$t);
      $this->assertPattern("/[0-9a-z]{40}/",$token);
    }

    function testCreateMultiAccountAuthToken() {
      global $doctorId, $user1Id;
      $t = new Template();
      $token = get_authentication_token(array($doctorId,$user1Id),$t);
      $this->assertPattern("/[0-9a-z]{40}/",$token);
    }

    function testAlwaysFail() {
      //$this->assertEqual(0,1,"This is just an example of a failure!");
    }
}

//$test = new AuthenticationTest();
//$test->run(new HtmlReporter());
?>
