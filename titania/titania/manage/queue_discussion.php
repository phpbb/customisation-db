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
	titania_tracking::track(TITANIA_QUEUE_DISCUSSION, 0);
}

$queue_type = request_var('queue', '');

// Force the queue_type if we have a queue_id
$queue_type = titania_types::type_from_url($queue_type);

// Setup the base url we will use
$base_url = titania_url::build_url('manage/queue_discussion');

if ($queue_type === false)
{
	// We need to select the queue if they only have one that they can access, else display the list
	$authed = titania_types::find_authed('queue_discussion');

	if (empty($authed))
	{
		titania::needs_auth();
	}
	else if (sizeof($authed) == 1)
	{
		$queue_type = $authed[0];
	}
	else
	{
		foreach ($authed as $type_id)
		{
			$sql = 'SELECT COUNT(topic_id) AS cnt FROM ' . TITANIA_TOPICS_TABLE . '
				WHERE topic_type = ' . TITANIA_QUEUE_DISCUSSION . '
					AND topic_category = ' . (int) $type_id;
			phpbb::$db->sql_query($sql);
			$cnt = phpbb::$db->sql_fetchfield('cnt');
			phpbb::$db->sql_freeresult();

			phpbb::$template->assign_block_vars('categories', array(
				'U_VIEW_CATEGORY'	=> titania_url::append_url($base_url, array('queue' => titania_types::$types[$type_id]->url)),
				'CATEGORY_NAME'		=> titania_types::$types[$type_id]->lang,
				'CATEGORY_CONTRIBS' => $cnt,
			));
		}

		phpbb::$template->assign_vars(array(
			'S_QUEUE_LIST'	=> true,
		));

		titania::page_header('QUEUE_DISCUSSION');
		titania::page_footer(true, 'manage/queue.html');
	}
}
else
{
	if (!titania_types::$types[$queue_type]->acl_get('queue_discussion'))
	{
		titania::needs_auth();
	}
}

// Add the queue type to the base url
$base_url = titania_url::append_url($base_url, array('queue' => titania_types::$types[$queue_type]->url));

// Add to Breadcrumbs
titania::generate_breadcrumbs(array(
	titania_types::$types[$queue_type]->lang	=> $base_url,
));

topics_overlord::display_forums_complete('queue_discussion', false, array('topic_category' => $queue_type));

// Mark all topics read
phpbb::$template->assign_var('U_MARK_TOPICS', titania_url::append_url($base_url, array('mark' => 'topics')));

titania::page_header('QUEUE_DISCUSSION');

titania::page_footer(true, 'manage/queue_discussion.html');
