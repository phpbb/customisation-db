<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2009 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * @ignore
 */
if (!defined('IN_TITANIA'))
{
	exit;
}

/**
 * phpBB class that will be used in place of globalising these variables.
 */
class phpbb
{
	public static $auth;
	public static $cache;
	public static $config;
	public static $db;
	public static $template;
	public static $user;

	/**
	 * Static Constructor.
	 */
	public static function initialise()
	{
		global $auth, $config, $db, $template, $user, $cache;

		self::$auth		= &$auth;
		self::$config	= &$config;
		self::$db		= &$db;
		self::$template	= &$template;
		self::$user		= &$user;
		self::$cache	= &$cache;
	}
}
