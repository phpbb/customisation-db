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

namespace phpbb\titania\attachment\files;


class factory extends \phpbb\files\factory
{
	/**
	 * @{inheritDoc}
	 */
	public function get($name)
	{
		// Use Titania's definition of the filespec class
		// in order to ensure that Titania's Plupload instance
		// is also used.
		if ($name == 'filespec')
		{
			$name = 'phpbb.titania.attachment.files.filespec';
		}
		return parent::get($name);
	}
}
