<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

//define('TEST_INSTALLATION', true);

titania::$hook->register_ary('phpbb_com_', array(
	'titania_page_header',
	'titania_page_footer',
	array('titania_queue', 'update_first_queue_post'),
	array('titania_topic', '__construct'),
	array('titania_post', '__construct'),
	array('titania_post', 'post'),
	array('titania_post', 'edit'),
	array('titania_post', 'hard_delete'),
	array('titania_queue', 'approve'),
	array('titania_queue', 'deny'),
	array('titania_queue', 'close'),
	array('titania_queue', 'delete'),
	array('titania_contribution', 'assign_details'),
));

// Do we need to install the DB stuff?
if (!isset(phpbb::$config['titania_hook_phpbb_com']) || version_compare(phpbb::$config['titania_hook_phpbb_com'], '1.0.1', '<'))
{
	phpbb::_include('../umil/umil', false, 'umil');

	$umil = new umil(true, phpbb::$db);

	$umil->run_actions('update', array(
		'1.0.0' => array(
			'table_column_add' => array(
				array(TITANIA_TOPICS_TABLE, 'phpbb_topic_id', array('UINT', 0)),
			),
		),
		'1.0.1' => array(
			'table_column_add' => array(
				array(TITANIA_POSTS_TABLE, 'phpbb_post_id', array('UINT', 0)),
			),
		),
	),
	'titania_hook_phpbb_com');

	unset($umil);
}

/**
* .com custom header and footer
*/

function phpbb_com_titania_page_header($hook, $page_title)
{
	if (defined('TEST_INSTALLATION'))
	{
		return;
	}

	phpbb::$template->assign_vars(array(
		'S_BODY_CLASS'		=> 'customise customisation-database',
		'S_IS_WEBSITE'		=> true,
	));

	global $auth, $phpEx, $template, $user;
	$root_path = TITANIA_ROOT . '../../';
	$base_path = generate_board_url(true) . '/';
	include($root_path . 'vars.' . PHP_EXT);

	// Setup the phpBB.com header
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/', 'website');
	phpbb::$template->set_filenames(array(
		'phpbb_com_header'		=> 'overall_header.html',
	));
	phpbb::$template->assign_display('phpbb_com_header', 'PHPBB_COM_HEADER', false);

	titania::set_custom_template();
}

function phpbb_com_titania_page_footer($hook, $run_cron, $template_body)
{
	if (defined('TEST_INSTALLATION'))
	{
		return;
	}

	// Setup the phpBB.com footer
	phpbb::$template->set_custom_template(TITANIA_ROOT . '../../template/', 'website');
	phpbb::$template->set_filenames(array(
		'phpbb_com_footer'		=> 'overall_footer.html',
	));
	phpbb::$template->assign_display('phpbb_com_footer', 'PHPBB_COM_FOOTER', false);

	titania::set_custom_template();
}

// Display a warning for styles not meeting the licensing guidelines
function phpbb_com_titania_contribution_assign_details($hook, &$vars, $contrib)
{
	if ($contrib->contrib_type != TITANIA_TYPE_STYLE)
	{
		return;
	}

	if (isset($contrib->download['revision_license']) && $contrib->download['revision_license'] == '')
	{
		if (isset($vars['WARNING']))
		{
			$vars['WARNING'] .= '<br />';
		}
		else
		{
			$vars['WARNING'] = '';
		}

		$vars['WARNING'] .= 'WARNING: This style currently does not meet our licensing guidelines.';
	}
}

/**
* Copy new posts for queue discussion, queue to the forum
*/
function phpbb_com_titania_queue_update_first_queue_post($hook, &$post_object, $queue_object)
{
	if ($queue_object->queue_status == TITANIA_QUEUE_HIDE || !$queue_object->queue_topic_id)
	{
		return;
	}

	// First we copy over the queue discussion topic if required
	$sql = 'SELECT topic_id, phpbb_topic_id, topic_category FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE parent_id = ' . $queue_object->contrib_id . '
			AND topic_type = ' . TITANIA_QUEUE_DISCUSSION;
	$result = phpbb::$db->sql_query($sql);
	$topic_row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	// Do we need to create the queue discussion topic or not?
	if ($topic_row['topic_id'] && !$topic_row['phpbb_topic_id'])
	{
		$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, TITANIA_QUEUE_DISCUSSION);

		$temp_post = new titania_post;

		// Go through any posts in the queue discussion topic and copy them
		$topic_id = false;
		$sql = 'SELECT * FROM ' . TITANIA_POSTS_TABLE . ' WHERE topic_id = ' . $topic_row['topic_id'];
		$result = phpbb::$db->sql_query($sql);
		while($row = phpbb::$db->sql_fetchrow($result))
		{
			$temp_post->__set_array($row);

			$post_text = $row['post_text'];
			titania_decode_message($post_text, $row['post_text_uid']);

			$post_text .= "\n\n" . titania_url::remove_sid($temp_post->get_url());

			$options = array(
				'poster_id'				=> $row['post_user_id'],
				'forum_id' 				=> $forum_id,
				'topic_title'			=> $row['post_subject'],
				'post_text'				=> $post_text,
			);

			titania::_include('functions_posting', 'phpbb_posting');

			if ($topic_id)
			{
				$options = array_merge($options, array(
					'topic_id'	=> $topic_id,
				));

				phpbb_posting('reply', $options);
			}
			else
			{
				switch ($topic_row['topic_category'])
				{
					case TITANIA_TYPE_MOD :
						$options['poster_id'] = titania::$config->forum_mod_robot;
					break;

					case TITANIA_TYPE_STYLE :
						$options['poster_id'] = titania::$config->forum_style_robot;
					break;
				}

				$topic_id = phpbb_posting('post', $options);
			}
		}
		phpbb::$db->sql_freeresult($result);

		if ($topic_id)
		{
			$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
				SET phpbb_topic_id = ' . $topic_id . '
				WHERE topic_id = ' . $topic_row['topic_id'];
			phpbb::$db->sql_query($sql);
		}

		unset($temp_post);
	}

	// Does a queue topic already exist?  If so, don't repost.
	$sql = 'SELECT phpbb_topic_id FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE topic_id = ' . $queue_object->queue_topic_id;
	phpbb::$db->sql_query($sql);
	$phpbb_topic_id = phpbb::$db->sql_fetchfield('phpbb_topic_id');
	phpbb::$db->sql_freeresult();
	if ($phpbb_topic_id)
	{
		return;
	}

	$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, $post_object->topic->topic_type);

	if (!$forum_id)
	{
		return;
	}

	$post_object->submit();

	titania::_include('functions_posting', 'phpbb_posting');

	// Need some stuff
	titania::add_lang('contributions');
	$contrib = new titania_contribution;
	$contrib->load((int) $queue_object->contrib_id);
	$revision = $queue_object->get_revision();
	$contrib->get_download($revision->revision_id);

	switch ($post_object->topic->topic_category)
	{
		case TITANIA_TYPE_MOD :
			$post_object->topic->topic_first_post_user_id = titania::$config->forum_mod_robot;
			$lang_var = 'MOD_QUEUE_TOPIC';
		break;

		case TITANIA_TYPE_STYLE :
			$post_object->topic->topic_first_post_user_id = titania::$config->forum_style_robot;
			$lang_var = 'STYLE_QUEUE_TOPIC';
		break;

		default :
			return;
		break;
	}

	$description = $contrib->contrib_desc;
	titania_decode_message($description, $contrib->contrib_desc_uid);

	$post_text = sprintf(phpbb::$user->lang[$lang_var],
		$contrib->contrib_name,
		$contrib->author->get_url(),
		users_overlord::get_user($contrib->author->user_id, '_username'),
		$description,
		$revision->revision_version,
		titania_url::build_url('download', array('id' => $revision->attachment_id)),
		$contrib->download['real_filename'],
		$contrib->download['filesize']
	);

	$post_text .= "\n\n" . $post_object->post_text;
	titania_decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . titania_url::remove_sid($post_object->get_url());

	$options = array(
		'poster_id'				=> $post_object->topic->topic_first_post_user_id,
		'forum_id' 				=> $forum_id,
		'topic_title'			=> $post_object->topic->topic_subject,
		'post_text'				=> $post_text,
	);

	$topic_id = phpbb_posting('post', $options);

	$post_object->topic->phpbb_topic_id = $topic_id;

	$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . '
		SET phpbb_topic_id = ' . (int) $topic_id . '
		WHERE topic_id = ' . $post_object->topic->topic_id;
	phpbb::$db->sql_query($sql);
}


function phpbb_com_titania_post_post($hook, &$post_object)
{
	if (defined('IN_TITANIA_CONVERT') || !$post_object->topic->phpbb_topic_id)
	{
		return;
	}

	$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, $post_object->post_type);

	if (!$forum_id)
	{
		return;
	}

	titania::_include('functions_posting', 'phpbb_posting');

	$post_text = $post_object->post_text;
	titania_decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . titania_url::remove_sid($post_object->get_url());

	$options = array(
		'poster_id'				=> $post_object->post_user_id,
		'topic_id'				=> $post_object->topic->phpbb_topic_id,
		'topic_title'			=> $post_object->post_subject,
		'post_text'				=> $post_text,
	);

	$post_object->phpbb_post_id = phpbb_posting('reply', $options);

	$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
		SET phpbb_post_id = ' . $post_object->phpbb_post_id . '
		WHERE post_id = ' . $post_object->post_id;
	phpbb::$db->sql_query($sql);
}

function phpbb_com_titania_post_edit($hook, &$post_object)
{
	if (defined('IN_TITANIA_CONVERT') || !$post_object->phpbb_post_id)
	{
		return;
	}

	$forum_id = phpbb_com_forum_id($post_object->topic->topic_category, $post_object->post_type);

	if (!$forum_id)
	{
		return;
	}

	titania::_include('functions_posting', 'phpbb_posting');

	$post_text = $post_object->post_text;
	titania_decode_message($post_text, $post_object->post_text_uid);

	$post_text .= "\n\n" . titania_url::remove_sid($post_object->get_url());

	$options = array(
		'post_id'				=> $post_object->phpbb_post_id,
		'topic_title'			=> $post_object->post_subject,
		'post_text'				=> $post_text,
	);

	phpbb_posting('edit', $options);
}

function phpbb_com_titania_post_hard_delete($hook, &$post_object)
{
	if (defined('IN_TITANIA_CONVERT') || !$post_object->phpbb_post_id)
	{
		return;
	}
	
	phpbb::_include('functions_posting', 'delete_post');
	
	$sql = 'SELECT t.*, p.*
	FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
		WHERE p.post_id = ' . $post_object->phpbb_post_id . '
		AND t.topic_id = p.topic_id';
	$result = phpbb::$db->sql_query($sql);
	$post_data = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);
		
	$data = array(
		'topic_first_post_id'	=> $post_data['topic_first_post_id'],
		'topic_last_post_id'	=> $post_data['topic_last_post_id'],
		'topic_replies_real'	=> $post_data['topic_replies_real'],
		'topic_approved'		=> $post_data['topic_approved'],
		'topic_type'			=> $post_data['topic_type'],
		'post_approved'			=> $post_data['post_approved'],
		'post_reported'			=> $post_data['post_reported'],
		'post_time'				=> $post_data['post_time'],
		'poster_id'				=> $post_data['poster_id'],
		'post_postcount'		=> $post_data['post_postcount']
	);
	
	delete_post($post_data['forum_id'], $post_data['topic_id'], $post_data['post_id'], $data);
}

function phpbb_com_titania_topic___construct($hook, &$topic_object)
{
	$topic_object->object_config = array_merge($topic_object->object_config, array(
		'phpbb_topic_id'	=> array('default' => 0),
	));
}

function phpbb_com_titania_post___construct($hook, &$post_object)
{
	$post_object->object_config = array_merge($post_object->object_config, array(
		'phpbb_post_id'	=> array('default' => 0),
	));
}

/**
* Move queue topics to the trash can
*/

function phpbb_com_titania_queue_approve($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_titania_queue_deny($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_titania_queue_close($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_titania_queue_delete($hook, &$queue_object)
{
	phpbb_com_move_queue_topic($queue_object);
}

function phpbb_com_move_queue_topic($queue_object)
{
	$sql = 'SELECT phpbb_topic_id, topic_category FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE topic_id = ' . (int) $queue_object->queue_topic_id;
	$result = phpbb::$db->sql_query($sql);
	$row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if (!$row['phpbb_topic_id'])
	{
		return;
	}

	phpbb::_include('functions_admin', 'move_topics');

	move_topics($row['phpbb_topic_id'], phpbb_com_forum_id($row['topic_category'], 'trash'));
}

function phpbb_com_forum_id($type, $mode)
{
	if (defined('TEST_INSTALLATION'))
	{
		return 2;
	}

	switch ($type)
	{
		case TITANIA_TYPE_MOD :
			switch ($mode)
			{
				case TITANIA_QUEUE_DISCUSSION :
					return 61;
				break;

				case TITANIA_QUEUE :
					return 38;
				break;

				case 'trash' :
					return 28;
				break;
			}
		break;

		case TITANIA_TYPE_STYLE :
			switch ($mode)
			{
				case TITANIA_QUEUE_DISCUSSION :
					return 87;
				break;

				case TITANIA_QUEUE :
					return 40;
				break;

				case 'trash' :
					return 83;
				break;
			}
		break;
	}

	return false;
}
