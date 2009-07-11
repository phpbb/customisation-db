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

titania::$author->load();

titania::$author->get_rating();
titania::$author->assign_details();

titania::page_header('AUTHOR_DETAILS');
titania::page_footer(true, 'authors/author_details.html');