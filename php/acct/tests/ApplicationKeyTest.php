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

class ApplicationKeyTest extends UnitTestCase {

    function setUp() {
      pdo_execute("delete from external_application where ea_code = ?",array('UNIT TEST'));
    }
    
    function tearDown() {
      pdo_execute("delete from external_application where ea_code = ?",array('UNIT TEST'));
    }
  
    
    function testSignAndVerify() {

      global $user1Id,$practiceId,$user1Auth;

      $key = sha1(rand()."UNIT_TEST_KEY".rand());

      $eaId = pdo_execute("insert into external_application (ea_key, ea_code, ea_name, ea_active_status, ea_ip_address)
                           values (?,?,?,'Pending',?)", array($key, 'UNIT TEST', "Unit test Application", "127.0.0.1"));

      // Sign a url
      $url = "http://foo.bar.com?test=a&test=b";

      $signed = sign_application_url('UNIT TEST',$key,$url);
      echo "<p>Signed url is $signed with key $key</p>";

      $verify = verify_external_application_url($signed."&fb_sig=sdfadfda&fb_sig_time=12345");

      $this->assertTrue($verify);
    }
}

$test = new ApplicationKeyTest();
$test->run(new HtmlReporter());
?>

