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

titania::_include('tools/subscriptions');

// Did they hit "Subscribe"?
if(isset($_POST['subscribe']))
{
	titania_subscriptions::subscribe(TITANIA_CONTRIB, titania::$contrib->contrib_id, phpbb::$user->data['user_id']);
}

// Are they Subscribed?
phpbb::$template->assign_var('IS_SUBSCRIBED', (titania_subscriptions::is_subscribed(TITANIA_CONTRIB, titania::$contrib->contrib_id, phpbb::$user->data['user_id'])) ? true : false);

titania::page_header('CONTRIB_DETAILS');
titania::page_footer(true, 'contributions/contribution_details.html');