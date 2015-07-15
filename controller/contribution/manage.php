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

namespace phpbb\titania\controller\contribution;

use phpbb\titania\access;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use phpbb\exception\http_exception;

class manage extends base
{
	/** @var array */
	protected $settings;

	/** @var \phpbb\titania\message\message */
	protected $message;

	/** @var \phpbb\titania\attachment\uploader */
	protected $screenshots;

	/** @var \phpbb\titania\attachment\uploader */
	protected $colorizeit_sample;

	/** @var bool */
	protected $is_moderator;

	/** @var bool */
	protected $can_edit_demo;

	/** @var bool */
	protected $use_colorizeit;

	/** @var array */
	protected $status_list;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\controller\helper $helper
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param access $access
	 * @param \phpbb\titania\message\message $message
	 * @param \phpbb\titania\attachment\uploader $screenshots
	 * @param \phpbb\titania\attachment\uploader $colorizeit_sample
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\cache\service $cache, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\access $access, \phpbb\titania\message\message $message, \phpbb\titania\attachment\uploader $screenshots, \phpbb\titania\attachment\uploader $colorizeit_sample)
	{
		parent::__construct($auth, $config, $db, $template, $user, $helper, $request, $cache, $ext_config, $display, $access);

		$this->message = $message;
		$this->screenshots = $screenshots;
		$this->colorizeit_sample = $colorizeit_sample;
	}

	/**
	* Manage contribution.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function manage($contrib_type, $contrib)
	{
		$this->setup($contrib_type, $contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		if (confirm_box(true) && $this->check_auth('change_author'))
		{
			$this->change_author();
		}

		$old_settings = $this->contrib->__get_array();
		$old_settings['categories'] = array_keys($this->contrib->category_data);

		$this->settings = array(
			'permalink'			=> $this->request->variable('permalink', $this->contrib->contrib_name_clean, true),
			'status'			=> $this->request->variable('contrib_status', (int) $this->contrib->contrib_status),
			'categories'		=> $old_settings['categories'],
			'demo'				=> array(),
			'coauthors'			=> array(
				'active'		=> $this->request->variable('active_coauthors', '', true),
				'nonactive'		=> $this->request->variable('nonactive_coauthors', '', true),
			),
			'new_author'		=> $this->request->variable('change_owner', '', true),
			'limited_support'	=> $this->request->variable('limited_support', (bool) $this->contrib->contrib_limited_support),
		);
		$this->settings['custom'] = $this->contrib->get_custom_fields();

		foreach ($this->contrib->type->get_allowed_branches(true) as $branch => $name)
		{
			$this->settings['demo'][$branch] = $this->contrib->get_demo_url($branch);
		}

		$this->load_message();
		$this->load_screenshot();

		$submit	= $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');
		$error = $this->screenshots->get_errors();

		if ($preview || $submit || $this->screenshots->uploaded)
		{
			$this->settings = array_merge($this->settings, array(
				'categories'		=> $this->request->variable('contrib_category', array(0 => 0)),
				'custom'			=> $this->request->variable('custom_fields', array('' => ''), true),
			));
			$demos = $this->request->variable('demo', array(0 => ''));

			foreach ($this->contrib->type->get_allowed_branches(true) as $branch => $name)
			{
				if (isset($demos[$branch]))
				{
					$this->settings['demo'][$branch] = $demos[$branch];
				}
			}

			$this->contrib->post_data($this->message);
			$this->contrib->__set_array(array(
				'contrib_demo'				=> ($this->can_edit_demo) ? json_encode($this->settings['demo']) : $this->contrib->contrib_demo,
				'contrib_limited_support'	=> $this->settings['limited_support'],
			));
		}

		// ColorizeIt sample
		if ($this->use_colorizeit)
		{
			$this->load_colorizeit();
			$error = array_merge($error, $this->colorizeit_sample->get_errors());

			if ($this->colorizeit_sample->uploaded)
			{
				$this->contrib->post_data($this->message);
			}
		}

		$this->handle_screenshot_action();

		if ($preview)
		{
			$this->preview();
		}
		else if ($submit)
		{
			if (($form_key_error = $this->message->validate_form_key()) !== false)
			{
				$error[] = $form_key_error;
			}
			else if ($this->request->is_set_post('delete') && $this->check_auth('delete'))
			{
				// Handle the deletion routine
				$this->contrib->delete();
				redirect($this->helper->route('phpbb.titania.index'));
			}

			$this->contrib->post_data($this->message);

			$authors = $this->contrib->get_authors_from_usernames(array(
				'active_coauthors'		=> $this->settings['coauthors']['active'],
				'nonactive_coauthors'	=> $this->settings['coauthors']['nonactive'],
				'new_author'			=> $this->settings['new_author'],
			));
			$author_username = \users_overlord::get_user($this->contrib->contrib_user_id, 'username', true);
			$authors['author'] = array($author_username => $this->contrib->contrib_user_id);

			$error = array_merge($error, $this->contrib->validate(
				$this->settings['categories'],
				$authors,
				$this->settings['custom'],
				$this->contrib->contrib_name_clean
			));

			// Did we succeed or have an error?
			if (empty($error))
			{
				$this->submit($authors, $old_settings);
			}
		}

		$this->assign_vars($error);

		return $this->helper->render(
			'contributions/contribution_manage.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['MANAGE_CONTRIBUTION']
		);
	}

	/**
	* Trigger author change confirmation.
	*
	* @param string $username		New author's username.
	* @param int $user_id			New author's user id.
	*
	* @return null
	*/
	protected function confirm_author_change($username, $user_id)
	{
		$s_hidden_fields = array(
			'submit'			=> true,
			'change_owner_id'	=> $user_id,
		);
		$author_profile = '<a href="' .  \phpbb::append_sid('memberlist', 'mode=viewprofile&amp;u=' . $user_id) . '">' . $username . '</a>';

		confirm_box(
			false,
			$this->user->lang('CONTRIB_CONFIRM_OWNER_CHANGE', $author_profile),
			build_hidden_fields($s_hidden_fields)
		);
	}

	/**
	* Change contribution author.
	*
	* @return null
	*/
	protected function change_author()
	{
		$new_author = $this->request->variable('change_owner_id', 0);

		if ($new_author && $new_author !== ANONYMOUS)
		{
			$this->contrib->set_contrib_user_id($new_author);

			$this->contrib->load($this->contrib->contrib_id); // Reload the contrib (to make sure the authors list is updated)
			// Update the release topic and reindex the contrib
			$this->contrib->update_release_topic();
			$this->contrib->index();
			$this->cache->destroy('sql', TITANIA_CONTRIBS_TABLE);

			redirect($this->contrib->get_url());
		}
	}

	/**
	* Handle screenshot delete/move action.
	*
	* @return null
	*/
	protected function handle_screenshot_action()
	{
		// Screenshots
		$attach_id	= $this->request->variable('a', 0);
		$action		= $this->request->variable('action', '');
		$link_hash	= $this->request->variable('hash', '');

		if ($attach_id && $action && check_link_hash($link_hash, 'attach_manage'))
		{
			if ($action == 'delete_attach')
			{
				// The delete() method will check if the attachment is part of the screenshot/clr_sample array
				$this->screenshots->get_operator()->delete(array($attach_id));
				if ($this->use_colorizeit)
				{
					$this->colorizeit_sample->get_operator()->delete(array($attach_id));
				}
			}
			else if ($action == 'attach_up' || $action == 'attach_down')
			{
				$move_attach = ($action == 'attach_up') ? 'up' : 'down';
				$this->screenshots->get_operator()
					->change_order($attach_id, $move_attach)
					->submit()
				;
			}
		}
	}

	/**
	* Common setup tasks.
	*
	* @param string $contrib_type	Contrib type URL identifier.
	* @param string $contrib		Contrib name clean.
	* @return null
	*/
	protected function setup($contrib_type, $contrib)
	{
		$this->load_contrib($contrib_type, $contrib);
		$this->is_moderator = $this->contrib->type->acl_get('moderate');
		$this->use_colorizeit = strlen($this->ext_config->colorizeit) && $this->contrib->type->acl_get('colorizeit');
		$this->can_edit_demo = $this->ext_config->can_modify_style_demo_url || \titania_types::$types[TITANIA_TYPE_STYLE]->acl_get('moderate')
				|| $this->contrib->contrib_type != TITANIA_TYPE_STYLE;

		$this->status_list = array(
			TITANIA_CONTRIB_NEW					=> 'CONTRIB_NEW',
			TITANIA_CONTRIB_APPROVED			=> 'CONTRIB_APPROVED',
			TITANIA_CONTRIB_DOWNLOAD_DISABLED	=> 'CONTRIB_DOWNLOAD_DISABLED',
			TITANIA_CONTRIB_CLEANED				=> 'CONTRIB_CLEANED',
			TITANIA_CONTRIB_HIDDEN				=> 'CONTRIB_HIDDEN',
			TITANIA_CONTRIB_DISABLED			=> 'CONTRIB_DISABLED',
		);
	}

	/**
	* Check user's authorization.
	*
	* @param bool|string $action	Check auth for specific action. Defaults to false.
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth($action = false)
	{
		$action_auth = true;

		if ($action !== false)
		{
			switch ($action)
			{
				case 'change_author':
					$action_auth = $this->is_moderator || $this->user->data['user_id'] == $this->contrib->contrib_user_id;
				break;

				case 'delete':
					$action_auth = $this->auth->acl_get('u_titania_admin');
				break;

				default:
					$action_auth = false;
			}
		}
		$is_author_editable = !in_array($this->contrib->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED));

		return $action_auth && ($this->is_moderator ||
			($this->is_author && $is_author_editable && $this->auth->acl_get('u_titania_post_edit_own')));
	}

	/**
	* Load screenshot handler.
	*
	* @return null
	*/
	protected function load_screenshot()
	{
		$this->screenshots
			->configure(TITANIA_SCREENSHOT, $this->contrib->contrib_id, false, 175, true)
			->get_operator()->load()
		;
		$this->screenshots->handle_form_action();
	}

	/**
	* Load ColorizeIt handler.
	*
	* @return null
	*/
	protected function load_colorizeit()
	{
		$this->colorizeit_sample
			->configure(TITANIA_CLR_SCREENSHOT, $this->contrib->contrib_id)
			->handle_form_action()
		;
	}

	/**
	* Load message object.
	*
	* @return null
	*/
	protected function load_message()
	{
		$this->message
			->set_parent($this->contrib)
			->set_auth(array(
				'bbcode'		=> $this->auth->acl_get('u_titania_bbcode'),
				'smilies'		=> $this->auth->acl_get('u_titania_smilies'),
				'edit_subject'	=> $this->is_moderator || $this->contrib->is_author(),
			))
			->set_settings(array(
				'display_error'		=> false,
				'display_subject'	=> false,
				'subject_name'		=> 'name',
			))
		;
	}

	/**
	* Handle contribution settings preview.
	*
	* @return null
	*/
	protected function preview()
	{
		$this->message->preview();
		$attach_order = $this->request->variable('attach_order', array(0 => 0));

		// Preserve the attachment order when preview is hit just in case it has been modified
		if (!empty($attach_order))
		{
			$this->screenshots->get_operator()->sort(true, $attach_order, true);
		}
	}

	/**
	* Submit contribution.
	*
	* @param array $authors
	* @param array $old_settings
	*
	* @return null
	*/
	protected function submit($authors, $old_settings)
	{
		$this->submit_screenshots();
		$this->submit_colorizeit();

		// Update contrib_status/permalink if we can moderate. only if contrib_status is valid and permalink altered
		if ($this->is_moderator)
		{
			if (array_key_exists($this->settings['status'], $this->status_list))
			{
				$this->contrib->change_status($this->settings['status']);
			}

			if ($this->settings['permalink'] != $this->contrib->contrib_name_clean)
			{
				if ($this->settings['permalink'] == '')
				{
					$this->contrib->generate_permalink();
					$this->settings['permalink'] = $this->contrib->contrib_name_clean;
				}
				$this->contrib->change_permalink($this->settings['permalink']);
			}
		}
		else
		{
			// Check for changes in the description or categories to file a report
			$this->create_change_report($old_settings);
		}

		// Set custom field values.
		$this->contrib->set_custom_fields($this->settings['custom']);

		// Create relations
		$this->contrib->put_contrib_in_categories(
			$this->settings['categories'],
			!$this->contrib->type->acl_get('moderate')
		);
		// Submit the changes
		$this->contrib->submit();
		// Set the coauthors
		$this->contrib->set_coauthors($authors['active_coauthors'], $authors['nonactive_coauthors'], true);
		// Update the release topic
		$this->contrib->update_release_topic();

		$new_author = $authors['new_author'];

		if ($new_author)
		{
			$this->confirm_author_change(key($new_author), array_shift($new_author));
		}

		$this->cache->destroy('sql', TITANIA_CONTRIBS_TABLE);

		redirect($this->contrib->get_url());
	}

	/**
	* Submit screenshots.
	*
	* @return null
	*/
	protected function submit_screenshots()
	{
		$new_order = $this->request->variable('attach_order', array(0 => 0));
		$this->screenshots->get_operator()
			->sort(true, $new_order, true)
			->submit()
		;
	}

	/**
	* Submit ColorizeIt settings.
	*
	* @return null
	*/
	protected function submit_colorizeit()
	{
		// ColorizeIt stuff
		if ($this->use_colorizeit)
		{
			$this->colorizeit_sample->get_operator()->submit();
			$contrib_clr_colors = $this->request->variable('change_colors', $this->contrib->contrib_clr_colors);
			$this->contrib->__set('contrib_clr_colors', $contrib_clr_colors);
		}
	}

	/**
	 * Handle demo management.
	 *
	 * @param string $contrib_type		Contrib type URL identifier.
	 * @param string $contrib			Contrib name clean.
	 * @param string $action
	 * @return \phpbb\titania\controller\Response|JsonResponse|RedirectResponse
	 */
	public function manage_demo($contrib_type, $contrib, $action)
	{
		$hash = $this->request->variable('hash', '');

		if (!check_link_hash($hash, 'manage_demo'))
		{
			throw new http_exception(403, 'PAGE_REQUEST_INVALID');
		}
		$this->setup($contrib_type, $contrib);

		if (!$this->is_moderator || $this->contrib->contrib_status != TITANIA_CONTRIB_APPROVED)
		{
			return $this->helper->needs_auth();
		}

		$branch = $this->request->variable('branch', 0);
		$data = array();

		if ($action == 'install')
		{
			$data = $this->install_demo($branch);
		}

		if ($this->request->is_ajax())
		{
			return new JsonResponse($data);
		}
		return new RedirectResponse($this->contrib->get_url('manage'));
	}

	/**
	 * Install contribution demo.
	 *
	 * @param int $branch
	 * @return array
	 */
	protected function install_demo($branch)
	{
		$this->contrib->get_download();
		$demo_url = '';

		if (!empty($this->contrib->download[$branch]))
		{
			$revision = new \titania_revision($this->contrib);
			$revision->__set_array($this->contrib->download[$branch]);
			$demo_url = $this->contrib->type->install_demo($this->contrib, $revision);
		}
		return array(
			'url'	=> $demo_url,
		);
	}

	/**
	* Assign template variables.
	*
	* @param array $error		Array containing any errors found.
	* @return null
	*/
	protected function assign_vars($error)
	{
		// ColorizeIt
		if ($this->use_colorizeit)
		{
			$this->assign_colorizeit_vars();
		}

		foreach ($this->status_list as $status => $lang)
		{
			$this->template->assign_block_vars('status_select', array(
				'NAME'				=> $this->user->lang($lang),
				'VALUE'				=> $status,
				'S_SELECTED'		=> $status == $this->contrib->contrib_status,
			));
		}

		$this->display->generate_custom_fields(
			$this->contrib->type->contribution_fields,
			$this->settings['custom'],
			$this->contrib->type->id
		);

		foreach ($this->settings['demo'] as $branch => $demo_url)
		{
			$this->template->assign_block_vars('demo', array(
				'BRANCH'	=> $branch,
				'URL'		=> $demo_url,
				'NAME'		=> $this->ext_config->phpbb_versions[$branch]['name'],
				'U_INSTALL'	=> $this->contrib->get_url('manage_demo', array(
					'action'	=> 'install',
					'branch'	=> $branch,
					'hash'		=> generate_link_hash('manage_demo'),
				)),
			));
		}

		$coauthors = $this->get_coauthor_usernames();

		$this->template->assign_vars(array(
			'S_CONTRIB_APPROVED'		=> $this->contrib->contrib_status == TITANIA_CONTRIB_APPROVED,
			'S_POST_ACTION'				=> $this->contrib->get_url('manage'),
			'S_EDIT_SUBJECT'			=> $this->is_moderator || $this->contrib->is_author(),
			'S_DELETE_CONTRIBUTION'		=> $this->check_auth('delete'),
			'S_IS_OWNER'				=> $this->contrib->is_author,
			'S_IS_MODERATOR'			=> $this->is_moderator,
			'S_CAN_EDIT_DEMO'			=> $this->can_edit_demo,
			'S_CAN_EDIT_CONTRIB'		=> $this->auth->acl_get('u_titania_contrib_submit'),
			'S_LIMITED_SUPPORT'			=> $this->settings['limited_support'],

			'CONTRIB_PERMALINK'			=> $this->settings['permalink'],
			'CONTRIB_TYPE'				=> $this->contrib->contrib_type,
			'SCREENSHOT_UPLOADER'		=> ($this->auth->acl_get('u_titania_contrib_submit')) ? $this->screenshots->parse_uploader('posting/attachments/simple.html', true) : false,
			'ERROR_MSG'					=> (!empty($error)) ? implode('<br />', $error) : false,
			'ACTIVE_COAUTHORS'			=> implode("\n", $coauthors['active']),
			'NONACTIVE_COAUTHORS'		=> implode("\n", $coauthors['nonactive']),
		));

		$this->display->generate_category_select(
			$this->settings['categories'],
			false,
			true,
			$this->contrib->type->id
		);
		$this->message->display();
		$this->contrib->assign_details();
		$this->display->assign_global_vars();
		$this->generate_navigation('manage');
		$this->generate_breadcrumbs();
	}

	/**
	* Assign ColorizeIt template variables.
	*
	* @return null
	*/
	protected function assign_colorizeit_vars()
	{
		$clr_testsample = $test_sample = '';

		if ($this->contrib->has_colorizeit(true) || is_array($this->contrib->clr_sample))
		{
			$sample_url = $this->colorizeit_sample->get_operator()->get($this->contrib->clr_sample['attachment_id'])->get_url();
			$test_sample = 'http://' . $this->ext_config->colorizeit_url .
				'/testsample.html?sub=' . $this->ext_config->colorizeit . '&amp;sample=' . urlencode($sample_url);
		}
		$this->template->assign_vars(array(
			'MANAGE_COLORIZEIT'         => $this->ext_config->colorizeit,
			'CLR_SCREENSHOTS'           => $this->colorizeit_sample->parse_uploader('posting/attachments/simple.html'),
			'CLR_COLORS'                => htmlspecialchars($this->contrib->contrib_clr_colors),
			'U_TESTSAMPLE'              => $test_sample,
		));
	}

	/**
	* Get coauthor usernames.
	*
	* @return array
	*/
	protected function get_coauthor_usernames()
	{
		$coauthors = array(
			'active'	=> array(),
			'nonactive'	=> array(),
		);

		foreach ($this->contrib->coauthors as $data)
		{
			$user = \users_overlord::get_user($data['user_id']);

			// Make sure user still exists.
			if ($user['user_id'] == $data['user_id'])
			{
				$status = ($data['active']) ? 'active' : 'nonactive';
				$coauthors[$status][] = $user['username'];
			}
		}

		return $coauthors;
	}

	/**
	* Create attention reports for changes made.
	*
	* @return null
	*/
	protected function create_change_report($old_settings)
	{
		$name_change = $this->get_name_change($old_settings);
		$description_change = $this->get_description_change($old_settings);
		$category_change = $this->get_category_change($old_settings['categories']);

		if ($name_change)
		{
			$this->contrib->report($name_change, false, TITANIA_ATTENTION_NAME_CHANGED);
		}
		if ($description_change)
		{
			$this->contrib->report($description_change, false, TITANIA_ATTENTION_DESC_CHANGED);
		}

		if ($category_change)
		{
			$this->contrib->report($category_change, false, TITANIA_ATTENTION_CATS_CHANGED);
		}
	}

	/**
	 * Generate report message for a change in name.
	 *
	 * @param array $old_settings
	 * @return string
	 */
	protected function get_name_change($old_settings)
	{
		$old_name = $old_settings['contrib_name'];
		$name = $this->contrib->contrib_name;

		return ($old_name != $name) ? "$old_name>>>>>>>>>>$name" : '';
	}

	/**
	* Generate report message for a change in description.
	*
	* @return string
	*/
	protected function get_description_change($old_settings)
	{
		$contrib = new \titania_contribution;
		$contrib->__set_array($old_settings);
		$old_description = $contrib->generate_text_for_edit();
		$old_description = $old_description['text'];

		$description = $this->contrib->generate_text_for_edit();
		$description = $description['text'];

		return ($old_description !== $description) ? "$old_description>>>>>>>>>>$description" : '';
	}

	/**
	* Generate report message for a change in categories.
	*
	* @param array $old_categories		Array containing old category id's.
	*
	* @return string
	*/
	protected function get_category_change($old_categories)
	{
		$new_categories = $this->settings['categories'];

		sort($old_categories);
		sort($new_categories);

		if ($old_categories === $new_categories)
		{
			return '';
		}

		$categories = $this->cache->get_categories();
		$old_names = $new_names = array();

		foreach ($old_categories as $id)
		{
			$old_names[] = $this->user->lang($categories[$id]['category_name']);
		}
		foreach ($new_categories as $id)
		{
			$new_names[] = $this->user->lang($categories[$id]['category_name']);
		}

		return implode("\n", $old_names) . '>>>>>>>>>>' . implode("\n", $new_names);
	}
}
