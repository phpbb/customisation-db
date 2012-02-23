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
	*/
	public static function load_types()
	{
		$dh = @opendir(TITANIA_ROOT . 'includes/types/');

		if (!$dh)
		{
			trigger_error('Could not open the types directory');
		}

		while (($fname = readdir($dh)) !== false)
		{
			if (strpos($fname, '.' . PHP_EXT) && substr($fname, 0, 1) != '_' && $fname != 'base.' . PHP_EXT)
			{
				include(TITANIA_ROOT . 'includes/types/' . $fname);

				$class_name = 'titania_type_' . substr($fname, 0, strpos($fname, '.' . PHP_EXT));

				titania::add_lang('types/' . substr($fname, 0, strpos($fname, '.' . PHP_EXT)));

				$class = new $class_name;

				$class->auto_install();

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

	public static function increment_count($type)
	{
		self::$types[$type]->increment_count();

		set_config('titania_num_mods', ++phpbb::$config['titania_num_contribs'], true);
	}

	public static function decrement_count($type)
	{
		self::$types[$type]->decrement_count();

		set_config('titania_num_mods', --phpbb::$config['titania_num_contribs'], true);
	}

	public static function get_count($type = false)
	{
		if ($type)
		{
			return self::$types[$type]->get_count();
		}

		return phpbb::$config['titania_num_mods'];
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

	/**
	 * Should the package be validated as a translation?
	 *
	 * @var bool
	 */
	public $validate_translation = false;

	/**
	* Find the root of the install package for this type?  If so, what to search for (see contrib_tools::find_root())?
	*
	* @var mixed
	*/
	public $clean_and_restore_root = false;
	public $root_search = false;
	public $root_not_found_key = 'COULD_NOT_FIND_ROOT';

	/**
	* Display the install file to the users?
	* Note that this only works with ModX files
	*
	* @var bool
	*/
	public $display_install_file = false;

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
}
