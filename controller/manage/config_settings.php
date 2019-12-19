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

use phpbb\exception\http_exception;
use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\ext;

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

		add_form_key('config_settings');

		if ($this->request->is_set_post('submit'))
		{
			if (check_form_key('config_settings'))
			{
				$this->save();
			}
			else
			{
				throw new http_exception(200, 'FORM_INVALID');
			}
		}

		\phpbb::_include('functions_admin', 'make_forum_select');
		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		$this->template->assign_block_vars_array('options', $this->parse_configs());

		$this->display->generate_breadcrumbs([
			'CONFIG_SETTINGS'	=> $this->helper->route('phpbb.titania.manage.config_settings'),
		]);

		$this->template->assign_vars([
			'SECTION_NAME'	=> $this->user->lang('CONFIG_SETTINGS'),
			'FORUM_SELECT'	=> make_forum_select(false, false, false, false, true, false, true),

			'U_ACTION'		=> $this->helper->route('phpbb.titania.manage.config_settings'),
		]);

		return $this->helper->render('@phpbb_titania/manage/config_settings.html', 'CONFIG_SETTINGS');
	}

	public function save()
	{
		foreach ($this->ext_config->get_configurables() as $config => $type)
		{
			if (strpos($type, 'array') === 0)
			{
				$value = [];
				foreach ($this->ext_config->__get($config) as $key => $current)
				{
					$value[$key] = $this->request->variable($config . '_' . $key, '');
				}
			}
			else
			{
				$value = $this->request->variable($config, '');
			}

			$this->config->set(ext::TITANIA_CONFIG_PREFIX . $config, json_encode($value));
			$this->ext_config->__set($config, $value);
		}
	}

	protected function parse_configs()
	{
		$configurable = $this->ext_config->get_configurables();

		foreach ($configurable as $config => $type)
		{
			try
			{
				if (null !== $this->ext_config->__get($config))
				{
					$configurable[$config] = ['NAME' => $config, 'TYPE' => $type, 'VALUE' => $this->ext_config->{$config}];
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
