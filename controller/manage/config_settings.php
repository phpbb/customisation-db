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
	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\group\helper $group_helper */
	protected $group_helper;

	/** @var \phpbb\titania\message\message */
	protected $message;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                  $auth
	 * @param \phpbb\config\config              $config
	 * @param \phpbb\config\db_text             $config_text
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template          $template
	 * @param \phpbb\user                       $user
	 * @param \phpbb\titania\cache\service      $cache
	 * @param \phpbb\titania\controller\helper  $helper
	 * @param type_collection                   $types
	 * @param \phpbb\request\request            $request
	 * @param \phpbb\titania\config\config      $ext_config
	 * @param \phpbb\titania\display            $display
	 * @param \phpbb\titania\message\message    $message
	 * @param \phpbb\group\helper               $group_helper
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\config\db_text $config_text, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request $request, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\message\message $message, \phpbb\group\helper $group_helper)
	{
		parent::__construct($auth, $config, $db, $template, $user, $cache, $helper, $types, $request, $ext_config, $display);

		$this->message = $message;
		$this->group_helper = $group_helper;
		$this->config_text = $config_text;
	}

	/**
	 * Display the configuration settings
	 *
	 * @throws \phpbb\titania\entity\UnknownPropertyException
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
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

		$this->display->generate_breadcrumbs(array(
			'CONFIG_SETTINGS'	=> $this->helper->route('phpbb.titania.manage.config_settings'),
		));

		$this->template->assign_vars(array(
			'SECTION_NAME'	=> $this->user->lang('CONFIG_SETTINGS'),
			'FORUM_SELECT'	=> make_forum_select(false, false, false, false, true, false, true),
			'GROUP_SELECT'	=> $this->get_groups(),

			'U_ACTION'		=> $this->helper->route('phpbb.titania.manage.config_settings'),
		));

		return $this->helper->render('@phpbb_titania/manage/config_settings.html', 'CONFIG_SETTINGS');
	}

	/**
	 * Save configuration settings. Saves them to phpBB's config table and
	 * updates titania's config object with the new values.
	 *
	 * @throws \phpbb\titania\entity\UnknownPropertyException
	 */
	public function save()
	{
		foreach ($this->ext_config->get_configurables() as $config => $type)
		{
			if (strpos($type, 'array') === 0)
			{
				$value = array();
				$type = explode('|', $type);
				foreach ($this->ext_config->__get($config) as $key => $current)
				{
					$value[$key] = $this->request->variable($config . '_' . $key, $this->get_default($type[1]));
				}
				$this->config_text->set(ext::TITANIA_CONFIG_PREFIX . $config, json_encode($value));
				$this->config->delete(ext::TITANIA_CONFIG_PREFIX . $config); // delete it from config if its still there for any reason
			}
			else
			{
				$value = $this->request->variable($config, $this->get_default($type));
				$this->config->set(ext::TITANIA_CONFIG_PREFIX . $config, json_encode($value));
			}

			$this->ext_config->__set($config, $value);
		}
	}

	/**
	 * Gets all the configurable options and creates an array of them
	 * and their current values in titania's config object.
	 *
	 * @return array
	 */
	protected function parse_configs()
	{
		$configurable = $this->ext_config->get_configurables();

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

	/**
	 * Get all user groups (except bots and guests) and return
	 * an array of their IDs and translated group names.
	 *
	 * @return array
	 */
	protected function get_groups()
	{
		$sql = 'SELECT group_id, group_name
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $this->db->sql_in_set('group_name', array('BOTS', 'GUESTS'), true, true) . '
			ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);

		$rowset = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[$row['group_id']] = array(
				'group_id' => $row['group_id'],
				'group_name' => $this->group_helper->get_name($row['group_name']),
			);
		}
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * Define the default value and type casting for the various
	 * configuration types we are using. Some are obvious, like int
	 * string and bool. Some are custom like the forums and groups
	 * types.
	 *
	 * @param $type
	 * @return array|bool|int|string
	 */
	protected function get_default($type)
	{
		switch ($type)
		{
			case 'int':
			case 'forums':
				return 0;
			break;
			case 'bool':
				return false;
			break;
			case 'groups':
				return array(0);
			break;
			default:
				return '';
			break;
		}
	}
}
