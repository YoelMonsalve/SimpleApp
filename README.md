# README

## Overview

This is a Demo App, for interview.

**Author**: Yoel Monsalve.

**Date**:   May 22th, 2022.

## Requisites

Some PHP modules are required to this app, like `json` and `mysql`. On UNIX-like systems, type
```
sudo update
sudo apt install php-mysql php-json
```

On Windows systems, consult your documentation of XAMPP/WAMPP to see how to install them.

## Introduction

The purpose of this task was to read some data (related to medications) from a JSON-like file (see `data/FakeSample.json`), write it to a Database, then build a system capable to display a home page, presenting the data on a stylized table.

It is also desirable to code sort of a simple API for the website to manage data (see folder `api` and its modules.).

## Installation

**1.** Copy the content of this repository into the host folder for your server. On Windows systems a good choice for that destination folder might be `C:\xampp\SimpleApp`, whereas the same for UNIX-like systems would be `/var/www/html/SimpleApp`.

**2.** On UNIX-like systems, it could also be necessary to set up permissions (`755` for directories, and `644` for plain files). 
The script `configure` allows you to automatically do this:
```bash
cd <path/to/host>
sudo chmod u+x configure
sudo ./configure
```

If you want to proceed manually instead, do just
```bash
find . -type f ! -name "configure" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

Also, change the group of folder `./data` to `www-data` and grant it `read/write` permissions, in order to make sure that PHP is able to access and modify any file in that folder.
```bash
sudo chgrp -R www-data data
chmod -R g+rw data
```

**3.** Set Database. Some scripts are provided to achieve this. As MySQL root user, execute:
```bash
mysql -uroot -p < path/to/host/scripts/setup.sql
```
This will create a database and a user, which will be used for the application. It is always a good idea to create a separate MySQL user, so that the application can act on behalf of him (instead of proceding with `root` user, what is a *very bad* idea for security reasons).

```bash
mysql -uroot -p < path/to/host/scripts/database.mysql
```

This will create the tables, and you are ready to work.

Once done with the application, if you want to return the system to a clean state, try:

```bash
mysql -uroot -p < path/to/host/scripts/clean.sql
```

This will destroy the database and user.

**4.** Make sure the file `FakeSample.json` is into the folder `data`, and that it is well-formed. At this point, you should be able to show the page in your browser, by typing

```
localhost/SimpleApp
```

in the address bar. Change the `json` file in the folder `data` and reload the page to see changes.

<img alt="Screenshot-HomePage-1" src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/HomePage-1.png" width="600">

<img alt="Screenshot-HomePage-2" src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/HomePage-2.png" width="600">

<img alt="Screenshot-Mobile-Collapsed"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Mobile-collapsed.png" width="300">

<img alt="Screenshot-Mobile-Expanded"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Mobile-expanded.png" width="300">

### How it is made

This App reads a JSON file, then stores the information parsed in a `SQL` database. Then, reads the info and shows it on a simple but stylized webpage, this way offering a comfortable view to users. I used some CSS/Bootstrap styles to achieve that.

#### Data

The first step, is to structure the information using tables. My proposal to deal with data contained in the file, was creating three tables: `samples`, `CurrentMedications` and `GeneInfo`:

```
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

CREATE TABLE `GeneInfo` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,    /* auto key */
	`SampleNumber` int unsigned,                     /* foreign key */
	`MedicationNo` int unsigned NOT NULL DEFAULT 1,
	`Gene`      varchar(64),
	`Genotype`  varchar(64),
	`Phenotype` varchar(64),

	CONSTRAINT GeneInfo_fk1 FOREIGN KEY (`SampleNumber`) REFERENCES `samples`(`SampleNumber`) ON DELETE CASCADE ON UPDATE CASCADE
);
```

The field types and keys were conveniently defined to work with the nature of the data. It was supposed that `SampleNumer` is an *integer*, and that this number is unique for each row. Therefore, I used it as the primary key for the table `samples`. Note that this brings in some restrictions to our system that rather serve to make it robust. For example, database will reject any attemp to introduce a `SampleNumber` that is not numeric, or repeated. Same if we try to introdue a medication, or gene-info, that is not correlated with a valid sample (this is the pretended purpose of protecting tables with foreign keys). 

Note that this verification is made at *database level*, not at a `PHP` level, meaning that it would be really difficult to introduce corruption or discrepancies into the data, even if the backend code is unintentionally broken.

#### JSON parsing

Parsing JSON data is a quite simple using the `json_encode()` PHP function:
```php
$data_str = file_get_contents($path);
if ($data_str === false) {
	// error reading ...
	return NULL;
}

/* JSON parsed */
$data = json_decode($data_str, true);
```

The `data.php` file contains all the method to handle data. 

function | meaning
---------|----------
`parse()`    | to read and parse data from JSON file
`readDB()`   | to read from database
`saveDB()`   | to store info into database
`clearData()` | to clear all info stored into database

It is important to mention that the info shown in the page is *NOT* coming directly from the file. Instead, the system first stores it into database, then reads from DB to show in the browser. So, theoretically the system should be able to preserve the info even if the file is deleted.
**However**, you cannot appreciate this effect by the reason explained below.

If you read through the home page `index.php`, from line 17:

```php
  /* load data on page reloading */
  $path = './data/FakeSample.json';         // <-- datafile name
  $data = parse($path);
  if (!$data) { 
    die("<h2>Ups! Unable to read data from file '${path}'. Badformed, or it does not exist.<h2>"); 
  }
  clearData();      // clean DB
  saveDB($data);    // store info from file

  /* return JSON-encoded data, read from the DB (not the file) */
  $data = readDB();
  $data = $data[0];       // take the first element only
 ```

it can be appreciated that we first load the file, then **clean** the database, and then store the info coming from the file. This implies that if you change the file, by simply reloading the page you can see the changes and show the new file. However, old database is missed so that you cannot appreciate the data persistence. 

*Of course*, this is not the ideal behavior, but as it is just a sample, I want to keep it simple.

An alternative design would have been using buttons to enable user for manually controlling when a new file is to be uploaded, ... but that would be a more complicated architecture.

#### Modularizing and reusable code

When building this app, I also wanted to illustrate the concept of preferring modular design and reusable code, to keep the project ordered and easier to expand. In order to that, a class was defined in `api/sql/sql.php` (in its own namespace `\API`) to keep all the basic functions related with SQL management (a kind of small framework), and the script `api/medication.php` contains methods to consult medications and gene info.

```php
namespace API;

/** 
 * This script is automatically called by 'load.php'
 *
 * SQL.PHP
 * Common functions that interact with the MySQL Database.
 *
 * Author  : Yoel Monsalve
 * Date    : 2021-03-15
 * Modified: 2023-09-22
 *
 * (C) 2022 Yoel Monsalve. All rights reserved.
 */
require_once dirname(__FILE__).'/../../include/load.php';

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
  public function escape($str): ?string 
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
  public function tableExists(string $table_name): bool 
  {
    $db = $this->db; 
    $r = $db->query('SHOW TABLES FROM `'.DB_NAME.'` LIKE "' . $db->escape($table_name).'"');
    if (!is_null($r) && $db->num_rows($r) > 0) 
      return TRUE;
    else
      return FALSE; 
  }
  ...
```

#### Test scripts

Some code to test classes was added. For example, to test the performing of a `SQL` query through the class `\API\sql`, type on a terminal:

```bash
$ php -f api/sql/test_sql.php
```

Answer:
```
array(2) {
  ["pipelineVersion"]=>
  string(5) "0.0.1"
  ["sequencer"]=>
  string(10) "iontorrent"
}
Total of records in `samples`: 1

```

#### Stylizing

Some Boostrap styles were added to the page to get a nice appearance, and other CSS rules were defined in `css/table.css` file to manage the effects of the table. 
```css
.table-drug{
	border-top: 2px solid black;
	border-bottom: 2px solid black;
    border-collapse: collapse;
    letter-spacing: 1px;
    font-family: sans-serif;
}
...
```
Look at the file `css/table.css` for more details. Nicely, and because of using Bootstrap, the page is made responsive and looks well on all screens (phones or tablets).

Colors were taken from the page [https://html-color.codes/](https://html-color.codes/), which offers free and public color palettes.

#### Hiding/Showing the table by clicking over it

This feature was built via Javascript (`jQuery` specifically), by puting/quiting the class `.col-hidden` . All can be found in `js/FakeSample.js`. Particularly:
```javascript
/**
 * Toogle hiden/visible table
 */
function toggleTable() {
  $('.hiddenable').toggleClass('col-hidden');

  table = $('#table-drug');
  table.toggleClass('table-drug-collapsed');

  if (table.hasClass('table-drug-collapsed')) {
    table.attr('data-toggle', "tooltip");
    table.attr('data-placement', "right");
    table.attr('title', "Click to see detail");
    table.tooltip('enable')
  }
  else {
    table.removeAttr('data-toggle')
    table.removeAttr('data-placement')
    table.removeAttr('title')
    table.tooltip('disable')
  }
}
```

**CSS**
```css
.col-hidden{
	display: none;
}
```

