<?php

require_once(dirname(__FILE__).'/sql.php');

function test1() 
{
  global $db;
  $sql = new \API\sql($db);

  $table_name = "samples";

  $r = $sql->findById($table_name, 12345);
  //$r = $sql->findByKey2($table_name, "SampleNumber", 1, ["name", "username"]);
  var_dump($r);

  echo "Total of records in `{$table_name}`: {$sql->countRecords($table_name)}\n";
}

test1();

?>