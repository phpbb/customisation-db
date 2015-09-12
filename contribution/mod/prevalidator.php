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

namespace phpbb\titania\contribution\mod;

class prevalidator
{
	/** @var \phpbb\titania\config\config */
	protected $ext_config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\titania\contribution\prevalidator_helper */
	protected $helper;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $ext_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array */
	protected $errors = array();

	/**
	 * Constructor.
	 *
	 * @param \phpbb\titania\config\config $ext_config
	 * @param \phpbb\user $user
	 * @param \phpbb\template\template $template
	 * @param \phpbb\titania\contribution\prevalidator_helper $helper
	 * @param string $phpbb_root_path
	 * @param string $ext_root_path
	 * @param string $php_ext
	 */
	public function __construct(\phpbb\titania\config\config $ext_config, \phpbb\user $user, \phpbb\template\template $template, \phpbb\titania\contribution\prevalidator_helper $helper, $phpbb_root_path, $ext_root_path, $php_ext)
	{
		$this->ext_config = $ext_config;
		$this->user = $user;
		$this->template = $template;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->ext_root_path = $ext_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Get helper.
	 *
	 * @return \phpbb\titania\contribution\prevalidator_helper
	 */
	public function get_helper()
	{
		return $this->helper;
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public function get_errors()
	{
		return $this->errors;
	}

	/**
	 * Run the prevalidator.
	 * @param $download_location	Revision download URL.
	 * @return bool|mixed
	 */
	public function run_mpv($download_location)
	{
		$server_list = $this->ext_config->__get('mpv_server_list');

		$server = $server_list[array_rand($server_list)];

		$downloader = new \phpbb\file_downloader;
		$mpv_result = $downloader->get(
			$server['host'],
			$server['directory'],
			$server['file'] . '?titania-' . $download_location
		);

		if ($mpv_result === false)
		{
			$this->errors[] = $this->user->lang['MPV_TEST_FAILED'];
			return false;
		}
		else
		{
			$mpv_result = str_replace('<br />', "\n", $mpv_result);
			set_var($mpv_result, $mpv_result, 'string', true);
			$mpv_result = utf8_normalize_nfc($mpv_result);

			return $mpv_result;
		}
	}

	/**
	 * Run AutoMOD test.
	 *
	 * @param \phpbb\titania\entity\package $package
	 * @param string $phpbb_path Path to phpBB files we run the test on
	 * @param string $details Will hold the details of the mod
	 * @param string $results Will hold the results for output
	 * @param string $bbcode_results Will hold the results for storage
	 * @return bool true on success, false on failure
	 */
	public function run_automod_test($package, $phpbb_path, &$details, &$results, &$bbcode_results)
	{
		require($this->phpbb_root_path . 'includes/functions_transfer.' . $this->php_ext);
		require($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
		require($this->ext_root_path . 'includes/library/automod/acp_mods.' . $this->php_ext);
		require($this->ext_root_path . 'includes/library/automod/editor.' . $this->php_ext);
		require($this->ext_root_path . 'includes/library/automod/mod_parser.' . $this->php_ext);
		require($this->ext_root_path . 'includes/library/automod/functions_mods.' . $this->php_ext);

		$this->user->add_lang_ext('phpbb/titania', 'automod');

		// Find the main modx file
		$modx_root = $package->find_directory(array('files' => array('required' => 'install*.xml')));

		if ($modx_root === null)
		{
			$this->user->add_lang_ext('phpbb/titania', 'contributions');

			$this->errors[] = $this->user->lang['COULD_NOT_FIND_ROOT'];
			return false;
		}

		$modx_root = $package->get_temp_path() . '/' . $modx_root . '/';
		$modx_file = false;

		if (file_exists($modx_root . 'install.xml'))
		{
			$modx_file = $modx_root . 'install.xml';
		}
		else
		{
			$finder = new \Symfony\Component\Finder\Finder;
			$finder
				->name('install*.xml')
				->depth(0)
				->in($modx_root)
			;

			if ($finder->count())
			{
				foreach ($finder as $file)
				{
					$modx_file = $file->getPathname();
					break;
				}
			}
		}

		if (!$modx_file)
		{
			$this->user->add_lang_ext('phpbb/titania', 'contributions');

			$this->errors[] = $this->user->lang['COULD_NOT_FIND_ROOT'];
			return false;
		}

		// HAX
		global $phpbb_root_path;
		$phpbb_root_path = $phpbb_path;

		// The real stuff
		$acp_mods = new \acp_mods;
		$acp_mods->mods_dir = $this->ext_config->__get('contrib_temp_path');
		$acp_mods->mod_root = $modx_root;
		$editor = new \editor_direct;
		$details = $acp_mods->mod_details($modx_file, false);
		$actions = $acp_mods->mod_actions($modx_file);
		$installed = $acp_mods->process_edits($editor, $actions, $details, false, true, false);

		// Reverse HAX
		$phpbb_root_path = $this->phpbb_root_path;

		$this->template->set_filenames(array(
			'automod'			=> 'contributions/automod.html',
			'automod_bbcode'	=> 'contributions/automod_bbcode.html',
		));

		$this->template->assign_var('S_AUTOMOD_SUCCESS', $installed);

		$results = $this->template->assign_display('automod');
		$bbcode_results = $this->template->assign_display('automod_bbcode');

		return $installed;
	}
}
