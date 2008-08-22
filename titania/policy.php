<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
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
 * Policy class that specifies which operations
 * need to be performed in different cases
 *
 * @package Titania
 */
class policy implements titania_policy
{
	/**
	* Downloads
	*/
	public static function download_not_found($download)
	{
		$download->trigger_not_found();
	}

	public static function download_access_denied($download)
	{
		// Plausible deniability
		// We do not let anybody know the download exists at all.
		$download->trigger_not_found();

		// Alternative solution: $download->trigger_forbidden();
	}
}