<? 
  /*
   * Read a user's theme and return it dynamically
   */

  $theme = $_COOKIE['theme'];
  if($theme=="") { 
    $theme = 'lightgray'; // default
  }
  $file=file_get_contents($theme.'.css');
  header("Content-Type: text/css;charset=UTF-8");
  echo "$file";
?>
