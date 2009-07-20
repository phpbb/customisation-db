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
 * Handles display the Uploadify uploader.
 *
 */
class titania_uploadify
{
	/**
	 * Creates a new uploadify flash uploader on the page.
	 *
	 * Include uploadify.html on any page you wish to add attachments.
	 *
	 *
	 * @param string $upload_script
	 * @param array $swf_options An array of options for the flash object. For a list of avialable options please see
	 * the Uploadify documentation page: http://www.uploadify.com/documentation/.
	 */
	public function __construct($upload_script = '', $swf_options = '')
	{
		// @todo This method will setup the Javascript correctly to embed the Flash object into the page. More about this later ;)
	}
}
