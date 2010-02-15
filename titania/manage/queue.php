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

$queue_type = request_var('queue', '');
$queue_type = titania_types::type_from_url($queue_type);

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
$queue_id = request_var('q', 0);
if ($queue_id)
{
	phpbb::$user->add_lang('viewforum');

	queue_overlord::display_queue_item($queue_id);

	titania::page_header('VALIDATION_QUEUE');
}
else
{
	queue_overlord::display_queue($queue_type);

	titania::page_header('VALIDATION_QUEUE');
}

titania::page_footer(true, 'manage/queue.html');