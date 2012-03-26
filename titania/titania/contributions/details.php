<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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

if ($page == 'report')
{
	// Check permissions
	if (!phpbb::$user->data['is_registered'])
	{
		titania::needs_auth();
	}

	titania::add_lang('posting');
	phpbb::$user->add_lang('mcp');

	if (titania::confirm_box(true))
	{
		$message = utf8_normalize_nfc(request_var('report_text', '', true));
		titania::$contrib->report($message);

		// Notifications

		redirect(titania::$contrib->get_url());
	}
	else
	{
		//phpbb::$template->assign_var('S_CAN_NOTIFY', ((phpbb::$user->data['is_registered']) ? true : false));

		titania::confirm_box(false, 'REPORT_CONTRIBUTION', '', array(), 'posting/report_body.html');
	}

	redirect(titania::$contrib->get_url());
}

titania::$contrib->get_download();
titania::$contrib->get_revisions();
titania::$contrib->get_screenshots();
titania::$contrib->get_rating();

titania::$contrib->assign_details();

if (!phpbb::$user->data['is_bot'])
{
	titania::$contrib->increase_view_counter();
}

// Set tracking
titania_tracking::track(TITANIA_CONTRIB, titania::$contrib->contrib_id);

// Subscriptions
titania_subscriptions::handle_subscriptions(TITANIA_CONTRIB, titania::$contrib->contrib_id, titania::$contrib->get_url());

// Canonical URL
phpbb::$template->assign_var('U_CANONICAL', titania::$contrib->get_url());

titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['CONTRIB_DETAILS']);
titania::page_footer(true, 'contributions/contribution_details.html');