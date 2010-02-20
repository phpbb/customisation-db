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

$queue_id = request_var('q', 0);
$queue_type = request_var('queue', '');

// Force the queue_type if we have a queue_id
if ($queue_id)
{
	$sql = 'SELECT queue_type FROM ' . TITANIA_QUEUE_TABLE . '
		WHERE queue_id = ' . $queue_id;
	phpbb::$db->sql_query($sql);
	$queue_type = phpbb::$db->sql_fetchfield('queue_type');
}
else
{
	$queue_type = titania_types::type_from_url($queue_type);
}

// Setup the base url we will use
$base_url = titania_url::build_url('manage/queue');

if ($queue_type === false)
{
	// We need to select the queue if they only have one that they can access, else display the list
	$authed = titania_types::find_authed('view');

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
			phpbb::$template->assign_block_vars('categories', array(
				'U_VIEW_CATEGORY'	=> titania_url::append_url($base_url, array('queue' => titania_types::$types[$type_id]->url)),
				'CATEGORY_NAME'		=> titania_types::$types[$type_id]->lang,
			));
		}

		phpbb::$template->assign_vars(array(
			'S_QUEUE_LIST'	=> true,
		));

		titania::page_header('VALIDATION_QUEUE');
		titania::page_footer(true, 'manage/queue.html');
	}
}
else
{
	if (!titania_types::$types[$queue_type]->acl_get('view'))
	{
		titania::needs_auth();
	}
}

// Add the queue type to the base url
$base_url = titania_url::append_url($base_url, array('queue' => titania_types::$types[$queue_type]->url));

// Handle replying/editing/etc
$posting_helper = new titania_posting(TITANIA_ATTACH_EXT_SUPPORT);
$posting_helper->act('manage/queue_post.html');

// Main output
if ($queue_id)
{
	phpbb::$user->add_lang('viewforum');

	$action = request_var('action', '');
	$submit = (isset($_POST['submit'])) ? true : false;

	switch ($action)
	{
		case 'approve' :
			$queue = queue_overlord::get_queue_object($queue_id, true);
			if (!titania_types::$types[$contrib->contrib_type]->acl_get('validate'))
			{
				titania::needs_auth();
			}
		break;

		case 'deny' :
			$queue = queue_overlord::get_queue_object($queue_id, true);
			if (!titania_types::$types[$contrib->contrib_type]->acl_get('validate'))
			{
				titania::needs_auth();
			}
		break;

		case 'notes' :
			$queue = queue_overlord::get_queue_object($queue_id, true);
		break;

		case 'move' :
			$queue = queue_overlord::get_queue_object($queue_id, true);

			$tags = titania::$cache->get_tags(TITANIA_QUEUE);

			if (titania::confirm_box(true))
			{
				$new_tag = request_var('move_to', 0);
				if (!isset($tags[$new_tag]))
				{
					trigger_error('NO_TAG');
				}

				$queue->queue_status = $new_tag;
				$queue->submit(false);
			}
			else
			{
				$extra = '<select name="move_to">';
				foreach ($tags as $tag_id => $row)
				{
					$extra .= '<option value="' . $tag_id . '">' . ((isset(phpbb::$user->lang[$row['tag_field_name']])) ? phpbb::$user->lang[$row['tag_field_name']] : $row['tag_field_name']) . '</option>';
				}
				$extra .= '</select>';
				phpbb::$template->assign_var('CONFIRM_EXTRA', $extra);

				titania::confirm_box(false, 'MOVE_QUEUE');
			}
		break;
	}

	queue_overlord::display_queue_item($queue_id);

	titania::page_header('VALIDATION_QUEUE');
}
else
{
	$tag = request_var('tag', TITANIA_QUEUE_NEW);
	queue_overlord::display_queue($queue_type, $tag);
	queue_overlord::display_categories($queue_type);

	titania::page_header('VALIDATION_QUEUE');
}

titania::page_footer(true, 'manage/queue.html');