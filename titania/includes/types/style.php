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

define('TITANIA_TYPE_STYLE', 2);

class titania_type_style extends titania_type_base
{
	/**
	 * The type id
	 *
	 * @var int type id (for custom types not specified in titania to start, please start with 10 in case we add any extra later)
	 */
	public $id = 2;

	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'style';

	/**
	 * The name of the field used to hold the number of this item in the authors table
	 *
	 * @var string author count
	 */
	public $author_count = 'author_styles';

	// Validation messages (for the PM)
	public $validation_subject = 'STYLE_VALIDATION';
	public $validation_message_approve = 'STYLE_VALIDATION_MESSAGE_APPROVE';
	public $validation_message_deny = 'STYLE_VALIDATION_MESSAGE_DENY';
	public $create_public = 'STYLE_CREATE_PUBLIC';
	public $reply_public = 'STYLE_REPLY_PUBLIC';
	public $update_public = 'STYLE_UPDATE_PUBLIC';
	//public $upload_agreement = 'STYLE_UPLOAD_AGREEMENT';

	// License options
	public $license_options = array(
		'GPL v2.0',
		'GPL v2.0 or later',
		'GPL v3.0',
		'GPL v3.0 or later',
		'LGPL v2.1',
		'LGPL v3.0',
	);
	public $license_allow_custom = true;

	public function __construct()
	{
		$this->lang = phpbb::$user->lang['STYLE'];
		$this->langs = phpbb::$user->lang['STYLES'];
		$this->forum_database = titania::$config->forum_style_database;
		$this->forum_robot = titania::$config->forum_style_robot;
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
			// Can submit a style
			case 'submit' :
				return true;
			break;

			// Can view the style queue discussion
			case 'queue_discussion' :
				return phpbb::$auth->acl_get('u_titania_mod_style_queue_discussion');
			break;

			// Can view the style queue
			case 'view' :
				return phpbb::$auth->acl_get('u_titania_mod_style_queue');
			break;

			// Can validate styles in the queue
			case 'validate' :
				return phpbb::$auth->acl_get('u_titania_mod_style_validate');
			break;

			// Can moderate styles
			case 'moderate' :
				return phpbb::$auth->acl_gets(array('u_titania_mod_style_moderate', 'u_titania_mod_contrib_mod'));
			break;
			
			// Can edit ColorizeIt settings
			case 'colorizeit' :
			    return phpbb::$auth->acl_get('u_titania_mod_style_clr');
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

		if (!isset(phpbb::$config['titania_num_styles']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_add(array(
				'u_titania_mod_style_queue_discussion',
				'u_titania_mod_style_queue',
				'u_titania_mod_style_validate',
				'u_titania_mod_style_moderate',
				'u_titania_mod_style_clr',
			));

			// Style count holder
			$umil->config_add('titania_num_styles', 0, true);
		}
	}

	public function increment_count()
	{
		set_config('titania_num_styles', ++phpbb::$config['titania_num_styles'], true);
	}

	public function decrement_count()
	{
		set_config('titania_num_styles', --phpbb::$config['titania_num_styles'], true);
	}

	public function get_count()
	{
		return phpbb::$config['titania_num_styles'];
	}

	/**
	* Uninstall the type
	*/
	public function uninstall()
	{
		if (isset(phpbb::$config['titania_num_styles']))
		{
			if (!class_exists('umil'))
			{
				include(PHPBB_ROOT_PATH . 'umil/umil.' . PHP_EXT);
			}

			$umil = new umil(true, phpbb::$db);

			// Permissions
			$umil->permission_remove(array(
				'u_titania_mod_style_queue_discussion',
				'u_titania_mod_style_queue',
				'u_titania_mod_style_validate',
				'u_titania_mod_style_moderate',
				'u_titania_mod_style_clr',
			));

			// Mod count holder
			$umil->config_remove('titania_num_styles');
		}
	}

	/**
	* Function that will be run when a revision of this type is uploaded
	*
	* @param $revision_attachment titania_attachment
	* @return array (error array, empty for no errors)
	*/
	public function upload_check($revision_attachment)
	{
		$zip_file = titania::$config->upload_path . '/' . utf8_basename($revision_attachment->attachment_directory) . '/' . utf8_basename($revision_attachment->physical_filename);
		$new_dir_name = phpbb::$user->data['user_id'] . '_' . microtime();

		$contrib_tools = new titania_contrib_tools($zip_file, $new_dir_name);

        foreach (scandir($contrib_tools->unzip_dir) as $item)
		{
			if ($item == '.' || $item == '..')
			{
				continue;
			}

			// Allow the license to be either in the main directory or under one subdirectory
			if (is_dir($contrib_tools->unzip_dir . '/' . $item))
			{
				foreach (scandir($contrib_tools->unzip_dir . '/' . $item) as $sub_item)
				{
		            if ($sub_item == 'license.txt')
					{
						// License file found
						$contrib_tools->remove_temp_files();
						return array();
					}
				}
			}

            if ($item == 'license.txt')
			{
				// License file found
				$contrib_tools->remove_temp_files();
				return array();
			}
        }

		$contrib_tools->remove_temp_files();

		return array(phpbb::$user->lang['LICENSE_FILE_MISSING']);
	}
}