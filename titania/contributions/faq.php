<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 Customisation Database Team
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

titania::load_object('faq');
titania::add_lang('faq');

$faq_id		= request_var('f', 0);
$action 	= request_var('action', '');
$submit		= isset($_POST['submit']) ? true : false;

$faq = new titania_faq($faq_id);

// Load the FAQ/Contrib item
if ($faq_id)
{
	$faq->load();

	load_contrib($faq->contrib_id);
}
else
{
	load_contrib();
}

switch ($action)
{
	case 'create':
	case 'edit':
		if (!phpbb::$auth->acl_get('titania_faq_mod') && !phpbb::$auth->acl_get('titania_faq_' . $action) && !titania::$contrib->is_author)
		{
			return;
		}

		// Load the message object
		titania::load_tool('message');
		$message = new titania_message($faq);
		$message->set_auth(array(
			'bbcode'	=> phpbb::$auth->acl_get('titania_bbcode'),
			'smilies'	=> phpbb::$auth->acl_get('titania_smilies'),
		));

		if ($submit)
		{
			$post_data = $message->request_data();

			$faq->post_data($post_data);

			$error = $faq->validate();

			if (($validate_form_key = $message->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}

			if (sizeof($error))
			{
				$template->assign_var('ERROR', implode('<br />', $error));
			}
			else
			{
				$faq->submit();

				redirect($faq->get_url());
			}
		}

		$message->display();

		phpbb::$template->assign_vars(array(
			'L_POST_A'			=> phpbb::$user->lang[(($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ')],

			'S_EDIT'			=> true,
			'S_POST_ACTION'		=> $faq->get_url($action, $faq->faq_id),
		));

		titania::page_header((($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ'));
	break;

	case 'delete':

		if (!phpbb::$auth->acl_get('titania_faq_mod') && !phpbb::$auth->acl_get('titania_faq_delete') && !titania::$contrib->is_author)
		{
			return;
		}

		if (titania::confirm_box(true))
		{
			$faq->delete();

			// fix an entries order
			$faq->cleanup_order();

			trigger_error(phpbb::$user->lang['FAQ_DELETED'] . titania::back_link(titania::$contrib->get_url('faq')));
		}
		else
		{
			titania::confirm_box(false, 'DELETE_FAQ', $faq->get_url('delete'));
		}

		redirect(titania::$contrib->get_url('faq'));

	break;

	case 'move_up':
	case 'move_down':

		$faq->move($action);

		redirect(titania::$contrib->get_url('faq'));

	break;

	default:
		if ($faq_id)
		{
			titania::page_header('FAQ_DETAILS');

			if ($faq->faq_access < titania::$access_level)
			{
				trigger_error('NOT_AUTHORISED');
			}

			// increase a FAQ views counter
			$faq->increase_views_counter();

			phpbb::$template->assign_vars(array(
				'FAQ_SUBJECT'		=> $faq->faq_subject,
				'FAQ_TEXT'			=> $faq->generate_text_for_display(),
				'FAQ_VIEWS'			=> $faq->faq_views,

				'S_DETAILS'			=> true,

				'U_EDIT_FAQ'		=> (titania::$contrib->is_author || phpbb::$auth->acl_get('titania_faq_edit')) ? $faq->get_url('edit') : false,
			));
		}
		else
		{
			titania::page_header('FAQ_LIST');

			// Titania's access
			$sql_in = array();

			switch (titania::$access_level)
			{
				case 0:
					$sql_in[] = TITANIA_ACCESS_TEAMS;
				case 1:
					$sql_in[] = TITANIA_ACCESS_AUTHORS;
				case 2:
				default:
					$sql_in[] = TITANIA_ACCESS_PUBLIC;
				break;
			}

			$sql = 'SELECT *
				FROM ' . TITANIA_CONTRIB_FAQ_TABLE . '
				WHERE contrib_id = ' . titania::$contrib->contrib_id . '
					AND ' . $db->sql_in_set('faq_access', $sql_in) . '
				ORDER BY faq_order_id ASC';
			$result = phpbb::$db->sql_query($sql);

			while ($row = phpbb::$db->sql_fetchrow($result))
			{
				phpbb::$template->assign_block_vars('faqlist', array(
					'U_FAQ'			=> $faq->get_url('', $row['faq_id']),

					'SUBJECT'		=> $row['faq_subject'],
					'VIEWS'			=> $row['faq_views'],

					'U_MOVE_UP'		=> (phpbb::$auth->acl_get('titania_faq_mod') || titania::$contrib->is_author) ? $faq->get_url('move_up', $row['faq_id']) : false,
					'U_MOVE_DOWN'	=> (phpbb::$auth->acl_get('titania_faq_mod') || titania::$contrib->is_author) ? $faq->get_url('move_down', $row['faq_id']) : false,
					'U_EDIT'		=> (phpbb::$auth->acl_get('titania_faq_mod') || phpbb::$auth->acl_get('titania_faq_edit') || titania::$contrib->is_author) ? $faq->get_url('edit', $row['faq_id']) : false,
					'U_DELETE'		=> (phpbb::$auth->acl_get('titania_faq_mod') || phpbb::$auth->acl_get('titania_faq_delete') || titania::$contrib->is_author) ? $faq->get_url('delete', $row['faq_id']) : false,
				));
			}
			phpbb::$db->sql_freeresult($result);

			phpbb::$template->assign_vars(array(
				'ICON_MOVE_UP'				=> '<img src="' . titania::$absolute_board . 'adm/images/icon_up.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
				'ICON_MOVE_UP_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_up_disabled.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
				'ICON_MOVE_DOWN'			=> '<img src="' . titania::$absolute_board . 'adm/images/icon_down.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
				'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . titania::$absolute_board . 'adm/images/icon_down_disabled.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
				'ICON_EDIT'					=> '<img src="' . titania::$absolute_board . 'adm/images/icon_edit.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
				'ICON_EDIT_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_edit_disabled.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
				'ICON_DELETE'				=> '<img src="' . titania::$absolute_board . 'adm/images/icon_delete.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',
				'ICON_DELETE_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_delete_disabled.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',

				'S_LIST'					=> true,

				'U_CREATE_FAQ'				=> (phpbb::$auth->acl_get('titania_faq_mod') || phpbb::$auth->acl_get('titania_faq_create') || titania::$contrib->is_author) ? $faq->get_url('create') : false,
			));
		}
	break;
}

phpbb::$template->assign_vars(array(
	'CONTRIB_NAME'		=> titania::$contrib->contrib_name,
));

titania::page_footer(false, 'contributions/contribution_faq.html');