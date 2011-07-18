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
titania::add_lang('contributions');

// Go to the queue discussion for this contribution item (saves us from having to figure out the URL to the topic every time we generate it)
if ($page == 'queue_discussion')
{
	load_contrib();

	$sql = 'SELECT * FROM ' . TITANIA_TOPICS_TABLE . '
		WHERE topic_type = ' . TITANIA_QUEUE_DISCUSSION . '
			AND parent_id = ' . titania::$contrib->contrib_id;
	$result = phpbb::$db->sql_query($sql);
	$row = phpbb::$db->sql_fetchrow($result);
	phpbb::$db->sql_freeresult($result);

	if ($row)
	{
		$topic = new titania_topic;
		$topic->__set_array($row);

		redirect($topic->get_url());
	}

	trigger_error('NO_QUEUE_DISCUSSION_TOPIC');
}

// And now to load the appropriate page...
switch ($page)
{
	case 'faq' :
	case 'support' :
	case 'manage' :
	case 'revision' :
	case 'revision_edit' :
		include(TITANIA_ROOT . 'contributions/' . $page . '.' . PHP_EXT);
	break;

	case 'report' :
		include(TITANIA_ROOT . 'contributions/details.' . PHP_EXT);
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
	$contrib = (request_var('id', 0)) ? request_var('id', 0) : utf8_normalize_nfc(request_var('c', '', true));
	$type = request_var('type', '');

	// Load the contribution
	titania::$contrib = new titania_contribution();

	if (!titania::$contrib->load($contrib))
	{
		trigger_error('CONTRIB_NOT_FOUND');
	}

	// Make sure the contrib requested is the same as the contrib loaded
	if (($contrib_id !== false && $contrib_id != titania::$contrib->contrib_id) || $type != titania_types::$types[titania::$contrib->contrib_type]->url)
	{
		// Mismatch, redirect
		redirect(titania::$contrib->get_url(basename(request_var('page', 'details'))));
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

	// Count the number of FAQ items to display
	$flags = titania_count::get_flags(titania::$access_level);
	$faq_count = titania_count::from_db(titania::$contrib->contrib_faq_count, $flags);

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
			'auth'		=> (titania::$access_level != TITANIA_ACCESS_PUBLIC || $faq_count) ? true : false,
			'count'		=> $faq_count,
		),
		'support' => array(
			'title'		=> 'CONTRIB_SUPPORT',
			'url'		=> titania::$contrib->get_url('support'),
			'auth'		=> (titania::$config->support_in_titania || titania::$access_level < TITANIA_ACCESS_PUBLIC) ? true : false,
		),
		'manage' => array(
			'title'		=> 'CONTRIB_MANAGE',
			'url'		=> titania::$contrib->get_url('manage'),
			'auth'		=> ((((titania::$contrib->is_author || titania::$contrib->is_active_coauthor) && phpbb::$auth->acl_get('u_titania_post_edit_own')) && !in_array(titania::$contrib->contrib_status, array(TITANIA_CONTRIB_CLEANED, TITANIA_CONTRIB_DISABLED))) || phpbb::$auth->acl_get('u_titania_mod_contrib_mod') || titania_types::$types[titania::$contrib->contrib_type]->acl_get('moderate'))
		),
	);

	// Display nav menu
	$page = request_var('page', '');
	titania::generate_nav($nav_ary, $page, 'details');

	// Search for a category with the same name as the contrib type.  This is a bit ugly, but there really isn't any better option
	$categories_ary = titania::$cache->get_categories();
	foreach ($categories_ary as $category_id => $category_row)
	{
		$category_row['category_name'] = (isset(phpbb::$user->lang[$category_row['category_name']])) ? phpbb::$user->lang[$category_row['category_name']] : $category_row['category_name'];

		if ($category_row['category_name'] == titania_types::$types[titania::$contrib->contrib_type]->lang || $category_row['category_name'] == titania_types::$types[titania::$contrib->contrib_type]->langs)
		{
			$category_object = new titania_category;
			$category_object->__set_array($categories_ary[$category_id]);

			// Generate the main breadcrumbs
			titania::generate_breadcrumbs(array(
				$category_object->category_name => titania_url::build_url($category_object->get_url()),
			));

			break;
		}
	}

	titania::generate_breadcrumbs(array(
		titania::$contrib->contrib_name	=> titania::$contrib->get_url(),
	));

	if ($page)
	{
		titania::generate_breadcrumbs(array(
			$nav_ary[$page]['title']	=> $nav_ary[$page]['url'],
		));
	}
}
