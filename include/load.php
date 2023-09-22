<?php
// -----------------------------------------------------------------------
// DEFINE SEPARATOR ALIASES
// -----------------------------------------------------------------------
define("URL_SEPARATOR", '/');
define("DS", DIRECTORY_SEPARATOR);

// -----------------------------------------------------------------------
// DEFINE PATHS
// -----------------------------------------------------------------------
/* Defining the SITE_ROOT properly
   This is very important, by security */
if ( !defined('SITE_ROOT') ) {
	$INC_PATH = dirname(__FILE__);
	define("SITE_ROOT", realpath($INC_PATH.'/..'));
}

//define("SITE_URL", "");
define("SITE_URL", "/SimpleApp");

defined("INC_ROOT")? null: define("INC_ROOT", realpath(dirname(__FILE__)));
define("LIB_PATH_INC", INC_ROOT.DS);

require_once(LIB_PATH_INC.'config.php');
require_once(LIB_PATH_INC.'functions.php');
require_once(LIB_PATH_INC.'database.php');
require_once(LIB_PATH_INC.'session.php');
require_once(LIB_PATH_INC.'upload.php');
//require_once(LIB_PATH_INC.'sql.php');
require_once(SITE_ROOT.'/api/sql/sql.php');

?>
