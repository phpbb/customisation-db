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

titania::add_lang('faq');

$faq_id = request_var('f', 0);
$action = request_var('action', '');
$error = array();

// Setup faq object
$faq = new titania_faq($faq_id);

if ($faq_id)
{
	if (!$faq->contrib_id)
	{
		// Faq does not exist
		trigger_error('FAQ_NOT_FOUND');
	}

	load_contrib($faq->contrib_id);
}
else
{
	load_contrib();
}

// Output the simple info on the contrib
titania::$contrib->assign_details(true);

switch ($action)
{
	case 'create':
	case 'edit':
		if (!phpbb::$auth->acl_get('u_titania_mod_faq_mod') && !phpbb::$auth->acl_get('u_titania_faq_' . $action) && !titania::$contrib->is_author && !titania::$contrib->is_active_coauthor)
		{
			titania::needs_auth();
		}

		// Load the message object
		$message = new titania_message($faq);
		$message->set_auth(array(
			'bbcode'		=> phpbb::$auth->acl_get('u_titania_bbcode'),
			'smilies'		=> phpbb::$auth->acl_get('u_titania_smilies'),
			'attachments'	=> true,
		));

		// Submit check...handles running $faq->post_data() if required
		$submit = $message->submit_check();

		$error = array_merge($error, $message->error);

		if ($submit)
		{

			$error = array_merge($error, $faq->validate());

			if (($validate_form_key = $message->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}

			if (!sizeof($error))
			{
				$faq->submit();
				$message->submit($faq->faq_id);

				$sql = 'SELECT right_id FROM ' . TITANIA_CONTRIB_FAQ_TABLE . '
					WHERE contrib_id = ' . titania::$contrib->contrib_id . '
					ORDER BY right_id DESC LIMIT 1';
				$result = phpbb::$db->sql_query($sql);
				$right_id = (string) phpbb::$db->sql_fetchfield('right_id');
				phpbb::$db->sql_freeresult($result);

				// Update the faqs table
				$sql_ary = array(
					'left_id'	=> $right_id + 1,
					'right_id'	=> $right_id + 2,
				);

				$sql = 'UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE faq_id = ' . (int) $faq->faq_id;
				phpbb::$db->sql_query($sql);

				redirect($faq->get_url());
			}
		}

		$message->display();

		phpbb::$template->assign_vars(array(
			'L_POST_A'			=> phpbb::$user->lang[(($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ')],
			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',

			'S_EDIT'			=> true,
			'S_POST_ACTION'		=> $faq->get_url($action, $faq->faq_id),
		));

		titania::page_header((($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ'));
	break;

	case 'delete':
		if (!phpbb::$auth->acl_get('u_titania_mod_faq_mod') && !phpbb::$auth->acl_get('u_titania_faq_delete') && !titania::$contrib->is_author && !titania::$contrib->is_active_coauthor)
		{
			titania::needs_auth();
		}

		if (titania::confirm_box(true))
		{
			$faq->delete();

			redirect(titania::$contrib->get_url('faq'));
		}
		else
		{
			titania::confirm_box(false, 'DELETE_FAQ', $faq->get_url('delete'));
		}

		redirect(titania::$contrib->get_url('faq'));

	break;

	case 'move_up':
	case 'move_down':
		if (!phpbb::$auth->acl_get('u_titania_mod_faq_mod') && !titania::$contrib->is_author && !titania::$contrib->is_active_coauthor)
		{
			titania::needs_auth();
		}

		if (!$faq_id)
		{
			trigger_error('FAQ_NOT_FOUND');
		}

		$sql = 'SELECT * FROM ' . TITANIA_CONTRIB_FAQ_TABLE . ' WHERE faq_id = ' . (int) $faq_id;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error('MUST_SELECT_FAQ');
		}

		$faq->move($row, $action, 1);
		redirect(titania::$contrib->get_url('faq'));

	break;

	default:
		if ($faq_id)
		{
			titania::page_header($faq->faq_subject . ' - ' . titania::$contrib->contrib_name);

			if ($faq->faq_access < titania::$access_level)
			{
				trigger_error('NOT_AUTHORISED');
			}

			// increase a FAQ views counter
			$faq->increase_views_counter();

			// tracking
			titania_tracking::track(TITANIA_FAQ, $faq_id);

			$message = $faq->generate_text_for_display();

			// Grab attachments
			$attachments = new titania_attachment(TITANIA_FAQ, $faq->faq_id);
			$attachments->load_attachments();
			$parsed_attachments = $attachments->parse_attachments($message);

			phpbb::$template->assign_vars(array(
				'FAQ_SUBJECT'			=> $faq->faq_subject,
				'FAQ_TEXT'				=> $message,
				'FAQ_VIEWS'				=> $faq->faq_views,

				'S_DETAILS'				=> true,
				'S_ACCESS_TEAMS'		=> ($faq->faq_access == TITANIA_ACCESS_TEAMS) ? true : false,
				'S_ACCESS_AUTHORS'		=> ($faq->faq_access == TITANIA_ACCESS_AUTHORS) ? true : false,

				'U_EDIT_FAQ'		=> (titania::$contrib->is_author || phpbb::$auth->acl_get('u_titania_faq_edit')) ? $faq->get_url('edit') : false,

				// Canonical URL
				'U_CANONICAL'		=> $faq->get_url(),
			));

			foreach ($parsed_attachments as $attachment)
			{
				phpbb::$template->assign_block_vars('attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment,
				));
			}
		}
		else
		{
			titania::page_header(titania::$contrib->contrib_name . ' - ' . phpbb::$user->lang['FAQ_LIST']);

			titania::_include('functions_display', 'titania_topic_folder_img');

			// Setup the sort tool
			$sort = new titania_sort();
			$sort->set_url(titania::$contrib->get_url('faq'));
			$sort->set_defaults(phpbb::$config['topics_per_page']);
			$sort->request();
			$faqs = array();

			$sql_ary = array(
				'SELECT' => 'f.*',
				'FROM'		=> array(
					TITANIA_CONTRIB_FAQ_TABLE => 'f',
				),
				'WHERE' => 'f.contrib_id = ' . titania::$contrib->contrib_id . '
						AND f.faq_access >= ' . titania::$access_level,
				'ORDER_BY'	=> 'f.left_id ASC',
			);

			// Main SQL Query
			$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

			// Handle pagination
			if ($sort->sql_count($sql_ary, 'faq_id'))
			{
				$sort->build_pagination($faq->get_url());

				// Get the data
				$result = phpbb::$db->sql_query_limit($sql, $sort->limit, $sort->start);

				while ($row = phpbb::$db->sql_fetchrow($result))
				{
					$faqs[$row['faq_id']] = $row;
				}
				phpbb::$db->sql_freeresult($result);

				// Grab the tracking info
				titania_tracking::get_tracks(TITANIA_FAQ, array_keys($faqs));
			}

			// Output
			foreach ($faqs as $id => $row)
			{
				// @todo probably should setup an edit time or something for better read tracking in case it was edited
				$folder_img = $folder_alt = '';
				$unread = (titania_tracking::get_track(TITANIA_FAQ, $id, true) === 0) ? true : false;
				titania_topic_folder_img($folder_img, $folder_alt, 0, $unread);

				phpbb::$template->assign_block_vars('faqlist', array(
					'U_FAQ'							=> $faq->get_url('', $row['faq_id']),

					'SUBJECT'						=> $row['faq_subject'],
					'VIEWS'							=> $row['faq_views'],

					'FOLDER_IMG'					=> phpbb::$user->img($folder_img, $folder_alt),
					'FOLDER_IMG_SRC'				=> phpbb::$user->img($folder_img, $folder_alt, false, '', 'src'),
					'FOLDER_IMG_ALT'				=> phpbb::$user->lang[$folder_alt],
					'FOLDER_IMG_ALT'				=> phpbb::$user->lang[$folder_alt],
					'FOLDER_IMG_WIDTH'				=> phpbb::$user->img($folder_img, '', false, '', 'width'),
					'FOLDER_IMG_HEIGHT'				=> phpbb::$user->img($folder_img, '', false, '', 'height'),

					'U_MOVE_UP'						=> (phpbb::$auth->acl_get('u_titania_mod_faq_mod') || titania::$contrib->is_author || titania::$contrib->is_active_coauthor) ? $faq->get_url('move_up', $row['faq_id']) : false,
					'U_MOVE_DOWN'					=> (phpbb::$auth->acl_get('u_titania_mod_faq_mod') || titania::$contrib->is_author || titania::$contrib->is_active_coauthor) ? $faq->get_url('move_down', $row['faq_id']) : false,
					'U_EDIT'						=> (phpbb::$auth->acl_get('u_titania_mod_faq_mod') || (phpbb::$auth->acl_get('u_titania_faq_edit') && (titania::$contrib->is_author || titania::$contrib->is_active_coauthor))) ? $faq->get_url('edit', $row['faq_id']) : false,
					'U_DELETE'						=> (phpbb::$auth->acl_get('u_titania_mod_faq_mod') || (phpbb::$auth->acl_get('u_titania_faq_delete') && (titania::$contrib->is_author || titania::$contrib->is_active_coauthor))) ? $faq->get_url('delete', $row['faq_id']) : false,


					'S_ACCESS_TEAMS'				=> ($row['faq_access'] == TITANIA_ACCESS_TEAMS) ? true : false,
					'S_ACCESS_AUTHORS'				=> ($row['faq_access'] == TITANIA_ACCESS_AUTHORS) ? true : false,
				));
			}

			phpbb::$template->assign_vars(array(
				'ICON_MOVE_UP'				=> '<img src="' . titania::$images_path . 'icon_up.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
				'ICON_MOVE_UP_DISABLED'		=> '<img src="' . titania::$images_path . 'icon_up_disabled.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
				'ICON_MOVE_DOWN'			=> '<img src="' . titania::$images_path . 'icon_down.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
				'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . titania::$images_path . 'icon_down_disabled.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
				'ICON_EDIT'					=> '<img src="' . titania::$images_path . 'icon_edit.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
				'ICON_EDIT_DISABLED'		=> '<img src="' . titania::$images_path . 'icon_edit_disabled.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
				'ICON_DELETE'				=> '<img src="' . titania::$images_path . 'icon_delete.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',
				'ICON_DELETE_DISABLED'		=> '<img src="' . titania::$images_path . 'icon_delete_disabled.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',

				'S_LIST'					=> true,

				'U_CREATE_FAQ'				=> (phpbb::$auth->acl_get('u_titania_mod_faq_mod') || (phpbb::$auth->acl_get('u_titania_faq_create') && (titania::$contrib->is_author || titania::$contrib->is_active_coauthor))) ? $faq->get_url('create') : false,

				// Canonical URL
				'U_CANONICAL'				=> $sort->build_canonical(),
			));
		}
	break;
}

phpbb::$template->assign_vars(array(
	'CONTRIB_NAME'		=> titania::$contrib->contrib_name,
));

titania::page_footer(false, 'contributions/contribution_faq.html');