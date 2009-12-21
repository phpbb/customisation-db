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
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', '../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);

// Setup some vars
$page = request_var('page', 'details');
$author = request_var('u', '');

// Add common lang
titania::add_lang('authors');

// Load the contribution
titania::$author = new titania_author();

if (!titania::$author->load($author))
{
	trigger_error('AUTHOR_NOT_FOUND');
}

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
		'title'		=> 'AUTHOR_CONTRIBUTIONS',
		'url'		=> titania::$author->get_url('contributions'),
	),
	'support' => array(
		'title'		=> 'AUTHOR_SUPPORT',
		'url'		=> titania::$author->get_url('support'),
		'auth'		=> (phpbb::$user->data['user_id'] == titania::$author->user_id) ? true : false,
	),
	'submit' => array(
		'title'		=> 'CREATE_CONTRIBUTION',
		'url'		=> titania_url::build_url('contributions/create'),
		'auth'		=> (titania::$author->user_id == phpbb::$user->data['user_id']) ? true : false,
	),
);

// Display nav menu
titania::generate_nav($nav_ary, $page);

// And now to load the appropriate page...
switch ($page)
{
	case 'details' :
	case 'contributions' :
	case 'manage':
	case 'support' :
		include(TITANIA_ROOT . 'authors/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'authors/details.' . PHP_EXT);
	break;
}