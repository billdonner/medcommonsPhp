<?
  require_once "JSON.php";
  require_once "utils.inc.php";
  require_once "../alib.inc.php";

  nocache();

  $result = new stdClass;
  try {
    $query = req('query');
    $groups = pdo_query("select name, accid from groupinstances where name like concat('%',?,'%')",array($query));
    $json = new Services_JSON();
    $result->status = "ok";
    $result->groups = $groups;
  }
  catch(Exception $ex) {
    $result->status = "failed";
    $result->error = $ex->getMessage();
  }
  echo $json->encode($result);
?>
