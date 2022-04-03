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

namespace phpbb\titania\contribution\bridge;

use phpbb\titania\contribution\type\base;

class type extends base
{
	const ID = 5;
	const NAME = 'bridge';
	const URL = 'bridge';

	/**
	 * @{inheritDoc}
	 */
	public function configure()
	{
		// Language strings
		$this->lang = array(
			'lang'		=> $this->user->lang('BRIDGE'),
			'langs'		=> $this->user->lang('BRIDGES'),
			'new'		=> $this->user->lang('BRIDGE_CONTRIB_NEW'),
			'cleaned'	=> $this->user->lang('BRIDGE_CONTRIB_CLEANED'),
			'hidden'	=> $this->user->lang('BRIDGE_CONTRIB_HIDDEN'),
			'disabled'	=> $this->user->lang('BRIDGE_CONTRIB_DISABLED'),
		);
		$this->validation_subject = 'BRIDGE_VALIDATION';
		$this->validation_message_approve = 'BRIDGE_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'BRIDGE_VALIDATION_MESSAGE_DENY';
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// Can submit a bridge
			case 'submit' :
				return true;
			break;

			// Can view the bridge queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_bridge_queue_discussion');
			break;

			// Can view the bridge queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_bridge_queue');
			break;

			// Can validate bridges in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_bridge_validate');
			break;

			// Can moderate bridges
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_bridge_moderate',
					'u_titania_mod_contrib_mod',
				));
			break;
		}

		return false;
	}
}
