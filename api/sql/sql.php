<?php
namespace API;

/** 
 * This script is automatically called by 'load.php'
 *
 * SQL_PHP
 * Common functions that interact with the MySQL Database.
 *
 * Author  : Yoel Monsalve
 * Date    : 2021-03-15
 * Modified: 2021-03-15
 *
 * (C) 2022 Yoel Monsalve. All rights reserved.
 */
require_once(dirname(__FILE__).'/../../include/load.php');

class sql {

  private $db;

  public function __construct($the_db) {
    $this->db = $the_db;
  }

  /**
   * Get the internal database object
   *
   * @return  [class database]  the Database
   */
  public function getDb() 
  {
    return $this->db;
  }

  /**
   * Escape method: to prevent against SQL-injection
   */
  public function escape($str) 
  {
    if (is_string($str)) 
      return $this->db->escape($str);
    else
      return null;
  }

  /**
   * True in the table exists for the current database
   *
   * @param   string  $table  the table name
   * @return  bool            true iff the table exists
   */
  //function tableExists($table)         PHP5 or older
  public function tableExists(string $table_name)
  {
    $db = $this->db; 
    $r = $db->query('SHOW TABLES FROM `'.DB_NAME.'` LIKE "' . $db->escape($table_name).'"');
    if (!is_null($r) && $db->num_rows($r) > 0) 
      return TRUE;
    else
      return FALSE; 
  }

  /**
   * Retrieves the results of a SQL query, as an associative array
   *
   * @param   string  $sql  the query
   * @return  array
   */
  public function findBySql($sql): array
  {
    $my_result = $this->db->query($sql);
    $result_set = $this->fetchAll($my_result);
    return $result_set;
  }

  /**
   * Retrieves the result of a query (as returned by find_by_sql), in the 
   * form of an array of associative arrays, each of them corresponding
   * to a record in the set.
   *
   * @param   \mysqli_result  $mysql_result  The mysqli_result
   * @return  array                         Array of records
   */
  public function fetchAll($mysql_result) 
  {
    $results = array();
    
    while ($result = $this->db->fetch_assoc($mysql_result)) {
      // append the current record to the array of results
      $results[] = $result;
    }
    return $results;
  }

  /**
   * Retrieve a record in the table by ids ID.
   *
   * @param   string  $table_name  The table name
   * @param   int     $id          The ID for the record found
   * @return  array                The record, as an associative array
   */
  public function findById($table_name, $id): ?array
  {
    if (!is_numeric($id) || !is_integer($id)) return NULL;
    $id = (int)$id;
    $db = $this->db;

    if ($this->tableExists($table_name)) {
      $sql  = "SELECT * FROM `" . $db->escape($table_name) . "`";
      $sql .= " WHERE `id`=".$id;
      $sql .= " LIMIT 1";
      $sql_result = $db->query($sql);
      if( $sql_result && $result = $db->fetch_assoc($sql_result) )
        return $result;
      else
        return NULL;
    }
    else {
      return NULL;
    }
  }

  public function findById2($table_name, $id, $fields)
  {
    if (!is_numeric($id) || !is_integer($id)) return NULL;
    if (!is_array($fields)) return NULL;
    if (empty($fields)) return [];

    $id = (int)$id;
    $db = $this->db;

    if ($this->tableExists($table_name)) {

      foreach ($fields as &$k) {
        // anti-code-injection: filter only elements of type string
        if (!is_string($k)) continue;
        $k = "`" . $db->escape($k) . "`";
      }

      $sql  = "SELECT "
            . implode(",", $fields)
            . " FROM `" . $db->escape($table_name) . "`"
            . " WHERE `id`=".$id
            . " LIMIT 1";

      $sql_result = $db->query($sql);
      if( $sql_result && $result = $db->fetch_assoc($sql_result) )
        return $result;
      else
        return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * Retrieve a record in the table by unique key.
   *
   * @param   string  $table_name  The table name
   * @param   string  $key_name    The name of a (UNIQUE) key
   * @param   int     $key_val     The value (integer) for the primary key
   * @return  array                The record, as an associative array
   */
  public function findByKey(string $table_name, string $key_name, int $key_val): ?array
  {
    $db = $this->db;
    $key_name = $db->escape($key_name);

    if ($this->tableExists($table_name)) {
      $sql  = "SELECT * FROM `" . $db->escape($table_name) . "`";
      $sql .= " WHERE =`{$key_name}`={$key_val}";
      $sql .= " LIMIT 1";
      $sql_result = $db->query($sql);
      if( $sql_result && $result = $db->fetch_assoc($sql_result) )
        return $result;
      else
        return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * Retrieve a record in the table by unique key.
   *
   * @param   string  $table_name  The table name
   * @param   string  $key_name    The name of a (UNIQUE) key
   * @param   int     $key_val     The value (integer) for the primary key
   * @param   array   $fields      Arrays of string for specifying a list of columns to be retrieved only
   * @return  array                The record, as an associative array
   */
  public function findByKey2($table_name, $key_name, $key_val, $fields): ?array
  {
    if (!is_string($key_name)) return NULL;
    if (!is_numeric($key_val) || !is_integer($key_val)) return NULL;
    if (!is_array($fields)) return NULL;
    if (empty($fields)) return [];

    $key_val = (int)$key_val;
    $db = $this->db;

    if ($this->tableExists($table_name)) {

      foreach ($fields as &$k) {
        // anti-code-injection: filter only elements of type string
        if (!is_string($k)) continue;
        $k = "`" . $db->escape($k) . "`";
      }

      $sql  = "SELECT "
            . implode(",", $fields)
            . " FROM `" . $db->escape($table_name) . "`"
            . " WHERE `{$key_name}`={$key_val}"
            . " LIMIT 1";
      
      $sql_result = $db->query($sql);
      if( $sql_result && $result = $db->fetch_assoc($sql_result) )
        return $result;
      else
        return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * Retrieve all records in the table.
   *
   * @param   string  $table_name  The table name
   * @param   array   $fields      Arrays of string for specifying a list of columns to be retrieved only
   * @return  array                The record, as an associative array
   */
  public function findAll(string $table_name, array $fields=[]): ?array
  {
    if (!is_array($fields)) return NULL;
    if (empty($fields)) return [];

    $db = $this->db;

    if ($this->tableExists($table_name)) {

      foreach ($fields as &$k) {
        // anti-code-injection: filter only elements of type string
        if (!is_string($k)) continue;
        $k = "`" . $db->escape($k) . "`";
      }

      $sql  = "SELECT "
            . implode(",", $fields)
            . " FROM `" . $db->escape($table_name) . "`";

      $sql_result = $db->query($sql);
      if( $result = $this->fetchAll($sql_result) )
        return $result;
      else
        return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * Delete a record, given the value of its unique key.
   *
   * @param   string  $table_name  The table name
   * @param   int     $id          The ID for the record found
   * @return  array                The record, as an associative array
   */
  public function deleteByKey(
    string $table_name, 
    string $key_name, 
    int    $key_val): ?array
  {
    if (!is_string($key_name)) return NULL;
    if (!is_numeric($key_val) || !is_integer($key_val)) return NULL;

    $key_val = (int)$key_val;
    $db = $this->db;

    if ($this->tableExists($table_name)) {
      $sql  = "DELETE FROM `" . $db->escape($table_name) . "`";
      $sql .= " WHERE `{$key_name}`=$key_val";

      $sql_result = $db->query($sql);
      if( $result = $db->fetch_assoc($sql_result) )
        return $result;
      else
        return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * Count the number of records in table
   *
   * @param   string  $table_name  The table name
   * @return  int                  Number of records in table
   */
  function countRecords($table_name): ?int
  {
    $db = $this->db;
    if($this->tableExists($table_name))
    {
      $sql = "SELECT COUNT(*) AS total FROM `" . $db->escape($table_name) . "`";
      $sql_result = $db->query($sql);
      return intval($db->fetch_assoc($sql_result)["total"]);
    }
    else
      return NULL;
  }
}
