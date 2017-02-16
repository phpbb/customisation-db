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

use Phpbb\Epv\Output\HtmlOutput;
use Phpbb\Epv\Output\Output;
use Phpbb\Epv\Tests\TestRunner;

class prevalidator
{
	/**
	 * Run prevalidator.
	 *
	 * @param string $directory		Directory where extracted revision is located
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function run_epv($directory)
	{
		$int_output = new HtmlOutput(HtmlOutput::TYPE_BBCODE);
		$output = new Output($int_output, false);
		$runner = new TestRunner($output, $directory, false, true);
		$runner->runTests();

		// Write a empty line
		$output->writeln('');

		$found_msg = implode(', ', array(
			'Fatal: ' . $output->getMessageCount(Output::FATAL),
			'Error: ' . $output->getMessageCount(Output::ERROR),
			'Warning: ' . $output->getMessageCount(Output::WARNING),
			'Notice: ' . $output->getMessageCount(Output::NOTICE),
		));

		if ($output->getMessageCount(Output::FATAL) > 0 || $output->getMessageCount(Output::ERROR) > 0 || $output->getMessageCount(Output::WARNING) > 0)
		{
			$output->writeln('Validation: [b][color=#A91F1F]FAILED[/color][/b]');
		}
		else
		{
			$output->writeln('Validation: [b][color=#00BF40]PASSED[/color][/b]');
		}

		$output->writeln($found_msg);
		$output->writeln('');

		$output->writeln('Test results for extension:');
		$messages = $output->getMessages();

		if (!empty($messages))
		{
			$output->writeln('[list]');
			foreach ($messages as $msg)
			{
				$output->writeln('[*]' . (string) $msg);
			}
			$output->writeln('[/list]');
		}
		else
		{
			$output->writeln('[color=#00BF40]No issues found[/color]');
		}

		return $this->clear_formatting($int_output->getBuffer());
	}

	/**
	 * Remove EPV CLI style formatting from the message
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	protected function clear_formatting($text)
	{
		return preg_replace('/<\/?(success|notice|noticebg|warning|error|fatal|info)b?>/', '', $text);
	}
}
