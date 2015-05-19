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

namespace phpbb\titania\contribution\extension;

use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Output\HtmlOutput;
use Phpbb\Epv\Tests\TestRunner;

class prevalidator
{
	/**
	 * Run prevalidator.
	 *
	 * @param string $directory		Directory where extracted revision is located
	 * @return string
	 */
	public function run_epv($directory)
	{
		$int_output = new HtmlOutput(HtmlOutput::TYPE_BBCODE);
		$output = new Output($int_output, false);
		$runner = new TestRunner($output, $directory, false, true);
		$runner->runTests();

		// Write a empty line
		$output->writeLn('');

		$found_msg = ' ';
		$found_msg .= 'Fatal: ' . $output->getMessageCount(Output::FATAL);
		$found_msg .= ', Error: ' . $output->getMessageCount(Output::ERROR);
		$found_msg .= ', Warning: ' . $output->getMessageCount(Output::WARNING);
		$found_msg .= ', Notice: ' . $output->getMessageCount(Output::NOTICE);
		$found_msg .= ' ';

		if ($output->getMessageCount(Output::FATAL) > 0 || $output->getMessageCount(Output::ERROR) > 0 || $output->getMessageCount(Output::WARNING) > 0)
		{
			$output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
			$output->writeln('<fatal> Validation: [b][color=#A91F1F]FAILED[/color][/b]' . str_repeat(' ', strlen($found_msg) - 19) . '</fatal>');
			$output->writeln('<fatal>' . $found_msg . '</fatal>');
			$output->writeln('<fatal>' . str_repeat(' ', strlen($found_msg)) . '</fatal>');
			$output->writeln('');
		}
		else
		{
			$output->writeln('<success>PASSED: ' . $found_msg . '</success>');
		}

		$output->writeln("<info>Test results for extension:</info>");
		$messages = $output->getMessages();

		if (!empty($messages))
		{
			$output->writeln('[list]');
		}
		foreach ($messages as $msg)
		{
			$output->writeln('[*]' . (string) $msg);
		}
		if (!empty($messages))
		{
			$output->writeln('[/list]');
		}
		else
		{
			$output->writeln("<success>[color=#00BF40]No issues found[/c] </success>");
		}

		return $int_output->getBuffer();
	}
}
