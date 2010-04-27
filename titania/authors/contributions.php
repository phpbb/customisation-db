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

contribs_overlord::display_contribs('author', titania::$author->user_id);

titania::page_header(titania::$author->get_username_string('username') . ' - ' . phpbb::$user->lang['AUTHOR_CONTRIBS']);
titania::page_footer(true, 'authors/author_contributions.html');
