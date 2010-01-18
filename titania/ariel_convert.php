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
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;

if (!phpbb::$user->data['user_type'] == USER_FOUNDER)
{
	trigger_error('NO_AUTH');
}

// Table prefix
$ariel_prefix = 'community_site_';
$limit = 500;

$step = request_var('step', 0);
$start = request_var('start', 0);

// Populated later
$total = 0;
$display_message = '';

switch ($step)
{
	case 0 :
		trigger_error('Are you ready to begin the conversion?  All old data in Titania will be lost!<br /><br /><a href="' . append_sid(TITANIA_ROOT . 'ariel_convert.' . PHP_EXT, 'step=1') . '">Continue with the converter</a>');
	break;

	case 1 :
		$truncate = array(TITANIA_ATTACHMENTS_TABLE, TITANIA_AUTHORS_TABLE, TITANIA_CONTRIBS_TABLE, TITANIA_CONTRIB_COAUTHORS_TABLE, TITANIA_CONTRIB_FAQ_TABLE, TITANIA_CONTRIB_IN_CATEGORIES_TABLE, TITANIA_POSTS_TABLE, TITANIA_RATINGS_TABLE, TITANIA_REVISIONS_TABLE, TITANIA_TOPICS_TABLE, TITANIA_TRACK_TABLE, TITANIA_WATCH_TABLE);

		foreach ($truncate as $table)
		{
			phpbb::$db->sql_query('TRUNCATE TABLE ' . $table);
		}

		$display_message = 'Truncating Tables';
	break;

	case 2 :
		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . $ariel_prefix . 'contribs';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		$sql = 'SELECT * FROM ' . $ariel_prefix . 'contribs c, ' . $ariel_prefix . 'contrib_topics t
			WHERE t.contrib_id = c.contrib_id
				AND t.topic_type = 1
			ORDER BY contrib_id ASC'; // @todo topic_type
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if ($row['contrib_phpbb_version'][0] != '3')
			{
				// Skip 2.0 mods
				continue;
			}

			$permalink = titania_url::url_slug($row['contrib_name']);
			$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIBS_TABLE . '
				WHERE contrib_name_clean = \'' . phpbb::$db->sql_escape($permalink) . '\'';
			$p_result = phpbb::$db->sql_query($sql);
			if (phpbb::$db->sql_fetchrow($p_result))
			{
				// just trigger an error for now, we may not actually have conflicts.  Change later if we do
				trigger_error('Conflict! - ' . $permalink);
			}
			phpbb::$db->sql_freeresult($p_result);

			$sql_ary = array(
				'contrib_id'					=> $row['contrib_id'],
				'contrib_user_id'				=> $row['user_id'],
				'contrib_type'					=> $row['contrib_type'], // @todo
				'contrib_name'					=> $row['contrib_name'],
				'contrib_name_clean'			=> $permalink,
				'contrib_desc'					=> $row['contrib_description'],
				'contrib_desc_bitfield'			=> '',
				'contrib_desc_uid'				=> '',
				'contrib_desc_options'			=> 7,
				'contrib_status'				=> TITANIA_CONTRIB_NEW, // @todo
				'contrib_downloads'				=> $row['contrib_downloads'],
				'contrib_views'					=> 0,
				'contrib_rating'				=> 0,
				'contrib_rating_count'			=> 0,
				'contrib_visible'				=> 1,
				'contrib_last_update'			=> 0, // Update with ariel revisions table
				'contrib_demo'					=> $row['contrib_style_demo'],
				'contrib_topic'					=> $row['topic_id'],
			);

			// Insert
			phpbb::$db->sql_query('INSERT INTO ' . TITANIA_CONTRIBS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

			// @todo Categories
		}
		phpbb::$db->sql_freeresult($result);

		$display_message = 'Contributions table';
	break;

	case 3 :
		$sql = 'SELECT COUNT(revision_id) AS cnt FROM ' . $ariel_prefix . 'contrib_revisions';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		$sql = 'SELECT * FROM ' . $ariel_prefix . 'contrib_revisions
			ORDER BY revision_id ASC';
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if ($row['revision_phpbb_version'][0] != '3' || (!strpos($row['revision_filename'], '.mod') && !strpos($row['revision_filename'], '.zip')))
			{
				// Skip 2.0 mods and broken filenames (broken filenames seem to only be on really old files)
				continue;
			}

			$sql_ary = array(
				'object_type'			=> TITANIA_CONTRIB,
				'object_id'				=> $row['contrib_id'],
				'attachment_access'		=> TITANIA_ACCESS_PUBLIC,
				'attachment_comment'	=> '',
				'attachment_directory'	=> 'titania_contributions',
				'physical_filename'		=> $row['revision_filename_internal'],
				'real_filename'			=> $row['revision_filename'],
				'download_count'		=> 0,
				'filesize'				=> $row['revision_filesize'],
				'filetime'				=> $row['revision_date'],
				'extension'				=> (strpos($row['revision_filename'], '.zip')) ? 'zip' : 'mod',
				'mimetype'				=> (strpos($row['revision_filename'], '.zip')) ? 'application/x-zip-compressed' : 'text/plain',
				'hash'					=> $row['revision_md5'],
				'thumbnail'				=> 0,
				'is_orphan'				=> 0,
			);

			// Insert
			phpbb::$db->sql_query('INSERT INTO ' . TITANIA_ATTACHMENTS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));
			$attach_id = phpbb::$db->sql_nextid();

			$sql_ary = array(
				'revision_id'				=> $row['revision_id'],
				'contrib_id'				=> $row['contrib_id'],
				'attachment_id'				=> $attach_id,
				'revision_version'			=> $row['revision_version'],
				'revision_name'				=> $row['revision_name'],
				'revision_time'				=> $row['revision_date'],
				'revision_validated'		=> false, // @todo
				'validation_date'			=> $row['revision_date'],
				'phpbb_version'				=> $row['revision_phpbb_version'],
				'install_time'				=> 0,
				'install_level'				=> 0,
				'revision_submitted'		=> 1,
			);

			// Insert
			phpbb::$db->sql_query('INSERT INTO ' . TITANIA_CONTRIBS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));

			// Update the contrib_last_update
			$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
				SET contrib_last_update = ' . (int) $row['revision_date'] . '
				WHERE contrib_id = ' . (int) $row['contrib_id'] . '
					AND contrib_last_update < ' . (int) $row['revision_date'];
			phpbb::$db->sql_query($sql);
		}
		phpbb::$db->sql_freeresult($result);

		$display_message = 'Revisions table';
	break;

	case 4 :
		$sql = 'SELECT DISTINCT(contrib_user_id) AS user_id FROM ' . TITANIA_CONTRIBS_TABLE;
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'user_id'				=> $row['user_id'],
				'phpbb_user_id'			=> $row['user_id'],
				'author_realname'		=> '',
				'author_website'		=> '',
				'author_rating'			=> 0,
				'author_rating_count'	=> 0,
				'author_contribs'		=> 0,
				'author_snippets'		=> 0,
				'author_mods'			=> 0,
				'author_styles'			=> 0,
				'author_visible'		=> 1,
				'author_desc'			=> '',
				'author_desc_bitfield'	=> '',
				'author_desc_uid'		=> '',
				'author_desc_options'	=> 7,
			);

			// Count the contribution totals for each user
			foreach (titania_types::$types as $type_id => $class)
			{
				$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE . '
					WHERE contrib_type = ' . (int) $type_id . '
						AND contrib_user_id = ' . (int) $row['user_id'];
				phpbb::$db->sql_query($sql);
				$cnt = phpbb::$db->sql_fetchfield('cnt');

				$sql_ary['author_contribs'] += $cnt;
				$sql_ary[$class->author_count] += $cnt;
			}

			// Insert
			phpbb::$db->sql_query('INSERT INTO ' . TITANIA_AUTHORS_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', $sql_ary));
		}
		phpbb::$db->sql_freeresult($result);

		$display_message = 'Authors table';
	break;

	case 5 :
		phpbb::$cache->purge();

		trigger_error('Ariel Conversion Finished!');
	break;
}

if (($start + $limit) >= $total)
{
	// Move to the next step
	$next = append_sid(TITANIA_ROOT . 'ariel_convert.' . PHP_EXT, 'step=' . ++$step);
	$display_message .= '...done!';
}
else
{
	// Still more to do
	$next = append_sid(TITANIA_ROOT . 'ariel_convert.' . PHP_EXT, "step={$step}&amp;start=" . ($start + $limit));
	$display_message .= '...done with ' . ($start + $limit) . ' of ' . $total;
}

meta_refresh(0, $next);
trigger_error($display_message);
