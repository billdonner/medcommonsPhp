<?php

require 'login.inc.php';
require 'settings.php';

$mcid = login_required('settings.php');


if (isset($_FILES['picture'])) {
  $map = $_FILES['picture'];

  $content_type = $map['type'];
  $tmp_name = $map['tmp_name'];

  if ($content_type == 'image/jpeg')
    $img = imagecreatefromjpeg($tmp_name);
  else if ($content_type == 'image/gif')
    $img = imagecreatefromgif($tmp_name);
  else if ($content_type == 'image/png')
    $img = imagecreatefrompng($tmp_name);
  else 
    redirect('settings.php');

  $url = "/user-icons/200x240/{$mcid}.jpg";

  // currently saves as about ~8 kilobyte image file
  if (save_as_jpeg($img, 200, 240,
                   $_SERVER['DOCUMENT_ROOT'] . $url,
                   50)) {
    $db = new PDO($IDENTITY_PDO, $IDENTITY_USER, $IDENTITY_PASS);
    $stmt = $db->prepare("UPDATE users SET photoUrl=:url WHERE mcid=:mcid");
    $stmt->execute(array("url" => $url, "mcid" => $mcid));
  }
  // run /var/www/console/bin/mc-permissions to create this directory

  save_as_jpeg($img, 50, 60, $_SERVER['DOCUMENT_ROOT'] . "/user-icons/{$mcid}.jpg", 85);
}

function save_as_jpeg($img, $icon_w, $icon_h, $filename, $quality) {
  $w = imagesx($img);
  $h = imagesy($img);

  if ($icon_h * $w > $icon_w * $h) {
    /* source image is wider than 40/60 aspect ratio */
    $src_y = 0;
    $src_h = $h;

    /* correct aspect ratio */
    $src_w = ($h * $icon_w) / $icon_h;

    /* pull from center */
    $src_x = ($w - $src_w) / 2;
  }
  else {
    /* source image is taller than (or equal to) 40/60 aspect ratio */
    $src_x = 0;
    $src_w = $w;

    /* correct aspect ratio */
    $src_h = ($w * $icon_h) / $icon_w;

    /* pull from center */
    $src_y = ($h - $src_h) / 2;
  }

  $icon = imagecreatetruecolor($icon_w, $icon_h);
  imagecopyresampled($icon, $img,
                   0, 0,              /* dst x, dst y */
                   $src_x, $src_y,    /* src x, src y */
                   $icon_w, $icon_h,  /* dst w, dst h */
                   $src_w, $src_h);

  return imagejpeg($icon, $filename, $quality);
}

// It's necessary to re-login because the photo state is hashed into the mc cookie 
// and we want pages relying on that to be up-to-date
$user = User::load($mcid);
$user->login('settings.php');

?>
