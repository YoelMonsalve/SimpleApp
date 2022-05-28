<?php
namespace API;

/* This contains the API methods to handle the module customers
 * 
 * author:    Yoel Monsalve
 * date:      2022-03-30
 * modified:  2022-03-30
 *
 * (C) 2022 Yoel Monsalve. All rights reserved.
 */

require_once(dirname(__FILE__).'/sql/sql.php');
require_once(dirname(__FILE__).'/errors.php');

class API_medication {
  private $sql_object;

  public function __construct() {
    //$db = new MySqli_DB();
    global $db;
    $this->sql_object = new sql($db);
  }

  /**
   * Get all the medications recorded in the DB
   *
   * @return  array  Array of records, each corresponding to the data for a medication.
   */
  public function findMedications() 
  {  
    $results = array();
    $result = $this->sql_object->findAll(
      "CurrentMedications", 
      [                           // this is a list of fields we want to be retrieved
        "SampleNumber", 
        "MedicationNo",
        "DrugGeneric",
        "DrugTrade",
        "TherapeuticArea",
        "Action",
        "GroupPhenotype",
        "Recommendation"
      ]
    );

    return $result;
  }

  /**
   * Looks for the GeneInfo associated with a specific ($SampleNumber, $MedicationNo)
   *
   * @param   int     $SampleNumber  [description]
   * @param   int     $MedicationNo  [description]
   * @return  [type]                 [description]
   */
  public function findGeneInfo(int $SampleNumber, int $MedicationNo) 
  {
    $results = array();

    /* preventing against SQL-injection */
    $SampleNumber = $this->sql_object->escape(strval($SampleNumber));
    $MedicationNo = $this->sql_object->escape(strval($MedicationNo));

    $sql =  "SELECT `Gene`,`Genotype`,`Phenotype` FROM `GeneInfo`";
    $sql .= " WHERE `SampleNumber`=${SampleNumber} AND `MedicationNo`=${MedicationNo}";

    $results = $this->sql_object->findBySql($sql);
    return $results;
  }
}
?>