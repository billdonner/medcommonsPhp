<?
require_once "../mc_oauth.inc.php";
require_once('../../acct/testdata_ids.inc.php');

$base_url = "http://yowie:7080/mctest/api";
$consumer  = new OAuthConsumer("84547f4c4a7085a8c9960883d0fc60e06556604a", "6a5a83089b7eceada7382a8da436cc77d158b9af", NULL);
$req = OAuthRequest::from_consumer_and_token($consumer, $acc_token, "GET", $base_url . "/request_token.php", array());
$req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $acc_token);
$result = file_get_contents($req->to_url());
?>
<html>
<body>
<p>Request:</p>
<?= $req->to_url()?>
<p>Result:</p>
<?=$result?>
</body>
</html>

