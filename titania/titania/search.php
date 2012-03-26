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
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

phpbb::$user->add_lang('search');
titania::add_lang('search');

// Available Search Types
$search_types = array(
	TITANIA_CONTRIB		=> 'CONTRIBUTION_NAME_DESCRIPTION',
	TITANIA_FAQ			=> 'CONTRIB_FAQ',
);

if (titania::$config->support_in_titania)
{
	$search_types = array_merge($search_types, array(TITANIA_SUPPORT		=> 'CONTRIB_SUPPORT'));
}

$mode = request_var('mode', '');
$keywords = utf8_normalize_nfc(request_var('keywords', '', true));
$user_id = request_var('u', 0);
$contrib_id = request_var('contrib', 0);
$search_fields = request_var('sf', '');
$search_type = request_var('type', 0);
$categories = request_var('c', array(0));
$search_subcategories = request_var('sc', 0);
$phpbb_versions = request_var('versions', array(''));

// Display the advanced search page
if (!$keywords && !$user_id && !$contrib_id && !isset($_POST['submit']))
{
	if ($mode == 'find-contribution')
	{
		titania::_include('functions_posting', 'generate_category_select');

		titania::add_lang('contributions');

		phpbb::$template->assign_vars(array(
			'S_SEARCH_ACTION'	=> titania_url::build_url('find-contribution'),
		));

		// Display the list of phpBB versions available
		foreach (titania::$cache->get_phpbb_versions() as $version => $name)
		{
			$template->assign_block_vars('phpbb_versions', array(
				'VERSION'		=> $name,
			));
		}

		generate_category_select($categories, false, false);

		titania::page_header('SEARCH');
		titania::page_footer(true, 'find_contribution.html');
	}

	// Output search types
	foreach ($search_types as $value => $name)
	{
		phpbb::$template->assign_block_vars('types', array(
			'NAME'		=> (isset(phpbb::$user->lang[$name])) ? phpbb::$user->lang[$name] : $name,
			'VALUE'		=> $value,
		));
	}

	phpbb::$template->assign_vars(array(
		'S_SEARCH_ACTION'	=> titania_url::build_url('search'),
	));

	titania::page_header('SEARCH');
	titania::page_footer(true, 'search_body.html');
}

// Add some POST stuff to the url
if (isset($_POST['submit']))
{
	$author = utf8_normalize_nfc(request_var('author', '', true));

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

	$url_params = array(
		'keywords'		=> $keywords,
		'sf'			=> $search_fields,
		'type'			=> $search_type,
		'c'				=> $categories,
		'sc'			=> $search_subcategories,
		'versions'		=> $phpbb_versions,
	);

	foreach ($url_params as $name => $value)
	{
		if ($value)
		{
			titania_url::$params[$name] = $value;
		}
	}

	// Redirect if sent through POST so the parameters are in the URL (for easy copying/pasting to other users)
	redirect(titania_url::build_url(titania_url::$current_page, titania_url::$params));
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

// Contrib specified?
if ($contrib_id)
{
	$query->where($query->eq('parent_id', $contrib_id));
}

// Find contribution
if ($mode == 'find-contribution')
{
	if (sizeof($categories) == 1 && $categories[0] == 0)
	{
		// All
		$categories = array();
	}

	if (sizeof($categories) || sizeof($phpbb_versions))
	{
		// Prevent an error
		$contribs = array(0);

		// Grab the children
		if ($search_subcategories)
		{
			foreach ($categories as $category_id)
			{
				$categories = array_merge($categories, array_keys(titania::$cache->get_category_children($category_id)));
			}
		}

		// Build-a-query
		$prefix = ((sizeof($categories)) ? 'c' : 'v');
		$sql = 'SELECT DISTINCT(' . $prefix . '.contrib_id) FROM
			' .((sizeof($categories)) ? TITANIA_CONTRIB_IN_CATEGORIES_TABLE . ' c' : '') . '
			' .((sizeof($categories) && (sizeof($phpbb_versions))) ? ', ' : '') . '
			' .((sizeof($phpbb_versions)) ? TITANIA_REVISIONS_PHPBB_TABLE . ' v' : '') . '
			WHERE
				' . ((sizeof($categories)) ? phpbb::$db->sql_in_set('c.category_id', array_unique(array_map('intval', $categories))) : ''). '
				' . ((sizeof($categories) && sizeof($phpbb_versions)) ? ' AND c.contrib_id = v.contrib_id AND ' : '');

		if (sizeof($phpbb_versions))
		{
			$or_sql = '';

			foreach ($phpbb_versions as $version)
			{
				// Skip invalid versions
				if (strlen($version) < 5 || $version[1] != '.' || $version[3] != '.')
				{
					continue;
				}

				// Add OR if not empty
				$or_sql .= (($or_sql) ? ' OR ' : '');

				$or_sql .= '(v.phpbb_version_branch = ' . (int) $version[0] . (int) $version[2] . ' AND v.phpbb_version_revision = \'' . phpbb::$db->sql_escape(substr($version, 4)) . '\')';
			}

			$sql .= ' (' . $or_sql . ') ';
		}

		$sql .= 'GROUP BY ' . $prefix . '.contrib_id';

		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$contribs[] = $row['contrib_id'];
		}
		phpbb::$db->sql_freeresult($result);

		// Search in the set
		titania_search::in_set($query, 'id', $contribs);
	}

	$query->where($query->eq('type', TITANIA_CONTRIB));
}
else
{
	// Search type
	if ($search_type)
	{
		$query->where($query->eq('type', $search_type));
	}
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

titania::page_header('SEARCH');

phpbb::$template->assign_vars(array(
	'SEARCH_WORDS'		=> $keywords,
	'SEARCH_MATCHES'	=> ($sort->total == 1) ? sprintf(phpbb::$user->lang['FOUND_SEARCH_MATCH'], $sort->total) : sprintf(phpbb::$user->lang['FOUND_SEARCH_MATCHES'], $sort->total),

	'U_SEARCH_WORDS'	=> titania_url::build_url(titania_url::$current_page, titania_url::$params),
	'U_SEARCH'			=> titania_url::build_url((($mode == 'find-contribution') ? 'find-contribution' : 'search')),

	'S_IN_SEARCH'		=> true,

//	'S_SHOW_TOPICS'		=> ($display == 'topics') ? true : false,

	'S_SEARCH_ACTION'	=> titania_url::$current_page_url,
));

titania::page_footer(true, 'search_results.html');