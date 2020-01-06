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

		foreach ($tools as $title => $info)
		{
			$this->template->assign_block_vars('tools', array(
				'L_TITLE'			=> $this->user->lang($title),
				'U_TITLE'			=> $this->helper->route($info['route']),
				'S_AJAX'			=> $info['ajax'],
			));
		}
		$this->display->assign_global_vars();
		$this->generate_navigation('administration');

		return $this->helper->render('manage/administration.html', 'ADMINISTRATION');
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
