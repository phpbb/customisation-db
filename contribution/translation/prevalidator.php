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

namespace phpbb\titania\contribution\translation;

use phpbb\extension\exception;
use Phpbb\TranslationValidator\Cli;
use Phpbb\TranslationValidator\Command\ValidateCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Translation prevalidator
 *
 * @author Vojtěch Vondra
 */
class prevalidator
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\contribution\prevalidator_helper */
	protected $helper;

	const NOT_REQUIRED = 0;
	const REQUIRED = 1;
	const REQUIRED_EMPTY = 2;
	const REQUIRED_DEFAULT = 3;

	const BRITISH_ENGLISH = 'british_english_3_2_5';
	const EN = 'en';

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\contribution\prevalidator_helper $helper
	 */
	public function __construct(\phpbb\user $user, \phpbb\titania\contribution\prevalidator_helper $helper)
	{
		$this->user = $user;
		$this->helper = $helper;
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
	 * Run the phpBB Translation Validator
	 *
	 * @param \phpbb\titania\entity\package $package
	 * @param string $origin_iso
	 * @param string $phpbb_version
	 * @return int Returns an array of error messages encountered
	 */
	public function check_package($package, $origin_iso, $phpbb_version)
	{
		$results = '';

		if ($origin_iso == self::EN)
		{
			// We can't validate "en", because it would be trying to check against itself and
			// fail because we can't unpack both at the same time (and it would serve no purpose).
			$results = $this->user->lang('TRANSLATION_EN_SKIP');
		}

		else
		{
			// We don't need to clean up (delete) the temporary files here because that is
			// handled at revision.php:752
			$package->ensure_extracted();
			$path = $package->get_temp_path();

			// Rename the extracted directory to be the ISO code.
			$finder = new Finder();
			$iterator = $finder->directories()->in($path)->depth('== 0');

			if ($iterator->count() == 1)
			{
				$iterator = $finder->getIterator();
				$iterator->rewind();

				// We know there's only one result
				$root_extracted = $iterator->current();
			}

			// Rename the directory to use the iso code
			$new_directory_name = str_replace($root_extracted->getFilename(), $origin_iso, $root_extracted->getPathname());
			$file_system = new Filesystem();
			$file_system->rename($root_extracted->getPathname(), $new_directory_name);

			// Get the British English language in there too
			$en_path = $this->get_helper()->get_root_path() . 'includes/language_packages/' . self::BRITISH_ENGLISH . '.zip';
			$zip = new \ZipArchive();
			$result = $zip->open($en_path);

			if ($result)
			{
				// Unzip the revision to a temporary folder
				$zip->extractTo($path);
				$zip->close();

				// Change to "en"
				$file_system->rename(sprintf('%s/%s', $path, self::BRITISH_ENGLISH), sprintf('%s/%s', $path, self::EN));
			}

			// Before running the translation validator, check that an expected path exists. If it doesn't, it could be
			// because the user has typed the incorrect language ISO code. Tell the user, and crash out.
			if (!$file_system->exists(sprintf('%s/%s/language/%s/common.php', $path, $origin_iso, $origin_iso)))
			{
				$package->cleanup();
				throw new exception($this->user->lang('TRANSLATION_ISO_MISMATCH'));
			}

			// Parameters for the validation script
			$inputs = array(
				// Arguments
				'command' => 'validate',
				'origin-iso' => $origin_iso,

				// Options
				'--phpbb-version' => $phpbb_version,
				'--package-dir' => $path,
				'--safe-mode' => true,
				'--display-notices' => true,
			);

			// Set up an instance of the translation validation script
			// https://github.com/phpbb/phpbb-translation-validator
			$app = new Cli();
			$app->add(new ValidateCommand());
			$translation = $app->find('validate');

			$commandTester = new CommandTester($translation);
			$commandTester->execute($inputs);

			// Return the output of the translation validation script
			$results = $commandTester->getDisplay();
		}

		return $results;
	}
}
