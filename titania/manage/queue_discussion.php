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

// Mark all topics read
if (request_var('mark', '') == 'topics')
{
	titania_tracking::track(TITANIA_QUEUE_DISCUSSION, 0);
}

$authed = titania_types::find_authed('queue_discussion');
if (empty($authed))
{
	titania::needs_auth();
}

topics_overlord::display_forums_complete('queue_discussion');

// Mark all topics read
phpbb::$template->assign_var('U_MARK_TOPICS', titania_url::build_url('manage/queue_discussion/', array('mark' => 'topics')));

titania::page_header('QUEUE_DISCUSSION');

titania::page_footer(true, 'manage/queue_discussion.html');
