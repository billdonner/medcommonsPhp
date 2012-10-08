<?php

require 'login.inc.php';
require 'urls.inc.php';
require 'settings.php';

$mcid = login_required('backup_01.php');
$service = $_REQUEST['service'];

if ($service == 's3')
  $t = template($acTemplateFolder . 'backups/03_s3.tpl.php');
else
  $t = template($acTemplateFolder . 'backups/03_url.tpl.php');

$t->set('s3_user', 'cmo@medcommons.net');
$t->set('service', $service);

echo $t->fetch();

?>
