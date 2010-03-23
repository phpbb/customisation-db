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
titania::$contrib->get_screenshots();

titania::$contrib->assign_details();

// Set tracking
titania_tracking::track(TITANIA_CONTRIB, titania::$contrib->contrib_id);

// Are they Subscribed?
// Waiting on Nathan for which parameters to pass along.
// titania::_include('tools/subscriptions');
// phpbb::$template->assign_var('IS_SUBSCRIBED', (titania_subscriptions::is_subscribed()) ? true : false);

titania::page_header('CONTRIB_DETAILS');
titania::page_footer(true, 'contributions/contribution_details.html');