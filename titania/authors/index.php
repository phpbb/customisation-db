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
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', '../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);

// Add common lang
titania::add_lang('authors');

// Load the author
$author = utf8_normalize_nfc(request_var('u', '', true));
titania::$author = new titania_author();

if (!titania::$author->load($author))
{
	trigger_error('AUTHOR_NOT_FOUND');
}
titania::$author->assign_details();

// Check to see if the currently accessing user is the author
if (titania::$access_level == TITANIA_ACCESS_PUBLIC && phpbb::$user->data['user_id'] == titania::$author->user_id)
{
	titania::$access_level = TITANIA_ACCESS_AUTHORS;
}

/**
* Menu Array
*
* 'filename' => array(
*	'title'		=> 'nav menu title',
* 	'url'		=> $page_url,
*	'auth'		=> ($can_see_page) ? true : false, // Not required, always true if missing
* ),
*/
$nav_ary = array(
	'details' => array(
		'title'		=> 'AUTHOR_DETAILS',
		'url'		=> titania::$author->get_url(),
	),
	'contributions' => array(
		'title'		=> 'AUTHOR_CONTRIBS',
		'url'		=> titania::$author->get_url('contributions'),
	),
	'support' => array(
		'title'		=> 'AUTHOR_SUPPORT',
		'url'		=> titania::$author->get_url('support'),
		'auth'		=> (phpbb::$user->data['user_id'] == titania::$author->user_id && sizeof(titania::$cache->get_author_contribs(titania::$author->user_id))) ? true : false,
	),
	'create' => array(
		'title'		=> 'CREATE_CONTRIBUTION',
		'url'		=> titania::$author->get_url('create'),
		'auth'		=> (titania::$author->user_id == phpbb::$user->data['user_id'] && phpbb::$auth->acl_get('u_titania_contrib_submit')) ? true : false,
	),
	'manage' => array(
		'title'		=> 'MANAGE_AUTHOR',
		'url'		=> titania::$author->get_url('manage'),
		'auth'		=> (titania::$author->user_id != phpbb::$user->data['user_id'] && !phpbb::$auth->acl_get('u_titania_mod_author_mod')) ? false : true,
	),
);

// Display nav menu
$page = request_var('page', '');
titania::generate_nav($nav_ary, $page, 'details');

// Generate the main breadcrumbs
titania::generate_breadcrumbs(array(
	titania::$author->username	=> titania::$author->get_url(),
));
if ($page)
{
	titania::generate_breadcrumbs(array(
		$nav_ary[$page]['title']	=> $nav_ary[$page]['url'],
	));
}

// And now to load the appropriate page...
switch ($page)
{
	case 'contributions' :
	case 'manage':
	case 'support' :
	case 'create' :
		include(TITANIA_ROOT . 'authors/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'authors/details.' . PHP_EXT);
	break;
}