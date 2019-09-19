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

namespace phpbb\titania\contribution\type;

use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\titania\attachment\attachment;
use phpbb\titania\config\config as ext_config;
use phpbb\titania\url\url;
use phpbb\user;

class base implements type_interface
{
	/** @var ext_config */
	protected $ext_config;

	/** @var user */
	protected $user;

	/** @var auth */
	protected $auth;

	/** @var int **/
	public $id;

	/**
	 * Type identifier for URL slug.
	 *
	 * @var string
	 */
	public $url = 'contribution';

	/**
	 * The type name.
	 *
	 * The language file and any lang key that includes the
	 * type should match this value.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * The language string identifying the type.
	 *
	 * $langs is the plural forms of this.
	 *
	 * @var string
	 */
	public $lang = '';
	public $langs = '';

	/**
	 * Additional steps to run when uploading a revision.
	 *
	 * Ex: array(array(
	 * 		'name'		=> 'EPV',
	 * 		'function'	=> array($this, 'epv_test')
	 * ))
	 *
	 * The callable function will be passed the following parameters:
	 * 		\titania_contribution $contrib
	 * 		\titania_revision $revision
	 * 		\phpbb\titania\attachment\attachment $attachment
	 * 		string $download_package
	 * 		\phpbb\titania\entity\package $package
	 * 		\phpbb\template\template $template
	 *
	 * @var array
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
	 * Allowed phpBB branches.
	 *
	 * Examples:
	 *	Value					Matches
	 *	all						20, 30, 31
	 *	array('==', 30, 31)		30, 31
	 *	array('<=', 30)			20, 30
	 *	array('>', 30)			31
	 *
	 * @var string|array
	 */
	public $allowed_branches = 'all';

	/**
	 * Require validation/use queue for this type?
	 *
	 * False on either this or the require_validation config setting means
	 * validation is not required for the type
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
	public $create_composer_packages = false;

	/**
	 * Find the root of the install package for this type?
	 * If so, what to search for (see package:find_root())?
	 *
	 * @var mixed
	 */
	public $restore_root = false;
	public $clean_package = false;
	public $root_search = false;
	public $root_not_found_key = 'COULD_NOT_FIND_ROOT';

	/**
	 * The forum_database and forum_robot.
	 *
	 * @var int
	 */
	public $forum_database = 0;
	public $forum_robot = 0;

	/**
	 * Array of available licenses for this type of contribution
	 *
	 * @var array
	 */
	public $license_options = array();

	/**
	 * Allow specifying a custom license.
	 *
	 * @var bool
	 */
	public $license_allow_custom = false;

	/**
	 * Custom revision fields.
	 *
	 * Example:
	 * 	array(
	 * 		'revision_bbc_html_replace' => array(
	 * 			'type'		=> 'textarea',
	 * 			'name'		=> 'REVISION_HTML_REPLACE',
	 * 			'explain'	=> 'REVISION_HTML_REPLACE_EXPLAIN'
	 * 	))
	 * Available types are input|textarea
	 *
	 * @var array
	 */
	public $revision_fields = array();

	/**
	 * Custom contribution fields.
	 *
	 * Example:
	 * 	array(
	 * 		'contrib_local_name' => array(
	 * 			'type'		=> 'input',
	 * 			'name'		=> 'CONTRIB_LOCAL_NAME',
	 * 			'explain'	=> 'CONTRIB_LOCAL_NAME_EXPLAIN',
	 * 			'editable'	=> true,
	 * 	))
	 *
	 * @var array
	 */
	public $contribution_fields = array();

	const ID = 0;
	const NAME = 'contribution';
	const URL = 'contribution';

	/**
	 * Constructor
	 *
	 * @param ext_config $ext_config
	 * @param user $user
	 * @param auth $auth
	 */
	public function __construct(ext_config $ext_config, user $user, auth $auth)
	{
		$this->ext_config = $ext_config;
		$this->user = $user;
		$this->auth = $auth;

		$this->id = static::ID;
		$this->name = static::NAME;
		$this->url = static::URL;

		$this->user->add_lang_ext('phpbb/titania', 'types/' . static::NAME);

		$this->configure();
	}

	/**
	 * Configure type properties.
	 */
	protected function configure()
	{
	}

	/**
	 * Get type id
	 * @return int
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * @{inheritDoc}
	 */
	public function acl_get($action)
	{
		return false;
	}

	/**
	 * @{inheritDoc}
	 */
	public function upload_check(attachment $attachment)
	{
		return array();
	}

	/**
	 * @{inheritDoc}
	 */
	public function fix_package_name(\titania_contribution $contrib, \titania_revision $revision, attachment $attachment, $root_dir = null)
	{
		$new_real_filename =
			url::generate_slug($contrib->contrib_name_clean) .
			'_' .
			preg_replace('#[^0-9a-z]#', '_', strtolower($revision->revision_version))
		;

		$attachment->change_real_filename($new_real_filename . '.' . $attachment->extension);

		return $new_real_filename;
	}

	/**
	 * @{inheritDoc}
	 */
	public function deny(\titania_contribution $contrib, \titania_queue $queue, request_interface $request)
	{
	}

	/**
	 * @{inheritDoc}
	 */
	public function approve(\titania_contribution $contrib, \titania_queue $queue, request_interface $request)
	{
	}

	/**
	 * @{inheritDoc}
	 */
	public function install_demo(\titania_contribution $contrib, \titania_revision $revision)
	{
		return '';
	}

	/**
	 * @{inheritDoc}
	 */
	public function display_validation_options($action, request_interface $request, template $template)
	{
	}

	/**
	 * @{inheritDoc}
	 */
	public function validate_contrib_fields(array $fields)
	{
		return array();
	}

	/**
	 * @{inheritDoc}
	 */
	public function validate_revision_fields(array $fields)
	{
		return array();
	}

	/**
	 * @{inheritDoc}
	 */
	public function get_allowed_branches($name_only = false, $check_allow_upload = true)
	{
		$allowed_branches = $names = array();
		$rule = $this->allowed_branches;

		foreach ($this->ext_config->phpbb_versions as $branch => $info)
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

	/**
	 * @{inheritDoc}
	 */
	public function get_demo()
	{
		return null;
	}

	/**
	 * @{inheritDoc}
	 */
	public function get_prevalidator()
	{
		return null;
	}
}
