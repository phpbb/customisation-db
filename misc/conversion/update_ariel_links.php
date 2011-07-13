<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* Update links in the forums to redirect to Titania instead of Ariel (for phpbb.com)
*/

/**
 * @ignore
 */
define('IN_TITANIA', true);
define('IN_TITANIA_CONVERT', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
titania::add_lang('manage');

// Give founders access
if (phpbb::$user->data['user_type'] != USER_FOUNDER)
{
	titania::needs_auth();
}

// Hopefully this helps
@set_time_limit(0);

$limit = 2500;

$forum_ids = array(
	15, 16, 17, 22, 23, 35, 94, // 2.0 forums
	69, 70, 71, 72, // 3.0 Modification forums
	73, 74, 185, // 3.0 Styles forums
	225, // Automod
);

$start = request_var('start', 0);

// Rewritten URLs
$contrib_view = 'http://www.phpbb.com/customise/db/contribution/$1/';
$contrib_view_full = '<a class="postlink" href="' . $contrib_view . '">' . $contrib_view . '</a>';

// Count
$sql = 'SELECT COUNT(post_id) AS cnt FROM ' . POSTS_TABLE . '
	WHERE ' . phpbb::$db->sql_in_set('forum_id', $forum_ids);
phpbb::$db->sql_query($sql);
$total = phpbb::$db->sql_fetchfield('cnt');
phpbb::$db->sql_freeresult();

$sql = 'SELECT post_id, post_text FROM ' . POSTS_TABLE . '
	WHERE ' . phpbb::$db->sql_in_set('forum_id', $forum_ids) . '
	ORDER BY post_id ASC';
$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
while ($row = phpbb::$db->sql_fetchrow($result))
{
	$md5 = md5($row['post_text']);
$original = $row['post_text'];

	// Rewrite the full URLs
	$replace = array(
		'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=queue&amp;mode=overview&amp;contrib_id=([0-9]+)">.+</a>#',
		'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;contrib_id=([0-9]+)">.+</a>#',
	);
	$row['post_text'] = preg_replace($replace, $contrib_view_full, $row['post_text']);

	// Rewrite the unparsed URLs
	$replace = array(
		'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=queue&amp;mode=overview&amp;contrib_id=([0-9]+)#',
		'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;contrib_id=([0-9]+)#',
	);
	$row['post_text'] = preg_replace($replace, $contrib_view, $row['post_text']);

	// Rewrite the download URLs
	$replace = array(
		'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/download/([0-9]+)/?">(.+)</a>#',
		'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;download=1&amp;contrib_id=([0-9]+)">(.+)</a>#',
	);
	$row['post_text'] = preg_replace($replace, '<a class="postlink" href="http://www.phpbb.com/customise/db/download/contrib_$1">$2</a>', $row['post_text']);
	$replace = array(
		'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/download/([0-9]+)/?"#',
		'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;download=1&amp;contrib_id=([0-9]+)#',
	);
	$row['post_text'] = preg_replace($replace, 'http://www.phpbb.com/customise/db/download/contrib_$1', $row['post_text']);

	// Remove selected tags stuff
	$replace = "#\n\[b:[a-z0-9]+\]Selected tags:\[/b:[a-z0-9]+\]\n\[list.+\]\[/list:o:[a-z0-9]+\]\n#";
	$row['post_text'] = preg_replace($replace, '', $row['post_text']);

	if (md5($row['post_text']) != $md5)
	{
		// Post was updated
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_text = \'' . phpbb::$db->sql_escape($row['post_text']) . '\'
			WHERE post_id = ' . $row['post_id'];
		phpbb::$db->sql_query($sql);
	}
}
phpbb::$db->sql_freeresult($result);

if (($start + $limit) >= $total)
{
	trigger_error('Completed!');
}
else
{
	// Still more to do
	$next = append_sid(TITANIA_ROOT . 'update_ariel_links.' . PHP_EXT, "start=" . ($start + $limit));
	$display_message = '...done with ' . ($start + $limit) . ' of ' . $total;
}

$display_message .= '<br /><br /><a href="' . $next . '">Manual Continue</a>';

// Meta refresh only if no errors
if (!headers_sent())
{
	meta_refresh(0, $next);
}

trigger_error($display_message);