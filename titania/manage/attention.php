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

if (!phpbb::$auth->acl_gets('u_titania_mod_author_mod', 'u_titania_mod_contrib_mod', 'u_titania_mod_faq_mod', 'u_titania_mod_post_mod') && !sizeof(titania_types::find_authed('moderate')))
{
	titania::needs_auth();
}

phpbb::$user->add_lang('mcp');

$attention_id = request_var('a', 0);
$object_type = request_var('type', 0);
$object_id = request_var('id', 0);

if ($attention_id || ($object_type && $object_id))
{
	if ($attention_id)
	{
		$row = attention_overlord::load_attention($attention_id);
	}
	else
	{
		$row = attention_overlord::load_attention(false, $object_type, $object_id);
	}

	if (!$row)
	{
		trigger_error('NO_ATTENTION_ITEM');
	}

	// Setup
	$attention_object = new titania_attention;
	$attention_object->__set_array($row);

	$close = (isset($_POST['close'])) ? true : false;
	$approve = (isset($_POST['approve'])) ? true : false;
	$disapprove = (isset($_POST['disapprove'])) ? true : false;

	// Close, approve, or disapprove the items
	if ($close || $approve || $disapprove)
	{
		if (!check_form_key('attention'))
		{
			trigger_error('FORM_INVALID');
		}

		$sql_ary = array(
			'attention_close_time'	=> titania::$time,
			'attention_close_user'	=> phpbb::$user->data['user_id'],
		);

		$sql = 'UPDATE ' . TITANIA_ATTENTION_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE attention_object_id = ' . (int) $attention_object->attention_object_id . '
				AND attention_object_type = ' . (int) $attention_object->attention_object_type . '
				AND attention_type = ' . (($close || $delete) ? TITANIA_ATTENTION_REPORTED : TITANIA_ATTENTION_UNAPPROVED);
		phpbb::$db->sql_query($sql);
	}
	add_form_key('attention');

	// Display the current attention items
	$options = array(
		'attention_object_id'	=> $attention_object->attention_object_id,
	);
	attention_overlord::display_attention_list($options);

	// Display the old (closed) attention items
	$options['only_closed'] = true;
	$options['template_block'] = 'attention_closed';
	attention_overlord::display_attention_list($options);

	switch ((int) $attention_object->attention_object_type)
	{
		case TITANIA_POST :
			$post = new titania_post;
			$post->post_id = $attention_object->attention_object_id;
			if (!$post->load())
			{
				$attention_object->delete();
				trigger_error('NO_POST');
			}

			// Close or approve the report
			if ($close)
			{
				$post->post_reported = false;
				$post->submit();
			}
			if ($approve)
			{
				$post->post_approved = 1;
				$post->submit();
			}

			users_overlord::load_users(array($post->post_user_id, $post->post_edit_user, $post->post_delete_user));
			users_overlord::assign_details($post->post_user_id, 'POSTER_', true);

			phpbb::$template->assign_vars(array(
				'POST_SUBJECT'		=> censor_text($post->post_subject),
				'POST_DATE'			=> $user->format_date($post->post_time),
				'POST_TEXT'			=> $post->generate_text_for_display(),
				'EDITED_MESSAGE'	=> ($post->post_edited) ? sprintf(phpbb::$user->lang['EDITED_MESSAGE'], users_overlord::get_user($post->post_edit_user, '_full'), phpbb::$user->format_date($post->post_edited)) : '',
				'DELETED_MESSAGE'	=> ($post->post_deleted != 0) ? sprintf(phpbb::$user->lang['DELETED_MESSAGE'], users_overlord::get_user($post->post_delete_user, '_full'), phpbb::$user->format_date($post->post_deleted), $post->get_url('undelete')) : '',
				'POST_EDIT_REASON'	=> censor_text($post->post_edit_reason),

				'U_VIEW'			=> $post->get_url(),
				'U_EDIT'			=> $post->get_url('edit'),
			));
		break;
	}

	titania::page_header('ATTENTION');

	titania::page_footer(true, 'manage/attention_details.html');
}
else
{
	$type = request_var('type', '');
	$display_all = request_var('display_all', false);
	/*$close = (isset($_POST['close'])) ? true : false;
	$id_list = request_var('id_list', array(0));

	if ($close && sizeof($id_list))
	{
		$attention_object = new titania_attention;
		foreach ($id_list as $attention_id)
		{
			$attention_object->attention_id = $attention_id;
			$attention_object->load();
		}
	}*/

	switch ($type)
	{
		case 'reported' :
			$type = TITANIA_ATTENTION_REPORTED;
		break;

		case 'unapproved' :
			$type = TITANIA_ATTENTION_UNAPPROVED;
		break;

		default :
			$type = false;
		break;
	}


	$options = array(
		'attention_type'	=> $type,
		'display_all'		=> $display_all,
	);
	attention_overlord::display_attention_list($options);

	phpbb::$template->assign_vars(array(
		'S_ACTION'		=> titania_url::build_url('manage/attention'),
	));

	titania::page_header('ATTENTION');

	titania::page_footer(true, 'manage/attention.html');
}