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

use phpbb\titania\url\url;

class titania_types
{	
	/**
	* Store the types we've setup
	*
	* @var array(type_id => type_class)
	*/
	public static $types = array();

	/**
	 * Load the types into the $types array
	 *
	 * @param string $ext_root_path
	 * @param string $php_ext
	 */
	public static function load_types($ext_root_path, $php_ext)
	{
		$dh = @opendir($ext_root_path . 'includes/types/');

		if (!$dh)
		{
			trigger_error('Could not open the types directory');
		}

		while (($fname = readdir($dh)) !== false)
		{
			if (strpos($fname, '.' . $php_ext) && substr($fname, 0, 1) != '_' && $fname != 'base.' . $php_ext)
			{
				include($ext_root_path . 'includes/types/' . $fname);

				$class_name = 'titania_type_' . substr($fname, 0, strpos($fname, '.' . $php_ext));

				phpbb::$user->add_lang_ext('phpbb/titania', 'types/' . substr($fname, 0, strpos($fname, '.' . $php_ext)));

				$class = new $class_name;
				self::$types[$class->id] = $class;
			}
		}

		closedir($dh);

		ksort(self::$types);
	}

	/**
	* Get the type_id from the url string
	*
	* @param mixed $url
	*/
	public static function type_from_url($url)
	{
		foreach (self::$types as $type_id => $class)
		{
			if ($class->url == $url)
			{
				return $type_id;
			}
		}

		return false;
	}

	/**
	* Get the types this user is authorized to perform actions on
	*
	* @param mixed $type
	*/
	public static function find_authed($type = 'view')
	{
		$authed = array();

		foreach (self::$types as $type_id => $class)
		{
			if ($class->acl_get($type))
			{
				$authed[] = $type_id;
			}
		}

		return $authed;
	}

	/**
	* Get the types that do not require validation
	*/
	public static function find_validation_free()
	{
		$free = array();

		foreach (self::$types as $type_id => $class)
		{
			if (!$class->require_validation)
			{
				$free[] = $type_id;
			}
		}

		return $free;
	}
	
	/**
	* Get the types that require an upload
	*/
	public static function require_upload()
	{
		$free = array();

		foreach (self::$types as $type_id => $class)
		{
			if ($class->require_upload)
			{
				$strict[] = $type_id;
			}
		}
		
		return $strict;
	}

	/**
	* Get the types that use Composer
	*/
	public static function use_composer($negate = false)
	{
		$types = array();

		foreach (self::$types as $type_id => $class)
		{
			$include = ($negate) ? (!$class->create_composer_packages) : ($class->create_composer_packages);

			if ($include)
			{
				$types[] = $type_id;
			}
		}
		return $types;
	}
}

/**
* Base class for types
*/
class titania_type_base
{
	/**
	 * For the url slug
	 *
	 * @var string portion to be used in the URL slug
	 */
	public $url = 'contribution';

	/**
	 * The type name
	 *
	 * @var string (any lang key that includes the type should match this value)
	 */
	public $name = '';

	/**
	 * The language for this type, initialize in constructor ($langs is for the plural forms of the language variables, used in category management)
	 *
	 * @var string
	 */
	public $lang = '';
	public $langs = '';

	/**
	* Additional steps/functions to call when uploading
	*
	* @var array Ex: array('function_one', 'function_two')
	*	can use special keywords such as: array(array('contrib_type', 'function_one'), array('contrib_tools', 'function_two'))
	*	for calling the function in the contrib_type class or contrib_tools class respectively
	*/
	public $upload_steps = array();

	/**
	* Additional language keys (just the language key, not in the current user's language)
	*
	* @var string
	*/
	public $validation_subject = '';
	public $validation_message_approve = '';
	public $validation_message_deny = '';
	public $create_public = '';
	public $reply_public = '';
	public $update_public = '';
	public $upload_agreement = '';

	/**
	* Allowed phpBB branches. Examples:
	*	Value					Matches
	*	all						20, 30, 31
	*	array('==', 30, 31)		30, 31
	*	array('<=', 30)			20, 30
	*	array('>', 30)			31
	*
	* @var mixed
	*/
	public $allowed_branches = 'all';

	/**
	* Require validation/use queue for this type?
	* FALSE on either this or the require_validation config setting means validation is not required for the type
	*
	* @var bool
	*/
	public $require_validation = true;
	public $use_queue = true;

	/**
	* Can we upload extra files (on revisions) for this type?
	*
	* @var bool
	*/
	public $extra_upload = true;

	/**
	* Run MPV/Automod Test for this type?
	*
	* @var bool
	*/
	public $mpv_test = false;
	public $automod_test = false;
	public $epv_test = false;

	/**
	 * Should the package be validated as a translation?
	 *
	 * @var bool
	 */
	public $validate_translation = false;

	/**
	 * Should the type require a file upload?
	 *
	 * @var bool
	 */
	public $require_upload = true;

	/**
	 * Create Composer packages for the type
	 *
	 * @var bool
	 */
	public $create_composer_packages = true;

	/**
	 * Allow revisions for a future release to be submitted
	 *
	 * @var bool
	 */
	public $prerelease_submission_allowed = false;

	/**
	* Find the root of the install package for this type?  If so, what to search for (see contrib_tools::find_root())?
	*
	* @var mixed
	*/
	public $restore_root = false;
	public $clean_package = false;
	public $root_search = false;
	public $root_not_found_key = 'COULD_NOT_FIND_ROOT';

	/**
	 * The forum_database and forum_robot, initialize in constructor
	 *
	 * @var int
	 */
	public $forum_database = 0;
	public $forum_robot = 0;

	/**
	* Array of available licenses for this type of contribution
	*/
	public $license_options = array();
	public $license_allow_custom = false;

	/** Custom revision fields */
	public $revision_fields = array();

	/** Custom contribution fields */
	public $contribution_fields = array();

	/**
	* Function that will be run when a revision of this type is uploaded
	*
	* @param $revision_attachment titania_attachment
	* @return array (error array, empty for no errors)
	*/
	public function upload_check($revision_attachment)
	{
		return array();
	}

	/**
	* Function to fix package name to ensure naming convention is followed
	*
	* @param $contrib Contribution object
	* @param $revision Revision object
	* @param $revision_attachment Attachment object
	* @param $root_dir Package root directory
	*
	* @return New root dir name
	*/
	public function fix_package_name($contrib, $revision, $revision_attachment, $root_dir = null)
	{
		$new_real_filename =
			url::generate_slug($contrib->contrib_name_clean) .
			'_' .
			preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version))
		;

		$revision_attachment->change_real_filename($new_real_filename . '.' . $revision_attachment->extension);

		return $new_real_filename;
	}

	/**
	* Run custom action after revision has been denied.
	*
	* @param \titania_contribution $contrib
	* @param \titania_queue $queue
	*
	* @return null
	*/
	public function deny($contrib, $queue)
	{
	}

	/**
	* Run custom action after revision has been approved.
	*
	* @param \titania_contribution $contrib
	* @param \titania_queue $queue
	*
	* @return null
	*/
	public function approve($contrib, $queue)
	{
	}

	/**
	 * Install demo.
	 *
	 * @param \titania_contribution $contrib
	 * @param \titania_revision $revision
	 * @return string Demo url
	 */
	public function install_demo($contrib, $revision)
	{
		return '';
	}

	/**
	* Display additional options when approving/denying a revision
	*
	* @param string $action		Either approve or deny
	* @return null
	*/
	public function display_validation_options($action)
	{
	}

	/**
	* Validate contribution fields.
	*
	* @return array Returns array containing any errors found.
	*/
	public function validate_contrib_fields($fields)
	{
		return array();
	}

	/**
	* Validate revision fields.
	*
	* @return array Returns array containing any errors found.
	*/
	public function validate_revision_fields($fields)
	{
		return array();
	}

	/**
	* Get allowed branches.
	*
	* @param bool $name_only			Only return branch names.
	* @param bool $check_allow_upload	Only include branch if it allows uploads.
	*
	* @return array
	*/
	public function get_allowed_branches($name_only = false, $check_allow_upload = true)
	{
		$allowed_branches = $names = array();
		$rule = $this->allowed_branches;

		foreach (titania::$config->phpbb_versions as $branch => $info)
		{
			if ($check_allow_upload && !$info['allow_uploads'])
			{
				continue;
			}
			$branch = (int) $branch;

			if ($rule != 'all')
			{
				$allowed = ($rule[0] == '==') ? in_array($branch, $rule) : version_compare($branch, $rule[1], $rule[0]);

				if (!$allowed)
				{
					continue;
				}
			}
			$allowed_branches[$branch] = $info;
			$names[$branch] = $info['name']; 
		}
		return ($name_only) ? $names : $allowed_branches;
	}
}
