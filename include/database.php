<?php
require_once(LIB_PATH_INC."config.php");

class MySqli_DB {

  private $con;
  public $query_id;

  function __construct() {
    $this->db_connect();
  }

  /*--------------------------------------------------------------*/
  /* Function for Open database connection
  /*--------------------------------------------------------------*/
  public function db_connect()
  {
    $this->con = mysqli_connect(DB_HOST,DB_USER,DB_PASS);
    if (!$this->con) {
      die(" Database connection failed:". mysqli_connect_error());
    } 
    else {
      $select_db = $this->con->select_db(DB_NAME);
      if (!$select_db) {
        die("Failed to Select Database". " '". DB_NAME . "'".mysqli_connect_error());
      }
    }
  }
  /*--------------------------------------------------------------*/
  /* Function for Close database connection
  /*--------------------------------------------------------------*/

  public function db_disconnect()
  {
    if (isset($this->con)) {
      mysqli_close($this->con);
      unset($this->con);
    }
  }
  /*--------------------------------------------------------------*/
  /* Function for mysqli query
  /*--------------------------------------------------------------*/
  public function query($sql)
  {
    if (trim($sql != "")) {
      $this->query_id = $this->con->query($sql);
    }
    if (!$this->query_id) {
      //$err_msg = $this->get_last_error();
      //die("MySQL error: ".$err_msg);
      //$session->msg("d", $err_msg);
      return null;
    }

    return $this->query_id;
  }

  /** Retrieves the last error in the SQL consult
   *  ==============================================
   */
  public function get_last_error( ) {

    /* (new) objected oriented style */
    return $this->con->error;

    /* (old) procedural style */
    //return mysqli_error( $this->con );
  }

  /*--------------------------------------------------------------*/
  /* Function for Query Helper
  /*--------------------------------------------------------------*/
  public function fetch_array($my_result)    // mysqli_result
  {
    return mysqli_fetch_array($my_result);
  }
  public function fetch_object($my_result)
  {
    return mysqli_fetch_object($my_result);
  }
  public function fetch_assoc($my_result)
  {
    return mysqli_fetch_assoc($my_result);
  }
  public function num_rows($my_result)
  {
    return mysqli_num_rows($my_result);
  }
  public function insert_id()
  {
    return mysqli_insert_id($this->con);
  }
  public function affected_rows()
  {
    return mysqli_affected_rows($this->con);
  }
  /*--------------------------------------------------------------*/
  /* Function for Remove escapes special
  /* characters in a string for use in an SQL statement
  /*--------------------------------------------------------------*/
  public function escape($str) 
  {
    return $this->con->real_escape_string($str);
  }

  /*--------------------------------------------------------------*/
  /* Function for while loop
  /*--------------------------------------------------------------*/
  public function while_loop($mysql_result) 
  {
    $results = array();
    
    while ($result = $this->fetch_assoc($mysql_result)) {
      // append the current record to the array of results
      $results[] = $result;
    }
    return $results;
  }
}

$db = new MySqli_DB();
?>
