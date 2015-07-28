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

use phpbb\titania\attachment\attachment;
use Symfony\Component\HttpFoundation\JsonResponse;

class revision extends base
{
	/** @var int */
	protected $id;

	/** @var \titania_revision */
	protected $revision;

	/** @var \phpbb\titania\attachment\uploader */
	protected $uploader;

	/** @var \phpbb\titania\subscriptions */
	protected $subscriptions;

	/** @var \phpbb\titania\message\message */
	protected $message;

	/** @var \phpbb\titania\attachment\attachment */
	protected $attachment;

	/** @var \phpbb\titania\entity\package */
	protected $package;

	/** @var \titania_queue */
	protected $queue;

	/** @var array */
	protected $revisions_in_queue;

	/** @var array */
	protected $repackable_branches;

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
	 * @param \phpbb\titania\access $access
	 * @param \phpbb\titania\attachment\uploader $uploader
	 * @param \phpbb\titania\subscriptions $subscriptions
	 * @param \phpbb\titania\message\message $message
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, \phpbb\request\request $request, \phpbb\titania\cache\service $cache, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\access $access, \phpbb\titania\attachment\uploader $uploader, \phpbb\titania\subscriptions $subscriptions, \phpbb\titania\message\message $message)
	{
		parent::__construct($auth, $config, $db, $template, $user, $helper, $request, $cache, $ext_config, $display, $access);

		$this->uploader = $uploader;
		$this->subscriptions = $subscriptions;
		$this->message = $message;
	}

	/**
	* Repack revision submission action.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	* @param id $id						Id for revision being repacked.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function repack($contrib_type, $contrib, $id)
	{
		$this->setup($contrib_type, $contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$old_revision = new \titania_revision($this->contrib, $id);

		if (!$old_revision->load() || $old_revision->contrib_id != $this->contrib->contrib_id)
		{
			return $this->helper->error('NO_REVISION', 404);
		}

		if (!($old_queue = $old_revision->get_queue()))
		{
			$this->user->add_lang_ext('phpbb/titania', 'manage');
			return $this->helper->error('NO_QUEUE_ITEM', 404);
		}

		// Only allow revisions that are still in the queue to be repacked.
		if ($old_queue->queue_status < TITANIA_QUEUE_NEW || (!$old_queue->allow_author_repack && !$this->is_moderator))
		{
			return $this->helper->needs_auth();
		}

		$old_revision->load_phpbb_versions();
		$this->revisions_in_queue = $this->contrib->in_queue();

		foreach ($old_revision->phpbb_versions as $version)
		{
			$this->repackable_branches[] = (int) $version['phpbb_version_branch'];
		}
		$this->repackable_branches = array_unique($this->repackable_branches);

		$error = array();

		if ($this->request->is_set_post('new_revision'))
		{
			$result = $this->process_steps();

			if ($result['complete'])
			{
				$this->revision->repack($old_revision, $old_queue);
				$this->submit();

				if ($this->use_queue && !$this->is_author && $this->is_moderator)
				{
					redirect($this->helper->route('phpbb.titania.queue.item', array(
						'id'	=> $this->revision->revision_queue_id,
					)));
				}

				$old_queue->allow_author_repack = false;
				$old_queue->submit();

				redirect($this->contrib->get_url());
			}
			else if (isset($result['response']))
			{
				return $result['response'];
			}
			$error = $result['error'];
		}
		else if ($this->request->is_set_post('cancel'))
		{
			$this->cancel();
		}

		$settings = array(
			'name'				=> $old_revision->revision_name,
			'version'			=> $old_revision->revision_version,
			'vendor_versions'	=> $old_revision->get_selected_branches(),
			'custom'			=> $old_revision->get_custom_fields(),
			'allow_repack'		=> $old_queue->queue_allow_repack,
		);

		if ($this->is_custom_license($old_revision->revision_license))
		{
			$settings['custom_license'] = $old_revision->revision_license;
			$settings['license'] = $this->user->lang['CUSTOM_LICENSE'];
		}
		else
		{
			$settings['license'] = $old_revision->revision_license;
		}

		$this->assign_common_vars($error, !empty($this->id), $settings);
		$this->template->assign_vars(array(
			'S_REPACK'			=> true,
			'S_POST_ACTION'		=> $this->contrib->get_url('revision', array(
				'page'	=> 'repack',
				'id'	=> $old_revision->revision_id,
			)),
		));

		add_form_key('postform');

		return $this->helper->render(
			'contributions/contribution_revision.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['NEW_REVISION']
		);
	}

	/**
	* New revision submission action.
	*
	* @param string $contrib_type		Contrib type URL identifier.
	* @param string $contrib			Contrib name clean.
	*
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function add($contrib_type, $contrib)
	{
		$this->setup($contrib_type, $contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}
		$this->revisions_in_queue = $this->contrib->in_queue();
		$allowed_branches = $this->get_allowed_branches();

		if (empty($allowed_branches))
		{
			return $this->helper->error('REVISION_IN_QUEUE');
		}

		if ($this->use_queue)
		{
			$this->queue = new \titania_queue();
			$message = $this->get_message();
			$message->display();
		}
		$error = array();

		if ($this->request->is_set_post('new_revision'))
		{
			$result = $this->process_steps();

			if ($result['complete'])
			{
				$this->submit();

				if ($this->use_queue)
				{
					// Subscriptions
					$this->queue = $this->revision->get_queue();

					$email_vars = array(
						'NAME'		=> $this->user->lang['VALIDATION'] . ' - ' .
							$this->contrib->contrib_name . ' - ' .
							$this->revision->revision_version,
						'U_VIEW'	=> $this->queue->get_url(),
					);

					$this->subscriptions->send_notifications(
						TITANIA_QUEUE,
						$this->contrib->contrib_type,
						'subscribe_notify_forum',
						$email_vars,
						$this->user->data['user_id']
					);
				}
				redirect($this->contrib->get_url());
			}
			else if (isset($result['response']))
			{
				return $result['response'];
			}
			$error = $result['error'];
		}
		else if ($this->request->is_set_post('cancel'))
		{
			$this->cancel();
		}

		$this->assign_common_vars($error, !empty($this->id));
		$this->template->assign_vars(array(
			'S_CAN_SUBSCRIBE'			=> !$this->is_author_subscribed() && $this->use_queue,
			'SUBSCRIBE_AUTHOR'			=> $this->request->variable('subscribe_author', false),
			'S_POST_ACTION'				=> $this->contrib->get_url('revision'),
		));

		add_form_key('postform');

		return $this->helper->render(
			'contributions/contribution_revision.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['NEW_REVISION']
		);
	}

	/**
	* Handle initial revision, queue, and attachment creation.
	*
	* @return array Returns array in form for array('error' => array())
	*	where error contains any errors found.
	*/
	protected function create()
	{
		if ($this->request->variable('disagree', false))
		{
			// Did not agree to the agreement.
			redirect($this->contrib->get_url());
		}

		// Handle upload
		$this->uploader->handle_form_action();

		if ($this->uploader->uploaded)
		{
			$uploaded = $this->uploader->get_operator()->get_all_ids();

			// If multiple files are uploaded, then one is being replaced.
			if (sizeof($uploaded) > 1)
			{
				$this->uploader->get_operator()->delete(
					array_diff(
						$uploaded,
						array($this->uploader->uploaded)
					)
				);
			}
			$this->attachment = $this->uploader->get_uploaded_attachment();
		}

		if ($this->uploader->plupload_active())
		{
			return array(
				'response'	=> new JsonResponse($this->uploader->get_plupload_response_data()),
			);
		}
		else if ($this->request->is_set('attachment_data'))
		{
			$data = $this->uploader->get_filtered_request_data();

			if (!empty($data))
			{
				$attachment = array_shift($data);
				$this->load_attachment($attachment['attach_id']);
			}
		}

		$settings = array(
			'version'			=> $this->request->variable('revision_version', '', true),
			'name'				=> $this->request->variable('revision_name', '', true),
			'allow_repack'		=> $this->request->variable('queue_allow_repack', 0),
			'license'			=> $this->request->variable('revision_license', '', true),
			'custom'			=> $this->request->variable('custom_fields', array('' => ''), true),
			'vendor_versions'	=> $this->get_selected_branches(),
		);

		if ($this->has_custom_license($settings['license']))
		{
			$settings['license'] = $this->request->variable('revision_custom_license', '', true);
		}

		// Check for errors
		$error = array_merge(
			$this->uploader->get_errors(),
			$this->validate_settings($settings)
		);

		if (empty($error))
		{
			$this->create_revision($settings);

			// Add queue values to the queue table
			if ($this->use_queue)
			{
				$this->create_queue_item($settings['allow_repack']);

				// Subscribe author to queue discussion topic
				if ($this->request->variable('subscribe_author', false))
				{
					$this->subscriptions->subscribe(
						TITANIA_TOPIC,
						$this->queue->queue_discussion_topic_id
					);
				}
			}
			if ($this->attachment)
			{
				$this->set_package_paths();
			}

			$error = $this->clean_package();
		}

		return array('error' => $error);
	}

	/**
	* Final submission step.
	*
	* @return null
	*/
	protected function submit()
	{
		$this->revision->revision_submitted = true;
		$this->revision->allow_author_repack = false;
		$this->revision->validation_date = 0;

		// Automatically approve the revision and contrib if the queue is not being used
		if (!$this->use_queue)
		{
			$this->revision->validation_date = time();
			$this->revision->revision_status = TITANIA_REVISION_APPROVED;

			if ($this->contrib->contrib_status != TITANIA_CONTRIB_APPROVED)
			{
				$this->contrib->change_status(TITANIA_CONTRIB_APPROVED);
			}
			$this->revision->update_composer_package();
		}
		$this->revision->submit();
		$this->uploader->get_operator()->submit();

		// After revision is set to submitted we must update the queue
		$this->revision->update_queue($this->get_repack_exclusions());
	}

	/**
	* Cancel submission process.
	*
	* @param bool $redirect		Whether to redirect back to contrib details.
	* @return null
	*/
	protected function cancel($redirect = true)
	{
		if ($this->revision->revision_id)
		{
			$this->revision->delete();
		}
		$this->id = 0;

		if ($redirect)
		{
			redirect($this->contrib->get_url());
		}
	}

	/**
	* Determine which active revisions in the queue should not be marked as
	* repacked upon submitting the new revision to the queue.
	*
	* @return array Returns array of revision id's
	*/
	protected function get_repack_exclusions()
	{
		$exclude_from_repack = $revision_branches = array();
		$in_queue = $this->revisions_in_queue;

		if (empty($this->revision->phpbb_versions))
		{
			$this->revision->load_phpbb_versions();
		}

		foreach ($this->revision->phpbb_versions as $version)
		{
			$branch = (int) (is_array($version)) ? $version['phpbb_version_branch'] : $version;
			$revision_branches[] = $branch;

			if (in_array($branch, $this->repackable_branches) || $in_queue[$branch]['queue_status'] == TITANIA_QUEUE_NEW)
			{
				unset($in_queue[$branch]);
			}
		}

		foreach ($in_queue as $info)
		{
			$exclude_from_repack[] = (int) $info['revision_id'];
		}

		return $exclude_from_repack;

	}

	/**
	* Get branches that the user is allowed to submit a revision for.
	*
	* @return array
	*/
	protected function get_allowed_branches()
	{
		$allowed_branches = $this->contrib->type->get_allowed_branches(true);

		if (empty($this->revisions_in_queue))
		{
			return $allowed_branches;
		}

		foreach ($this->revisions_in_queue as $branch => $info)
		{
			if ($info['queue_status'] > TITANIA_QUEUE_NEW)
			{
				unset($allowed_branches[$branch]);
			}
		}
		return $allowed_branches;
	}

	/**
	* Create revision.
	*
	* @param array $settings
	* @return null
	*/
	protected function create_revision($settings)
	{
		$this->revision->__set_array(array(
			'attachment_id'			=> ($this->attachment) ? $this->attachment->get_id() : 0,
			'revision_name'			=> $settings['name'],
			'revision_version'		=> $settings['version'],
			'revision_status'		=> TITANIA_REVISION_NEW,
			'queue_allow_repack'	=> $settings['allow_repack'],
			'revision_license'		=> $settings['license'],
		));
		$this->revision->set_custom_fields($settings['custom']);
		$this->revision->phpbb_versions = $settings['vendor_versions'];

		$this->revision->submit();
		$this->id = $this->revision->revision_id;
	}

	/**
	* Create queue item
	*
	* @param bool $allow_repack		Whether author has allowed repacking.
	* @return null
	*/
	protected function create_queue_item($allow_repack)
	{
		// Create the queue
		$this->revision->update_queue();
		$this->queue = $this->revision->get_queue();

		// Load the message object
		$this->get_message();

		$this->queue->queue_allow_repack = $allow_repack;
		$this->queue->submit();
	}

	/**
	* Validate submitted settings
	*
	* @param array $settings
	* @return array Returns array containing any errors found.
	*/
	protected function validate_settings($settings)
	{
		$error = array();

		if ($this->require_upload && !$this->uploader->uploaded && !$this->attachment)
		{
			$error[] = $this->user->lang['NO_REVISION_ATTACHMENT'];
		}

		if (!$settings['version'])
		{
			$error[] = $this->user->lang['NO_REVISION_VERSION'];
		}

		if (!empty($this->contrib->type->license_options) && !$this->contrib->type->license_allow_custom
			&& !in_array($settings['license'], $this->contrib->type->license_options))
		{
			$error[] = $this->user->lang['INVALID_LICENSE'];
		}

		if (empty($settings['vendor_versions']))
		{
			$error[] = $this->user->lang['NO_PHPBB_BRANCH'];
		}
		else
		{
			$allowed_branches = $this->contrib->type->get_allowed_branches();

			foreach ($settings['vendor_versions'] as $branch)
			{
				if (!isset($allowed_branches[$branch]))
				{
					$error[] = $this->user->lang['INVALID_BRANCH'];
				}
				else if (
					isset($this->revisions_in_queue[$branch]) &&
					$this->revisions_in_queue[$branch]['queue_status'] > TITANIA_QUEUE_NEW &&
					!in_array($branch, $this->repackable_branches)
				)
				{
					$error[] = $this->user->lang('BRANCH_ALREADY_IN_QUEUE', $allowed_branches[$branch]['name']);
				}
			}
		}

		// Send the file to the type class so it can do custom error checks
		if ($this->uploader->uploaded)
		{
			$error = array_merge($error, $this->contrib->type->upload_check($this->attachment));
		}
		$error = array_merge($error, $this->contrib->type->validate_revision_fields($settings['custom']));

		return $error;
	}

	/**
	* Get selected vendor branches.
	*
	* @return array
	*/
	protected function get_selected_branches()
	{
		// phpBB branches
		$allowed_branches = array_keys($this->contrib->type->get_allowed_branches());

		if (sizeof($allowed_branches) == 1)
		{
			$selected_branches = $allowed_branches;
		}
		else
		{
			$selected_branches = $this->request->variable('phpbb_branch', array(0));
			$selected_branches = array_intersect($selected_branches, $allowed_branches);
		}
		return $selected_branches;
	}

	/**
	* Get queue message object.
	*
	* @return \phpbb\titania\message\message
	*/
	protected function get_message()
	{
		$this->message
			->set_parent($this->queue)
			->set_auth(array(
				'bbcode'	=> $this->auth->acl_get('u_titania_bbcode'),
				'smilies'	=> $this->auth->acl_get('u_titania_smilies'),
			))
			->set_settings(array(
				'display_error'		=> false,
				'display_subject'	=> false,
			))
		;

		$this->queue->post_data($this->message);

		return $this->message;
	}

	/**
	* Process revision submission steps.
	*
	* @return array Returns array in form of
	*	array(
	*		'error'				=> array(),
	*		'notice'			=> array(),
	*		'complete'			=> (bool),
	*		'allow_continue'	=> (bool),
	*	)
	*/
	protected function process_steps()
	{
		$id = $this->request->variable('revision_id', 0);
		$steps = $this->contrib->type->upload_steps;

		if (!check_form_key('postform'))
		{
			return $this->get_result(
				array('error' => array($this->user->lang['FORM_INVALID'])),
				$steps,
				-1
			);
		}

		// If not id is provided, then we're just starting the submission process.
		if (!$id)
		{
			return $this->get_result($this->create(), $steps, -1);
		}

		$this->load_revision($id);
		// We use the validation_date field during the submission process
		// to store the current step in order to ensure that the user does not
		// skip any steps.
		$step_num = $this->revision->validation_date;

		if (empty($steps[$step_num]))
		{
			return $this->get_result(array(), $steps, $step_num);
		}

		$step = $steps[$step_num];

		if ($this->attachment)
		{
			if (!$this->package->get_source())
			{
				$this->set_package_paths();
			}
		}

		$hash = $this->package->get_md5_hash();

		$result = $this->run_step($step['function']);
		$result = $this->get_result($result, $steps, $step_num);

		$this->package->cleanup();
		$new_hash = $this->package->get_md5_hash();

		if ($hash !== $new_hash)
		{
			$this->update_package_stats();
		}

		if (!$result['allow_continue'])
		{
			$this->cancel(false);
			return $result;
		}

		$this->revision->validation_date = $step_num + 1;
		$this->revision->submit();

		return $result;
	}

	/**
	* Run step function.
	*
	* @param string|array $function		Callable function.
	* @return array
	*/
	protected function run_step($function)
	{
		$download_url = ($this->attachment) ? $this->attachment->get_url() : '';

		return call_user_func_array($function, array(
			&$this->contrib,
			&$this->revision,
			&$this->attachment,
			$download_url,
			&$this->package
		));
	}

	/**
	* Get step result
	*
	* @param array $result
	* @param array $steps
	* @param int $step
	*
	* @return array
	*/
	protected function get_result($result, $steps, $step)
	{
		$_result = array(
			'error'				=> array(),
			'notice'			=> array(),
			'complete'			=> true,
			'allow_continue'	=> true,
		);
		$total_steps = sizeof($steps);

		if (!empty($result['error']))
		{
			$result['complete'] = $result['allow_continue'] = false;
		}
		else if ($total_steps > $step)
		{
			$result['complete'] = false;
			$result['allow_continue'] = true;
		}

		return array_merge($_result, $result);
	}

	/**
	* Clean attachment package.
	*
	* @return array Returns array containing any errors encountered.
	*/
	protected function clean_package()
	{
		$error = array();

		if (!$this->contrib->type->restore_root && !$this->contrib->type->clean_package)
		{
			return $error;
		}

		$root_directory = null;

		if ($this->contrib->type->restore_root && is_array($this->contrib->type->root_search))
		{
			$search = $this->contrib->type->root_search;
			$exclude = (!empty($search['exclude'])) ? $search['exclude'] : null;
			unset($search['exclude']);

			$root_directory = $this->package->find_directory($search, $exclude);

			if ($root_directory === null)
			{
				$error[] = $this->user->lang($this->contrib->type->root_not_found_key);
			}
		}

		if (empty($error))
		{
			if ($root_directory !== null)
			{
				// Adjust package name to follow naming conventions
				$new_root_name = $this->contrib->type->fix_package_name(
					$this->contrib,
					$this->revision,
					$this->attachment,
					$root_directory
				);
				$this->package->restore_root($root_directory, $new_root_name);
			}

			// Replace the uploaded zip package with the new one
			$this->package->repack($this->contrib->type->clean_package);
			$this->update_package_stats();
		}

		// Remove our temp files
		$this->package->cleanup();

		return $error;
	}

	/**
	 * Update package size and hash.
	 */
	protected function update_package_stats()
	{
		$sql_ary = array(
			'filesize'	=> $this->package->get_size(),
			'hash'		=> $this->package->get_md5_hash(),
		);

		// Update the attachment MD5 and filesize, it may have changed
		$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE attachment_id = ' . (int) $this->attachment->get_id();
		$this->db->sql_query($sql);
	}

	/**
	* Check user's authorization.
	*
	* @return bool Returns true if user is authorized.
	*/
	protected function check_auth()
	{
		return $this->auth->acl_get('u_titania_contrib_submit') &&
			($this->is_moderator || (!$this->contrib->is_restricted() && $this->is_author));
	}

	/**
	* Common handler for initial setup tasks
	*
	* @param string $contrib_type	Contrib type URL identifier.
	* @param string $contrib		Contrib name clean.
	* @return null
	*/
	protected function setup($contrib_type, $contrib)
	{
		$this->load_contrib($contrib_type, $contrib);

		$this->revision = new \titania_revision($this->contrib);
		$this->uploader->configure(
			TITANIA_CONTRIB,
			$this->contrib->contrib_id,
			true
		);
		$this->package = new \phpbb\titania\entity\package;
		$this->revisions_in_queue = $this->repackable_branches = array();

		$this->is_moderator = $this->contrib->type->acl_get('moderate');
		$this->use_queue = $this->ext_config->use_queue && $this->contrib->type->use_queue;
		$this->require_upload = $this->contrib->type->require_upload;
	}

	/**
	 * Set package location and temp path.
	 */
	protected function set_package_paths()
	{
		$this->package
			->set_temp_path($this->ext_config->__get('contrib_temp_path'), true)
			->set_source($this->attachment->get_filepath())
		;
	}

	/**
	* Load revision
	*
	* @param int $id		Revision id.
	* @throws \Exception	Throws exception if revision or its attachment
	*	cannot be loaded.
	* @return null
	*/
	protected function load_revision($id)
	{
		$this->id = (int) $id;

		if (!$this->id || !$this->revision->load($this->id) || $this->revision->contrib_id != $this->contrib->contrib_id ||
			($this->revision->attachment_id && !$this->load_attachment($this->revision->attachment_id)))
		{
			throw new \Exception($this->user->lang['NO_REVISION']);
		}
	}

	/**
	 * Load attachment
	 *
	 * @param int $id	Attachment id
	 * @return bool	Returns true if the attachment loaded successfully
	 */
	protected function load_attachment($id)
	{
		$operator = $this->uploader->get_operator();
		$attachment = $operator->load(array((int) $id), true, $this->user->data['user_id'])->get($id);
		$valid = false;

		if ($attachment &&
			$attachment->object_type == TITANIA_CONTRIB &&
			$attachment->object_id == $this->contrib->contrib_id &&
			$attachment->get('is_orphan')
		)
		{
			$this->attachment = $attachment;
			$valid = true;
		}
		return $valid;
	}

	/**
	* Check whether the author is subscribed to the queue discussion topic.
	*
	* @return bool
	*/
	protected function is_author_subscribed()
	{
		if ($this->use_queue)
		{
			$this->queue->contrib_id = $this->contrib->contrib_id;

			// Get queue discussion topic id if it exists
			$this->queue->get_queue_discussion_topic(true);

			if (!empty($this->queue->queue_discussion_topic_id))
			{
				// Is the author subscribed already?
				return $this->subscriptions->is_subscribed(
					TITANIA_TOPIC,
					$this->queue->queue_discussion_topic_id
				);
			}
		}
		return false;
	}

	/**
	* Check whether a custom license is expected.
	*
	* @param string $license		License name.
	* @return bool Returns true if the license name is the "Custom license" language string.
	*/
	protected function has_custom_license($license)
	{
		return $this->contrib->type->license_allow_custom && $license == $this->user->lang['CUSTOM_LICENSE'];
	}

	/**
	* Check whether the given license is a custom license.
	*
	* @param string $license	License name.
	* @return bool
	*/
	protected function is_custom_license($license)
	{
		return !empty($license) &&
			$this->contrib->type->license_allow_custom &&
			!in_array($license, $this->contrib->type->license_options);
	}

	/**
	* Assign common template variables.
	*
	* @param array $error			Array containing any errors found in form.
	* @param bool $basic_form		Whether we're outputting a basic form.
	* @param array $settings		Form settings.
	*
	* @return null
	*/
	protected function assign_common_vars($error, $basic_form, $settings = array())
	{
		$this->template->assign_vars(array(
			'REVISION_ID'			=> $this->id,
		));

		$this->display->assign_global_vars();
		$this->generate_navigation('manage');
		$this->generate_breadcrumbs();

		if ($basic_form && empty($error))
		{
			return;
		}

		$_settings = array_fill_keys(array('name', 'version', 'custom_license', 'license', 'allow_repack'), '');
		$_settings['custom'] = $this->request->variable('custom_fields', array('' => ''));
		$_settings['vendor_versions'] = array();
		$settings = array_merge($_settings, $settings);

		$this->display->generate_custom_fields(
			$this->contrib->type->revision_fields,
			$settings['custom'],
			$this->contrib->type->id
		);

		$this->template->assign_vars(array(
			'REVISION_NAME'				=> $this->request->variable('revision_name', $settings['name'], true),
			'REVISION_VERSION'			=> $this->request->variable('revision_version', $settings['version'], true),
			'REVISION_LICENSE'			=> $this->request->variable('revision_license', $settings['license'], true),
			'REVISION_CUSTOM_LICENSE'	=> $this->request->variable('revision_custom_license', $settings['custom_license'], true),

			'S_CUSTOM_LICENSE'			=> $this->has_custom_license($this->request->variable('revision_license', $settings['license'], true)),
			'S_ALLOW_CUSTOM_LICENSE'	=> $this->contrib->type->license_allow_custom,
			'S_REQUIRE_UPLOAD'			=> $this->require_upload,
			'S_REVISION_FORM'			=> true,
		));

		// Assign separately so we can output some data first
		$this->template->assign_var(
			'REVISION_UPLOADER',
			$this->uploader->parse_uploader('posting/attachments/revisions.html')
		);

		$this->display->generate_phpbb_version_select(
			$settings['vendor_versions'],
			$this->contrib->type->get_allowed_branches()
		);

		$this->template->assign_vars(array(
			'ERROR_MSG'				=> (!empty($error)) ? (is_array($error) ? implode('<br />', $error) : $error) : '',
			'AGREEMENT_NOTICE'		=> ($this->contrib->type->upload_agreement) ? nl2br($this->user->lang($this->contrib->type->upload_agreement)) : false,
			'QUEUE_ALLOW_REPACK'	=> $settings['allow_repack'],
		));

		// Output the available license options
		foreach ($this->contrib->type->license_options as $option)
		{
			$this->template->assign_block_vars('license_options', array(
				'NAME'		=> $option,
				'VALUE'		=> $option,
			));
		}
	}
}
