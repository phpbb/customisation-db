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

class base
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\titania\config */
	protected $ext_config;

	/** @var \phpbb\titania\display */
	protected $display;

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
	* @param \phpbb\request\request_interace $request
	* @param \titania_config $ext_config
	* @param \phpbb\titania\display $display
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\cache\service $cache, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \titania_config $ext_config, \phpbb\titania\display $display)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->cache = $cache;
		$this->helper = $helper;
		$this->request = $request;
		$this->ext_config = $ext_config;
		$this->display = $display;

		// Add common lang
		$this->user->add_lang_ext('phpbb/titania', array('manage'));
	}

	/**
	* Assign navigation tabs to the template.
	*
	* @param string $page		Current active page.
	* @return null
	*/
	protected function generate_navigation($page)
	{
		$nav_ary = $this->get_navigation_options();

		// Display nav menu
		$this->display->generate_nav($nav_ary, $page, 'attention');

		// Generate the main breadcrumbs
		$this->display->generate_breadcrumbs(array(
			$this->user->lang['MANAGE']	=> $this->helper->route('phpbb.titania.manage'),
		));

		if ($page)
		{
			$this->display->generate_breadcrumbs(array(
				$nav_ary[$page]['title']	=> $nav_ary[$page]['url'],
			));
		}
	}

	/**
	* Get navigation tab options.
	*
	* @return array
	*/
	protected function get_navigation_options()
	{
		/**
		* Menu Array
		*
		* 'filename' => array(
		*	'title'		=> 'nav menu title',
		* 	'url'		=> $page_url,
		*	'auth'		=> ($can_see_page) ? true : false, // Not required, always true if missing
		* ),
		*/
		return array(
			'attention' => array(
				'title'		=> 'ATTENTION',
				'url'		=> $this->helper->route('phpbb.titania.manage.attention'),
				'auth'		=> $this->auth->acl_gets(
						'u_titania_mod_author_mod',
						'u_titania_mod_contrib_mod',
						'u_titania_mod_faq_mod',
						'u_titania_mod_post_mod'
					) ||
					!empty(\titania_types::find_authed('moderate')),
				'count'		=> $this->get_open_attention_count(),
			),
			'queue' => array(
				'title'		=> 'VALIDATION_QUEUE',
				'url'		=> $this->helper->route('phpbb.titania.queue'),
				'auth'		=> !empty(\titania_types::find_authed('view')) && $this->ext_config->use_queue,
			),
			'queue_discussion' => array(
				'title'		=> 'QUEUE_DISCUSSION',
				'url'		=> $this->helper->route('phpbb.titania.queue_discussion'),
				'auth'		=> !empty(\titania_types::find_authed('queue_discussion')) && $this->ext_config->use_queue,
			),
			'administration' => array(
				'title'		=> 'ADMINISTRATION',
				'url'		=> $this->helper->route('phpbb.titania.administration'),
				'auth'		=> $this->auth->acl_get('u_titania_admin'),
				'match'		=> array('categories'),
			),
			'categories' => array(
				'title'		=> 'MANAGE_CATEGORIES',
				'url'		=> $this->helper->route('phpbb.titania.manage.categories'),
				'auth'		=> $this->auth->acl_get('u_titania_admin'),
				'display'	=> false,
			),
		);
	}

	/**
	* Get the number of open attention items.
	*
	* @return int
	*/
	protected function get_open_attention_count()
	{
		// Count the number of open attention items
		$sql = 'SELECT COUNT(a.attention_id) AS cnt
			FROM ' . TITANIA_ATTENTION_TABLE . ' a
			LEFT JOIN ' . TITANIA_CONTRIBS_TABLE . ' c
				ON (a.attention_object_type = ' . TITANIA_CONTRIB . '
					AND a.attention_object_id = c.contrib_id)
			WHERE a.attention_close_time = 0
				AND ' . \attention_overlord::get_permission_sql();
		$this->db->sql_query($sql);
		$attention_count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult();

		return $attention_count;
	}
}
