<?php
/**
*
* Titania Test Suite
* 
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

// Define some initial constants
define('CDBPATH', __DIR__ . '/../titania/');
define('PHPBB_FILES', __DIR__ . '/vendor/phpBB/phpBB/');
define('IN_TEST', true);

// Some to make phpBB files accessable in the first place
$phpbb_root_path = CDBPATH . '../';
$phpEx = 'php';
define('IN_PHPBB', true);

// Map the db constants to the phpBB vars
if (!defined('dbms'))
{
	define('dbms', 'sqlite');
	define('dbhost', __DIR__ . '/unit_tests.sqlite2'); // filename
	define('dbuser', '');
	define('dbpasswd', '');
	define('dbname', '');
	define('dbport', '');
	define('table_prefix', '');
}
$dbms = dbms;

$phpbb_tests_path = PHPBB_FILES . '../tests/';
$phpEx = 'php';

$table_prefix = (!defined('table_prefix')) ? 'phpbb_' : table_prefix;

require_once $phpbb_tests_path . 'test_framework/phpbb_test_case_helpers.php';
require_once $phpbb_tests_path . 'test_framework/phpbb_test_case.php';
require_once $phpbb_tests_path . 'test_framework/phpbb_database_test_case.php';
require_once $phpbb_tests_path . 'test_framework/phpbb_database_test_connection_manager.php';

require_once __DIR__ . '/test_framework/cdb_database_test_case.php';
require_once __DIR__ . '/test_framework/cdb_database_test_connection_manager.php';
require_once __DIR__ . '/test_framework/cdb_test_case.php';

require_once CDBPATH . 'includes/core/titania.php';

spl_autoload_register(array('titania', 'autoload'));
