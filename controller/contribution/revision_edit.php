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

use phpbb\titania\contribution\type\collection as type_collection;
use phpbb\titania\ext;
use Symfony\Component\HttpFoundation\JsonResponse;

class revision_edit extends revision
{
	/** @var array */
	protected $vendor_versions;

	/** @var \phpbb\titania\attachment\operator */
	protected $attachments;

	/** @var \phpbb\titania\attachment\uploader */
	protected $translations;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\template\template $template
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\controller\helper $helper
	 * @param type_collection $types
	 * @param \phpbb\request\request $request
	 * @param \phpbb\titania\cache\service $cache
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\titania\display $display
	 * @param \phpbb\titania\access $access
	 * @param \phpbb\titania\attachment\operator $attachments
	 * @param \phpbb\titania\attachment\uploader $translations
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, \phpbb\titania\controller\helper $helper, type_collection $types, \phpbb\request\request $request, \phpbb\titania\cache\service $cache, \phpbb\titania\config\config $ext_config, \phpbb\titania\display $display, \phpbb\titania\access $access, \phpbb\titania\attachment\operator $attachments, \phpbb\titania\attachment\uploader $translations)
	{
		base::__construct($auth, $config, $db, $template, $user, $helper, $types, $request, $cache, $ext_config, $display, $access);

		$this->attachments = $attachments;
		$this->translations = $translations;
	}

	/**
	* Edit revision action.
	*
	* @param string $contrib_type		Contrib type URL identifier
	* @param string $contrib			Contrib name clean
	* @param int $id					Revision id
	*
	* @return \Symfony\Component\HttpFoundation\Response|JsonResponse
	*/
	public function edit($contrib_type, $contrib, $id)
	{
		$this->setup($contrib_type, $contrib);

		if (!$this->check_auth())
		{
			return $this->helper->needs_auth();
		}

		$this->load_revision($id);
		$this->vendor_versions = $this->cache->get_phpbb_versions();

		$this->handle_revision_delete();

		// Translations
		$this->translations->configure(ext::TITANIA_TRANSLATION, $this->id, true);

		if ($this->contrib->type->extra_upload)
		{
			$this->translations->get_operator()->load();
			$this->translations->handle_form_action();
			$this->handle_translation_delete();

			if ($this->translations->plupload_active())
			{
				return new JsonResponse($this->translations->get_plupload_response_data());
			}
		}

		$settings = $this->get_settings();
		$error = array();

		// Submit the revision
		if ($this->request->is_set_post('submit'))
		{
			$error = array_merge(
				$this->validate_settings($settings),
				$this->translations->get_errors()
			);

			// If no errors, submit
			if (empty($error))
			{
				$this->submit_settings($settings);

				redirect($this->contrib->get_url());
			}
		}

		$this->assign_vars($settings, $error);
		add_form_key('postform');

		return $this->helper->render(
			'contributions/contribution_revision_edit.html',
			$this->contrib->contrib_name . ' - ' . $this->user->lang['EDIT_REVISION']
		);
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
		$this->is_moderator = $this->contrib->type->acl_get('moderate');
	}

	/**
	 * Load attachment
	 *
	 * @param int $id	Attachment id
	 * @return bool	Returns true if the attachment loaded successfully
	 */
	protected function load_attachment($id)
	{
		$this->attachment = $this->attachments
			->configure(ext::TITANIA_CONTRIB, $this->contrib->contrib_id)
			->load(array((int) $id))
			->get($id);

		return !empty($this->attachment);
	}

	/**
	* Get revision settings.
	*
	* @return array
	*/
	protected function get_settings()
	{
		$settings = array(
			'name'				=> $this->request->variable('revision_name', $this->revision->revision_name, true),
			'status'			=> $this->request->variable('revision_status', $this->revision->revision_status),
			'license'			=> $this->request->variable('revision_license', $this->revision->revision_license, true),
			'custom_license'	=> $this->request->variable('revision_custom_license', $this->revision->revision_license, true),
			'vendor_versions'	=> $this->get_selected_versions(),
		);

		if ($this->is_custom_license($settings['license']))
		{
			$settings['license'] = $this->user->lang['CUSTOM_LICENSE'];
		}

		return $settings;
	}

	/**
	* Get selected versions.
	*
	* @return array
	*/
	protected function get_selected_versions()
	{
		if ($this->request->is_set_post('submit'))
		{
			return $this->request->variable('revision_phpbb_versions', array(''));
		}

		$selected_versions = array();
		$this->revision->load_phpbb_versions();

		foreach ($this->revision->phpbb_versions as $row)
		{
			$selected_versions[] = $this->vendor_versions[$row['phpbb_version_branch'] . $row['phpbb_version_revision']];
		}

		return $selected_versions;
	}

	/**
	* Handle translation deletion.
	*
	* @return null
	*/
	protected function handle_translation_delete()
	{
		$action = $this->request->variable('action', '');
		$attach_id = $this->request->variable('a', 0);
		$hash = $this->request->variable('hash', '');

		if ($action == 'delete_attach' && check_link_hash($hash, 'attach_manage'))
		{
			$this->translations->get_operator()->delete(array($attach_id));
		}
	}

	/**
	* Handle revision deletion.
	*
	* @return null
	*/
	protected function handle_revision_delete()
	{
		if ($this->request->is_set_post('delete') && $this->is_moderator && check_form_key('postform'))
		{
			$this->revision->delete();
			redirect($this->contrib->get_url());
		}
	}

	/**
	* Validated submitted settings.
	*
	* @param array $settings
	* @return array Returns array containing any errors found.
	*/
	protected function validate_settings($settings)
	{
		$error = array();

		if (!check_form_key('postform'))
		{
			$error[] = $this->user->lang['FORM_INVALID'];
		}
		$license_options = ($this->contrib->type->license_options) ?: array();

		if ($license_options && !$this->contrib->type->license_allow_custom && !in_array($settings['license'], $license_options))
		{
			$error[] = $this->user->lang['INVALID_LICENSE'];
		}

		if ($this->is_moderator)
		{
			// Do some simple error checking on the versions
			if (empty($settings['vendor_versions']))
			{
				$error[] = $this->user->lang['MUST_SELECT_ONE_VERSION'];
			}
			else
			{
				foreach ($settings['vendor_versions'] as $version)
				{
					if (!$version || strlen($version) < 5 || $version[1] != '.' || $version[3] != '.')
					{
						$error[] = $this->user->lang('BAD_VERSION_SELECTED', $version);
					}
				}
			}
		}

		return $error;
	}

	/**
	* Submit revision.
	*
	* @param array $settings
	* @return null
	*/
	protected function submit_settings($settings)
	{
		$license = ($this->has_custom_license($settings['license'])) ? $settings['custom_license'] : $settings['license'];
		$this->revision->__set_array(array(
			'revision_name'			=> $settings['name'],
			'revision_license'		=> $license,
		));

		if ($this->is_moderator)
		{
			$can_change_status =
				!$this->is_author ||
				$this->ext_config->allow_self_validation ||
				$this->user->data['user_type'] == USER_FOUNDER;

			// Update the status
			if ($settings['status'] != $this->revision->revision_status && $settings['status'] != ext::TITANIA_REVISION_APPROVED && $can_change_status)
			{
				$this->revision->change_status($settings['status']);
			}

			// Update the phpBB versions
			$this->revision->phpbb_versions = array();

			foreach ($settings['vendor_versions'] as $version)
			{
				$branch = (int) $version[0] . (int) $version[2];
				$release = substr($version, 4);

				if (!isset($this->vendor_versions[$branch . $release]))
				{
					// Have we added some new phpBB version that does not exist?  We need to purge the cache then
					$this->cache->destroy('_titania_phpbb_versions');
				}

				// Update the list of phpbb_versions for the revision to update
				$this->revision->phpbb_versions[] = array(
					'phpbb_version_branch'		=> $branch,
					'phpbb_version_revision'	=> $release,
				);
			}
		}

		$this->translations->get_operator()->submit();
		$this->revision->submit();
	}

	/**
	* Assign template variables.
	*
	* @param array $settings
	* @param array $error
	*
	* @return null
	*/
	protected function assign_vars($settings, $error)
	{
		$this->display->assign_global_vars();
		$this->generate_navigation('manage');

		// Output the available license options
		foreach ($this->contrib->type->license_options as $option)
		{
			$this->template->assign_block_vars('license_options', array(
				'NAME'		=> $option,
				'VALUE'		=> $option,
			));
		}

		// Display the list of phpBB versions available
		foreach ($this->vendor_versions as $version => $name)
		{
			$this->template->assign_block_vars('phpbb_versions', array(
				'VERSION'		=> $name,
				'S_SELECTED'	=> in_array($name, $settings['vendor_versions']),
			));
		}

		$status_list = array(
			ext::TITANIA_REVISION_NEW				=> 'REVISION_NEW',
			ext::TITANIA_REVISION_APPROVED			=> 'REVISION_APPROVED',
			ext::TITANIA_REVISION_DENIED			=> 'REVISION_DENIED',
			ext::TITANIA_REVISION_PULLED_SECURITY	=> 'REVISION_PULLED_FOR_SECURITY',
			ext::TITANIA_REVISION_PULLED_OTHER		=> 'REVISION_PULLED_FOR_OTHER',
			ext::TITANIA_REVISION_REPACKED			=> 'REVISION_REPACKED',
			ext::TITANIA_REVISION_RESUBMITTED		=> 'REVISION_RESUBMITTED',
		);

		// Display the status list
		foreach ($status_list as $status => $lang)
		{
			$this->template->assign_block_vars('status_select', array(
				'S_SELECTED'		=> $status == $settings['status'],
				'VALUE'				=> $status,
				'NAME'				=> $this->user->lang[$lang],
			));
		}
		$has_custom_license = $this->has_custom_license($settings['license']);
		$translation_uploader = '';

		if ($this->contrib->type->extra_upload)
		{
			$translation_uploader = $this->translations->parse_uploader('posting/attachments/default.html');
		}

		// Display the rest of the page
		$this->template->assign_vars(array(
			'ERROR_MSG'					=> (!empty($error)) ? implode('<br />', $error) : '',
			'REVISION_NAME'				=> $settings['name'],
			'REVISION_LICENSE'			=> $settings['license'],
			'REVISION_CUSTOM_LICENSE'	=> ($has_custom_license) ? $settings['custom_license'] : '',

			'TRANSLATION_UPLOADER'		=> $translation_uploader,

			'S_IS_MODERATOR'			=> $this->is_moderator,
			'S_POST_ACTION'				=> $this->contrib->get_url('revision', array(
				'page'	=> 'edit',
				'id'	=> $this->id,
			)),
			'S_FORM_ENCTYPE'			=> ' enctype="multipart/form-data"',
			'S_CUSTOM_LICENSE'			=> $has_custom_license,
			'S_ALLOW_CUSTOM_LICENSE'	=> $this->contrib->type->license_allow_custom,
		));
	}
}
