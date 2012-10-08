<?
/*
 * Wrappers for db access using PDO
 */
require_once "utils.inc.php";

class DB {

  public function __construct() {
    $this->connect();
  }

  public function connect() {
    global $pdo;
    if($pdo === null) {
      $DB_SETTINGS = array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION );
      $pdo = new PDO("mysql:host=".$GLOBALS['DB_Connection'].";dbname=".$GLOBALS['DB_Database'], 
                     $GLOBALS['DB_User'], "", $DB_SETTINGS);
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
    try {
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
    catch(PDOException $ex) { // catch necessary because PDOException does not extend Exception
      throw new Exception("Database statement failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo()."[sql=$sql]");
    }
  }

  /**
   * Executes the given sql, binding the given parameters if passed.
   * Returns an array of PHP Objects containing the data returned.
   *
   * @throws Exception - for all database failures
   * @return array of objects, one for each row
   */
  public function query($sql, $parameters=array()) {
    try {
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
    catch(PDOException $ex) { // catch necessary because PDOException does not extend Exception
      throw new Exception("Database statement failed: ".$ex->getMessage()." Error Info: ".$pdo->errorInfo()."[sql=$sql]");
    }
  }

  /**
   * Executes the given sql, binding the given parameters if passed.
   * Returns the first row returned in the form of an object or
   * null if no rows returned from query.
   *
   * @throws Exception - for all database failures
   * @return array of objects, one for each row
   */
  public function first_row($sql, $params=array()) {
    $result = $this->query($sql,$params);
    if(count($result)<1)
      return null;
    else
      return $result[0];
  }

  public static function get() {
    return new DB();
  }
}
?>
