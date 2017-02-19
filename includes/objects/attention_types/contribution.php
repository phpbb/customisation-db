<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

use phpbb\titania\ext;

class titania_attention_contribution extends titania_attention
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Contrib object for the source contrib.
	 *
	 * @var object
	 */
	public $contrib;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->user = \phpbb::$user;
	}

	/**
	* {@inheritDoc}
	*/
	public function check_auth()
	{
		return $this->contrib->type->acl_get('moderate');
	}

	/**
	* Set up contrib object.
	*/
	public function load_source_object()
	{
		if (!is_object($this->contrib))
		{
			$this->contrib = new titania_contribution();
			$this->contrib->load((int) $this->attention_object_id);
		}

		return (is_object($this->contrib)) ? true : false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_title()
	{
		return $this->contrib->contrib_name;
	}

	public function get_lang_string($label)
	{
		$this->user->add_lang_ext('phpbb/titania', 'contributions');

		$labels = array(
			'object'	=> 'CONTRIBUTION',
			'closed'	=> 'CLOSED',
			'closed_by'	=> 'CLOSED_BY',
		);

		switch ((int) $this->attention_type)
		{
			case ext::TITANIA_ATTENTION_REPORTED :
				$labels = array_merge($labels, array(
					'reason' => 'REPORTED',
				));
			break;

			case ext::TITANIA_ATTENTION_CATS_CHANGED :
				$labels = array_merge($labels, array(
					'reason' => 'ATTENTION_CONTRIB_CATEGORIES_CHANGED',
				));
			break;

			case ext::TITANIA_ATTENTION_NAME_CHANGED :
				$labels = array_merge($labels, array(
					'reason' => 'ATTENTION_CONTRIB_NAME_CHANGED',
				));
			break;

			case ext::TITANIA_ATTENTION_DESC_CHANGED :
				$labels = array_merge($labels, array(
					'reason' => 'ATTENTION_CONTRIB_DESC_CHANGED',
				));
			break;
		}

		return phpbb::$user->lang[$labels[$label]];
	}

	/**
	* Assign details for the source contribution.
	*/
	public function assign_source_object_details()
	{
		users_overlord::load_users(array($this->contrib->contrib_user_id));
		users_overlord::assign_details($this->contrib->contrib_user_id, 'POSTER_', true);

		phpbb::$template->assign_vars(array(
			'OBJECT_TYPE'			=> $this->get_lang_string('object'),

			'POST_SUBJECT'			=> censor_text($this->contrib->contrib_name),
			'POST_DATE'				=> phpbb::$user->format_date($this->contrib->contrib_last_update),
			'POST_TEXT'				=> $this->contrib->generate_text_for_display(),

			'U_VIEW'				=> $this->contrib->get_url(),
			'U_EDIT'				=> $this->contrib->get_url('manage'),

			'SECTION_NAME'			=> '<a href="' . $this->contrib->get_url() . '">' . censor_text($this->contrib->contrib_name) . '</a>  - ' . phpbb::$user->lang['ATTENTION'],
		));
	}

	/**
	* Assign extra details for the attention item.
	*/
	public function get_extra_details()
	{
		$vars = array(
			'ATTENTION_DESCRIPTION'	=> $this->get_description_diff(),
		);

		return $vars;
	}
}
