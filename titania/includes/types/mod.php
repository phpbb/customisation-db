<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
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

define('TITANIA_TYPE_MOD', 1);

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

	// Validation messages (for the PM)
	public $validation_subject = 'MOD_VALIDATION';
	public $validation_message_approve = 'MOD_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'MOD_VALIDATION_MESSAGE_DENY';
	public $create_public = 'MOD_CREATE_PUBLIC';
	public $reply_public = 'MOD_REPLY_PUBLIC';
	public $update_public = 'MOD_UPDATE_PUBLIC';
	public $upload_agreement = 'MOD_UPLOAD_AGREEMENT';

	/**
	* Run MPV/Automod Test for this type?
	*/
	public $mpv_test = true;
	public $automod_test = true;
	public $clean_and_restore_root = true;
	public $root_search = array(array('install', '.xml'));
	public $display_install_file = true;

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['MODIFICATION'];
		$this->langs = phpbb::$user->lang['MODIFICATIONS'];
		$this->forum_database = titania::$config->forum_mod_database;
		$this->forum_robot = titania::$config->forum_mod_robot;

		if (titania::$config->use_queue && $this->use_queue)
		{
			if ($this->mpv_test)
			{
				$this->upload_steps[] = array('contrib_type', 'mpv_test');
			}

			if ($this->automod_test)
			{
				$this->upload_steps[] = array('contrib_type', 'automod_test');
			}
		}
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
			// Can submit a mod
			case 'submit' :
				return true;
			break;

			// Can view the mod queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_modification_queue_discussion');
			break;

			// Can view the mod queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_modification_queue');
			break;

			// Can validate mods in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_modification_validate');
			break;

			// Can moderate mods
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_modification_moderate', 'u_titania_mod_contrib_mod'));
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
		// If you change anything in here, remember to add the reverse to the uninstall() function below!

		if (!isset(phpbb::$config['titania_num_mods']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_modification_queue_discussion',
				'u_titania_mod_modification_queue',
				'u_titania_mod_modification_validate',
				'u_titania_mod_modification_moderate',
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
				'u_titania_mod_modification_queue_discussion',
				'u_titania_mod_modification_queue',
				'u_titania_mod_modification_validate',
				'u_titania_mod_modification_moderate',
			));

			// Mod count holder
			$umil->config_remove('titania_num_mods');
		}
	}

	public function mpv_test(&$contrib, &$revision, &$revision_attachment, &$contrib_tools, $download_package)
	{
		// Run MPV
		$mpv_results = $contrib_tools->mpv($download_package);

		if ($mpv_results === false)
		{
			return array(
				'notice'	=> $contrib_tools->error,
			);
		}
		else
		{
			$uid = $bitfield = $flags = false;
			generate_text_for_storage($mpv_results, $uid, $bitfield, $flags, true, true, true);

			// Add the MPV Results to the queue
			$queue = $revision->get_queue();
			$queue->mpv_results = $mpv_results;
			$queue->mpv_results_bitfield = $bitfield;
			$queue->mpv_results_uid = $uid;
			$queue->submit();

			$mpv_results = titania_generate_text_for_display($mpv_results, $uid, $bitfield, $flags);
			phpbb::$template->assign_var('MPV_RESULTS', $mpv_results);

			phpbb::$template->assign_var('S_AUTOMOD_TEST', titania_types::$types[$contrib->contrib_type]->automod_test);
		}
	}

	public function automod_test(&$contrib, &$revision, &$revision_attachment, &$contrib_tools, $download_package)
	{
		$new_dir_name = $contrib->contrib_name_clean . '_' . preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version));

		// Start up the machine
		$contrib_tools = new titania_contrib_tools($contrib_tools->original_zip, $new_dir_name);

		// Automod testing time
		$details = '';
		$error = $html_results = $bbcode_results = array();
		$sql = 'SELECT row_id, phpbb_version_branch, phpbb_version_revision FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			WHERE revision_id = ' . $revision->revision_id;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$version_string = $row['phpbb_version_branch'][0] . '.' . $row['phpbb_version_branch'][1] . '.' .$row['phpbb_version_revision'];
			$phpbb_path = $contrib_tools->automod_phpbb_files($version_string);

			if ($phpbb_path === false)
			{
				$error = array_merge($error, $contrib_tools->error);
				continue;
			}

			phpbb::$template->assign_vars(array(
				'PHPBB_VERSION'		=> $version_string,
				'TEST_ID'			=> $row['row_id'],
			));

			$html_result = $bbcode_result = '';
			$installed = $contrib_tools->automod($phpbb_path, $details, $html_result, $bbcode_result);

			$html_results[] = $html_result;
			$bbcode_results[] = $bbcode_result;
		}
		phpbb::$db->sql_freeresult($result);

		if (is_array($details))
		{
			$revision->install_time = $details['INSTALLATION_TIME'];

			switch ($details['INSTALLATION_LEVEL'])
			{
				case 'easy' :
					$revision->install_level = 1;
				break;

				case 'intermediate' :
					$revision->install_level = 2;
				break;

				case 'advanced' :
					$revision->install_level = 3;
				break;
			}

			$revision->submit();
		}

		$html_results = implode('<br /><br />', $html_results);
		$bbcode_results = implode("\n\n", $bbcode_results);

		// Update the queue with the results
		$queue = $revision->get_queue();
		$queue->automod_results = $bbcode_results;
		$queue->submit();

		phpbb::$template->assign_var('AUTOMOD_RESULTS', $html_results);

		// Remove our temp files
		$contrib_tools->remove_temp_files();

		return array(
			'error'	=> $error,
		);
	}
}
