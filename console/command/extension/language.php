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

namespace phpbb\titania\console\command\extension;

use Chumper\Zipper\Zipper;
use phpbb\db\driver\driver_interface as db;
use phpbb\user;
use phpbb\language\language as phpbb_language;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class language
 *
 * A script that will package British English from the latest phpBB version 
 * battye was here in 2019
 *
 * @package phpbb\titania\console\command\extension
 */
class language extends \phpbb\console\command\command
{
	// Execution can be run like these examples:
	// 1) Name only (referencing Titania): php bin/phpbbcli.php titania:extension:language phpBB-3.2.7.zip --name
	// 2) Full path: php bin/phpbbcli.php titania:extension:language /var/www/phpBB/ext/phpbb/titania/includes/phpbb_packages/phpBB-3.2.7.zip

	const COMMAND_NAME = 'titania:extension:language';
	const COMMAND_TMP_DIRECTORY = 'ext/phpbb/titania/files/contrib_temp/tmp';
	const COMMAND_LANGUAGE_DIRECTORY = 'ext/phpbb/titania/includes/language_packages';

	/** @var phpbb_language */
	protected $language;

	/** @var db */
	protected $db;

	/** @var $root_path */
	protected $root_path;

	/** @var OutputInterface */
	protected $output;

	/** @var InputInterface */
	protected $input;

	/** @var string */
	protected $tmp_folder;

	/** @var string */
	protected $language_folder;

	/**
	 * Constructor
	 *
	 * @param user $user
	 * @param phpbb_language $language
	 * @param db $db
	 * @param string $root_path
	 * @param string $php_ext
	 */
	public function __construct(user $user, phpbb_language $language, $root_path, $php_ext)
	{
		if (!defined('TITANIA_CONTRIBS_TABLE'))
		{
			include($root_path . 'ext/phpbb/titania/common.' . $php_ext);
		}

		// Set up the injected properties
		$this->language = $language;
		$this->root_path = $root_path;

		// Temporary folder
		$this->tmp_folder = $this->root_path . self::COMMAND_TMP_DIRECTORY;
		$this->language_folder = $this->root_path . self::COMMAND_LANGUAGE_DIRECTORY;

		$language_files = ['console'];
		$this->language->add_lang($language_files, 'phpbb/titania');

		parent::__construct($user);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		// Set up the arguments and options
		$this->setName(self::COMMAND_NAME)
			->setDescription($this->language->lang('CLI_DESCRIPTION_EXTENSION_LANGUAGE'))
			->addArgument('phpbb', InputArgument::REQUIRED, $this->language->lang('CLI_EXTENSION_LANGUAGE_PHPBB'))
			->addOption('name', 'name', InputOption::VALUE_NONE, $this->language->lang('CLI_EXTENSION_LANGUAGE_PHPBB_NAME'));
	}

	/**
	 * Execute the script
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int|null|void
	 * @throws \Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Store the IO object for use later
		$this->input = $input;
		$this->output = $output;

		// Show quick explanation
		$this->output->writeln($this->language->lang('CLI_DESCRIPTION_EXTENSION_LANGUAGE_EXPLAIN'));

		/** @var string $zip_path */
		$zip_path = $input->getArgument('phpbb');

		/** @var bool $name_only */
		$name_only = ($input->getOption('name') !== false);

		if ($name_only)
		{
			// If the user has specified it's just name only (eg. "phpBB-3.2.7.zip") then we'll assume the file is in Titania
			// Otherwise the assumption is the user has provided the full path to the zip.
			$zip_path = sprintf($this->root_path . 'ext/phpbb/titania/includes/phpbb_packages/%s', $zip_path);
		}

		// Create the package
		$this->package($zip_path);
	}

	/**
	 * Get the version number from the phpBB package
	 * @param $zip_path
	 * @return array
	 * @throws \Exception
	 */
	private function extract_version_number($zip_path)
	{
		// We're expecting a format like "phpBB-3.2.7.zip" somewhere in the file path, so we can extract the version
		// If we don't get that format, we'll quit the process
		$file = new \SplFileInfo($zip_path);
		$versions = explode('.', str_replace('phpBB-', '', $file->getBasename('.zip')));

		if (count($versions) !== 3)
		{
			// We need to have 3 parts to the version
			throw new \Exception($this->language->lang('CLI_EXTENSION_LANGUAGE_FILE_WRONG_FORMAT'));
		}

		return [
			'x' => $versions[0],
			'y' => $versions[1],
			'z' => $versions[2],
		];
	}

	/**
	 * Package British English
	 * @param string $zip_path
	 * @return void
	 * @throws \Exception
	 */
	private function package($zip_path)
	{
		$versions = $this->extract_version_number($zip_path);
		$save_version = sprintf('british_english_%d_%d_%d', $versions['x'], $versions['y'], $versions['z']);
		$save_name = sprintf('%s/%s.zip', $this->language_folder, $save_version);

		if (file_exists($save_name))
		{
			// The language pack already exists - quit.
			throw new \Exception($this->language->lang('CLI_EXTENSION_LANGUAGE_PACK_EXISTS', $save_name));
		}

		else if (!file_exists($zip_path))
		{
			// The phpBB package could not be found - quit.
			throw new \Exception($this->language->lang('CLI_EXTENSION_LANGUAGE_FILE_NOT_FOUND', $zip_path));
		}

		else
		{
			// These are the language directories (and license) we want to extract from phpBB in order to form British English
			$language_directories = [
				'docs/LICENSE.txt',
				'ext/phpbb/viglink/language/en',
				'language/en',
				'styles/prosilver/theme/en',
			];

			// Create our Zipper instance
			$zip = new Zipper();

			// Extract the relevant folders from the main phpBB3 directory into an appropriate temporary directory
			$zip->make($zip_path)
				->folder('phpBB3')
				->extractTo($this->tmp_folder, $language_directories, Zipper::WHITELIST);

			// Move the license file
			$system = new Filesystem();
			$system->copy($this->tmp_folder . '/docs/LICENSE.txt', $this->tmp_folder . '/language/en/LICENSE');
			$system->remove($this->tmp_folder . '/docs');

			// Create the new zip file with our British English language pack
			$zip->make($save_name)
				->folder($save_version)
				->add($this->tmp_folder)
				->close();

			// This deletes the entire temporary folder
			$system->remove($this->tmp_folder);

			// Script has completed
			$this->output->writeln($this->language->lang('CLI_EXTENSION_LANGUAGE_PACK_GENERATED', $save_name));
		}
	}
}
