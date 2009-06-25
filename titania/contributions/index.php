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
$contrib = request_var('c', '');

// Add common lang
titania::add_lang('contributions');

// Load the contribution
titania::load_object(array('contribution', 'author'));
titania::$contrib = new titania_contribution();

if (!titania::$contrib->load($contrib))
{
	trigger_error('CONTRIB_NOT_FOUND');
}

// Put the author in titania::$author
titania::$author = titania::$contrib->author;

// Check to see if the currently accessing user is an author
if (titania::$access_level == TITANIA_ACCESS_PUBLIC && phpbb::$user->data['is_registered'] && !phpbb::$user->data['is_bot'])
{
	if (titania::$author->user_id == phpbb::$user->data['user_id'] || isset(titania::$contrib->coauthors[phpbb::$user->data['user_id']]))
	{
		titania::$access_level = TITANIA_ACCESS_AUTHORS;
	}
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
		'title'		=> 'CONTRIB_DETAILS',
		'url'		=> titania::$contrib->get_url(),
	),
	'faq' => array(
		'title'		=> 'CONTRIB_FAQ',
		'url'		=> titania::$contrib->get_url() . '/faq',
	),
	'support' => array(
		'title'		=> 'CONTRIB_SUPPORT',
		'url'		=> titania::$contrib->get_url() . '/support',
	),
);

// Display nav menu
titania::generate_nav($nav_ary, $page);

// And now to load the appropriate page...
switch ($page)
{
	case 'details' :
	case 'faq' :
	case 'support' :
		include(TITANIA_ROOT . 'contributions/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'contributions/details.' . PHP_EXT);
	break;
}