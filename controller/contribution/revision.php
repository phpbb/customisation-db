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

class revision extends base
{
	/** @var int */
	protected $id;

	/** @var \titania_revision */
	protected $revision;

	/** @var \titania_attachment */
	protected $attachment;

	/** @var \titania_queue */
	protected $queue;

	/** @var array */
	protected $revisions_in_queue;

	/** @var array */
	protected $repackable_branches;

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
		$this->setup($contrib);

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
				$this->revision->repack($old_revision);
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
		$this->setup($contrib);

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

					\titania_subscriptions::send_notifications(
						TITANIA_QUEUE,
						$this->contrib->contrib_type,
						'subscribe_notify_forum.txt',
						$email_vars,
						$this->user->data['user_id']
					);
				}
				redirect($this->contrib->get_url());
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

		// Set up attachment object to get some default values
		$this->attachment->is_orphan = false;
		$this->attachment->upload();

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
			$this->attachment->error,
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
					\titania_subscriptions::subscribe(TITANIA_TOPIC, $this->queue->queue_discussion_topic_id);
				}
			}
			if ($this->contrib->type->clean_and_restore_root)
			{
				$error = $this->clean_package();
			}
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
			'attachment_id'			=> $this->attachment->attachment_id,
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

		if ($this->require_upload && !$this->attachment->uploaded)
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
					$error[] = $this->user->lang['INVALID_BRANCH'];
				}
			}
		}

		// Send the file to the type class so it can do custom error checks
		if ($this->attachment->uploaded)
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
	* @return \titania_message
	*/
	protected function get_message()
	{
		$message = new \titania_message($this->queue);
		$message->set_auth(array(
			'bbcode'	=> $this->auth->acl_get('u_titania_bbcode'),
			'smilies'	=> $this->auth->acl_get('u_titania_smilies'),
		));
		$message->set_settings(array(
			'display_error'		=> false,
			'display_subject'	=> false,
		));

		$this->queue->post_data($message);

		return $message;
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
				array('error' => array($this->user->lang['INVALID_FORM'])),
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

		if ($this->attachment->attachment_id)
		{
			// Start up the machine
			$this->contrib_tools = new \titania_contrib_tools(
				$this->attachment->get_filepath(),
				$this->attachment->get_unzip_dir($this->contrib->contrib_name, $this->revision->revision_version)
			);
		}
		else
		{
			$this->contrib_tools = null;
		}


		$result = $this->run_step($step['function']);
		$result = $this->get_result($result, $steps, $step_num);

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
		return call_user_func_array($function, array(
			&$this->contrib,
			&$this->revision,
			&$this->attachment,
			&$this->contrib_tools,
			$this->attachment->get_url()
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
			$result['complete'] = false;
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

		// Start up the machine
		$this->contrib_tools = new \titania_contrib_tools(
			$this->attachment->get_filepath(),
			$this->attachment->get_unzip_dir($this->contrib->contrib_name, $this->revision->revision_version)
		);

		// Clean the package
		$this->contrib_tools->clean_package();

		// Restore the root package directory
		if ($this->contrib->type->root_search)
		{
			$package_root = $this->contrib_tools->find_root(false, $this->contrib->type->root_search);
		}
		else
		{
			$package_root = $this->contrib_tools->find_root();
		}

		if ($package_root === false)
		{
			$error[] = $this->user->lang($this->contrib->type->root_not_found_key);
		}
		else
		{
			$this->contrib_tools->restore_root($package_root);
		}

		$error = array_merge($error, $this->contrib_tools->error);

		if (empty($error))
		{
			// Adjust package name to follow naming conventions
			$new_root_name = $this->contrib->type->fix_package_name($this->contrib, $this->revision, $this->attachment, $package_root);

			if ($new_root_name)
			{
				$this->contrib_tools->new_dir_name = $new_root_name;
			}

			// Replace the uploaded zip package with the new one
			$this->contrib_tools->replace_zip();

			$sql_ary = array(
				'filesize'	=> (int) $this->contrib_tools->filesize,
				'hash'		=> $this->contrib_tools->md5_hash,
			);

			// Update the attachment MD5 and filesize, it may have changed
			$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE attachment_id = ' . (int) $this->attachment->attachment_id;
			$this->db->sql_query($sql);
		}

		// Remove our temp files
		$this->contrib_tools->remove_temp_files();

		return $error;
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
	* @param string $contrib		Contrib name clean.
	* @return null
	*/
	protected function setup($contrib)
	{
		$this->load_contrib($contrib);

		$this->revision = new \titania_revision($this->contrib);
		$this->attachment = new \titania_attachment(TITANIA_CONTRIB, $this->contrib->contrib_id);
		$this->revisions_in_queue = $this->repackable_branches = array();

		$this->is_moderator = $this->contrib->type->acl_get('moderate');
		$this->use_queue = $this->ext_config->use_queue && $this->contrib->type->use_queue;
		$this->require_upload = $this->contrib->type->require_upload;

		\titania::_include('functions_posting', 'generate_phpbb_version_select');
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
			($this->revision->attachment_id && !$this->attachment->load($this->revision->attachment_id)))
		{
			throw new \Exception($this->user->lang['NO_REVISION']);
		}
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
				return \titania_subscriptions::is_subscribed(TITANIA_TOPIC, $this->queue->queue_discussion_topic_id);
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
		));

		// Assign separately so we can output some data first
		$this->template->assign_var('REVISION_UPLOADER', $this->attachment->parse_uploader('posting/attachments/revisions.html'));

		generate_phpbb_version_select($settings['vendor_versions'], $this->contrib->type->get_allowed_branches());

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
