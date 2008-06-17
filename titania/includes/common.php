<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

######################################################
######## Configuration parameters - START - ##########
######################################################
$phpbb_root_path = './../../phpBB/';

// Table prefix used in constants
$cdb_table_prefix = 'customisation_';

// custom template path to store templates
$template_location = TITANIA_ROOT . 'template';
######################################################
######## Configuration parameters - END - ############
######################################################

// Include titania constants
require(TITANIA_ROOT . 'constants.' . PHP_EXT);

// Include the general phpbb-related files
include(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// set the custom template path for titania. Default: root/titania/template
$template->set_custom_template($template_location, 'titania');
