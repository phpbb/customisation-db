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
* Instructions!
*
* Ariel needs indexes or this will take forever
* ALTER TABLE community_site_queue ADD INDEX (contrib_id)
* ALTER TABLE community_site_queue ADD INDEX (revision_id)
* ALTER TABLE community_site_queue ADD INDEX (queue_status)
* ALTER TABLE community_site_contrib_tags ADD INDEX (contrib_id)
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

// Hack for local
phpbb::$config['site_upload_dir'] = (!isset(phpbb::$config['site_upload_dir'])) ? 'ariel_files' : phpbb::$config['site_upload_dir'];

// Table prefix
$ariel_prefix = 'community_site_';
$limit = 1000;

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

		// Clean up the files directory
		foreach (scandir(titania::$config->upload_path) as $item)
		{
            if ($item == '.' || $item == '..' || $item == '.svn' || $item == 'contrib_temp')
			{
				continue;
			}

			if (is_dir(titania::$config->upload_path . $item))
			{
				titania_rmdir_recursive(titania::$config->upload_path . $item . '/');
			}
		}

		$display_message = 'Truncating Tables, Cleaning File Storage';
	break;

	case 2 :
		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . $ariel_prefix . 'contribs';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		$sql_ary = array(
			'SELECT'	=> 't.*, c.*',

			'FROM'		=> array(
				$ariel_prefix . 'contribs' => 'c',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($ariel_prefix . 'contrib_topics' => 't'),
					'ON'	=> 't.contrib_id = c.contrib_id AND t.topic_type = 1',
				),
			),

			'ORDER_BY'	=> 'c.contrib_id ASC',
		);
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$ignore = array(-1, 3);
			if ($row['contrib_phpbb_version'][0] != '3' || in_array($row['contrib_status'], $ignore) || !in_array($row['contrib_type'], array_keys(titania_types::$types)))
			{
				// Skip 2.0 mods, contribs that were denied or pulled and weird ones
				continue;
			}

			// Ignore things marked as new that do not have contributions in the queue
			if ($row['contrib_status'] == 0)
			{
				$sql = 'SELECT COUNT(revision_id) AS cnt FROM ' . $ariel_prefix . 'queue
					WHERE ' . phpbb::$db->sql_in_set('queue_status', array(1, 2, 3, 4)) . '
						AND contrib_id = ' . $row['contrib_id'];
				$result1 = phpbb::$db->sql_query($sql);
				$cnt = phpbb::$db->sql_fetchfield('cnt', $result1);
				phpbb::$db->sql_freeresult($result1);

				if (!$cnt)
				{
					// Somebody changed the status manually to new, should have been 3
					continue;
				}
			}

			$permalink = titania_url::url_slug($row['contrib_name']);
			$conflict = $cnt = false;
			do {
				$permalink_test = ($cnt !== false) ? $permalink . '_' . $cnt : $permalink;
				$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIBS_TABLE . '
					WHERE contrib_name_clean = \'' . phpbb::$db->sql_escape($permalink_test) . '\'';
				$p_result = phpbb::$db->sql_query($sql);
				if (phpbb::$db->sql_fetchrow($p_result))
				{
					$conflict = true;
					$cnt = ($cnt === false) ? 2 : $cnt + 1;
				}
				else
				{
					$conflict = false;
					$permalink = $permalink_test;
				}
				phpbb::$db->sql_freeresult($p_result);
			} while ($conflict == true);

			switch ((int) $row['contrib_status'])
			{
				case 4 : // Cleaned
					$contrib_status = TITANIA_CONTRIB_CLEANED;
				break;

				// None have been pulled for security reasons...

				default :
					$contrib_status = TITANIA_CONTRIB_APPROVED;
				break;
			}

			$sql_ary = array(
				'contrib_id'					=> $row['contrib_id'],
				'contrib_user_id'				=> $row['user_id'],
				'contrib_type'					=> $row['contrib_type'],
				'contrib_name'					=> $row['contrib_name'],
				'contrib_name_clean'			=> $permalink,
				'contrib_desc'					=> $row['contrib_description'],
				'contrib_desc_bitfield'			=> $row['contrib_bbcode_bitfield'],
				'contrib_desc_uid'				=> $row['contrib_bbcode_uid'],
				'contrib_desc_options'			=> $row['contrib_bbcode_flags'],
				'contrib_status'				=> $contrib_status,
				'contrib_downloads'				=> $row['contrib_downloads'],
				'contrib_views'					=> 0,
				'contrib_rating'				=> 0,
				'contrib_rating_count'			=> 0,
				'contrib_visible'				=> 1,
				'contrib_last_update'			=> 0, // Update with ariel revisions table
				'contrib_demo'					=> ($row['contrib_style_demo']) ? 'http://www.phpbb.com/styles/demo/3.0/index.php?style_id=' . $row['contrib_style_demo'] : '',
				'contrib_topic'					=> ($row['topic_id']) ? $row['topic_id'] : 0,
			);

			// Insert
			titania_insert(TITANIA_CONTRIBS_TABLE, $sql_ary);

			if ($row['contrib_type'] == 2)
			{
				$sql_ary = array(
					'contrib_id'	=> $row['contrib_id'],
					'category_id'	=> 3, // Styles
				);

				// Insert
				titania_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $sql_ary);
			}
			else
			{
				$sql = 'SELECT tag_id FROM ' . $ariel_prefix . 'contrib_tags
					WHERE contrib_id = ' . (int) $row['contrib_id'];
				$result1 = phpbb::$db->sql_query($sql);
				while ($tag_row = phpbb::$db->sql_fetchrow($result1))
				{
					$sql_ary = array(
						'contrib_id'	=> $row['contrib_id'],
					);

					switch ($tag_row['tag_id'])
					{
						case 30 :
							// Add-ons
							$sql_ary['category_id'] = 9;
						break;
						case 31 :
							// Cosmetic
							$sql_ary['category_id'] = 4;
						break;
						case 32 :
							// Admin Tools
							$sql_ary['category_id'] = 5;
						break;
						case 33 :
							// Syndication -> Communication
							$sql_ary['category_id'] = 7;
						break;
						case 34 :
							// BBCode -> Communication
							$sql_ary['category_id'] = 7;
						break;
						case 35 :
							// Security
							$sql_ary['category_id'] = 6;
						break;
						case 36 :
							// Communication
							$sql_ary['category_id'] = 7;
						break;
						case 37 :
							// Profile
							$sql_ary['category_id'] = 8;
						break;
						case 106 :
							// Anti-Spam
							$sql_ary['category_id'] = 10;
						break;
						case 107 :
							// Moderator tools -> Admin tools
							$sql_ary['category_id'] = 5;
						break;
						case 108 :
							// Entertainment
							$sql_ary['category_id'] = 11;
						break;
					}

					// Insert
					if (isset($sql_ary['category_id']))
					{
						titania_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $sql_ary);
					}
				}
				phpbb::$db->sql_freeresult($result1);
			}
		}
		phpbb::$db->sql_freeresult($result);

		$display_message = 'Contributions table';
	break;

	case 3 :
		$sql = 'SELECT COUNT(revision_id) AS cnt FROM ' . $ariel_prefix . 'contrib_revisions';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

$sql_ary = array(
	'SELECT'	=> 'q.*, r.*, c.contrib_name, c.contrib_phpbb_version, c.contrib_status, c.contrib_type',

	'FROM'		=> array(
		$ariel_prefix . 'contrib_revisions' => 'r',
		$ariel_prefix . 'contribs' => 'c',
	),

	'LEFT_JOIN'	=> array(
		array(
			'FROM'	=> array($ariel_prefix . 'queue' => 'q'),
			'ON'	=> 'q.revision_id = r.revision_id',
		),
	),

	'WHERE'		=> 'c.contrib_id = r.contrib_id',

	'ORDER_BY'	=> 'r.revision_id ASC',
);
$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
while ($row = phpbb::$db->sql_fetchrow($result))
{
	$ignore = array(-1, 3);
	if ($row['contrib_phpbb_version'][0] != '3' || in_array($row['contrib_status'], $ignore) || !in_array($row['contrib_type'], array_keys(titania_types::$types)))
	{
		// Skip 2.0 mods, contribs that were denied or pulled and weird ones
		continue;
	}

	if ($row['revision_phpbb_version'][0] != '3')
	{
		//echo 'Revision phpBB version is ' . $row['revision_phpbb_version'] . ' - ' . $row['contrib_name'] . ' - ' . $row['revision_id'] . '<br />';
	}

	$ignore = array(-2, -3, -4, -5, -6);
	if (in_array($row['queue_status'], $ignore))
	{
		// Skip revisions that were denied, canned, etc
		continue;
	}

	// mime_content_type bitches on me without using realpath
	$filename = realpath(PHPBB_ROOT_PATH . phpbb::$config['site_upload_dir'] . '/' . $row['revision_filename_internal']);
	if (!file_exists($filename))
	{
		echo 'Could Not Find File - ' . $filename . '<br />';
		continue;
	}
	$mime_type = mime_content_type($filename);

	switch ($mime_type)
	{
		case 'application/zip' :
		case 'application/octet-stream' :
			if (!strpos($row['revision_filename'], '.zip'))
			{
				$row['revision_filename'] .= '.zip';
			}
		break;

		default :
			//echo $row['revision_filename'] . ' ' . $mime_type . ' ' . $filename . '<br />';
			continue;
		break;
	}

	$move_dir = 'titania_contributions';
	$move_file = md5(unique_id());
	if (!file_exists(titania::$config->upload_path . $move_dir))
	{
		mkdir(titania::$config->upload_path . $move_dir);
		phpbb_chmod(titania::$config->upload_path . $move_dir, CHMOD_ALL);
	}
	if (!copy($filename, titania::$config->upload_path . $move_dir . '/' . $move_file))
	{
		echo 'Could Not Copy File - ' . $filename . '<br />';
		continue;
	}

	$sql_ary = array(
		'object_type'			=> TITANIA_CONTRIB,
		'object_id'				=> $row['contrib_id'],
		'attachment_access'		=> TITANIA_ACCESS_PUBLIC,
		'attachment_comment'	=> '',
		'attachment_directory'	=> $move_dir,
		'physical_filename'		=> $move_file,
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
	$attach_id = titania_insert(TITANIA_ATTACHMENTS_TABLE, $sql_ary);

	$sql_ary = array(
		'revision_id'				=> $row['revision_id'],
		'contrib_id'				=> $row['contrib_id'],
		'attachment_id'				=> $attach_id,
		'revision_version'			=> $row['revision_version'],
		'revision_name'				=> $row['revision_name'],
		'revision_time'				=> $row['revision_date'],
		'revision_validated'		=> ($row['queue_status'] == -1) ? true : false,
		'validation_date'			=> ($row['queue_status'] == -1) ? $row['revision_date'] : 0,
		'phpbb_version'				=> $row['revision_phpbb_version'],
		'install_time'				=> 0,
		'install_level'				=> 0,
		'revision_submitted'		=> 1,
	);

	// Insert
	titania_insert(TITANIA_REVISIONS_TABLE, $sql_ary);

	// Update the contrib_last_update
	if ($row['queue_status'] == -1 || !titania::$config->require_validation)
	{
		$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
			SET contrib_last_update = ' . (int) $row['revision_date'] . '
			WHERE contrib_id = ' . (int) $row['contrib_id'] . '
				AND contrib_last_update < ' . (int) $row['revision_date'];
		phpbb::$db->sql_query($sql);
	}
}
phpbb::$db->sql_freeresult($result);

		$display_message = 'Revisions table';
	break;

	// @todo Queue

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
			titania_insert(TITANIA_AUTHORS_TABLE, $sql_ary);
		}
		phpbb::$db->sql_freeresult($result);

		$display_message = 'Authors table';
	break;

	case 5 :
		$sync = new titania_sync;

		$sync->contribs('validated');

		$sync->categories('count');

		$display_message = 'Syncing';
	break;

	case 6 :
		$sync = new titania_sync;

		// Truncate index first
		titania_search::truncate();

		$sync->contribs('index');

		$display_message = 'Indexing';
	break;

	case 7 :
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

$display_message .= '<br /><br /><a href="' . $next . '">Manual Continue</a>';

// Meta refresh only if no errors
if (!headers_sent())
{
	meta_refresh(0, $next);
}

trigger_error($display_message);

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

function titania_rmdir_recursive($target_filename)
{
	if (!is_dir($target_filename))
	{
		return;
	}

    foreach (scandir($target_filename) as $item)
	{
        if ($item == '.' || $item == '..')
		{
			continue;
		}

		if (is_dir($target_filename . $item))
		{
			titania_rmdir_recursive($target_filename . $item . '/');
		}
		else
		{
			@unlink($target_filename . $item);
		}
    }

	return @rmdir($target_filename);
}