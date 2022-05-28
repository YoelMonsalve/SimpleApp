--
-- Current Database: `DemoApp`
--

USE `DemoApp`;

--
-- Table structure for table `samples`
--

DROP TABLE IF EXISTS `samples`;
CREATE TABLE `samples` (
	`SampleNumber` int unsigned NOT NULL PRIMARY KEY,
	`PipelineVersion` varchar(32),
	`Sequencer` varchar(32),
	`KnowledgebaseVersion` varchar(32),
	`DateGenerated` varchar(32)
);

DROP TABLE IF EXISTS `CurrentMedications`;
CREATE TABLE `CurrentMedications` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,    /* auto key */
	`SampleNumber` int unsigned,                     /* foreign key */
	`MedicationNo` int unsigned NOT NULL DEFAULT 1,  /* index into the array of medications */
	`DrugGeneric` varchar(32),                       /* generic name of the drug */
	`DrugTrade`   varchar(32),                       /* trade name of the drug */
	`TherapeuticArea` varchar(32),
	`GroupPhenotype` varchar(32),
	`Action` text,                                   /* up to 65,535 characters, https://dev.mysql.com/doc/refman/5.7/en/string-type-syntax.html */
	`Recommendation` text,

	UNIQUE KEY `uniref`(`SampleNumber`,`MedicationNo`),
	CONSTRAINT CurrentMedications_fk1 FOREIGN KEY (`SampleNumber`) REFERENCES `samples`(`SampleNumber`) ON DELETE CASCADE ON UPDATE CASCADE
);

DROP TABLE IF EXISTS `GeneInfo`;
CREATE TABLE `GeneInfo` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,    /* auto key */
	`SampleNumber` int unsigned,                     /* foreign key */
	`MedicationNo` int unsigned NOT NULL DEFAULT 1,
	`Gene`      varchar(64),
	`Genotype`  varchar(64),
	`Phenotype` varchar(64),

	CONSTRAINT GeneInfo_fk1 FOREIGN KEY (`SampleNumber`) REFERENCES `samples`(`SampleNumber`) ON DELETE CASCADE ON UPDATE CASCADE
);