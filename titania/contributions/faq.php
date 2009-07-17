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

require(TITANIA_ROOT . 'includes/objects/faq.' . PHP_EXT);

titania::add_lang('faq');

$faq_id		= request_var('f', 0);
$action 	= request_var('action', '');
$submit		= isset($_POST['submit']) ? true : false;

add_form_key('mods_faq');

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

		titania::page_header((($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ'));

		$errors = array();

		if ($submit)
		{
			if (!check_form_key('mods_faq'))
			{
				trigger_error('INVALID_FORM');
			}

			$faq->faq_subject 	= utf8_normalize_nfc(request_var('subject', '', true));
			$text 				= utf8_normalize_nfc(request_var('text', '', true));
			$faq->faq_access	= request_var('faq_access', TITANIA_ACCESS_PUBLIC);

			if (empty($faq->faq_subject))
			{
				$errors[] = phpbb::$user->lang['SUBJECT_EMPTY'];
			}

			if (empty($text))
			{
				$errors[] = phpbb::$user->lang['TEXT_EMPTY'];
			}

			if (!sizeof($errors))
			{
				// set order id after the last
				$faq->faq_order_id = ($action == 'add') ? $faq->get_next_order_id() : $faq->faq_order_id;

				// prepare a text to storage
				$faq->set_faq_text($text, true, true, true);

				// enable misc items in the text
				$faq->generate_text_for_storage(true, true, true);

				$faq->submit();

				$message = ($action == 'edit') ? phpbb::$user->lang['FAQ_EDITED'] : phpbb::$user->lang['FAQ_CREATED'];

				titania::error_box($message, TITANIA_SUCCESS);
			}
		}

		phpbb::$template->assign_vars(array(
			'U_ACTION'			=> $faq->get_url($action, $faq->faq_id),

			'S_EDIT'			=> true,

			'L_EDIT_FAQ'		=> ($action == 'edit') ? phpbb::$user->lang['EDIT_FAQ'] : phpbb::$user->lang['CREATE_FAQ'],

			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : false,

			'FAQ_SUBJECT'		=> $faq->faq_subject,
			'FAQ_TEXT'			=> $faq->get_faq_text(true),

			'S_ACCESS_OPTIONS'	=> titania_access_select($faq->faq_access),
		));

	break;

	case 'delete':

		titania::page_header('DELETE_FAQ');

		if (!phpbb::$auth->acl_get('titania_faq_mod') && !phpbb::$auth->acl_get('titania_faq_delete') && !titania::$contrib->is_author)
		{
			return;
		}

		if (confirm_box(true))
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
				'FAQ_TEXT'			=> $faq->get_faq_text(),
				'FAQ_VIEWS'			=> $faq->faq_views,

				'S_DETAILS'			=> true,

				'U_EDIT_FAQ'		=> (titania::$contrib->is_author || phpbb::$auth->acl_get('titania_faq_edit')) ? $faq->get_url('edit') : false,
			));
		}
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

	break;
}

phpbb::$template->assign_vars(array(
	'CONTRIB_NAME'		=> titania::$contrib->contrib_name,
));

titania::page_footer(false, 'contributions/contribution_faq.html');