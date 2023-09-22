<?php

require_once dirname(__FILE__).'/sql.php';

function test1() 
{
  global $db;
  $sql = new \API\sql($db);

  $table_name = "samples";

  /**
   * This query will look for any record into table `samples`
   * where `SampleNumber`=12345, and will return values for columns
   * `pipelineVersion` and `sequencer` only.
   */
  $r = $sql->findByKey2($table_name, "SampleNumber", 12345, ["pipelineVersion", "sequencer"]);
  
  var_dump($r);
  echo "Total of records in `{$table_name}`: {$sql->countRecords($table_name)}\n";
}

test1();

?>