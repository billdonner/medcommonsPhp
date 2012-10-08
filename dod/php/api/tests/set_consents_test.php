<?
require_once "../mc_oauth.inc.php";
require_once('../../acct/testdata_ids.inc.php');

$base_url = "http://yowie:7080/mctest/api";
$consumer  = new OAuthConsumer("e0f2e36173ff6f79f8d3aa6f5f00bb87c324099f", "unit test", NULL);
$acc_token = new OAuthToken("123456789012345678901234567890", "secret", 1);
$echo_req = OAuthRequest::from_consumer_and_token($consumer, $acc_token, "GET", $base_url . "/set_consents.php", 
                                                   array("accid" => $user1Id, "$user2Id" => "R"));
$echo_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $acc_token);
$result = "null";
$result = file_get_contents($echo_req->to_url());
?>
<html>
<body>
<p>Request:</p>
<?= $echo_req->to_url()?>
<p>Result:</p>
<?=$result?>
</body>
</html>

