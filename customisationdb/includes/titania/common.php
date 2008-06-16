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
if (!defined('IN_PHPBB'))
{
	exit;
}

// Include common stuff
require($phpbb_root_path . 'includes/titania/constants.' . $phpEx);

// Include the general phpbb-related files
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Now we init the template (this will later be replaced by the real template code)
$template->set_custom_template($phpbb_root_path . 'template', 'website');