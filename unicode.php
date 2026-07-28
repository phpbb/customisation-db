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

namespace phpbb\titania;

class unicode
{
	/**
	 * Escape four-byte UTF-8 characters as JSON surrogate pairs.
	 *
	 * This leaves the structure and formatting of the JSON document intact.
	 *
	 * @param string $json
	 * @return string
	 */
	public static function escape_json($json)
	{
		$escaped_json = preg_replace_callback(
			'/[\x{10000}-\x{10FFFF}]/u',
			function ($matches)
			{
				$codepoint = utf8_ord($matches[0]) - 0x10000;
				$high_surrogate = 0xD800 + ($codepoint >> 10);
				$low_surrogate = 0xDC00 + ($codepoint & 0x3FF);

				return sprintf('\\u%04X\\u%04X', $high_surrogate, $low_surrogate);
			},
			$json
		);

		if ($escaped_json === null)
		{
			throw new \UnexpectedValueException('JSON contains invalid UTF-8.');
		}

		return $escaped_json;
	}

	/**
	 * Check whether a string contains a four-byte character unsupported by
	 * phpBB's MySQL utf8 schema.
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function contains_unsupported($value)
	{
		$value = utf8_decode_ncr($value);

		return (bool) preg_match('/[\x{10000}-\x{10FFFF}]/u', $value);
	}

	/**
	 * Remove four-byte characters unsupported by phpBB's MySQL utf8 schema.
	 *
	 * @param string $value
	 * @return string
	 */
	public static function strip_unsupported($value)
	{
		$value = utf8_decode_ncr($value);

		return preg_replace(
			'/[\x{10000}-\x{10FFFF}]/u',
			' ',
			$value
		);
	}
}
