<?
require_once "../mc_oauth.inc.php";
require_once('../../acct/testdata_ids.inc.php');

$base_url = "http://yowie:7080/mctest/api";
$consumer  = new OAuthConsumer("84547f4c4a7085a8c9960883d0fc60e06556604a", "6a5a83089b7eceada7382a8da436cc77d158b9af", NULL);
$req_token = new OAuthToken("693b09f172832036b49f1bdba2de7e0befb30f6e", "3f209c0c0925642480ffa7c764b34daa9e758fd6");
$req = OAuthRequest::from_consumer_and_token($consumer, $req_token, "GET", $base_url . "/access_token.php", array());
$req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $req_token);
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

