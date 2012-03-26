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

// Mark all topics read
if (request_var('mark', '') == 'topics')
{
	foreach (titania::$cache->get_author_contribs(titania::$author->user_id) as $contrib_id)
	{
		titania_tracking::track(TITANIA_SUPPORT, $contrib_id);
	}
}

topics_overlord::display_forums_complete('author_support', titania::$author);

// Mark all topics read
phpbb::$template->assign_var('U_MARK_TOPICS', titania_url::build_url(titania::$author->get_url('support'), array('mark' => 'topics')));

titania::page_header(titania::$author->get_username_string('username') . ' - ' . phpbb::$user->lang['AUTHOR_SUPPORT']);
titania::page_footer(true, 'contributions/contribution_support.html');