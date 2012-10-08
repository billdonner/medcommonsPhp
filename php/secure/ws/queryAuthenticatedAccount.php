<?PHP
require_once "../ws/securewslibdb.inc.php";

/**
 * Accepts an authentication token as input and returns the
 * primary authenticated subject account as a result.
 */
class queryAuthenticatedAccountWs extends jsonrestws {

	function jsonbody() {

		$auth = $this->cleanreq('auth');

    // get authorized accounts
    $accounts = get_authorized_accounts($auth);

    if(count($accounts) > 0) {
      return $accounts[0];
    }
    else
      return null;
	}
}

//main
$x = new queryAuthenticatedAccountWs();
$x->handlews("response_queryAuthenticatedAccount");

?>
