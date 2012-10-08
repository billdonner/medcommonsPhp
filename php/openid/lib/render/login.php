<?php

require 'login.inc.php';
require 'settings.php';
require 'mc.inc.php';

require_once "lib/session.php";
require_once "lib/render.php";

define('login_needed_pat',
       'You must be logged in as %s to approve this request.');

function login_render($errors=null, $input=null, $needed=null)
{
    global $acTemplateFolder;

    $current_user = getLoggedInUser();
    if ($input === null) {
        $input = $current_user;
    }
    if ($needed) {
        $errors[] = sprintf(login_needed_pat, link_render($needed));
    }

    $t = Template($acTemplateFolder . 'openid_login.tpl.php');
    $t->esc('loginUrl', buildURL('login', true));
    $t->esc('mcid', $input);
    $t->set('errors', $errors);

    return array(array(), $t->fetch());
}
?>
