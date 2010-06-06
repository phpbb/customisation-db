<?php
/**
 *
 * @package Titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
* @ignore
*/
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

phpbb::$user->add_lang('search');
titania::add_lang('search');

// Available Search Types
$search_types = array(
	TITANIA_CONTRIB		=> 'CONTRIBUTION_NAME_DESCRIPTION',
	TITANIA_FAQ			=> 'CONTRIB_FAQ',
	TITANIA_SUPPORT		=> 'CONTRIB_SUPPORT',
);

//$search_fields =
$keywords = utf8_normalize_nfc(request_var('keywords', '', true));
$user_id = request_var('u', 0);
$search_fields = request_var('sf', '');
$search_type = request_var('type', 0);
//$display = request_var('display', '');

// Display the advanced search page
if (!$keywords && !$user_id)
{
	// Output search types
	foreach ($search_types as $value => $name)
	{
		phpbb::$template->assign_block_vars('types', array(
			'NAME'		=> (isset(phpbb::$user->lang[$name])) ? phpbb::$user->lang[$name] : $name,
			'VALUE'		=> $value,
		));
	}

	titania::page_header('SEARCH');
	titania::page_footer(true, 'search_body.html');
}

// Add some POST stuff to the url
if (isset($_POST['submit']))
{
	$author = utf8_normalize_nfc(request_var('author', '', true));

	if ($keywords)
	{
		titania_url::$params['keywords'] = $keywords;
	}
	if ($author)
	{
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
			WHERE username_clean = \'' . phpbb::$db->sql_escape(utf8_clean_string($author)) . '\'';
		phpbb::$db->sql_query($sql);
		$user_id = phpbb::$db->sql_fetchfield('user_id');
		phpbb::$db->sql_freeresult();

		if (!$user_id)
		{
			trigger_error('NO_USER');
		}

		titania_url::$params['u'] = $user_id;
	}
	if ($search_fields)
	{
		titania_url::$params['sf'] = $search_fields;
	}
	if ($search_type)
	{
		titania_url::$params['type'] = $search_type;
	}
	/*if ($display)
	{
		titania_url::$params['display'] = $display;
	}*/

	// Redirect if sent through POST so the parameters are in the URL (for easy copying/pasting to other users)
	redirect(titania_url::build_url('search', titania_url::$params));
}

// Setup the sort tool
$sort = new titania_sort();
$sort->set_defaults(phpbb::$config['posts_per_page']);
$sort->request();

// Setup the search tool and make sure it is working
titania_search::initialize();
if (titania_search::$do_not_index)
{
	// Solr service is down
	trigger_error('SEARCH_UNAVAILABLE');
}

// Initialize the query
$query = titania_search::create_find_query();

// Query fields
$query_fields = array();
switch ($search_fields)
{
	case 'titleonly' :
		$query_fields[] = 'title';
	break;

	case 'msgonly' :
		$query_fields[] = 'text';
	break;

	default:
		$query_fields[] = 'title';
		$query_fields[] = 'text';
	break;
}

// Keywords specified?
if ($keywords)
{
	titania_search::clean_keywords($keywords);

	$qb = new ezcSearchQueryBuilder();
	$qb->parseSearchQuery($query, $keywords, $query_fields);
	unset($qb);
}

// Author specified?
if ($user_id)
{
	$query->where($query->eq('author', $user_id));
}

// Search type
if ($search_type)
{
	$query->where($query->eq('type', $search_type));
}

// Do the search
$results = titania_search::custom_search($query, $sort);

// Grab the users
users_overlord::load_users($results['user_ids']);

/*switch ($display)
{
	case 'topics' :
		foreach ($results['documents'] as $document)
		{
			$url_base = $document->url;
			$url_params = '';
			if (substr($url_base, -1) != '/')
			{
				$url_params = substr($url_base, (strrpos($url_base, '/') + 1));
				$url_base = substr($url_base, 0, (strrpos($url_base, '/') + 1));
			}

			phpbb::$template->assign_block_vars('searchresults', array(
				'TOPIC_TITLE'		=> censor_text($document->title),

				'TOPIC_AUTHOR_FULL'	=> users_overlord::get_user($document->author, '_full'),
				'FIRST_POST_TIME'	=> phpbb::$user->format_date($document->date),

				'U_VIEW_TOPIC'		=> titania_url::build_url($url_base, $url_params),

				'S_TOPIC_REPORTED'		=> ($document->reported) ? true : false,
				//'S_TOPIC_UNAPPROVED'	=> (!$document->approved) ? true : false,
			));
		}
	break;

	default : */
		foreach ($results['documents'] as $document)
		{
			$url_base = $url_params = '';
			titania_url::split_base_params($url_base, $url_params, $document->url);

			phpbb::$template->assign_block_vars('searchresults', array(
				'POST_SUBJECT'		=> censor_text($document->title),
				'MESSAGE'			=> titania_generate_text_for_display($document->text, $document->text_uid, $document->text_bitfield, $document->text_options),

				'POST_AUTHOR_FULL'	=> ($document->author) ? users_overlord::get_user($document->author, '_full') : false,
				'POST_DATE'			=> ($document->date) ? phpbb::$user->format_date($document->date) : false,

				'U_VIEW_POST'		=> titania_url::build_url($url_base, $url_params),

				'S_POST_REPORTED'	=> ($document->reported) ? true : false,
			));
		}
/*	break;
}*/

$sort->build_pagination(titania_url::$current_page, titania_url::$params);

phpbb::$template->assign_vars(array(
	'SEARCH_WORDS'		=> $keywords,
	'SEARCH_MATCHES'	=> ($sort->total == 1) ? sprintf(phpbb::$user->lang['FOUND_SEARCH_MATCH'], $sort->total) : sprintf(phpbb::$user->lang['FOUND_SEARCH_MATCHES'], $sort->total),

	'U_SEARCH_WORDS'	=> titania_url::build_url(titania_url::$current_page, titania_url::$params),

	'S_IN_SEARCH'		=> true,

//	'S_SHOW_TOPICS'		=> ($display == 'topics') ? true : false,

	'S_SEARCH_ACTION'	=> titania_url::$current_page_url,
));

titania::page_header('SEARCH');
titania::page_footer(true, 'search_results.html');