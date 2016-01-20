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

namespace phpbb\titania\console\command\composer;

use phpbb\titania\manage\tool\composer\rebuild_repo as rebuild_tool;
use phpbb\user;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class rebuild_repo extends \phpbb\console\command\command
{
	/** @var rebuild_tool */
	protected $tool;

	/**
	 * Constructor
	 *
	 * @param user $user
	 * @param rebuild_tool $tool
	 */
	public function __construct(user $user, rebuild_tool $tool)
	{
		$user->add_lang_ext('phpbb/titania', array('console', 'manage_tools'));
		parent::__construct($user);

		$this->tool = $tool;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('titania:composer:rebuild_repo')
			->setDescription($this->user->lang('CLI_DESCRIPTION_REBUILD_COMPOSER'))
			->addOption(
				'from-file',
				null,
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_REBUILD_COMPOSER_FROM_FILE')
			)
			->addOption(
				'force',
				null,
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_REBUILD_COMPOSER_FORCE')
			)
		;
	}

	/**
	 * Executes the command titania:composer:rebuild_packages.
	 *
	 * Rebuilds the Composer repository.
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$progress = $this->getHelper('progress');
		$progress->start($output, $this->tool->get_total());

		$result = $this->tool
			->set_start(0)
			->set_limit(false)
			->run(
				$input->getOption('from-file'),
				$input->getOption('force'),
				$progress
			)
		;
		$progress->finish();
		$output->writeln('');
		$output->writeln('<info>' . $this->user->lang($result['message']) . '</info>');
	}
}
