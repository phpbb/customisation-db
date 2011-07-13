<?php
/**
*
* @package Titania
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

titania::$author->get_rating();
//titania::$author->assign_details();

// Canonical URL
phpbb::$template->assign_var('U_CANONICAL', titania::$author->get_url());

titania::page_header(titania::$author->get_username_string('username') . ' - ' . phpbb::$user->lang['AUTHOR_DETAILS']);
titania::page_footer(true, 'authors/author_details.html');