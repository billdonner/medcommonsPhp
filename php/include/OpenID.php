<?php

require 'settings.php';

/*
 * Functions that support linking of OpenIDs with MCIDs.
 */
function canonical_openid($url) {
    if (substr_compare($url, 'https://', 0, 8) != 0 &&
        substr_compare($url, 'http://', 0, 7) != 0)
        $url = "http://${url}";

    if (substr_compare($url, '/', -1) != 0)
        $url .= '/';

    return $url;
}

function match_openid($url, $pattern) {
    $url = canonical_openid($url);
    $pattern = canonical_openid($pattern);

    $a = explode('%', $pattern);
    $head = $a[0];
    $tail = $a[1];

    return substr_compare($url, $head, 0, strlen($head)) == 0 &&
           substr_compare($url, $tail, -strlen($tail)) == 0;
}

function link_openid_to_mcid($pdo, $mcid, $openid, $provider_id) {
    global $acGlobalsRoot;

    $sql = <<<EOF
            DELETE FROM external_users
            WHERE  provider_id = :provider_id AND
                   username = :openid;
            INSERT INTO external_users (mcid, provider_id, username)
                   VALUES(:mcid, :provider_id, :openid);
EOF;


    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        $e = $pdo->errorInfo();
        return $e[2];
    }
    else if (!$stmt->execute(array('openid' => canonical_openid($openid),
                                   'provider_id' => $provider_id,
                                   'mcid' => $mcid))) {
        $e = $stmt->errorInfo();
        return $e[2];
    }
    else
        $stmt->closeCursor();

    /* Register with the globals service */
    $openid = urlencode($openid);
    file_get_contents("${acGlobalsRoot}login/register.php?name=${openid}&mcid=${mcid}");

}

?>
