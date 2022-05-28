# README

## Overview

This is a Demo App, for interview.

**Author**: Yoel Monsalve.

**Date**:   May 22th, 2022.

## Requisites

Some PHP modules are required to this app, like `json` and `sqlite3`. On UNIX-like systems, type
```
sudo update
sudo apt install php-sqlite3 php-json
```

On Windows systems, consult your documentation of XAMPP/WAMPP to see how to install them.

## Installation

**1.** Copy the content of this repository into the host folder for your server. On Windows systems a good choice for that destination folder could be `C:\xampp\SimpleApp`, while for UNIX-like systems it would be `/var/www/html/SimpleApp`.

**2.** On UNIX-like systems, it could be necessary to set right permissions to folders (`750` for directories, and `640` for plain files). 
The script `configure` allows you to automatically do this:
```bash
cd <path/to/host>
sudo chmod u+x configure
sudo ./configure
```

If instead, you want to proceed manually, just do
```bash
find . -type f ! -name "configure" -exec chmod 644 {} \;
find . -type d -exec chmod 750 {} \;
```

Also, change the group to `www-data` in order to make sure that PHP be able to access the files.
```bash
chgrp -R www-data .
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

<img alt="Screenshot-HomePage"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Screenshot%20from%202022-05-28%2009-16-34.png" width="600">

<img alt="Screenshot-HomePage-2"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Screenshot%20from%202022-05-28%2009-16-37.png" width="600">

<img alt="Screenshot-TableExpanded"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Screenshot%20from%202022-05-28%2009-16-43.png" width="600">

<img alt="Screenshot-Mobile-Collapsed"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Screenshot%20from%202022-05-28%2009-16-53.png" width="600">

<img alt="Screenshot-Mobile-Expanded"  src="https://github.com/YoelMonsalve/SimpleApp/blob/master/assets/screenshots/Screenshot%20from%202022-05-28%2009-16-59.png" width="600">

### How it is made

This App reads a JSON file, and is able to store the information contained in it to a MySQL database. Then, reads the info and shows it on a simple but stylized webpage, to show a handy presentation. I used some CSS/Bootstrap styles to give that appearance.

#### Data

The first step, is to structure the information using tables. My proposal to work with what is contained in the file, was by creating three tables: `samples`, `CurrentMedications` and `GeneInfo`:

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

The field types and keys were conveniently defined to work with the nature of the data. It was supposed that `SampleNumer` is an *integer*, and an unique number for each row. Therefore, I used it as the primary key for the `samples` table. Note that this brings in some restrictions to our system that rather serve to make it robust. For example, database will reject any attemp to introduce a `SampleNumber` that is not numeric, or repeated. The same will happen if we try introducing a medication, or gene-info, that is not correlated with a respetive sample (that is the purpose of protecting tables with foreign keys). 

If these assumptions are not correct for the case study, they need to be redefined. 

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

If you review the lines 6-17 of the home page `index.php`:

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

An alternative design would have been through buttons to allow the user manually controlling when a new file is going to be uploaded, ... but that would be a more complicated architecture.

#### Modularizing and reusable code

When building this app, I also wanted to illustrate the concept of preferring modular design and reusable code, to keep the project ordered and easier to expand. In order to that, a class was defined in `api/sql/sql.php` (in its own namespace `\API`) to keep all the basic functions related with SQL management (a kind of small framework), and the script `api/medication.php` contains methods to consult medications and gene info.

```php
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
  ...
```

#### Stylizing

Some Boostrap styles were added to the page to get a nice appearance, and other CSS rules were defined in `css/table.css` file to manage the effects of the table.

Note that due do this, the page is responsive, meaning that it looks well even in small screens (phones or tablets).

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

