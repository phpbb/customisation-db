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
	 * Checks the file for the array contents
	 * Make sure it has all the keys present in the newest version
	 *
	 * @param \phpbb\titania\entity\package $package
	 * @param string $reference_filepath The path to the files against I want to validate the uploaded package
	 * @return array Returns an array of error messages encountered
	 * @throws \Exception
	 */
	public function check_package($package, $reference_filepath)
	{
		$package->ensure_extracted();
		$path = $package->get_temp_path();

		$inputs = array(
			'command' => 'translation.php validate ja',
			'--phpbb-version' => '3.2',
			'--safe-mode' => true,
			'--display-notices' => true,
		);
		
		$commandTester = new CommandTester('translation.php validate ja'); // todo: add lang
		$commandTester->setInputs($inputs);
		return $commandTester->execute();

		//$app = new Cli();
		//$app->add(new ValidateCommand());
		//$app->run();
	}
}
