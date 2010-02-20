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

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);

// Load the topic and contrib items
if ($post_id)
{
	$topic_id = topics_overlord::load_topic_from_post($post_id);

	// Load the topic into a topic object
	$topic = topics_overlord::get_topic_object($topic_id);
	if ($topic === false)
	{
		trigger_error('NO_TOPIC');
	}

	// Load the contrib item
	load_contrib($topic->contrib_id);
	$topic->contrib = titania::$contrib;

	titania::generate_breadcrumbs(array(
		censor_text($topic->topic_subject)	=> $topic->get_url(),
	));
}
else if ($topic_id)
{
	topics_overlord::load_topic($topic_id);

	// Load the topic into a topic object
	$topic = topics_overlord::get_topic_object($topic_id);
	if ($topic === false)
	{
		trigger_error('NO_TOPIC');
	}

	// Load the contrib item
	load_contrib($topic->contrib_id);

	$topic->contrib = titania::$contrib;

	titania::generate_breadcrumbs(array(
		censor_text($topic->topic_subject)	=> $topic->get_url(),
	));
}
else
{
	// Load the contrib item
	load_contrib();
}

// Output the simple info on the contrib
titania::$contrib->assign_details(true);

// Handle replying/editing/etc
$posting_helper = new titania_posting(TITANIA_ATTACH_EXT_SUPPORT);
$posting_helper->act('contributions/contribution_support_post.html', titania::$contrib, TITANIA_SUPPORT);

phpbb::$user->add_lang('viewforum');

if ($topic_id)
{
	posts_overlord::display_topic_complete($topic);

	titania::page_header(phpbb::$user->lang['CONTRIB_SUPPORT'] . ' - ' . censor_text($topic->topic_subject));

	if (phpbb::$auth->acl_get('u_titania_post'))
	{
		phpbb::$template->assign_var('U_POST_REPLY', titania_url::append_url($topic->get_url(), array('action' => 'reply')));
	}
}
else
{
	topics_overlord::display_forums_complete('support', titania::$contrib);

	titania::page_header('CONTRIB_SUPPORT');

	if (phpbb::$auth->acl_get('u_titania_topic'))
	{
		phpbb::$template->assign_var('U_POST_TOPIC', titania_url::append_url(titania::$contrib->get_url('support'), array('action' => 'post')));
	}
}

titania::page_footer(true, 'contributions/contribution_support.html');