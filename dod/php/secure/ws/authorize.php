<?PHP
require_once "../ws/securewslibdb.inc.php";

/**
 * Creates an authentication token for the requested accounts and returns it.
 * The authentication token may then be used subsequently to access services
 * using the authority of the returned token.
 */
class authorizeWs extends dbrestws {

	function xmlbody() {

    // Generate a random id
    $token = sha1(strval(time()).strval(rand()));

    // Parse account ids
    $id = $_REQUEST['id'];
    $secret = isset($_REQUEST['secret']) ? $_REQUEST['secret'] : '';
    $type = $_REQUEST['type'];

    $result = new stdClass;

    $inputs = $this->xmfield("inputs",
                  $this->xmfield("id",$id).
                  $this->xmfield("secret",$secret).
                  $this->xmfield("type",$type));

    if($type != "openid") {
      $this->xm($inputs.
                $this->xmfield("summary_status","failed - invalid authorization type provided").
                $this->xmfield("outputs",
                  $this->xmfield("status","failed - invalid authorization type provided")));
    }

    // Find the external shares for this openid
    $result = $this->dbexec("select * from external_share where es_identity = '$id'","Failed to select from external_share");
    while($es = mysql_fetch_object($result)) {
      $this->dbexec("insert into authentication_token (at_id, at_token, at_es_id) 
                     values (NULL, '$token', ".$es->es_id.")", "unable to insert authorization_token");
    }

    $this->xm($inputs.
              $this->xmfield("summary_status","success").
              $this->xmfield("outputs",
                $this->xmfield("status","ok").
                $this->xmfield("auth",$token)));
	}
}

//main
$x = new authorizeWs();
$x->handlews("response_authorize");

?>
