<?php
/**
*
* @package Titania
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

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);

// Load the topic and contrib items
if ($post_id)
{
	$topic_id = topics_overlord::load_topic_from_post($post_id, true);

	// Load the topic into a topic object
	$topic = topics_overlord::get_topic_object($topic_id);
	if ($topic === false)
	{
		trigger_error('NO_TOPIC');
	}

	// Load the contrib item
	load_contrib($topic->parent_id);
}
else if ($topic_id)
{
	topics_overlord::load_topic($topic_id, true);

	// Load the topic into a topic object
	$topic = topics_overlord::get_topic_object($topic_id);
	if ($topic === false)
	{
		trigger_error('NO_TOPIC');
	}

	// Load the contrib item
	load_contrib($topic->parent_id);
}
else
{
	// Load the contrib item
	load_contrib();
}

// Output the simple info on the contrib
titania::$contrib->assign_details(true);

if (!titania::$config->support_in_titania && titania::$access_level == TITANIA_ACCESS_PUBLIC)
{
	titania::needs_auth();
}

// Handle replying/editing/etc
$posting_helper = new titania_posting();
$posting_helper->act('contributions/contribution_support_post.html', titania::$contrib->contrib_id, titania::$contrib->get_url('support'), TITANIA_SUPPORT);

phpbb::$user->add_lang('viewforum');

if ($topic_id)
{
	// Subscriptions
	titania_subscriptions::handle_subscriptions(TITANIA_TOPIC, $topic_id, $topic->get_url());

	// Check access level
	if ($topic->topic_access < titania::$access_level || ($topic->topic_type == TITANIA_QUEUE_DISCUSSION && !titania::$contrib->is_author && !titania::$contrib->is_active_coauthor && !titania_types::$types[titania::$contrib->contrib_type]->acl_get('queue_discussion')))
	{
		titania::needs_auth();
	}

	posts_overlord::display_topic_complete($topic);

	titania::page_header(censor_text($topic->topic_subject) . ' - ' . titania::$contrib->contrib_name);

	if (phpbb::$auth->acl_get('u_titania_post'))
	{
		phpbb::$template->assign_var('U_POST_REPLY', titania_url::append_url($topic->get_url(), array('action' => 'reply')));
	}

	// Canonical URL
	phpbb::$template->assign_var('U_CANONICAL', $topic->get_url());
}
else
{
	// Subscriptions
	titania_subscriptions::handle_subscriptions(TITANIA_SUPPORT, titania::$contrib->contrib_id, titania::$contrib->get_url('support'));

	// Mark all topics read
	if (request_var('mark', '') == 'topics')
	{
		titania_tracking::track(TITANIA_SUPPORT, titania::$contrib->contrib_id);
	}

	$data = topics_overlord::display_forums_complete('support', titania::$contrib);

	titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['CONTRIB_SUPPORT']);

	if (phpbb::$auth->acl_get('u_titania_topic') && titania::$config->support_in_titania)
	{
		phpbb::$template->assign_var('U_POST_TOPIC', titania_url::append_url(titania::$contrib->get_url('support'), array('action' => 'post')));
	}

	$data['sort']->set_url(titania::$contrib->get_url('support'));
	phpbb::$template->assign_vars(array(
		// Mark all topics read
		'U_MARK_TOPICS'			=> titania_url::append_url(titania::$contrib->get_url('support'), array('mark' => 'topics')),

		// Canonical URL
		'U_CANONICAL'			=> $data['sort']->build_canonical(),

		'S_DISPLAY_SEARCHBOX'	=> true,
		'S_SEARCHBOX_ACTION'	=> titania_url::build_url('search', array('type' => TITANIA_SUPPORT, 'contrib' => titania::$contrib->contrib_id)),
	));
}

titania::page_footer(true, 'contributions/contribution_support.html');