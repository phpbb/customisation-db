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

phpbb::$user->add_lang('viewforum');
titania::add_lang('posting');

titania_display_forums('author_support', titania::$author);

phpbb::$template->assign_vars(array(
	'S_TOPIC_LIST'			=> true,
));

titania::page_header('AUTHOR_SUPPORT');

titania::page_footer(true, 'contributions/contribution_support.html');