<?
// Because simple test has warnings
error_reporting(E_ALL ^ E_WARNING);
global $is_test;
$is_test = true;

require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
require_once('../../acct/testdata_ids.inc.php');
require_once('../ws/updateAccess.php');

global $auth;
$auth = "97a49ea6137dc95bc02a3775282b5e19c47d7892";
$user2auth = "87a49ea6137dc95bc02a3775282b5e19c47d7893";

error_reporting(E_ALL);
class UpdateAccessTest extends UnitTestCase {

    private $esId = null;

    function setUp() {
      global $user2Id,$doctor3Id,$user1Id,$auth;
      $this->ws = new updateAccessWs();
      $this->ws->dbconnect();
      mysql_query("delete from rights where storage_account_id = $user1Id");
      mysql_query("insert into external_share values(NULL, 'unittest', 'unit test idp', 'unit', 'test')");
      $this->esId = mysql_insert_id();
      $_REQUEST = array();
      $_REQUEST['auth'] = "token:$auth"; // NOTE: this auth is defined in ../testdata.php
    }
    
    function tearDown() {
      global $user2Id,$doctor3Id,$user1Id;
      mysql_query("delete from rights where storage_account_id = $user1Id");
    }
    
    function testMcids() {
      global $user2Id,$doctor3Id,$user1Id,$auth,$user2auth;
      $_REQUEST['accid']=$user1Id;
      $_REQUEST[$user2Id]='R';
      $_REQUEST[$doctor3Id]='W';
      $result = $this->ws->jsonbody();
      $r = mysql_fetch_object(mysql_query("select * from rights where storage_account_id = $user1Id and account_id = $user2Id and active_status = 'Active'"));
      $this->assertTrue($r->rights == 'R');
      $r = mysql_fetch_object(mysql_query("select * from rights where storage_account_id = $user1Id and account_id = $doctor3Id and active_status = 'Active'"));
      $this->assertTrue($r->rights == 'W');

      // Resolve access using api
      $rights = get_rights("$user2auth", $user1Id);
      $this->assertTrue($rights == 'R');
    }

    function testEsIds() {
      global $user2Id,$doctor3Id,$user1Id;
      $_REQUEST['accid']=$user1Id;

      // First test read only access
      $_REQUEST["es_".$this->esId]='R';
      $this->ws->dbconnect();
      $result = $this->ws->jsonbody();
      $r = mysql_fetch_object(mysql_query("select * from rights where storage_account_id = $user1Id and es_id = {$this->esId} and active_status = 'Active'"));
      $this->assertTrue($r->rights == 'R');

      // Now test RW access
      $_REQUEST["es_".$this->esId]='RW';
      $result = $this->ws->jsonbody();
      $r = mysql_fetch_object(mysql_query("select * from rights where storage_account_id = $user1Id and es_id = {$this->esId} and active_status = 'Active'"));
      $this->assertTrue($r->rights == 'RW');
    }

    function testOpenIds() {
      global $user2Id,$doctor3Id,$user1Id;
      $_REQUEST['accid']=$user1Id;

      // First test read only access
      $_REQUEST['http://openid.badboy.com.au']='R';
      $this->ws->dbconnect();
      $result = $this->ws->jsonbody();
      $r = mysql_fetch_object(mysql_query("select r.* 
                                           from rights r, external_share es
                                           where es.es_id = r.es_id
                                           and es.es_identity = 'http://openid.badboy.com.au'
                                           and r.storage_account_id = '$user1Id'
                                           and r.active_status = 'Active'"));
        
      $this->assertTrue($r->rights == 'R');

      $token = generate_authentication_token();

      // Use the es_id to create an auth token, then test we can resolve it using the auth token
      mysql_query("INSERT INTO authentication_token (at_id,at_token,at_es_id) VALUES (NULL,'$token','{$r->es_id}')") or die("unable to insert auth token");

      $rights = get_rights($token, $user1Id);
      $this->assertTrue($rights == 'R');

      $_REQUEST['http://openid.badboy.com.au']='RW';
      $result = $this->ws->jsonbody();
      $rights = get_rights($token, $user1Id);
      $this->assertTrue($rights == 'RW');

      $_REQUEST['http://openid.badboy.com.au']='';
      $result = $this->ws->jsonbody();
      $rights = get_rights($token, $user1Id);
      $this->assertTrue($rights == '');
      mysql_query("delete from authentication_token where at_token = '$token'") or die("Can't delete $token from authentication_token");
    }
}

$test = new UpdateAccessTest();
$test->run(new HtmlReporter());
?>

