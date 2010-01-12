<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_type_base'))
{
	include(TITANIA_ROOT . 'includes/types/base.' . PHP_EXT);
}

class titania_type_mod extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 1;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'mod';

	/**
	 * The name of the field used to hold the number of this item in the authors table
	 *
	 * @var string author count
	 */
	public $author_count = 'author_mods';

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['MODIFICATION'];
	}

	/**
	* Check auth level
	*
	* @param string $auth ('view', 'test', 'validate')
	* @return bool
	*/
	public function acl_get($auth)
	{
		switch ($auth)
		{
			// Can view the mod queue
			case 'view' :
				return phpbb::$auth->acl_get('titania_mod_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('titania_mod_validate');
			break;

			// Can moderate mods
			case 'moderate' :
				return phpbb::$auth->acl_get('titania_mod_moderate');
			break;
		}

		return false;
	}

	/**
	* Automatically install the type if required
	*
	* For adding type specific permissions, etc.
	*/
	public function auto_install()
	{
		if (!isset(phpbb::$config['titania_num_mods']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'titania_mod_queue',
				'titania_mod_validate',
				'titania_mod_moderate',
			));

			// Mod count holder
			$umil->config_add('titania_num_mods', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_mods', ++phpbb::$config['titania_num_mods'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_mods', --phpbb::$config['titania_num_mods'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_mods'];
	}

	/**
	* Each different type needs to handle revision submission differently, use this function to properly create a new revision
	*/
	public function create_revision($contrib)
	{
		$new_revision_step = request_var('new_revision_step', 0);

		$error = array();
		if (!check_form_key('new_revision'))
		{
			$error[] = phpbb::$user->lang['FORM_INVALID'];
		}

		switch ($new_revision_step)
		{
			case 1 :
				// Upload the revision
				$revision_attachment = new titania_attachment(TITANIA_CONTRIB, $contrib->contrib_id);
				$revision_attachment->upload(TITANIA_ATTACH_EXT_CONTRIB);
				$revision_version = utf8_normalize_nfc(request_var('revision_version', '', true));

				// Check for errors
				$error = array_merge($error, $revision_attachment->error);
				if (!$revision_attachment->uploaded)
				{
					$error[] = phpbb::$user->lang['NO_REVISION_ATTACHMENT'];
				}
				if (!$revision_version)
				{
					$error[] = phpbb::$user->lang['NO_REVISION_VERSION'];
				}

				if (sizeof($error))
				{
					// Start over...
					phpbb::$template->assign_vars(array(
						'REVISION_UPLOADER'		=> $revision_attachment->parse_uploader('posting/attachments/revisions.html'),
					));
				}
				else
				{
					// Success, create a new revision to start
					$revision = new titania_revision(titania::$contrib);
					$revision->__set_array(array(
						'attachment_id'		=> $revision_attachment->attachment_id,
						'revision_name'		=> utf8_normalize_nfc(request_var('revision_name', '', true)),
						'revision_version'	=> $revision_version,
					));
					$revision->submit();

					$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
					$new_dir_name = $contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision_version));

					// Start up the machine
					$contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);

					// Clean the package
					$contrib_tools->clean_package();

					// Restore the root package directory
					$contrib_tools->restore_root();

					$error = array_merge($error, $contrib_tools->error);

					if (sizeof($error))
					{
						// Start over...
						phpbb::$template->assign_vars(array(
							'REVISION_UPLOADER'		=> $revision_attachment->parse_uploader('posting/attachments/revisions.html'),
						));
					}
					else
					{
						// Replace the uploaded zip package with the new one
						$contrib_tools->replace_zip();
						$contrib_tools->remove_temp_files();
					}
				}
			break;
		}

		phpbb::$template->assign_vars(array(
			'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',
			'STEP'				=> (sizeof($error)) ? ($new_revision_step - 1) : $new_revision_step,
			'NEXT_STEP'			=> (sizeof($error)) ? $new_revision_step : ($new_revision_step + 1),

			'S_NEW_REVISION'	=> true,
		));

		add_form_key('new_revision');

		titania::page_header('NEW_REVISION');
		titania::page_footer(true, 'contributions/contribution_manage.html');
	}


	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_mods']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'titania_mod_queue',
				'titania_mod_validate',
				'titania_mod_moderate',
			));

			// Mod count holder
			$umil->config_remove('titania_num_mods');
		}
	}
}