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

namespace phpbb\titania\contribution\mod;

use phpbb\titania\contribution\type\base;

class type extends base
{
	public const ID = 1;
	public const NAME = 'mod';
	public const URL = 'mod';

	/**
	 * {@inheritDoc}
	 */
	protected function configure()
	{
		$this->allowed_branches = ['<=', 30];

		$this->restore_root = true;
		$this->clean_package = true;
		$this->root_search = [
			'files' => [
				'required' => 'install*.xml',
			],
		];

		$this->forum_database = $this->ext_config->forum_mod_database;
		$this->forum_robot = $this->ext_config->forum_mod_robot;

		$this->author_count = 'author_mods';

		// Language strings
		$this->lang = [
			'lang'     => $this->user->lang('MODIFICATION'),
			'langs'    => $this->user->lang('MODIFICATIONS'),
			'new'      => $this->user->lang('MOD_CONTRIB_NEW'),
			'cleaned'  => $this->user->lang('MOD_CONTRIB_CLEANED'),
			'hidden'   => $this->user->lang('MOD_CONTRIB_HIDDEN'),
			'disabled' => $this->user->lang('MOD_CONTRIB_DISABLED'),
		];
		$this->validation_subject = 'MOD_VALIDATION';
		$this->validation_message_approve = 'MOD_VALIDATION_MESSAGE_APPROVE';
		$this->validation_message_deny = 'MOD_VALIDATION_MESSAGE_DENY';
		$this->create_public = 'MOD_CREATE_PUBLIC';
		$this->reply_public = 'MOD_REPLY_PUBLIC';
		$this->update_public = 'MOD_UPDATE_PUBLIC';
		$this->upload_agreement = 'MOD_UPLOAD_AGREEMENT';
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// Can submit a mod
			case 'submit' :
				return false; // Disabled MODs due to 3.0.x EOM
			break;

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return $this->auth->acl_get('u_titania_mod_modification_queue_discussion');
			break;

			// Can view the mod queue
			case 'view' :
				return $this->auth->acl_get('u_titania_mod_modification_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return $this->auth->acl_get('u_titania_mod_modification_validate');
			break;

			// Can moderate mods
			case 'moderate' :
				return $this->auth->acl_gets([
					'u_titania_mod_modification_moderate',
					'u_titania_mod_contrib_mod',
				]);
			break;
		}

		return false;
	}
}
