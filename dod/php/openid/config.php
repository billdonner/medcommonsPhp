<?php

require_once 'settings.php';

/**
 * The URL for the server.
 *
 * This is the location of server.php. For example:
 *
 * $server_url = 'http://example.com/~user/server.php';
 *
 * This must be a full URL.
 */
$server_url = "http://garble.com/openid/server.php";

/**
 * Initialize an OpenID store
 *
 * @return object $store an instance of OpenID store (see the
 * documentation for how to create one)
 */
function getOpenIDStore()
{
    require_once 'Auth/OpenID/MySQLStore.php';
    require_once 'DB.php';

    global $IDENTITY_USER, $IDENTITY_PASS, $IDENTITY_HOST, $IDENTITY_DB;

    $dsn = array(
                 'phptype'  => 'mysql',
                 'username' => $IDENTITY_USER,
                 'password' => $IDENTITY_PASS,
                 'hostspec' => $IDENTITY_HOST
                 );

    $db =& DB::connect($dsn);

    if (PEAR::isError($db)) {
        return null;
    }

    $db->query("USE $IDENTITY_DB");
        
    $s =& new Auth_OpenID_MySQLStore($db);

    $s->createTables();

    return $s;
}

?>
