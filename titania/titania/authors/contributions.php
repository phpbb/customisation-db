<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

// Setup the sort tool to sort by contribution name ascending
$sort = contribs_overlord::build_sort();
$sort->set_url(titania::$author->get_url('contributions'));
$sort->set_defaults(false, 'c', 'a');

contribs_overlord::display_contribs('author', titania::$author->user_id, $sort);

phpbb::$template->assign_vars(array(
	'S_AUTHOR_LIST'		=> true,

	// Canonical URL
	'U_CANONICAL'		=> $sort->build_canonical(),
));

titania::page_header(titania::$author->get_username_string('username') . ' - ' . phpbb::$user->lang['AUTHOR_CONTRIBS']);
titania::page_footer(true, 'authors/author_contributions.html');
