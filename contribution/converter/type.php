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

namespace phpbb\titania\contribution\converter;

use phpbb\titania\contribution\type\base;

class type extends base
{
	const ID = 3;
	const NAME = 'converter';
	const URL = 'converter';

	/**
	 * @{inheritDoc}
	 */
	public function configure()
	{
		$this->forum_database = $this->ext_config->forum_converter_database;
		$this->forum_robot = $this->ext_config->forum_converter_robot;

		// Language strigs
		$this->lang = $this->user->lang('CONVERTER');
		$this->langs = $this->user->lang('CONVERTERS');
		$this->validation_subject = 'CONVERTER_VALIDATION';
		$this->validation_message_approve = 'CONVERTER_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'CONVERTER_VALIDATION_MESSAGE_DENY';
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// Can submit a converter
			case 'submit' :
				return true;
			break;

			// Can view the convertor queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_converter_queue_discussion');
			break;

			// Can view the convertor queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_converter_queue');
			break;

			// Can validate convertors in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_converter_validate');
			break;

			// Can moderate convertors
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_converter_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;
		}

		return false;
	}
}
