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
			$text 			= utf8_normalize_nfc(request_var('text', '', true));

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
				$faq->faq_order_id = $faq->get_next_order_id();

				// prepare a text to storage
				$faq->set_faq_text($text, true, true, true);

				// enable misc items in the text
				$faq->generate_text_for_storage(true, true, true);

				$faq->submit();

				$message = ($action == 'edit') ? phpbb::$user->lang['FAQ_EDITED'] : phpbb::$user->lang['FAQ_CREATED'];
				$message .= '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_FAQ'], '<a href="' . $faq->get_url('details') . '">', '</a>');
				$message .= '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_FAQ_LIST'], '<a href="' . titania::$contrib->get_url() . '/faq' . '">', '</a>');

				trigger_error($message);
			}
		}

		if ($action == 'edit')
		{
			$faq->load();
		}

		phpbb::$template->assign_vars(array(
			'U_ACTION'		=> $faq->get_url($action, $faq->faq_id),

			'S_EDIT'		=> true,

			'L_EDIT_FAQ'		=> ($action == 'edit') ? phpbb::$user->lang['EDIT_FAQ'] : phpbb::$user->lang['CREATE_FAQ'],

			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : false,

			'FAQ_SUBJECT'		=> $faq->faq_subject,
			'FAQ_TEXT'		=> $faq->get_faq_text(true),
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

			titania::error_box('SUCCESS', 'FAQ_DELETED', TITANIA_SUCCESS);
		}
		else
		{
			confirm_box(false, 'DELETE_FAQ', build_hidden_fields(array(
				'page'		=> 'faq',
				'action'	=> 'delete',
				'c'		=> titania::$contrib->contrib_id,
				'f'		=> $faq_id,
			)));
		}

		redirect(titania::$contrib->get_url() . '/faq');

	break;

	case 'move_up':
	case 'move_down':

		// Get current order id...
		$sql = 'SELECT faq_order_id as current_order
			FROM ' . TITANIA_CONTRIB_FAQ_TABLE . "
			WHERE faq_id = $faq_id";
		$result = phpbb::$db->sql_query($sql);
		$current_order = (int) phpbb::$db->sql_fetchfield('current_order');
		phpbb::$db->sql_freeresult($result);

		if (!($current_order == 0 && $action == 'move_up'))
		{
			// on move_down, switch position with next order_id...
			// on move_up, switch position with previous order_id...
			$switch_order_id = ($action == 'move_down') ? $current_order + 1 : $current_order - 1;

			$sql = 'UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . '
				SET faq_order_id = ' . $current_order . '
				WHERE faq_order_id = ' . $switch_order_id . '
					AND faq_id <> ' . $faq_id . '
					AND contrib_id = ' . titania::$contrib->contrib_id;
			phpbb::$db->sql_query($sql);

			// Only update the other entry too if the previous entry got updated
			if (phpbb::$db->sql_affectedrows())
			{
				$sql = 'UPDATE ' . TITANIA_CONTRIB_FAQ_TABLE . "
					SET faq_order_id = $switch_order_id
					WHERE faq_order_id = $current_order
						AND faq_id = $faq_id";
				phpbb::$db->sql_query($sql);
			}
		}
		
		redirect(titania::$contrib->get_url() . '/faq');

	break;

	case 'details':

		titania::page_header('FAQ_DETAILS');
		
		$faq->load();

		// increase a FAQ views counter
		$faq->increase_views_counter();

		phpbb::$template->assign_vars(array(
			'FAQ_SUBJECT'		=> $faq->faq_subject,
			'FAQ_TEXT'		=> $faq->get_faq_text(),
			'FAQ_VIEWS'		=> $faq->faq_views,

			'S_DETAILS'		=> true,

			'U_EDIT_FAQ'		=> (titania::$contrib->is_author || phpbb::$auth->acl_get('titania_faq_edit')) ? $faq->get_url('edit') : false,
		));

	case 'list':
	default:

		titania::page_header('FAQ_LIST');

		$sql = 'SELECT *
			FROM ' . TITANIA_CONTRIB_FAQ_TABLE . '
			WHERE contrib_id = ' . titania::$contrib->contrib_id . '
			ORDER BY faq_order_id ASC';
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			phpbb::$template->assign_block_vars('faqlist', array(
				'U_FAQ'			=> $faq->get_url('details', $row['faq_id']),

				'SUBJECT'		=> $row['faq_subject'],
				'VIEWS'			=> $row['faq_views'],

				'U_MOVE_UP'		=> (phpbb::$auth->acl_get('titania_faq_mod') || titania::$contrib->is_author) ? $faq->get_url('move_up', $row['faq_id']) : false,
				'U_MOVE_DOWN'		=> (phpbb::$auth->acl_get('titania_faq_mod') || titania::$contrib->is_author) ? $faq->get_url('move_down', $row['faq_id']) : false,
				'U_EDIT'		=> (phpbb::$auth->acl_get('titania_faq_mod') || phpbb::$auth->acl_get('titania_faq_edit') || titania::$contrib->is_author) ? $faq->get_url('edit', $row['faq_id']) : false,
				'U_DELETE'		=> (phpbb::$auth->acl_get('titania_faq_mod') || phpbb::$auth->acl_get('titania_faq_delete') || titania::$contrib->is_author) ? $faq->get_url('delete', $row['faq_id']) : false,
			));
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$template->assign_vars(array(
			'ICON_MOVE_UP'			=> '<img src="' . titania::$absolute_board . 'adm/images/icon_up.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_UP_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_up_disabled.gif" alt="' . phpbb::$user->lang['MOVE_UP'] . '" title="' . phpbb::$user->lang['MOVE_UP'] . '" />',
			'ICON_MOVE_DOWN'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_down.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
			'ICON_MOVE_DOWN_DISABLED'	=> '<img src="' . titania::$absolute_board . 'adm/images/icon_down_disabled.gif" alt="' . phpbb::$user->lang['MOVE_DOWN'] . '" title="' . phpbb::$user->lang['MOVE_DOWN'] . '" />',
			'ICON_EDIT'			=> '<img src="' . titania::$absolute_board . 'adm/images/icon_edit.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
			'ICON_EDIT_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_edit_disabled.gif" alt="' . phpbb::$user->lang['EDIT'] . '" title="' . phpbb::$user->lang['EDIT'] . '" />',
			'ICON_DELETE'			=> '<img src="' . titania::$absolute_board . 'adm/images/icon_delete.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',
			'ICON_DELETE_DISABLED'		=> '<img src="' . titania::$absolute_board . 'adm/images/icon_delete_disabled.gif" alt="' . phpbb::$user->lang['DELETE'] . '" title="' . phpbb::$user->lang['DELETE'] . '" />',

			'S_LIST'			=> true,

			'U_CREATE_FAQ'			=> (phpbb::$auth->acl_get('titania_faq_mod') || phpbb::$auth->acl_get('titania_faq_create') || titania::$contrib->is_author) ? $faq->get_url('create') : false,
		));

	break;
}

phpbb::$template->assign_vars(array(
	'CONTRIB_NAME'		=> titania::$contrib->contrib_name,
));

titania::page_footer(false, 'contributions/contribution_faq.html');