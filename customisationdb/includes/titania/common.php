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
if (!defined('IN_PHPBB') || (!defined('IN_TITANIA')))
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
require(TITANIA_ROOT . 'constants.' . $phpEx);

// Include the general phpbb-related files
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Now we init the template (this will later be replaced by the real template code)
$template->set_custom_template($template_location, 'website');
