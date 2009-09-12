<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2009 Customisation Database Team
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

titania::load_object('revision');
titania::add_lang('revisions');

$action 	= request_var('action', '');
$submit		= isset($_POST['submit']) ? true : false;

$revision = new titania_revision();

load_contrib();

switch ($action)
{
	case 'create':
	case 'edit':
		// For now we will only check basic permisions. Must be an anther or team member to manage revisions.
		if (!titania::$contrib->is_author || titania::$access_level > TITANIA_ACCESS_TEAMS)
		{
			return;
		}

		if ($submit)
		{
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

		add_form_key('postform');

		phpbb::$template->assign_vars(array(
			'L_POST_A'			=> phpbb::$user->lang[(($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ')],

			'S_EDIT'			=> true,
			'S_POST_ACTION'		=> $faq->get_url($action, $faq->faq_id),
		));

		titania::page_header((($action == 'edit') ? 'EDIT_FAQ' : 'CREATE_FAQ'));
	break;

	default:

		titania::page_header('REVISIONS');

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

				'SUBJECT'		=> $row['faq_subject'],
				'VIEWS'			=> $row['faq_views'],

			));
		}
		phpbb::$db->sql_freeresult($result);

		$can_add = ((titania::$contrib->is_author) ? true : ((titania::$access_level > TITANIA_ACCESS_TEAMS) ? false : true));

		phpbb::$template->assign_vars(array(
			'S_ADD'	=> $can_add,
		));
	break;
}

phpbb::$template->assign_vars(array(
	'CONTRIB_NAME'		=> titania::$contrib->contrib_name,
));

titania::page_footer(false, 'contributions/contribution_revisions.html');