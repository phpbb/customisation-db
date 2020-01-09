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

	/** @var \phpbb\language\language */
	protected $language;

	const NOT_REQUIRED = 0;
	const REQUIRED = 1;
	const REQUIRED_EMPTY = 2;
	const REQUIRED_DEFAULT = 3;

	const BRITISH_ENGLISH = 'british_english_';
	const LANGUAGE_PACKAGES = 'includes/language_packages/';
	const EN = 'en';

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param \phpbb\titania\contribution\prevalidator_helper $helper
	 * @param \phpbb\language\language $language
	 */
	public function __construct(\phpbb\user $user, \phpbb\titania\contribution\prevalidator_helper $helper, \phpbb\language\language $language)
	{
		$this->user = $user;
		$this->helper = $helper;
		$this->language = $language;
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
	 * Get the latest British English pack
	 * @return array Return the path and version to the pre-supplied British English language pack
	 */
	private function get_latest_english_pack($selected_branch = null)
	{
		/** @var Finder $finder */
		$finder = new Finder();

		$finder->files()
			->in($this->get_helper()->get_root_path() . self::LANGUAGE_PACKAGES)
			->name(self::BRITISH_ENGLISH . '*');

		if (iterator_count($finder) > 0)
		{
			$latest = ['name' => '', 'x' => 0, 'y' => 0, 'z' => 0];

			foreach ($finder as $file)
			{
				// Extract the version number
				preg_match_all('/' . self::BRITISH_ENGLISH . '(\d+)_(\d+)_(\d+)\.zip/', $file->getFilename(), $result);

				if ($result[0])
				{
					// Save the path and the version
					$pack = [
						'name' => $file->getPathname(), // file path
						'x' => $result[1][0], // major version (eg. 3)
						'y' => $result[2][0], // minor version (eg. 2)
						'z' => $result[3][0], // revision version (eg. 5)
					];

					// Check if it's a higher number
					$new_major = ($pack['x'] > $latest['x']);
					$new_minor = ($pack['x'] === $latest['x'] && $pack['y'] > $latest['y']);
					$new_revision = ($pack['x'] === $latest['x'] && $pack['y'] === $latest['y'] && $pack['z'] > $latest['z']);

					// Continue if no phpBB version offered, otherwise perform a comparison to make sure we are matching
					// language packs of the same minor version.
					$matches_branch = ($selected_branch === null) ? true : ($selected_branch[0] == $pack['x'] && $selected_branch[1] == $pack['y']);

					if ($matches_branch && ($new_major || $new_minor || $new_revision))
					{
						// This is the new latest version
						$latest = $pack;
					}
				}
			}
		}

		else
		{
			// Could not locate the British English language pack
			throw new \phpbb\extension\exception($this->language->lang('TRANSLATION_EN_PACK_NOT_FOUND'));
		}

		return $latest;
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
		if ($origin_iso === self::EN)
		{
			// We can't validate "en", because it would be trying to check against itself and
			// fail because we can't unpack both at the same time (and it would serve no purpose).
			$results = $this->language->lang('TRANSLATION_EN_SKIP');
		}

		else
		{
			try
			{
				// Language pack directory names use an underscore
				$sanitised_iso = str_replace('-', '_', $origin_iso);

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

				else
				{
					throw new \phpbb\extension\exception($this->language->lang('TRANSLATION_DIRECTORY_MISMATCH'));
				}

				// Rename the directory to use the iso code
				$new_directory_name = str_replace($root_extracted->getFilename(), $sanitised_iso, $root_extracted->getPathname());
				$file_system = new Filesystem();
				$file_system->rename($root_extracted->getPathname(), $new_directory_name);

				// Get the British English language in there too
				$selected_branch = null;
				
				if (preg_match('/(\d+)\.(\d+)/', $phpbb_version, $version_match))
				{
					// Pass the selected phpBB version through so we can limit which
					// base language pack we compare against.
					$selected_branch = [$version_match[1], $version_match[2]];
				}

				$english_pack = $this->get_latest_english_pack($selected_branch);
				$zip = new \ZipArchive();
				$result = $zip->open($english_pack['name']);

				if ($result)
				{
					// Unzip the revision to a temporary folder
					$zip->extractTo($path);
					$zip->close();

					// Change to "en"
					$replace_name = sprintf('%s%d_%d_%d', self::BRITISH_ENGLISH, $english_pack['x'], $english_pack['y'], $english_pack['z']);
					$file_system->rename(sprintf('%s/%s', $path, $replace_name), sprintf('%s/%s', $path, self::EN));
				}

				// Before running the translation validator, check that an expected path exists. If it doesn't, it could be
				// because the user has typed the incorrect language ISO code. Tell the user, and crash out.
				if (!$file_system->exists(sprintf('%s/%s/language/%s/common.php', $path, $sanitised_iso, $sanitised_iso)))
				{
					throw new \phpbb\extension\exception($this->language->lang('TRANSLATION_ISO_MISMATCH'));
				}

				// Parameters for the validation script
				$inputs = array(
					// Arguments
					'command' => 'validate',
					'origin-iso' => $sanitised_iso,

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

			catch (\phpbb\extension\exception $e)
			{
				// Clean up and send the error message to the next catch block
				$package->cleanup();
				throw new \phpbb\extension\exception($e->getMessage());
			}
		}

		return $results;
	}
}
