<?
// Because simple test has warnings
error_reporting(E_ALL ^ E_WARNING);

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('../testdata_ids.inc.php');
require_once('../ws/wsUpdateWorkflow.php');
  
error_reporting(E_ALL);

function set($name,$value) {
  $_REQUEST[$name]=$value;
}

class WorkflowTest extends UnitTestCase {
    
    function testAddWorkflowItem() {
      global $user1Id,$practiceId,$user1Auth;
      set("src_accid",$user1Id);
      set("target_accid",$user1Id);
      set("key",sha1(""+time()+""+rand()));
      set("type","Unit Test");
      set("status","Active");
      set("auth",$user1Auth);

      // Invoke service
      $ws = new updateWorkflowWs();
      ob_start();
      $ws->test = true;
      try { $ws->handlews("updateWorkflow_Response"); } catch(Exception $e) { }

      // now update status
      set("status","Passed");
      try { $ws->handlews("updateWorkflow_Response"); } catch(Exception $e) { }
      $result = ob_get_contents();
    }
}

//$test = new AuthenticationTest();
//$test->run(new HtmlReporter());
?>
