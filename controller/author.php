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

namespace phpbb\titania\controller;

class author
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request_inteface */
	protected $request;

	/** @var \titania_config */
	protected $ext_config;

	/** @var \phpbb\titania\display */
	protected $display;

	/** @var \phpbb\titania\cache\service */
	protected $cache;

	/** @var \titania_author */
	protected $author;

	/** @var bool */
	protected $is_owner;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth
	* @param \phpbb\config\config $config
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param \phpbb\titania\controller\helper $helper
	* @param \phpbb\request\request_interface $request
	* @param \titania_config $ext_config
	* @param \phpbb\titania\display $display
	* @param \phpbb\titania\cache\service $cache
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\display $display, \titania_config $ext_config, \phpbb\titania\cache\service $cache)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->request = $request;
		$this->ext_config = $ext_config;
		$this->display = $display;
		$this->cache = $cache;

		// Add common lang
		$this->user->add_lang_ext('phpbb/titania', 'authors');
	}

	/**
	* Load author object and set access level.
	*
	* @param string|int $author		Author username or user id.
	* @throws \Exception			Throws exception if user is not found.
	* @return null
	*/
	protected function load_author($author)
	{
		$this->author = new \titania_author(false);

		if (!$this->author->load($author))
		{
			throw new \Exception($this->user->lang['AUTHOR_NOT_FOUND']);
		}

		$this->is_owner = $this->user->data['user_id'] == $this->author->user_id;

		// Check to see if the currently accessing user is the author
		if (\titania::$access_level == TITANIA_ACCESS_PUBLIC && $this->is_owner)
		{
			\titania::$access_level = TITANIA_ACCESS_AUTHORS;
		}
	}

	/**
	* Get page title.
	*
	* @param string $title	Page title language key.
	* @return string Returns translated page title prefixed with author's username.
	*/
	protected function get_title($title)
	{
		return $this->author->get_username_string('username') . ' - ' . $this->user->lang($title);
	}

	/**
	* Delegates requested page to appropriate method.
	*
	* @param string $author		Author's username clean value.
	* @param string $page		Requested page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function base($author, $page)
	{
		$page = ($page) ?: 'details';

		if (!in_array($page, array('details', 'contributions', 'support', 'create', 'manage')))
		{
			return $this->helper->error('NO_PAGE', 404);
		}

		$this->load_author($author);
		$this->display->assign_global_vars();
		$this->generate_navigation($page);
		$this->author->assign_details();

		return $this->{$page}();
	}

	/**
	* Generate navigation tabs.
	*
	* @param string $page	Active page.
	* @return null
	*/
	protected function generate_navigation($page)
	{
		$nav_ary = array(
			'details' => array(
				'title'		=> 'AUTHOR_DETAILS',
				'url'		=> $this->author->get_url(),
			),
			'contributions' => array(
				'title'		=> 'AUTHOR_CONTRIBS',
				'url'		=> $this->author->get_url('contributions'),
			),
			'support' => array(
				'title'		=> 'AUTHOR_SUPPORT',
				'url'		=> $this->author->get_url('support'),
				'auth'		=> $this->is_owner && $this->cache->get_author_contribs($this->author->user_id, $this->user),
			),
			'create' => array(
				'title'		=> 'NEW_CONTRIBUTION',
				'url'		=> $this->author->get_url('create'),
				'auth'		=> $this->is_owner && $this->auth->acl_get('u_titania_contrib_submit'),
			),
			'manage' => array(
				'title'		=> 'MANAGE_AUTHOR',
				'url'		=> $this->author->get_url('manage'),
				'auth'		=> $this->is_owner || $this->auth->acl_get('u_titania_mod_author_mod'),
			),
		);

		// Display nav menu
		$this->display->generate_nav($nav_ary, $page, 'details');

		// Generate the main breadcrumbs
		$this->display->generate_breadcrumbs(array(
			$this->author->username	=> $this->author->get_url(),
		));

		if ($page != 'details')
		{
			$this->display->generate_breadcrumbs(array(
				$nav_ary[$page]['title']	=> $nav_ary[$page]['url'],
			));
		}
	}

	/**
	* Display author's support topics page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function support()
	{
		if (!$this->is_owner)
		{
			return $this->helper->needs_auth();
		}

		// Mark all topics read
		if ($this->request->variable('mark', '') == 'topics')
		{
			foreach ($this->cache->get_author_contribs($this->author->user_id, $this->user) as $contrib_id)
			{
				\titania_tracking::track(TITANIA_SUPPORT, $contrib_id);
			}
		}

		\topics_overlord::display_forums_complete('author_support', $this->author);

		// Mark all topics read
		$this->template->assign_var('U_MARK_TOPICS', $this->author->get_url('support', array('mark' => 'topics')));

		return $this->helper->render('contributions/contribution_support.html', $this->get_title('AUTHOR_SUPPORT'));
	}

	/**
	* Display author details page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function details()
	{
		$this->author->get_rating();

		// Canonical URL
		$this->template->assign_var('U_CANONICAL', $this->author->get_url());

		return $this->helper->render('authors/author_details.html', $this->get_title('AUTHOR_DETAILS'));
	}

	/**
	* Display author management page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function manage()
	{ 
		if (!$this->is_owner && !$this->auth->acl_get('u_titania_mod_author_mod'))
		{
			return $this->helper->needs_auth();
		}

		$error = array();
		$message = new \titania_message($this->author);
		$message->set_auth(array(
			'bbcode'	=> $this->auth->acl_get('u_titania_bbcode'),
			'smilies'	=> $this->auth->acl_get('u_titania_smilies'),
		));
		$message->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
		));

		if ($this->request->is_set_post('submit'))
		{
			$this->author->post_data($message);

			$this->author->__set_array(array(
				'author_realname'	=> $this->request->variable('realname', '', true),
				'author_website'	=> $this->request->variable('website', ''),
			));

			$error = $this->author->validate();

			if (($validate_form_key = $message->validate_form_key()) !== false)
			{
				$error[] = $validate_form_key;
			}

			if (empty($error))
			{
				$this->author->submit();

				redirect($this->author->get_url());
			}
		}

		$message->display();

		$this->template->assign_vars(array(
			'S_POST_ACTION'				=> $this->author->get_url('manage'),
			'AUTHOR_WEBSITE'			=> ($this->author->get_website_url() || !$this->is_owner) ? $this->author->get_website_url() : '',
			'ERROR_MSG'					=> (!empty($error)) ? implode('<br />', $error) : false,
		));

		return $this->helper->render('authors/author_manage.html', $this->get_title('MANAGE_AUTHOR'));
	}

	/**
	* Display author contributions page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function contributions()
	{
		// Setup the sort tool to sort by contribution support status and name ascending
		$sort = \contribs_overlord::build_sort();
		$sort->set_url($this->author->get_url('contributions'));
		$sort->set_sort_keys(array(
			'sc' => array('SORT_CONTRIB_NAME', 'c.contrib_limited_support, c.contrib_name', true),
		));
		$sort->set_defaults(false, 'sc', 'a');

		\contribs_overlord::display_contribs('author', $this->author->user_id, $sort);

		$this->template->assign_vars(array(
			'S_AUTHOR_LIST'		=> true,
			'U_CANONICAL'		=> $sort->build_canonical(),
		));

		return $this->helper->render('authors/author_contributions.html', $this->get_title('AUTHOR_CONTRIBS'));
	}

	/**
	* Display new contribution page.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	protected function create()
	{
		if (!$this->is_owner && !$this->auth->acl_get('u_titania_contrib_submit'))
		{
			return $this->helper->needs_auth();
		}

		$this->user->add_lang_ext('phpbb/titania', 'contributions');

		$contrib = new \titania_contribution;
		$contrib->contrib_user_id = $this->user->data['user_id'];
		$contrib->author = $this->author;
		$contrib->get_options();

		// Set some main vars up
		$message = $this->setup_message($contrib);
		$submit = $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');
		$error = array();

		$settings = array(
			'type'					=> $this->request->variable('contrib_type', 0),
			'permalink'				=> $this->request->variable('permalink', '', true),
			'categories'			=> $this->request->variable('contrib_category', array(0)),
			'coauthors'				=> array(
				'active'			=> $this->request->variable('active_coauthors', '', true),
				'nonactive'			=> $this->request->variable('nonactive_coauthors', '', true),
			),
			'custom'				=> $this->request->variable('custom_fields', array('' => ''), true),
		);

		if ($preview || $submit)
		{
			$contrib->post_data($message);
			$contrib->__set_array(array(
				'contrib_type'			=> $settings['type'],
				'contrib_name_clean'	=> $settings['permalink'],
				'contrib_visible'		=> 1,
			));
		}

		if ($preview)
		{
			$message->preview();
		}
		else if ($submit)
		{
			$authors = $contrib->get_authors_from_usernames(array(
				'active_coauthors'		=> $settings['coauthors']['active'],
				'nonactive_coauthors'	=> $settings['coauthors']['nonactive'],
			));
			$authors['author'] = array($this->user->data['username'] => $this->user->data['user_id']);

			$error = $contrib->validate($settings['categories'], $authors, $settings['custom']);

			if (($form_key_error = $message->validate_form_key()) !== false)
			{
				$error[] = $form_key_error;
			}

			if (empty($error))
			{
				$contrib->set_type($contrib->contrib_type);
				$contrib->set_custom_fields($settings['custom']);
				$contrib->contrib_categories = implode(',', $settings['categories']);
				$contrib->contrib_creation_time = time();
				$contrib->submit();

				$contrib->set_coauthors($authors['coauthors']['active'], $authors['coauthors']['nonactive'], true);

				// Create relations
				$contrib->put_contrib_in_categories($settings['categories']);

				redirect($contrib->get_url('revision'));
			}
		}

		\titania::_include('functions_posting', 'generate_type_select');

		// Generate some stuff
		generate_type_select($contrib->contrib_type);
		generate_category_select($settings['categories']);
		$contrib->assign_details();
		$message->display();

		foreach (\titania_types::$types as $type)
		{
			$this->display->generate_custom_fields($type->contribution_fields, $settings['custom'], $type->id);
		}

		$this->template->assign_vars(array(
			'S_POST_ACTION'			=> $this->author->get_url('create'),
			'S_CREATE'				=> true,
			'S_CAN_EDIT_CONTRIB'	=> $this->auth->acl_get('u_titania_contrib_submit'),

			'CONTRIB_PERMALINK'		=> $settings['permalink'],
			'ERROR_MSG'				=> (!empty($error)) ? implode('<br />', $error) : false,
			'ACTIVE_COAUTHORS'		=> $settings['coauthors']['active'],
			'NONACTIVE_COAUTHORS'	=> $settings['coauthors']['nonactive'],
		));

		return $this->helper->render('contributions/contribution_manage.html', 'NEW_CONTRIBUTION');
	}

	/**
	* Set up message object for contribution description.
	*
	* @param \titania_contribution
	* @return \titania_message
	*/
	protected function setup_message($contrib)
	{
		$message = new \titania_message($contrib);
		$message->set_auth(array(
			'bbcode'	=> $this->auth->acl_get('u_titania_bbcode'),
			'smilies'	=> $this->auth->acl_get('u_titania_smilies'),
		));
		$message->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
			'subject_name'		=> 'name',
		));

		return $message;
	}
}
