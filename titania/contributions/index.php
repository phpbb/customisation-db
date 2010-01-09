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
$page = basename(request_var('page', 'details'));

// Add common lang
titania::add_lang('contributions');

// And now to load the appropriate page...
switch ($page)
{
	case 'details' :
	case 'faq' :
	case 'support' :
	case 'create' :
	case 'manage' :
		include(TITANIA_ROOT . 'contributions/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'contributions/details.' . PHP_EXT);
	break;
}

/**
* Load contribution
*
* Call this AFTER you have loaded the main object (like the FAQ item if requested for example)
*
* @param mixed $contrib contrib_id (always send if you have loaded an item for this contrib!)
*/
function load_contrib($contrib_id = false)
{
	$contrib = request_var('c', '');

	// Load the contribution
	titania::$contrib = new titania_contribution();

	if (!titania::$contrib->load($contrib))
	{
		trigger_error('CONTRIB_NOT_FOUND');
	}

	// Make sure the contrib requested is the same as the contrib loaded
	if ($contrib_id !== false && $contrib_id != titania::$contrib->contrib_id)
	{
		// Mismatch
		// @todo redirect them to the new page
		trigger_error('CONTRIB_NOT_FOUND');
	}

	// Put the author in titania::$author
	titania::$author = titania::$contrib->author;

	// Check to see if the currently accessing user is an author
	if (titania::$access_level == TITANIA_ACCESS_PUBLIC && phpbb::$user->data['is_registered'] && !phpbb::$user->data['is_bot'])
	{
		if (titania::$contrib->is_author || titania::$contrib->is_active_coauthor)
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
			'url'		=> titania::$contrib->get_url('faq'),
		),
		'support' => array(
			'title'		=> 'CONTRIB_SUPPORT',
			'url'		=> titania::$contrib->get_url('support'),
		),
		'manage' => array(
			'title'		=> 'CONTRIB_MANAGE',
			'url'		=> titania::$contrib->get_url('manage'),
			'auth'		=> (titania::$contrib->is_author || titania::$contrib->is_active_coauthor || phpbb::$auth->acl_get('titania_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
		),
	);

	// Display nav menu
	$page = request_var('page', 'details');
	titania::generate_nav($nav_ary, $page);
}