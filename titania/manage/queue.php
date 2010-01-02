<?php
/**
 *
 * @package titania
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

topics_overlord::display_forums_complete('author_support', titania::$author);

titania::page_header('AUTHOR_SUPPORT');

titania::page_footer(true, 'contributions/contribution_support.html');