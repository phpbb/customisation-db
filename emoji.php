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

class emoji
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
		return preg_replace_callback(
			'/[\xF0-\xF4][\x80-\xBF]{3}/',
			function ($matches)
			{
				$codepoint = utf8_ord($matches[0]) - 0x10000;
				$high_surrogate = 0xD800 + ($codepoint >> 10);
				$low_surrogate = 0xDC00 + ($codepoint & 0x3FF);

				return sprintf('\\u%04X\\u%04X', $high_surrogate, $low_surrogate);
			},
			$json
		);
	}

	/**
	 * Check whether a string contains emoji or another four-byte character
	 * unsupported by phpBB's MySQL utf8 schema.
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function contains($value)
	{
		$value = utf8_decode_ncr($value);
		$emoji_pattern = '/[' .
			'\x{00A9}\x{00AE}\x{200D}\x{203C}\x{2049}\x{20E3}\x{2122}\x{2139}' .
			'\x{2194}-\x{21FF}\x{2300}-\x{23FF}\x{24C2}\x{25AA}-\x{27BF}' .
			'\x{2B00}-\x{2BFF}\x{3030}\x{303D}\x{3297}\x{3299}\x{FE0E}\x{FE0F}' .
			'\x{10000}-\x{10FFFF}' .
		']/u';

		return (bool) preg_match($emoji_pattern, $value);
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
