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
$page = basename(request_var('page', ''));

// Add common lang
titania::add_lang('manage');

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
	'queue' => array(
		'title'		=> 'VALIDATION_QUEUE',
		'url'		=> titania_url::build_url('manage/queue'),
		'auth'		=> (sizeof(titania_types::find_authed('view'))) ? true : false,
	),
	'queue_discussion' => array(
		'title'		=> 'QUEUE_DISCUSSION',
		'url'		=> titania_url::build_url('manage/queue_discussion'),
		'auth'		=> (sizeof(titania_types::find_authed('view'))) ? true : false,
	),
);


$page = (isset($nav_ary[$page])) ? $page : 'queue';

// Display nav menu
titania::generate_nav($nav_ary, $page);

// And now to load the appropriate page...
switch ($page)
{
	case 'queue' :
	case 'queue_discussion' :
		include(TITANIA_ROOT . 'manage/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'manage/queue.' . PHP_EXT);
		exit;
	break;
}