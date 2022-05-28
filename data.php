<?php

/**
 * This is to parse info from the JSON data file
 *
 * Author   : Yoel Monsalve.
 * Date     : 2022-05-26
 * Version  : ---
 * Modified : 2022-05-26
 */

/* load libraries */
require_once(dirname(__FILE__).'/include/load.php');    
require_once(dirname(__FILE__).'/api/sql/sql.php');
require_once(dirname(__FILE__).'/api/medication.php');

/* POST/REST API */
if (isset($_POST['filename'])) {

	$path = $_POST['filename'];
	$data = parse($path);
	if (!$data) { exit(1); }
	saveDB($data);

	/* return JSON-encoded data, read from the DB (not the file) */
	$data_from_db = readDB();
	print(json_encode($data_from_db));
	exit(0);
}

/**
 * Parses a JSON file
 *
 * @param   [string]  $path  path to the file
 * @return            an associative array representing the JSON data, or NULL
 *                    if an error occurred.
 */
function parse($path) {

	/* raw string */
	$data_str = file_get_contents($path);
	if ($data_str === false) {
		print(json_encode(array("error" => "Unable to read file: '{$path}'")));
		return NULL;
	}

	/* JSON parsed */
	$data = json_decode($data_str, true);
	if ($data === null) {
		print(json_encode(array("error" => "Unable to parse JSON file: '{$path}'")));
		return NULL;
	}
	
	return $data;
}

/**
 * Read the information about medications stored in the DB.
 *
 * @return  [JSON]  Array of medications. Each element in the array will correspond
 *                  with a big-row in the table.
 */
function readDB() {

/* 
The structure of the retrieved data should look like

Array
	(
	    [0] => Array
	        (
	            [SampleNumber] => 12345
	            [MedicationNo] => 1
	            [DrugGeneric] => Acetaminophen
	            [DrugTrade] => Tylenol
	            [TherapeuticArea] => Pain Management
	            [Action] => CAUTION
	            [GroupPhenotype] => Decreased Efficacy
	            [Recommendation] => Patients with this genotype are expected to have a Poor response to Tylenol. Physicians should follow FDA label recommendations.
	            [GeneInfo] => Array
	                (
	                    [0] => Array
	                        (
	                            [Gene] => FakeGene1
	                            [Genotype] => WT/WT
	                            [Phenotype] => Normal Metabolizer
	                        )

	                    [1] => Array
	                        (
	                            [Gene] => FakeGene2
	                            [Genotype] => WT/WT
	                            [Phenotype] => Poor Metabolizer
	                        )

	                )

	        )

	)
*/


	global $db;

	/** 
	 * @var [API_medication] Using a custom-defined API, to illustrate the concept
	 *                       of reusable code.
	 */
	$api = new \API\API_medication();

	$rs = $api->findMedications();    // rs: 'records-set', each item correspondig to a row
	$Medications = [];
	if ($rs) {
		foreach ($rs as $row) {
			$med = [];
			foreach ($row as $k => $v) {
				$med[$k] = $v;
			}

			/* loading GeneInfo */
			$SampleNumber = isset($med['SampleNumber']) ? $med['SampleNumber'] : null;
			$MedicationNo = isset($med['MedicationNo']) ? $med['MedicationNo'] : null;

			if ($SampleNumber && $MedicationNo) {
				$GeneInfo = $api->findGeneInfo($SampleNumber, $MedicationNo);
			}
			$med['GeneInfo'] = $GeneInfo;

			$Medications[] = $med;
		}
	}

	return $Medications;
}

/**
 * Stores the information into DB
 *
 * @param   [JSON]  $data  the JSON data, as associative array.
 * @return  [boolean]      true/false to indicate the status of the transaction.
 */
function saveDB($data = NULL) {

	global $db;

	if (!$data) return false;

	/* Sample 
	 * ========================== */
	$SampleNumber    = isset($data['SampleNumber']) ? $data['SampleNumber'] : null;
	$PipelineVersion = isset($data['PipelineVersion']) ? $data['PipelineVersion'] : null;
	$Sequencer       = isset($data['Sequencer']) ? $data['Sequencer'] : null;
	$KnowledgebaseVersion = isset($data['KnowledgebaseVersion']) ? $data['KnowledgebaseVersion'] : null;
	$DateGenerated   = isset($data['DateGenerated']) ? $data['DateGenerated'] : null;
	
	/* build query */
	$query  = "INSERT INTO `samples` (";
	$query .= "`SampleNumber`,`PipelineVersion`,`Sequencer`,`KnowledgebaseVersion`,`DateGenerated`";
	$query .= ") VALUES (";
	$query .= "${SampleNumber}, '${PipelineVersion}','${Sequencer}','${KnowledgebaseVersion}','${DateGenerated}'";
	$query .= ")";

	//print($query."\n");

	/* and execute */
	if (!$db->query($query)) {
		//failed
		//print("{$db->get_last_error()}\n");    // prompt error
		//return false;
	}

	/* Current Medications
	 * ========================== */
	$CurrentMedications = isset($data['CurrentMedications']) ? $data['CurrentMedications'] : null;
	if ($CurrentMedications) {
		$idx = 1;
		foreach($CurrentMedications as $medication) {
			
			$MedicationNo = $idx++;

			/* Gene info
	 		 * ========================== */
			$GeneInfo = isset($medication['GeneInfo']) ? $medication['GeneInfo'] : null;

			/* check if this info was already uploaded (for avoiding duplication) */
			$sql =  "SELECT COUNT(*) AS total FROM `CurrentMedications`";
			$sql .= " WHERE `SampleNumber`=${SampleNumber} AND `MedicationNo`=${MedicationNo}";
      		$sql_result = $db->query($sql);
      		/** if $n_records > 0, then genes for this medication were already uploaded */
      		if ($sql_result) {
      			$n_genes = intval($db->fetch_assoc($sql_result)["total"]);
      		}
      		else
      			$n_genes = 0;

			if ($GeneInfo && $n_genes == 0) {
				foreach ($GeneInfo as $ginfo) {
					$Gene      = isset($ginfo['Gene']) ? $ginfo['Gene'] : null;
					$Genotype  = isset($ginfo['Genotype']) ? $ginfo['Genotype'] : null;
					$Phenotype = isset($ginfo['Phenotype']) ? $ginfo['Phenotype'] : null;

					$query  = "INSERT INTO `GeneInfo` (";
					$query .= "`SampleNumber`,`MedicationNo`";
					$query .= ",`Gene`,`Genotype`,`Phenotype`";
					$query .= ") VALUES (";
					$query .= "${SampleNumber}, ${MedicationNo}";
					$query .= ", '${Gene}', '${Genotype}', '${Phenotype}'";
					$query .= ")";

					//print($query."\n");

					/* and execute */
					if (!$db->query($query)) {
						//failed
						print("{$db->get_last_error()}\n");    // prompt error
						return false;
					}					
				}
			}

			$Drugs = isset($medication['Drugs']) ? $medication['Drugs'] : null;
			if ($Drugs) {
				$DrugGeneric = $Drugs[0]['Generic'][0];
				$DrugTrade   = $Drugs[0]['Trade'][0];
			}
			$TherapeuticArea = isset($medication['TheraputicArea']) ? $medication['TheraputicArea'][0] : null;
			$GroupPhenotype = isset($medication['GroupPhenotype']) ? $medication['GroupPhenotype'] : null;
			$Action = isset($medication['Action']) ? $medication['Action'][0] : null;
			$Recommendation = isset($medication['Recommendation']) ? $medication['Recommendation'] : null;

			/* build query */
			$query  = "INSERT INTO `CurrentMedications` (";
			$query .= "`SampleNumber`,`MedicationNo`";
			$query .= ",`DrugGeneric`,`DrugTrade`";
			$query .= ",`TherapeuticArea`,`GroupPhenotype`,`Action`,`Recommendation`";
			$query .= ") VALUES (";
			$query .= "${SampleNumber}, ${MedicationNo}";
			$query .= ",'${DrugGeneric}','${DrugTrade}'";
			$query .= ",'${TherapeuticArea}', '${GroupPhenotype}', '${Action}', '${Recommendation}'";
			$query .= ")";

			//print($query."\n");

			/* and execute */
			if (!$db->query($query)) {
				//failed
				//print("{$db->get_last_error()}\n");    // prompt error
				//return false;
			}
		}
	}
	
	//success
	return true;
}

/**
 * Clear all data from the DB
 *
 * @return  [boolean]      true/false to indicate the status of the transaction.
 */
function clearData() {

	global $db;

	$query  = "DELETE FROM `GeneInfo`";
	if (!$db->query($query)) {
		//failed
		//print("{$db->get_last_error()}\n");    // prompt error
		//return false;
	}
	$query  = "DELETE FROM `CurrentMedications`";
	if (!$db->query($query)) {
		//failed
		//print("{$db->get_last_error()}\n");    // prompt error
		//return false;
	}
	$query  = "DELETE FROM `samples`";
	if (!$db->query($query)) {
		//failed
		//print("{$db->get_last_error()}\n");    // prompt error
		//return false;
	}

	//success
	return true;
}

function test() {
	
	$DIR = "./data";
	$filename = "FakeSample.json";

	$data = parse("{$DIR}/${filename}");
	print_r($data);

	if ($data) {
		saveDB($data);
	}
	else {
		return;
	}
	
	$data = readDB();
	print_r($data);
}

//test();
clearData();