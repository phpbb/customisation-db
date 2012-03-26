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
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', '../');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include(TITANIA_ROOT . 'common.' . PHP_EXT);

// Setup some vars
$page = basename(request_var('page', ''));

// Add common lang
titania::add_lang('manage');

// Count the number of open attention items
$sql = 'SELECT COUNT(attention_id) AS cnt FROM ' . TITANIA_ATTENTION_TABLE . '
	WHERE attention_close_time = 0';
phpbb::$db->sql_query($sql);
$attention_count = phpbb::$db->sql_fetchfield('cnt');
phpbb::$db->sql_freeresult();

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
	'attention' => array(
		'title'		=> 'ATTENTION',
		'url'		=> titania_url::build_url('manage/attention'),
		'auth'		=> (!phpbb::$auth->acl_gets('u_titania_mod_author_mod', 'u_titania_mod_contrib_mod', 'u_titania_mod_faq_mod', 'u_titania_mod_post_mod') && !sizeof(titania_types::find_authed('moderate'))) ? false : true,
		'count'		=> $attention_count,
	),
	'queue' => array(
		'title'		=> 'VALIDATION_QUEUE',
		'url'		=> titania_url::build_url('manage/queue'),
		'auth'		=> (sizeof(titania_types::find_authed('view')) && titania::$config->use_queue) ? true : false,
	),
	'queue_discussion' => array(
		'title'		=> 'QUEUE_DISCUSSION',
		'url'		=> titania_url::build_url('manage/queue_discussion'),
		'auth'		=> (sizeof(titania_types::find_authed('queue_discussion')) && titania::$config->use_queue) ? true : false,
	),
	'administration' => array(
		'title'		=> 'ADMINISTRATION',
		'url'		=> titania_url::build_url('manage/administration'),
		'auth'		=> (phpbb::$auth->acl_get('u_titania_admin')) ? true : false,
		'match'		=> array('categories'),
	),
	'categories' => array(
		'title'		=> 'MANAGE_CATEGORIES',
		'url'		=> titania_url::build_url('manage/categories'),
		'auth'		=> (phpbb::$auth->acl_get('u_titania_admin')) ? true : false,
		'display'	=> false,
	),
);

// Display nav menu
titania::generate_nav($nav_ary, $page, 'attention');

// Generate the main breadcrumbs
titania::generate_breadcrumbs(array(
	phpbb::$user->lang['MANAGE']	=> titania_url::build_url('manage'),
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
	case 'queue' :
	case 'queue_discussion' :
	case 'attention' :
	case 'administration' :
	case 'categories' :
		include(TITANIA_ROOT . 'manage/' . $page . '.' . PHP_EXT);
	break;

	default :
		include(TITANIA_ROOT . 'manage/queue.' . PHP_EXT);
		exit;
	break;
}
