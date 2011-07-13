<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_TITANIA_CONVERT', true);
define('IN_TITANIA', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

// Hack for local
phpbb::$config['site_upload_dir'] = (!isset(phpbb::$config['site_upload_dir'])) ? '../phpBB3_titania/files/contribdb' : '../../' . phpbb::$config['site_upload_dir'];
$screenshots_dir = phpbb::$config['site_upload_dir'] . '/demo/';
$ariel_prefix = 'community_site_';
$mod_validation_trash_forum = 28;
$style_validation_trash_forum = 83;

$queue_swap = array(
	1	=> TITANIA_QUEUE_NEW, // QUEUE_NEW
	2	=> TITANIA_QUEUE_NEW, //17, // QUEUE_SPECIAL
	3	=> 19, // QUEUE_APPROVE
	4	=> 20, // QUEUE_DENY
	-1	=> TITANIA_QUEUE_APPROVED, // QUEUE_CLOSED
	-2	=> TITANIA_QUEUE_DENIED, // QUEUE_DENIED
);

titania::add_lang('manage');

if (phpbb::$user->data['user_type'] != USER_FOUNDER)
{
	titania::needs_auth();
}

// We index later...
titania_search::initialize();
titania_search::$do_not_index = true;

// Hopefully this helps
@set_time_limit(0);


// Cleanup
$sql_ary = array(
	'SELECT'	=> 'c.contrib_id',

	'FROM'		=> array(
		$ariel_prefix . 'contribs' => 'c',
	),

	'WHERE'		=> 'c.contrib_status = 0',
);
$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

$result = phpbb::$db->sql_query($sql);
while ($row = phpbb::$db->sql_fetchrow($result))
{
	$sql = 'SELECT queue_topic_id FROM ' . TITANIA_QUEUE_TABLE . ' WHERE contrib_id = ' . $row['contrib_id'];
	$result1 = phpbb::$db->sql_query($sql);
	while ($row1 = phpbb::$db->sql_fetchrow($result1))
	{
		phpbb::$db->sql_query('DELETE FROM ' . TITANIA_TOPICS_TABLE . ' WHERE topic_id = ' . $row1['queue_topic_id']);
	}
	phpbb::$db->sql_freeresult($result1);

	phpbb::$db->sql_query('DELETE FROM ' . TITANIA_QUEUE_TABLE . ' WHERE contrib_id = ' . $row['contrib_id']);
}


// Reconvert
$sql_ary = array(
	'SELECT' => 'q.*, ct.topic_id, c.contrib_name, c.contrib_name_clean, c.contrib_type, r.revision_version',
	'FROM'		=> array(
		$ariel_prefix . 'queue'		=> 'q',
		TITANIA_CONTRIBS_TABLE		=> 'c',
		TITANIA_REVISIONS_TABLE		=> 'r',
		$ariel_prefix . 'contribs'	=> 'c1',
	),
	'LEFT_JOIN'	=> array(
		array(
			'FROM'	=> array($ariel_prefix . 'contrib_topics' => 'ct'),
			'ON'	=> 'ct.topic_type = 4 AND ct.contrib_id = q.contrib_id'
		)
	),
	'WHERE' => 'c.contrib_id = q.contrib_id
		AND r.revision_id = q.revision_id
		AND c1.contrib_id = q.contrib_id
		AND c1.contrib_status = 0',
	'ORDER_BY' => 'queue_id DESC',
);
$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
$result = phpbb::$db->sql_query($sql);
while ($row = phpbb::$db->sql_fetchrow($result))
{
	// Ariel only stores the latest
	$sql = 'SELECT MAX(queue_id) AS max FROM ' . $ariel_prefix . 'queue
		WHERE contrib_id = ' . (int) $row['contrib_id'];
	phpbb::$db->sql_query($sql);
	$max = phpbb::$db->sql_fetchfield('max');
	phpbb::$db->sql_freeresult();
	if ($max != $row['queue_id'])
	{
		// So we attempt to find older ones in the trash can forums
		$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . '
			WHERE forum_id = ' . (($row['contrib_type'] == 1) ? $mod_validation_trash_forum : $style_validation_trash_forum) . '
				AND topic_title = \'' . phpbb::$db->sql_escape($row['contrib_name']) . '\'
				AND topic_time BETWEEN ' . ($row['queue_opened'] - 10) . '
					AND ' . ($row['queue_opened'] + 10);
		phpbb::$db->sql_query($sql);
		$row['topic_id'] = phpbb::$db->sql_fetchfield('topic_id');
		phpbb::$db->sql_freeresult();
	}

	$topic = new titania_topic;
	$topic->parent_id = $row['queue_id'];
	$topic->topic_url = 'manage/queue/q_' . $row['queue_id'];
	$topic->phpbb_topic_id = $row['topic_id'];
	$topic->topic_category = $row['contrib_type'];
	titania_move_topic($row['topic_id'], $topic, TITANIA_QUEUE, $row['contrib_name'], $row['revision_version']);
	$queue_topic_id = $topic->topic_id;
	unset($topic);

	// Ariel = shit.  Closing queue items but leaving them as not approved or denied.  Going to have to assume they were denied.
	if ($row['queue_closed'] && $row['queue_status'] != -1)
	{
		$row['queue_status'] = -2;
	}

	// Now insert to the queue table
	$sql_ary = array(
		'queue_id'				=> $row['queue_id'],
		'revision_id'			=> $row['revision_id'],
		'contrib_id'			=> $row['contrib_id'],
		'submitter_user_id'		=> $row['user_id'],
		'queue_topic_id'		=> $queue_topic_id,

		'queue_type'			=> $row['contrib_type'],
		'queue_status'			=> (isset($queue_swap[$row['queue_status']])) ? $queue_swap[$row['queue_status']] : TITANIA_QUEUE_NEW,
		'queue_submit_time'		=> $row['queue_opened'],
		'queue_close_time'		=> $row['queue_closed'],

		'queue_notes'			=> '',
		'queue_notes_bitfield'	=> '',
		'queue_notes_uid'		=> '',
		'queue_notes_options'	=> 7,

		'queue_validation_notes'			=> '',
		'queue_validation_notes_bitfield'	=> '',
		'queue_validation_notes_uid'		=> '',
		'queue_validation_notes_options'	=> 7,

		'mpv_results'			=> '',
		'mpv_results_bitfield'	=> '',
		'mpv_results_uid'		=> '',
		'automod_results'		=> '',
	);

	titania_insert(TITANIA_QUEUE_TABLE, $sql_ary);
}
phpbb::$db->sql_freeresult($result);

$sync = new titania_sync;

$sync->contribs('validated');

$sync->categories('count');

phpbb::$cache->purge();

trigger_error('Completed');



function titania_insert($table, $sql_ary)
{
	$sql = 'INSERT INTO ' . $table . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary);

	phpbb::$db->sql_return_on_error(true);

	phpbb::$db->sql_query($sql);

	if (phpbb::$db->sql_error_triggered && phpbb::$db->sql_error_returned['code'] != 1062) // Ignore duplicate entry errors
	{
		echo '<br />' . $sql . '<br />';
		echo 'SQL ERROR [ ' . phpbb::$db->sql_layer . ' ]<br /><br />' . phpbb::$db->sql_error_returned['message'] . ' [' . phpbb::$db->sql_error_returned['code'] . ']<br />';
	}

	phpbb::$db->sql_return_on_error(false);

	return phpbb::$db->sql_nextid();
}

/**
* Make a directory recursively (from functions_compress)
*
* @param string $target_filename The target directory we wish to have
*/
function titania_mkdir_recursive($target_filename)
{
	if (!is_dir($target_filename))
	{
		$str = '';
		$folders = explode('/', $target_filename);

		// Create and folders and subfolders if they do not exist
		foreach ($folders as $folder)
		{
			$folder = trim($folder);
			if (!$folder)
			{
				continue;
			}

			$str = (!empty($str)) ? $str . '/' . $folder : $folder;
			if (!is_dir($str))
			{
				@mkdir($str, 0777);
				phpbb_chmod($str, CHMOD_READ | CHMOD_WRITE);
			}
		}
	}
}

// Move a topic from phpBB ($topic_id) to Titania ($topic; object)
function titania_move_topic($topic_id, $topic, $topic_type, $contrib_name = '', $revision_version = '')
{
	$post = false;

	// Convert the topics over from the phpBB forums
	if ($topic_id)
	{
		$sql = 'SELECT * FROM ' . POSTS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id . '
			ORDER BY post_id ASC';
		$post_result = phpbb::$db->sql_query($sql);
		while ($post_row = phpbb::$db->sql_fetchrow($post_result))
		{
			$post = new titania_post($topic_type, $topic);

			// Rewrite some URLs
			$contrib_view = 'http://www.phpbb.com/customise/db/contribution/$1/';
			$contrib_view_full = '<a class="postlink" href="' . $contrib_view . '">' . $contrib_view . '</a>';

			// Rewrite the full URLs
			$replace = array(
				'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=queue&amp;mode=overview&amp;contrib_id=([0-9]+)">.+</a>#',
				'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;contrib_id=([0-9]+)">.+</a>#',
			);
			$post_row['post_text'] = preg_replace($replace, $contrib_view_full, $post_row['post_text']);

			// Rewrite the unparsed URLs
			$replace = array(
				'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=queue&amp;mode=overview&amp;contrib_id=([0-9]+)#',
				'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;contrib_id=([0-9]+)#',
			);
			$post_row['post_text'] = preg_replace($replace, $contrib_view, $post_row['post_text']);

			// Rewrite the download URLs
			$replace = array(
				'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/download/([0-9]+)/?">(.+)</a>#',
				'#<a class="postlink" href="http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;download=1&amp;contrib_id=([0-9]+)">(.+)</a>#',
			);
			$post_row['post_text'] = preg_replace($replace, '<a class="postlink" href="http://www.phpbb.com/customise/db/download/contrib_$1">$2</a>', $post_row['post_text']);
			$replace = array(
				'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/download/([0-9]+)/?"#',
				'#http(?:\:|&\#58;)//www(?:\.|&\#46;)phpbb(?:\.|&\#46;)com/(?:styles|mods)/db/index(?:\.|&\#46;)php\?i=misc&amp;mode=display&amp;download=1&amp;contrib_id=([0-9]+)#',
			);
			$post_row['post_text'] = preg_replace($replace, 'http://www.phpbb.com/customise/db/download/contrib_$1', $post_row['post_text']);

			// Remove selected tags stuff (they are completely useless with Titania)
			$replace = "#\n\[b:[a-z0-9]+\]Selected tags:\[/b:[a-z0-9]+\]\n\[list.+\]\[/list:o:[a-z0-9]+\]\n#";
			$post_row['post_text'] = preg_replace($replace, '', $post_row['post_text']);

			$post_row['post_text'] = str_replace("===INT===", '', $post_row['post_text']);

			$post->__set_array(array(
				'post_access'			=> ($topic_type == TITANIA_QUEUE) ? TITANIA_ACCESS_TEAMS : TITANIA_ACCESS_AUTHORS,
				'post_user_id'			=> $post_row['poster_id'],
				'post_ip'				=> $post_row['poster_ip'],
				'post_time'				=> $post_row['post_time'],
				'post_subject'			=> $post_row['post_subject'],
				'post_text'				=> $post_row['post_text'],
				'post_text_bitfield'	=> $post_row['bbcode_bitfield'],
				'post_text_uid'			=> $post_row['bbcode_uid'],
				'post_text_options'		=> (($post_row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0) + (($post_row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0) + (($post_row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0),
			));

			if ($topic_type == TITANIA_QUEUE_DISCUSSION)
			{
				$post->topic->topic_sticky = true;
			}

			$post->submit();
		}
		phpbb::$db->sql_freeresult($post_result);
	}

	// We didn't convert any posts?  (Local install perhaps?)
	if ($post === false && $contrib_name)
	{
		$post = new titania_post($topic_type, $topic);
		$post->__set_array(array(
			'post_access'			=> TITANIA_ACCESS_TEAMS,
			'post_subject'			=> phpbb::$user->lang['VALIDATION'] . ' - ' . $contrib_name . ' - ' . $revision_version,
			'post_text'				=> 'Converted from Ariel',
		));
		switch ($post->topic->topic_category)
		{
			case TITANIA_TYPE_MOD :
				$post->post_user_id = titania::$config->forum_mod_robot;
			break;

			case TITANIA_TYPE_STYLE :
				$post->post_user_id = titania::$config->forum_style_robot;
			break;
		}

		$post->submit();
	}
}