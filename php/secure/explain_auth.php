<?
  require_once "utils.inc.php";
  require_once "securelib.inc.php";

  if(isset($_POST['explain'])) {
    $guid = $_POST['guid'];
    $auth = $_POST['auth'];
?>
  <html>
    <head>
      <style type='text/css'>
        .documentmatch {
          background-color: yellow;
        }
      </style>
    </head>
    <body>
    <h3>Guid <?=$guid?> and Auth <?=$auth?></h3>
    <hr/>
<?
    // First lets see if the auth token exists
    dbconnect();
    $r = mysql_query("select * from authentication_token where at_token = '$auth'");

    if(mysql_num_rows($r) == 0) {
      echo "<p><b>The supplied authentication token does not exist on this system.</b></p></body></html>";
      exit;
    }
    else
    while($at = mysql_fetch_object($r)) {
      $ats[]=$at;
    }

    echo "<h4>Document Details</h4>";
    $r = mysql_query("select d.*, l.node_node_id from document d, document_location l where d.guid = '$guid' and l.document_id = d.id");
    if(mysql_num_rows($r) == 0) {
      echo "<p><b>The specified document does not exist on any nodes in this system.</b></p></body></html>";
      exit;
    }

    $document_ids = array();
    echo "<table border=1><tr><th>Document Id</th><th>Storage Account</th><th>Node</th></tr>";
    while($doc = mysql_fetch_object($r)) {
      echo "<tr><td>{$doc->id}</td><td>{$doc->storage_account_id}</td><td>{$doc->node_node_id}</tr>";
      $document_ids[]=$doc->id;
    }
    echo "</table>";

    echo "<h4>Authentication Token Scope</h4>
            <table border=1 cellspacing=0 cellpadding=2><tr><th>Account</th><th>External Share</th><th>Rights</th></tr>";

    foreach($ats as $at) {
      echo "<tr><td>".($at->at_account_id ? $at->at_account_id : "n/a")."</td>";
      echo "<td>".($at->at_es_id ? $at->at_es_id: "n/a")."</td><td>";

      // Find rights for this entry
      if($at->at_account_id) {
        $r = mysql_query("select * from rights where account_id = {$at->at_account_id} and active_status = 'Active'");
      }
      else
      if($at->at_es_id) {
        $r = mysql_query("select * from rights where es_id = {$at->at_es_id} and active_status = 'Active'");
      }
      $first = true;
      while($right = mysql_fetch_object($r)) {
        if($first) 
          $first = false;
        else
          echo "<br/>";

        $clazz = "";
        if(in_array($right->document_id,$document_ids)) {
          $clazz = "documentmatch";
        }
        echo $right->rights." access to ".($right->storage_account_id ? " Account ".$right->storage_account_id : "<span class='$clazz'>Document  ".$right->document_id."</span>");
      }
      
      echo "&nbsp;</td></tr>";
    }
    echo "</table>";

  }
  else {
    $auth = "";
    if(isset($_COOKIE['mc']))
      $auth = get_auth();
?>
  <html>
    <body>
      <h3>Enter guid and auth to be explained:</h3>
      <form method='post'>
      <table>
        <tr><th>guid</th><td><input type='text' name='guid' size='40'/></td></tr>
        <tr><th>auth</th><td><input type='text' name='auth' size='40'/></td></tr>
        <tr><th>&nbsp;</th><td><input type='submit' name='explain' value='Explain'/></td></tr>
      </table>
      </form>
      <?if($auth != ""):?>
      <p>Your current authentication token is <b><?=$auth?></b></p>
      <?endif;?>
    </body>
  </html>
<?
  }
?>

