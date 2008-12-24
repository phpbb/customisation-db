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

require(TITANIA_ROOT . 'includes/class_faq.' . PHP_EXT);

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

		$faq = new titania_faq($faq_id, 'mods');

		switch ($mode)
		{
			case 'main':

			break;

			case 'manage':
				switch ($action)
				{
					case 'add':
					case 'edit':
						if ($submit)
						{
							$subject 	= utf8_normalize_nfc(request_var('subject', '', true));
							$text 		= utf8_normalize_nfc(request_var('text', '', true));

							$error = array();
							
							if (empty($subject))
							{
								$error[] = $user->lang['SUBJECT_EMPTY'];
							}
							
							if (empty($text))
							{
								$error = $user->lang['TEXT_EMPTY'];
							}
							
							if (!sizeof($error))
							{
								/*
								 * todo
								 */
								$faq->submit();
							}
						}

						if ($mode == 'edit')
						{
							$faq->load();
						}
						
						$this->tpl_name 	= 'contrib_faq_edit';
						$this->page_title 	= ($action == 'edit') ? 'FAQ_EDITION' : 'FAQ_ADDITION';						
						
						$template->assign_vars(array(
							'U_ACTION'		=> $this->u_action . $this->page,

							'ERRORS'		=> (sizeof($error)) ? implode('<br />', $error) : false,
							
							'FAQ_SUBJECT'	=> ($submit) ? $subject : $faq->faq_subject,
							'FAQ_TEXT'		=> ($submit) ? $text : $faq->faq_text,
						));
					break;

					case 'delete':
						if ($submit)
						{
							if (confirm_box(true))
							{
								$faq->delete();
								
								// todo: redirect to faqs list
							}
							else
							{
								$redirect_url = append_sid(TITANIA_ROOT . 'mods/index.php', "id=faq&amp;mode=view&amp;faq_id=$faq_id");
							}
							redirect($redirect_url);
						}
						else
						{
							$s_hidden_fields = build_hidden_fields(array(
								'submit'	=> true,
								'faq_id'	=> $faq_id
							));
							confirm_box(false, 'DELETE_FAQ', $s_hidden_fields);
						}
					break;
				}
			break;

			case 'view':
			default:
				if ($faq_id)
				{
					$this->tpl_name 	= 'contrib_faq_details';
					$this->page_title 	= 'MODS_FAQ_DETAILS';

					$found = $faq->faq_details('mod');
					
					if (!$found)
					{
						trigger_error('FAQ_NOT_FOUND');
					}
					
					$faq->similar_faqs();
				}
				else
				{
					$contrib_id = request_var('mod', 0);

					if (!$contrib_id)
					{
						trigger_error('NO_CONTRIB_SELECTED');
					}

					$this->tpl_name 	= 'contrib_faq_list';
					$this->page_title 	= 'MODS_FAQ_LIST';

					$found = $faq->faq_list($contrib_id, 'mod');
					
					if (!$found)
					{
						trigger_error('NO_FAQ');
					}
				}
			break;
		}
	}
}
