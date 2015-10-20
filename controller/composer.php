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

namespace phpbb\titania\controller;

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class composer
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\titania\controller\helper */
	protected $helper;

	/** @var string */
	protected $titania_root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\user $user
	 * @param helper $helper
	 */
	public function __construct(\phpbb\user $user, \phpbb\titania\controller\helper $helper, $titania_root_path)
	{
		$this->user = $user;
		$this->helper = $helper;
		$this->titania_root_path = $titania_root_path;
	}

	/**
	* Serve composer's files.
	*
	* @param string $filename	Filename to serve (without extension)
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function serve_file($filename)
	{
		if (strpos($filename, '..') !== false)
		{
			throw new http_exception(404, 'NO_PAGE_FOUND');
		}

		$filename = $this->titania_root_path . 'composer_packages/prod/' . $filename . '.json';

		try
		{
			return new BinaryFileResponse($filename, 200);
		}
		catch (\Exception $e)
		{
			throw new http_exception(404, 'NO_PAGE_FOUND');
		}
	}
}
