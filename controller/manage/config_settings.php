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

namespace phpbb\titania\controller\manage;

use phpbb\titania\contribution\type\collection as type_collection;

class config_settings extends base
{
	/** @var \phpbb\titania\message\message */
	protected $message;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\controller\helper $helper
	 * @param type_collection $types
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\message\message $message
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\message\message $message)
	{
		parent::__construct($auth, $config, $db, $template, $user, $cache, $helper, $types, $request, $ext_config, $display);

		$this->message = $message;
	}

	public function display()
	{
		if (!$this->auth->acl_get('u_titania_admin'))
		{
			return $this->helper->needs_auth();
		}

		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		$this->template->assign_block_vars_array('options', $this->get_configs());

		$this->display->generate_breadcrumbs(array(
			'CONFIG_SETTINGS'		=> $this->helper->route('phpbb.titania.manage.config_settings'),
		));

		$this->template->assign_vars(array(
			'SECTION_NAME'			=> $this->user->lang['CONFIG_SETTINGS'],

			//'S_MANAGE'			=> true,
		));

		return $this->helper->render('manage/config_settings.html', 'CONFIG_SETTINGS');
	}

	protected function get_configs()
	{
		$configurable = array(
			'phpbb_root_path' 				=> 'string',
			'phpbb_script_path' 			=> 'string',
			'titania_script_path' 			=> 'string',
			'table_prefix' 					=> 'string',
			'search_backend' 				=> 'string',
			'forum_mod_database' 			=> 'array',
			'forum_mod_robot' 				=> 'int',
			'forum_extension_database' 		=> 'array',
			'forum_extension_robot' 		=> 'int',
			'forum_style_database' 			=> 'array',
			'forum_style_robot' 			=> 'int',
			'colorizeit' 					=> 'string',
			'colorizeit_auth' 				=> 'string',
			'colorizeit_var' 				=> 'string',
			'colorizeit_value' 				=> 'string',
			'can_modify_style_demo_url' 	=> 'bool',
			'demo_style_path' 				=> 'array',
			'demo_style_url' 				=> 'array',
			'demo_style_hook' 				=> 'array',
			//'team_groups'					=> 'array',
			//'upload_max_filesize'			=> 'array',
			'cleanup_titania' 				=> 'bool',
		);

		foreach ($configurable as $config => $type)
		{
			try
			{
				if (null !== $this->ext_config->__get($config))
				{
					$configurable[$config] = array('NAME' => $config, 'TYPE' => $type, 'VALUE' => $this->ext_config->{$config});
					continue;
				}
			}
			catch (\Exception $e)
			{
				// ignore exception, we only care about valid config settings
			}

			unset($configurable[$config]);
		}

		return $configurable;
	}
}
