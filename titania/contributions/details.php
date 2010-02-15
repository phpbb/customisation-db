<?php
/**
 *
 * @package titania
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

titania::add_lang('authors');

// Load the Contrib item
load_contrib();

titania::$contrib->get_download();
titania::$contrib->get_revisions();

titania::$contrib->assign_details();

// Set tracking
titania_tracking::track(TITANIA_CONTRIB, titania::$contrib->contrib_id);

if (titania::$contrib->is_author || titania::$contrib->is_active_coauthor || phpbb::$auth->acl_get('m_titania_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
{
	phpbb::$template->assign_var('U_NEW_REVISION', titania::$contrib->get_url('revision'));
}

titania::page_header('CONTRIB_DETAILS');
titania::page_footer(true, 'contributions/contribution_details.html');