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

namespace phpbb\titania\contribution\official_tool;

use phpbb\titania\contribution\type\base;

class type extends base
{
	const ID = 4;
	const NAME = 'official_tool';
	const URL = 'official_tool';

	/**
	 * @{inheritDoc}
	 */
	protected function configure()
	{
		// Official tools do not require validation (only team members can submit them)
		$this->require_validation = false;
		$this->use_queue = false;

		// Language strings
		$this->lang = array(
			'lang'		=> $this->user->lang('OFFICIAL_TOOL'),
			'langs'		=> $this->user->lang('OFFICIAL_TOOLS'),
			'new'		=> $this->user->lang('OFFICIAL_TOOL_CONTRIB_NEW'),
			'cleaned'	=> $this->user->lang('OFFICIAL_TOOL_CONTRIB_CLEANED'),
			'hidden'	=> $this->user->lang('OFFICIAL_TOOL_CONTRIB_HIDDEN'),
			'disabled'	=> $this->user->lang('OFFICIAL_TOOL_CONTRIB_DISABLED'),
		);
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		switch ($action)
		{
			// No queue for the official tools
			case 'queue_discussion' :
			case 'view' :
			case 'validate' :
				return false;
			break;

			case 'submit' :
			case 'moderate' :
				return $this->auth->acl_gets(array(
					'u_titania_mod_official_tool_moderate',
					'u_titania_mod_contrib_mod',
					'u_titania_admin',
				));
			break;
		}

		return false;
	}
}
