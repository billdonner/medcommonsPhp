<?
/*
 * Wrappers for db access using PDO
 */
require_once "settings.php";
require_once "utils.inc.php";

class DB {

  public function __construct() {
    $this->connect();
  }

  public function connect() {
    global $pdo,$CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS;
    if($pdo === null) {
      $pdo = new PDO($CENTRAL_PDO, $CENTRAL_USER, $CENTRAL_PASS, $DB_SETTINGS);
      $pdo->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo; 
  }
  
   /**
    * Executes the given insert / update, throwing an exception if there is
    * any kind of failure.
    *
    * @param sql - sql string containing question marks (?) for bind parameters
    * @param params - optional array of parameters, one for each ? in the sql
    *
    * @throws Exception - for all database failures
    * @return - the id of inserted row (if any)
    */
  public function execute($sql, $params = array()) {
    global $pdo;

    $pdo = $this->connect();


    dbg("SQL: $sql (".implode(",",$params).")");

    $s = $pdo->prepare($sql);
    if(!$s) {
      throw new Exception("Failed to prepare sql [$sql]");
    }
    
    if(!$s->execute($params)) {
      throw new Exception("Failed to execute sql [$sql] with params (".var_dump($params).")");
    }
    return $pdo->lastInsertId();
  }

  /**
   * Executes the given sql, binding the given parameters if passed.
   * Returns an array of PHP Objects containing the data returned.
   *
   * @throws Exception - for all database failures
   * @return array of objects, one for each row
   */
  public function query($sql, $parameters=array()) {
    global $pdo;
    $pdo = $this->connect();
    $s = $pdo->prepare($sql);
    if(!$s)
     throw new Exception("query $sql failed with Error Info: ".$pdo->errorInfo());

    dbg("SQL: $sql (".implode(",",$parameters).")");
    $index = 1;
    foreach($parameters as $p) {
      // NOTE: do NOT bind $p, it's bound by reference
      // you will lose several hours of your life figuring out
      // why it doesn't work
      $s->bindParam($index,$parameters[$index-1]);
      $index++;
    }

    $results = array();
    if($s && $s->execute()) {
      while($r = $s->fetch(PDO::FETCH_OBJ)) {
        $results[]=$r;
      }
    }
    else 
     throw new Exception("query $sql failed with Error Info: ".$pdo->errorInfo());
    
    return $results;
  }


  function begin_tx() {
    global $pdo;
    try {
      $pdo = $this->connect();
      $pdo->beginTransaction();
    }
    catch(PDOException $ex) {
      error_log("begin_tx failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
      throw new Exception("Database begin_tx failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    }
  }

  function commit() {
    global $pdo;
    try {
      $pdo = $this->connect();
      $pdo->commit();
    }
    catch(PDOException $ex) {
      error_log("commit failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
      throw new Exception("Database statement failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    }
  }

  function rollback() {
    global $pdo;
    try {
      $pdo = $this->connect();
      $pdo->rollback();
    }
    catch(PDOException $ex) {
      error_log("rollback failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
      throw new Exception("Database statement failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo());
    }
  }

  public static function get() {
    return new DB();
  }
}
?>
