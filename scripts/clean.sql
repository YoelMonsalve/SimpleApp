/**
 * Delete the Database and the drop the App user, to leave the system 
 * in a 'clean' state.
 * 
 * Ejecutar como root en MySQL. 
 *
  * Run as MySQL root, for example:
 *
 *  ~$ mysql -uroot -p < path/to/this/dir/clean.sql
 *
 * -- Be careful:  ALL DATA WILL BE DESTRUYED !!!
 *
 * Author  : Yoel Monsalve.
 * Date    : 2022-05-26
 * Version : ---
 * Modified: 2022-05-26
 */

-- Drop database
DROP DATABASE IF EXISTS `DemoApp`;

-- Dorp user
DROP USER IF EXISTS 'app_user'@'localhost';

