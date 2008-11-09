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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* faq_main
* Class for FAQ module
* @package mods
*/
class mods_faq extends titania_object
{
	public $p_master;
	public $u_action;

	/**
	 * Constructor
	 */
	public function __construct($p_master)
	{
		global $user;

		$this->p_master = $p_master;

		$this->page = $user->page['script_path'] . $user->page['page_name'];
	}

	/**
	 * main method for this module
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $user, $template, $cache;

		$user->add_lang(array('titania_contrib'));

		$faq_id		= request_var('faq_id', 0);
		$action 	= request_var('action', '');

		$submit		= isset($_POST['submit']) ? true : false;

		$form_key = 'mods_faq';
		add_form_key($form_key);

		require(TITANIA_ROOT . 'includes/class_faq.' . PHP_EXT);

		$faq = new titania_faq($faq_id);

		switch ($mode)
		{
			case 'main':

			break;

			case 'manage':
				if ($action == 'add' || $action == 'edit')
				{
					if ($submit)
					{
						$subject 	= utf8_normalize_nfc(request_var('subject', '', true));
						$text 		= utf8_normalize_nfc(request_var('text', '', true));

						$faq->submit();
					}

					if ($mode == 'edit')
					{
						$faq->load();
					}

					$template->assign_vars(array(
						'U_ACTION'		=> $this->u_action . $this->page,

						'FAQ_SUBJECT'	=> $faq->faq_subject,
						'FAQ_TEXT'		=> $faq->faq_text
					));
				}
				else if ($action == 'delete')
				{
					if (confirm_box(true))
					{
						$faq->delete();
					}
					else
					{
						$s_hidden_fields = build_hidden_fields(array(
							'submit'	=> true,
							'faq_id'	=> $faq_id
						));
						confirm_box(false, 'DELETE_FAQ', $s_hidden_fields);
					}
				}
			break;

			case 'view':
			default:
				if ($faq_id)
				{
					$this->tpl_name = 'mods/mod_faq_details';
					$this->page_title = 'MODS_FAQ_DETAILS';

					$faq->faq_details($faq_id);

					$faq->similar_faq($faq_id);
				}
				else
				{
					$contrib_id = request_var('contrib_id', 0);

					if (!$contrib_id)
					{
						titania::trigger_error('NO_CONTRIB_SELECTED');
					}

					$this->tpl_name = 'mods/mod_faq_list';
					$this->page_title = 'MODS_FAQ_LIST';

					$found = $faq->faq_list($contrib_id);

					if (!$found)
					{
						titania::error_box('ERROR', 'FAQ_NOT_FOUND', ERROR_ERROR);
					}
				}
			break;
		}
	}
}
