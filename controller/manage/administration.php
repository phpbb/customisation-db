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

class administration extends base
{
	/**
	* List administration tools.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function list_tools()
	{
		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$this->setup();

		$tools = array(
			'MANAGE_CATEGORIES'		=> array(
				'route'		=> 'phpbb.titania.manage.categories',
				'ajax'		=> false,
			),
			'REBUILD_COMPOSER_REPO'	=> array(
				'route'		=> 'phpbb.titania.manage.composer.rebuild_repo',
				'ajax'		=> true,
			),
			'REBUILD_TOPIC_URLS'	=> array(
				'route'		=> 'phpbb.titania.manage.topic.rebuild_urls',
				'ajax'		=> true,
			),
			'RESYNC_DOTTED_TOPICS'	=> array(
				'route'		=> 'phpbb.titania.manage.topic.resync_dots',
				'ajax'		=> true,
			),
			'REINDEX'				=> array(
				'route'		=> 'phpbb.titania.manage.search.reindex',
				'ajax'		=> true,
			),
			'RESYNC_CONTRIB_COUNT'	=> array(
				'route'		=> 'phpbb.titania.manage.contrib.resync_count',
				'ajax'		=> true,
			),
			'UPDATE_RELEASE_TOPICS'	=> array(
				'route'		=> 'phpbb.titania.manage.contrib.update_release_topics',
				'ajax'		=> true,
			),
			'CONFIG_SETTINGS'		=> array(
				'route'		=> 'phpbb.titania.manage.config_settings',
				'ajax'		=> false,
			),
		);

		foreach ($this->ext_config->demo_style_path as $branch => $path)
		{
			if (empty($path))
			{
				continue;
			}
			$tools['INSTALL_DEMO_STYLES_' . $branch] = array(
				'route'		=> 'phpbb.titania.manage.demo.install_all',
				'params'	=> array('branch' => $branch),
				'title'		=> $this->user->lang('INSTALL_DEMO_STYLES', $this->get_branch_name($branch)),
				'ajax'		=> true,
			);
		}

		foreach ($tools as $title => $info)
		{
			$this->template->assign_block_vars('tools', array(
				'L_TITLE'			=> isset($info['title']) ? $info['title'] : $this->user->lang($title),
				'U_TITLE'			=> $this->helper->route($info['route'], isset($info['params']) ? $info['params'] : array()),
				'S_AJAX'			=> $info['ajax'],
			));
		}
		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		return $this->helper->render('manage/administration.html', 'ADMINISTRATION');
	}

	/**
	* Get the display name of a phpBB branch.
	*
	* @param int $branch	Branch in the form of 33 for 3.3.
	* @return string Returns the configured branch name, like phpBB 3.3.x.
	*/
	protected function get_branch_name($branch)
	{
		$versions = $this->ext_config->phpbb_versions;

		if (isset($versions[$branch]['name']))
		{
			return $versions[$branch]['name'];
		}
		return 'phpBB ' . implode('.', str_split((string) $branch));
	}

	/**
	* Check user's authorization.
	*
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth()
	{
		return $this->auth->acl_get('u_titania_admin');
	}

	/**
	* Run common initial tasks.
	*
	* @return null
	*/
	protected function setup()
	{
		$this->user->add_lang('acp/common');
		$this->user->add_lang_ext('phpbb/titania', 'manage_tools');
	}
}
