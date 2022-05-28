/**
 * This script will create the database and application user.
 * It is a good idea to handle ane exclusive MySQL user for the application,
 * which have limited privileges.
 * 
 * Run as MySQL root, for example:
 *
 *  ~$ mysql -uroot -p < <path_to_this_dir>/setup.sql
 *
 * Author  : Yoel Monsalve.
 * Date    : 2022-05-26
 * Version : ---
 * Modified: 2022-05-26
 */

CREATE DATABASE IF NOT EXISTS `DemoApp` \
    DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Create user, and give him limited privileges */
-- CREATE USER IF NOT EXISTS 'app_user'@'localhost' IDENTIFIED WITH mysql_native_password BY '1234';
CREATE USER IF NOT EXISTS 'app_user'@'localhost' IDENTIFIED BY '1234';
GRANT SELECT, INSERT, UPDATE, DELETE, LOCK TABLES ON `DemoApp`.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;
