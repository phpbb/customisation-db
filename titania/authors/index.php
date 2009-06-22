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
titania::load_object('author');
titania::$author = new titania_author();

if (!titania::$author->load($author))
{
	trigger_error('AUTHOR_NOT_FOUND');
}

// Check to see if the currently accessing user is the author
if (titania::$access_level == TITANIA_ACCESS_PUBLIC && phpbb::$user->data['user_id'] == $user_id)
{
	titania::$access_level = TITANIA_ACCESS_AUTHORS;
}

/**
* Menu Array
*
* 'filename' => array(
*	'title'		=> 'nav menu title',
*	'auth'		=> ($can_see_page) ? true : false, // Not required, always true if missing
* ),
*/
$pages = array(
	'details' => array(
		'title'		=> 'AUTHOR_DETAILS',
	),
	'contributions' => array(
		'title'		=> 'AUTHOR_CONTRIBUTIONS',
	),
);

// Display nav menu
foreach ($pages as $name => $data)
{
	// If they do not have authorization, skip.
	if (isset($data['auth']) && !$data['auth'])
	{
		continue;
	}

	phpbb::$template->assign_block_vars('nav_menu', array(
		'L_TITLE'		=> (isset(phpbb::$user->lang[$data['title']])) ? phpbb::$user->lang[$data['title']] : $data['title'],

		'U_TITLE'		=> titania::$author->get_url() . (($name != 'details') ? '/' . $name : ''),

		'S_SELECTED'	=> ($page == $name) ? true : false,
	));
}

// And now to load the appropriate page...
switch ($page)
{
	case 'details' :
	case 'contributions' :
		include(TITANIA_ROOT . 'authors/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'authors/details.' . PHP_EXT);
	break;
}