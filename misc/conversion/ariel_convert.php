<?php
/**
*
* @package Titania
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
define('IN_TITANIA_CONVERT', true);
if (!defined('TITANIA_ROOT')) define('TITANIA_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
require TITANIA_ROOT . 'common.' . PHP_EXT;
titania::add_lang('manage');

// Give founders access to run this
if (phpbb::$user->data['user_type'] != USER_FOUNDER)
{
	titania::needs_auth();
}

// Hopefully this helps
@set_time_limit(0);

if (!isset(phpbb::$config['titania_hook_phpbb_com']))
{
	phpbb::_include('../umil/umil', false, 'umil');

	$umil = new umil(true, phpbb::$db);

	$umil->run_actions('update', array(
		'1.0.0' => array(
			'table_column_add' => array(
				array(TITANIA_TOPICS_TABLE, 'phpbb_topic_id', array('UINT', 0)),
			),
		),
	),
	'titania_hook_phpbb_com');

	unset($umil);
}

// Hack for local
phpbb::$config['site_upload_dir'] = (!isset(phpbb::$config['site_upload_dir'])) ? '../phpBB3_titania/files/contribdb' : '../../' . phpbb::$config['site_upload_dir'];
$screenshots_dir = phpbb::$config['site_upload_dir'] . '/demo/';

// Table prefix
$ariel_prefix = 'community_site_';
$limit = 1000;
$mod_validation_trash_forum = 28;
$style_validation_trash_forum = 83;

$step = request_var('step', 0);
$start = request_var('start', 0);

// Populated later
$total = 0;
$display_message = '';

// We index later...
titania_search::initialize();
titania_search::$do_not_index = true;

$tags_to_cats = array(
	9 => 12, // Board Styles
	10 => 13, // Smilies
	11 => 15, // Ranks
	12 => 14, // Avatars
	30 => 8, // Add-ons
	31 => 3, // Cosmetic
	32 => 4, // Admin Tools -> Tools
	33 => 6, // Syndication -> Communication
	34 => 6, // BBCode -> Communication
	35 => 5, // Security
	36 => 6, // Communication
	37 => 7, // Profile
	106 => 9, // Anti-Spam
	107 => 4, // Moderator Tools -> Tools
	108 => 10, // Entertainment
	155 => 12, // Imageset -> Board Styles
	165 => 12, // Theme -> Board Styles
	175 => 12, // Template -> Board Styles
	195 => 16, // Topic Icons -> Miscellaneous
	235 => 16, // Tools -> Miscellaneous
);

$queue_swap = array(
	1	=> TITANIA_QUEUE_NEW, // QUEUE_NEW
	2	=> TITANIA_QUEUE_NEW, //17, // QUEUE_SPECIAL
	3	=> 19, // QUEUE_APPROVE
	4	=> 20, // QUEUE_DENY
	-1	=> TITANIA_QUEUE_APPROVED, // QUEUE_CLOSED
	-2	=> TITANIA_QUEUE_DENIED, // QUEUE_DENIED
);

switch ($step)
{
	case 0 :
		trigger_error('Are you ready to begin the conversion?  All old data in Titania will be lost!<br /><br /><a href="' . append_sid(TITANIA_ROOT . 'ariel_convert.' . PHP_EXT, 'step=1') . '">Continue with the converter</a>');
	break;

	case 1 :
		$truncate = array(TITANIA_AUTOMOD_QUEUE_TABLE, TITANIA_REVISIONS_PHPBB_TABLE, TITANIA_ATTENTION_TABLE, TITANIA_QUEUE_TABLE, TITANIA_ATTACHMENTS_TABLE, TITANIA_AUTHORS_TABLE, TITANIA_CONTRIBS_TABLE, TITANIA_CONTRIB_COAUTHORS_TABLE, TITANIA_CONTRIB_FAQ_TABLE, TITANIA_CONTRIB_IN_CATEGORIES_TABLE, TITANIA_POSTS_TABLE, TITANIA_RATINGS_TABLE, TITANIA_REVISIONS_TABLE, TITANIA_TOPICS_TABLE, TITANIA_TRACK_TABLE, TITANIA_WATCH_TABLE);

		foreach ($truncate as $table)
		{
			phpbb::$db->sql_query('TRUNCATE TABLE ' . $table);
		}

		// Truncate search index
		titania_search::truncate();

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

		titania_mkdir_recursive(titania::$config->upload_path . 'titania_screenshots');

		$display_message = 'Truncating Tables, Cleaning File Storage';
	break;

	case 2 :
		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . $ariel_prefix . 'contribs';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		$sql_ary = array(
			'SELECT'	=> 't.topic_id, c.*',

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
			if (in_array($row['contrib_status'], $ignore) || !in_array($row['contrib_type'], array_keys(titania_types::$types)))
			{
				// Skip contribs that were denied or pulled and weird ones
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

			// Set 2.0 mods to cleaned
			if ($row['contrib_phpbb_version'][0] != '3')
			{
				$contrib_status = TITANIA_CONTRIB_CLEANED;
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
				'contrib_release_topic_id'		=> ($row['topic_id']) ? $row['topic_id'] : 0,
			);

			// Insert
			titania_insert(TITANIA_CONTRIBS_TABLE, $sql_ary);

			// Convert 2.0 mods, but do not put them in any categories
			if ($row['contrib_phpbb_version'][0] == '3')
			{
				$sql = 'SELECT tag_id FROM ' . $ariel_prefix . 'contrib_tags
					WHERE contrib_id = ' . (int) $row['contrib_id'];
				$result1 = phpbb::$db->sql_query($sql);
				while ($tag_row = phpbb::$db->sql_fetchrow($result1))
				{
					$sql_ary = array(
						'contrib_id'	=> $row['contrib_id'],
					);

					if (isset($tags_to_cats[$tag_row['tag_id']]))
					{
						$sql_ary['category_id'] = $tags_to_cats[$tag_row['tag_id']];

						titania_insert(TITANIA_CONTRIB_IN_CATEGORIES_TABLE, $sql_ary);
					}
				}
				phpbb::$db->sql_freeresult($result1);
			}

			// Screenshots
			if (file_exists(TITANIA_ROOT . $screenshots_dir . (int) $row['contrib_id'] . '.gif'))
			{
				$new_filename = md5($row['contrib_id'] . rand(0, 100));
				if (!copy(TITANIA_ROOT . $screenshots_dir . (int) $row['contrib_id'] . '.gif', titania::$config->upload_path . 'titania_screenshots/' . $new_filename))
				{
					echo 'Could Not Copy File - ' . TITANIA_ROOT . $screenshots_dir . (int) $row['contrib_id'] . '.gif<br />';
					continue;
				}

				$sql_ary = array(
					'object_type'			=> TITANIA_SCREENSHOT,
					'object_id'				=> $row['contrib_id'],
					'attachment_access'		=> TITANIA_ACCESS_PUBLIC,
					'attachment_comment'	=> '',
					'attachment_directory'	=> 'titania_screenshots',
					'physical_filename'		=> $new_filename,
					'real_filename'			=> 'Screenshot',
					'download_count'		=> 0,
					'filesize'				=> filesize(titania::$config->upload_path . 'titania_screenshots/' . $new_filename),
					'filetime'				=> 0,
					'extension'				=> 'gif',
					'mimetype'				=> 'image/gif',
					'hash'					=> md5_file(titania::$config->upload_path . 'titania_screenshots/' . $new_filename),
					'thumbnail'				=> false,
					'is_orphan'				=> 0,
				);

				// Insert
				titania_insert(TITANIA_ATTACHMENTS_TABLE, $sql_ary);
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
			'SELECT'	=> 'q.queue_id, q.queue_status, r.*, c.contrib_name, c.contrib_phpbb_version, c.contrib_status, c.contrib_type, c.contrib_filename_internal, c.contrib_phpbb_version',

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

			'ORDER_BY'	=> 'r.revision_id DESC, q.queue_id DESC',
		);
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);

		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$ignore = array(-1, 3);
			if (in_array($row['contrib_status'], $ignore) || !in_array($row['contrib_type'], array_keys(titania_types::$types)))
			{
				// Skip contribs that were denied or pulled and weird ones
				continue;
			}

			$ignore = array(-3, -4, -5, -6);
			if (in_array($row['queue_status'], $ignore))
			{
				// Skip revisions that were canned, etc
				continue;
			}

			// mime_content_type bitches on me without using realpath
			$filename = realpath(TITANIA_ROOT . phpbb::$config['site_upload_dir'] . '/' . $row['revision_filename_internal']);
			if (!file_exists($filename))
			{
				echo 'Could Not Find File - ' . TITANIA_ROOT . phpbb::$config['site_upload_dir'] . '/' . $row['revision_filename_internal'] . '<br />';
				continue;
			}

			if (function_exists('mime_content_type'))
			{
				$mime_type = mime_content_type($filename);
			}
			else
			{
				ob_start();
				system('/usr/bin/file -i -b ' . $filename);
				$type = ob_get_clean();
				$parts = explode(';', $type);
				$mime_type = trim($parts[0]);
			}

			switch ($mime_type)
			{
				case 'application/zip' :
				case 'application/octet-stream' :
					if (!strpos($row['revision_filename'], '.zip'))
					{
						$row['revision_filename'] .= '.zip';
					}
				break;

				case 'text/plain' :
				case 'text/x-c' :
				case 'text/x-c++' :
				case 'text/x-pascal' :
				case 'text/x-lisp' :
				case 'application/xml' :
					if (!strpos($row['revision_filename'], '.mod'))
					{
						$row['revision_filename'] .= '.mod';
					}
				break;

				case 'application/x-rar' :
					continue; // Silently ignore...
				break;

				default :
					echo $row['revision_filename'] . ' ' . $mime_type . ' ' . $filename . '<br />';
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
				'install_time'				=> 0,
				'install_level'				=> 0,
				'revision_submitted'		=> 1,
				'revision_queue_id'			=> (isset($row['queue_id'])) ? (int) $row['queue_id'] : 0,
			);

			// Insert
			$revision_id = titania_insert(TITANIA_REVISIONS_TABLE, $sql_ary);

			if ($row['contrib_filename_internal'] == $row['revision_filename_internal'])
			{
				// More Ariel nubish.
				$sql_ary = array(
					'revision_id'				=> $revision_id,
					'contrib_id'				=> $row['contrib_id'],
					'phpbb_version_branch'		=> ($row['contrib_phpbb_version'][0] == '3') ? 30 : 20,
					'phpbb_version_revision'	=> get_real_revision_version(substr($row['contrib_phpbb_version'], 4)),
					'revision_validated'		=> ($row['queue_status'] == -1) ? true : false,
				);
			}
			else
			{
				$sql_ary = array(
					'revision_id'				=> $revision_id,
					'contrib_id'				=> $row['contrib_id'],
					'phpbb_version_branch'		=> ($row['revision_phpbb_version'][0] == '3') ? 30 : 20,
					'phpbb_version_revision'	=> get_real_revision_version(substr($row['revision_phpbb_version'], 4)),
					'revision_validated'		=> ($row['queue_status'] == -1) ? true : false,
				);
			}
			titania_insert(TITANIA_REVISIONS_PHPBB_TABLE, $sql_ary);

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

	case 4 :
		$limit = $limit / 2;

		$sql = 'SELECT COUNT(topic_id) AS cnt FROM ' . $ariel_prefix . 'contrib_topics t, ' . TITANIA_CONTRIBS_TABLE . ' c
			WHERE t.topic_type = 5
				AND c.contrib_id = t.contrib_id';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		// Move the queue discussion topics to our own side
		$sql = 'SELECT * FROM ' . $ariel_prefix . 'contrib_topics t, ' . TITANIA_CONTRIBS_TABLE . ' c
			WHERE t.topic_type = 5
				AND c.contrib_id = t.contrib_id
			ORDER BY t.topic_id DESC';
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$topic = new titania_topic;
			$topic->parent_id = $row['contrib_id'];
			$topic->topic_category = $row['contrib_type'];
			$topic->phpbb_topic_id = $row['topic_id'];
			$topic->topic_category = $row['contrib_type'];
			$topic->topic_url = titania_types::$types[$row['contrib_type']]->url . '/' . $row['contrib_name_clean'] . '/support/';
			titania_move_topic($row['topic_id'], $topic, TITANIA_QUEUE_DISCUSSION);
			unset($topic);
		}
		phpbb::$db->sql_freeresult();

		$display_message = 'Queue Discussion';
	break;

	case 5 :
		$limit = $limit / 2;

		$sql = 'SELECT COUNT(queue_id) AS cnt FROM ' . $ariel_prefix . 'queue';
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		$sql_ary = array(
			'SELECT' => 'q.*, ct.topic_id, c.contrib_name, c.contrib_name_clean, c.contrib_type, r.revision_version',
			'FROM'		=> array(
				$ariel_prefix . 'queue'		=> 'q',
				TITANIA_CONTRIBS_TABLE		=> 'c',
				TITANIA_REVISIONS_TABLE		=> 'r',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($ariel_prefix . 'contrib_topics' => 'ct'),
					'ON'	=> 'ct.topic_type = 4 AND ct.contrib_id = q.contrib_id'
				)
			),
			'WHERE' => 'c.contrib_id = q.contrib_id
				AND r.revision_id = q.revision_id',
			'ORDER_BY' => 'queue_id DESC',
		);
		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$ignore = array(-3, -4, -5, -6);
			if (in_array($row['queue_status'], $ignore))
			{
				// Skip revisions that were canned, etc
				continue;
			}

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

		$display_message = 'Queue';
	break;

	case 6 :
		$sql_ary = $contrib_ids = array();
		$sql = 'SELECT contrib_id FROM ' . TITANIA_CONTRIB_IN_CATEGORIES_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('category_id', array(13, 14, 15, 16));
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			$contrib_ids[] = $row['contrib_id'];
		}
		phpbb::$db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . TITANIA_REVISIONS_PHPBB_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('contrib_id', $contrib_ids);
		phpbb::$db->sql_query($sql);

		$versions = array(
			array(20, '23'),
			array(30, '0'),
			array(30, '1'),
			array(30, '2'),
			array(30, '3'),
			array(30, '4'),
			array(30, '5'),
			array(30, '6'),
			array(30, '7'),
			array(30, '7-pl1'),
		);

		$sql = 'SELECT contrib_id, revision_id, revision_validated FROM ' . TITANIA_REVISIONS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('contrib_id', $contrib_ids);
		$result = phpbb::$db->sql_query($sql);
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			foreach ($versions as $data)
			{
				$sql_ary[] = array(
					'contrib_id'				=> $row['contrib_id'],
					'revision_id'				=> $row['revision_id'],
					'phpbb_version_branch'		=> $data[0],
					'phpbb_version_revision'	=> get_real_revision_version($data[1]),
					'revision_validated'		=> $row['revision_validated'],
				);
			}
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$db->sql_multi_insert(TITANIA_REVISIONS_PHPBB_TABLE, $sql_ary);

		$display_message = 'Smilies, Avatars, Ranks, Styles Misc support the latest phpBB';
	break;

	case 7 :
		$sync = new titania_sync;

		$sync->authors('count');

		$display_message = 'Authors';
	break;

	case 8 :
		$sync = new titania_sync;

		$sync->contribs('validated');

		$sync->categories('count');

		$display_message = 'Syncing';
	break;

	case 9 :
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
