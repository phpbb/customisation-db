<?php
/**
*
* @package Titania
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

function titania_custom($action, $version)
{
	switch ($action)
	{
		case 'install' :
			switch ($version)
			{
				case '0.1.40' :
					titania_tags();
					titania_categories();
				break;
			}
		break;

		case 'update' :
			switch ($version)
			{
				case '0.1.34' :
					$sync = new titania_sync;
					$sync->topics('post_count');
				break;

				case '0.1.37' :
					$sync = new titania_sync;
					$sync->queue('revision_queue_id');
				break;

				case '0.1.47' :
					$sync = new titania_sync;
					$sync->topics('queue_discussion_category');
				break;

				case '0.1.49' :
					$sql = 'UPDATE ' . TITANIA_TOPICS_TABLE . ' SET topic_sticky = 1
						WHERE topic_type = ' . TITANIA_QUEUE_DISCUSSION;
					phpbb::$db->sql_query($sql);
				break;

				case '0.1.53' :
					$sql_ary = array();
					$sql = 'SELECT contrib_id, revision_id, phpbb_version FROM ' . TITANIA_REVISIONS_TABLE;
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$sql_ary[] = array(
							'revision_id'				=> $row['revision_id'],
							'contrib_id'				=> $row['contrib_id'],
							'phpbb_version_branch'		=> $row['phpbb_version'][0] . $row['phpbb_version'][2],
							'phpbb_version_revision'	=> get_real_revision_version(substr($row['phpbb_version'], 4)),
						);
					}
					phpbb::$db->sql_freeresult($result);

					phpbb::$db->sql_multi_insert(TITANIA_REVISIONS_PHPBB_TABLE, $sql_ary);
				break;

				case '0.1.55' :
					$validated = array();
					$sql = 'SELECT revision_id FROM ' . TITANIA_REVISIONS_TABLE . '
						WHERE revision_validated = 1';
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$validated[] = $row['revision_id'];
					}
					phpbb::$db->sql_freeresult($result);

					if (sizeof($validated))
					{
						$sql = 'UPDATE ' . TITANIA_REVISIONS_PHPBB_TABLE . '
							SET revision_validated = 1
							WHERE ' . phpbb::$db->sql_in_set('revision_id', $validated);
						phpbb::$db->sql_query($sql);
					}
				break;

				case '0.3.2' :
					$update = array();

					// Reset the status
					$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
						SET revision_status = 0';
					phpbb::$db->sql_query($sql);

					$sql = 'SELECT r.revision_id, q.queue_status FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_QUEUE_TABLE . ' q
						WHERE q.revision_id = r.revision_id';
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						switch ($row['queue_status'])
						{
							case TITANIA_QUEUE_DENIED :
								$update[TITANIA_REVISION_DENIED][] = $row['revision_id'];
							break;

							case TITANIA_QUEUE_APPROVED :
								$update[TITANIA_REVISION_APPROVED][] = $row['revision_id'];
							break;

							case TITANIA_QUEUE_NEW :
								$update[TITANIA_REVISION_NEW][] = $row['revision_id'];
							break;
						}
					}
					phpbb::$db->sql_freeresult($result);

					foreach ($update as $status => $revision_ids)
					{
						$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
							SET revision_status = ' . (int) $status . '
							WHERE ' . phpbb::$db->sql_in_set('revision_id', $revision_ids);
						phpbb::$db->sql_query($sql);
					}

					// Any that are left should be repacked
					$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
						SET revision_status = ' . TITANIA_REVISION_REPACKED . '
						WHERE revision_status = 0';
					phpbb::$db->sql_query($sql);
				break;

				case '0.3.3' :
					titania_sync::contribs('faq_count');
				break;

				case '0.3.9' :
					titania_sync::attachments('hash');
				break;

				case '0.3.11' :
					$with_preview = $needs_preview = array();

					//Need a list of all objects with previews
					$sql = 'SELECT object_id FROM ' . TITANIA_ATTACHMENTS_TABLE . '
						WHERE is_preview = 1 AND object_type = ' . TITANIA_SCREENSHOT;
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$with_preview[] = $row['object_id'];
					}
					phpbb::$db->sql_freeresult($result);

					//Now we get a list of attachments to update
					$sql = 'SELECT MIN(attachment_id) AS attachment_id, object_id FROM ' . TITANIA_ATTACHMENTS_TABLE . '
						WHERE is_preview = 0 AND object_type = ' . TITANIA_SCREENSHOT . (sizeof($with_preview) ? ' AND ' . phpbb::$db->sql_in_set('object_id', $with_preview, true) : '') . '
						GROUP BY object_id';
					$result = phpbb::$db->sql_query($sql);
					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$needs_preview[] = $row['attachment_id'];
					}
					phpbb::$db->sql_freeresult($result);

					//Finally let's update
					if (sizeof($needs_preview))
					{
						$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . ' SET is_preview = 1 WHERE ' . phpbb::$db->sql_in_set('attachment_id', $needs_preview);
						phpbb::$db->sql_query($sql);
					}
				break;

				case '0.3.16' :
				case '0.3.17' :
					$sql = 'SELECT DISTINCT topic_id, post_user_id
						FROM ' . TITANIA_POSTS_TABLE . '
						WHERE post_approved = 1 AND post_deleted = 0';
					$result = phpbb::$db->sql_query($sql);

					$data = array();
					$i = 0;

					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$batch = floor($i / 500);

						$data[$batch][] = array(
							'topic_id'		=> (int) $row['topic_id'],
							'user_id'		=> (int) $row['post_user_id'],
							'topic_posted'	=> 1
						);
						++$i;
					}
					phpbb::$db->sql_freeresult($result);

					if (sizeof($data))
					{
						phpbb::$db->sql_query('TRUNCATE ' . TITANIA_TOPICS_POSTED_TABLE);

						foreach ($data as $batch => $rows)
						{
							phpbb::$db->sql_multi_insert(TITANIA_TOPICS_POSTED_TABLE, $rows);
						}

						unset($data);
					}
				break;

				case '0.4.0' :
					$sql = 'UPDATE ' . TITANIA_TRACK_TABLE . '
						SET track_type = ' . TITANIA_ALL_SUPPORT . '
						WHERE track_type = ' . TITANIA_SUPPORT . '
							AND track_id = 0';
					phpbb::$db->sql_query($sql);
				break;

				case '0.4.1' :
					titania::_include('objects/category', false, 'titania_category');
					$category = new titania_category();
					$category->update_contrib_categories(false, false);
				break;

				case '0.4.2' :
					// Delete contribs using disallowed exclamation point character
					// As they were never accessible, there shouldn't be a risk of losing data
					$sql = 'DELETE FROM ' . TITANIA_CONTRIBS_TABLE . ' 
						WHERE contrib_name_clean LIKE "%!%"';
					$result = phpbb::$db->sql_query($sql);
				break;

				case '0.5.5' :
					// Set the correct revision_status for revisions that were resubmitted before the TITANIA_REVISION_SUBMITTED status was added.

					// Skip if the queue is not used at all
					if (!titania::$config->use_queue)
					{
						break;
					}
					$nonvalidation_types = titania_types::find_validation_free();

					$sql = 'SELECT r.revision_id
						FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_CONTRIBS_TABLE . ' c
						WHERE r.contrib_id = c.contrib_id AND r.validation_date = 0 AND r.revision_queue_id = 0
							AND r.revision_submitted = 1 AND r.revision_status = ' . TITANIA_REVISION_NEW . '
							AND ' . phpbb::$db->sql_in_set('c.contrib_type', $nonvalidation_types, true);
					$result = phpbb::$db->sql_query($sql);

					$affected_revisions = array();

					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$affected_revisions[] = (int) $row['revision_id'];
					}
					phpbb::$db->sql_freeresult($result);

					if (sizeof($affected_revisions))
					{
						$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . ' 
							SET revision_status = ' . TITANIA_REVISION_RESUBMITTED . '
							WHERE ' . phpbb::$db->sql_in_set('revision_id', $affected_revisions);
						phpbb::$db->sql_query($sql);
					}
				break;

				case '0.5.7' :
					$sql = 'UPDATE ' . TITANIA_REVISIONS_TABLE . '
						SET revision_status = ' . TITANIA_REVISION_APPROVED . ', validation_date = revision_time
						WHERE validation_date = 0 AND revision_queue_id = 0
							AND revision_submitted = 1 AND (revision_status = ' . TITANIA_REVISION_NEW . ' OR revision_status = ' . TITANIA_REVISION_APPROVED . ')';
					phpbb::$db->sql_query($sql);

					$nonvalidation_types = (titania::$config->use_queue) ? titania_types::find_validation_free() : array();
					$affected_contribs = array();

					$sql = 'SELECT DISTINCT c.contrib_id
						FROM ' . TITANIA_CONTRIBS_TABLE . ' c 
						LEFT JOIN ' . TITANIA_REVISIONS_TABLE . ' r
							ON (r.contrib_id = c.contrib_id)
						WHERE c.contrib_status = ' . TITANIA_REVISION_NEW . 
							((sizeof($nonvalidation_types)) ? ' AND ' . phpbb::$db->sql_in_set('c.contrib_type', $nonvalidation_types) : '') . '
							AND r.revision_status = ' . TITANIA_REVISION_APPROVED . '
							AND r.revision_submitted = 1';
					$result = phpbb::$db->sql_query($sql);

					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$affected_contribs[] = (int) $row['contrib_id'];
					}
					phpbb::$db->sql_freeresult($result);

					if (sizeof($affected_contribs))
					{
						$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
							SET contrib_status = ' . TITANIA_CONTRIB_APPROVED . '
							WHERE ' . phpbb::$db->sql_in_set('contrib_id', $affected_contribs);
						phpbb::$db->sql_query($sql);
					}
				break;

				case '0.5.9' :
					// Recalculate the filesize and hash for revisions that were cleaned on upload

					$sql = 'SELECT attachment_id, attachment_directory, physical_filename, filesize
						FROM ' . TITANIA_ATTACHMENTS_TABLE . '
						WHERE object_type = ' . TITANIA_CONTRIB . '
							AND filetime > 1272334920'; // Only worry about revisions submitted after Titania was released
					$result = phpbb::$db->sql_query($sql);

					while ($row = phpbb::$db->sql_fetchrow($result))
					{
						$file_location = titania::$config->upload_path . utf8_basename($row['attachment_directory']) . '/' . $row['physical_filename'];
						$filesize = (int) @filesize($file_location);
						$row['filesize'] = (int) $row['filesize'];

						// The zip file was cleaned...
						if ($filesize != $row['filesize'])
						{
							$hash = md5_file($file_location);

							$sql = 'UPDATE ' . TITANIA_ATTACHMENTS_TABLE . '
								SET filesize = ' . $filesize . ', hash = "' . phpbb::$db->sql_escape($hash) . '"
								WHERE attachment_id = ' . (int) $row['attachment_id'];
							phpbb::$db->sql_query($sql);
						}
					}
					phpbb::$db->sql_freeresult($result);
				break;
			}
		break;

		case 'uninstall' :
			// Uninstall the types (prevent errors)
			foreach (titania_types::$types as $class)
			{
				$class->uninstall();
			}

			titania_search::truncate();
		break;
	}

	return 'CUSTOM_ACTION_PERFORMED';
}

function fix_attachment_indices()
{
	// Get posts with inline attachments
	$sql_ary = array(
		'SELECT'	=> 'p.post_id, p.post_text, p.post_text_options, a.attachment_id, a.real_filename',

		'FROM' 		=> array(
			TITANIA_POSTS_TABLE => ' p',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(TITANIA_ATTACHMENTS_TABLE => 'a'),
				'ON'	=> 'a.object_id = p.post_id AND a.object_type = p.post_type',
			)
		),

		'WHERE'		=> phpbb::$db->sql_in_set('p.post_type', array(TITANIA_SUPPORT, TITANIA_QUEUE_DISCUSSION, TITANIA_QUEUE, TITANIA_FAQ)) . '
							AND p.post_text LIKE "%[attachment=%"'
	);

	$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
	$result = phpbb::$db->sql_query($sql);
	$posts = array();

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		if (!isset($posts[$row['post_id']]))
		{
			$posts[$row['post_id']] = $row;
			$posts[$row['post_id']]['attachments'] = array();
		}

		if (!empty($row['attachment_id']))
		{
			$posts[$row['post_id']]['attachments'][$row['attachment_id']] = $row['real_filename'];
		}
	}
	phpbb::$db->sql_freeresult($result);

	if (sizeof($posts))
	{
		foreach ($posts as $post_id => $data)
		{
			// Attachments missing or only has one attachment or BBCode is disabled, we can ignore it
			if (sizeof($data['attachments']) < 2 || !($data['post_text_options'] & OPTION_FLAG_BBCODE))
			{
				unset($posts[$post_id]);
				continue;
			}

			preg_match_all('#\[attachment=([0-9]+):(.*?)\]<!\-\- ia\1 \-\->(.*?)<!\-\- ia\1 \-\->\[\/attachment:\2\]#', $data['post_text'], $matches, PREG_PATTERN_ORDER);

			$replace = array();
			// Sort attachments by descending id
			krsort($data['attachments']);
			// Get indices
			$indices = array_flip(array_keys($data['attachments']));

			foreach ($matches[0] as $num => $original)
			{
				// Search the attachment list by the file name provided inside the bbcode tag
				$result = array_keys($data['attachments'], $matches[3][$num]);

				// If we don't have any results then we don't have a valid attachment
				// If we have more than one result, then that means that there are multiple attachments with the same name, so we can't do anything as 
				// we have no way of determining the proper order for them.
				if (sizeof($result) !== 1)
				{
					continue;
				}

				$index = $indices[$result[0]];

				// If the existing index doesn't match the index that we've just generated, then we'll need to replace it.
				if ($index != $matches[1][$num])
				{
					$replace['from'][] = $original;
					$replace['to'][] = "[attachment={$index}:{$matches[2][$num]}]<!-- ia{$index} -->{$matches[3][$num]}<!-- ia{$index} -->[/attachment:{$matches[2][$num]}]";
				}
			}

			if (isset($replace['from']))
			{
				$data['post_text'] = str_replace($replace['from'], $replace['to'], $data['post_text']);

				$sql = 'UPDATE ' . TITANIA_POSTS_TABLE . '
					SET post_text = "' . phpbb::$db->sql_escape($data['post_text']) . '"
					WHERE post_id = ' . (int) $post_id;
				phpbb::$db->sql_query($sql);
			}
			unset($posts[$post_id]);
		}
	}

	return 'CUSTOM_ACTION_PERFORMED';
}

function titania_tags()
{
	global $umil;

	// Empty the tag tables first
	$sql = 'DELETE FROM ' . TITANIA_TAG_TYPES_TABLE;
	phpbb::$db->sql_query($sql);
	$sql = 'DELETE FROM ' . TITANIA_TAG_FIELDS_TABLE;
	phpbb::$db->sql_query($sql);

	$tag_types = array(
		array(
			'tag_type_id'	=> TITANIA_QUEUE,
			'tag_type_name'	=> 'QUEUE_TAGS',
		)
	);

	$umil->table_row_insert(TITANIA_TAG_TYPES_TABLE, $tag_types);

	$tags = array(
		array(
			'tag_id'			=> 1,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_NEW',
			'tag_clean_name'	=> 'new',
			'no_delete'			=> true,
		),
		// Leave space for others if we need to hard-code any
		array(
			'tag_id'			=> 15,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_ATTENTION',
			'tag_clean_name'	=> 'attention',
			'no_delete'			=> false,
		),
		array(
			'tag_id'			=> 16,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_REPACK',
			'tag_clean_name'	=> 'repack',
			'no_delete'			=> false,
		),
		array(
			'tag_id'			=> 17,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_VALIDATING',
			'tag_clean_name'	=> 'validating',
			'no_delete'			=> false,
		),
		array(
			'tag_id'			=> 18,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_TESTING',
			'tag_clean_name'	=> 'testing',
			'no_delete'			=> false,
		),
		array(
			'tag_id'			=> 19,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_APPROVE',
			'tag_clean_name'	=> 'approve',
			'no_delete'			=> false,
		),
		array(
			'tag_id'			=> 20,
			'tag_type_id'		=> TITANIA_QUEUE,
			'tag_field_name'	=> 'QUEUE_DENY',
			'tag_clean_name'	=> 'deny',
			'no_delete'			=> false,
		),
	);

	$umil->table_row_insert(TITANIA_TAG_FIELDS_TABLE, $tags);
}

function titania_categories()
{
	global $umil;

	// Empty the categories table first
	$sql = 'DELETE FROM ' . TITANIA_CATEGORIES_TABLE;
	phpbb::$db->sql_query($sql);

	$categories = array(
		array(
			'category_id'	=> 1,
			'parent_id'		=> 0,
			'left_id'		=> 1,
			'right_id'		=> 20,
			'category_type'	=> 0,
			'category_name'	=> 'CAT_MODIFICATIONS',
			'category_name_clean'	=> 'modifications',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 2,
			'parent_id'		=> 0,
			'left_id'		=> 21,
			'right_id'		=> 32,
			'category_type'	=> 0,
			'category_name'	=> 'CAT_STYLES',
			'category_name_clean'	=> 'styles',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 3,
			'parent_id'		=> 1,
			'left_id'		=> 2,
			'right_id'		=> 3,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_COSMETIC',
			'category_name_clean'	=> 'cosmetic',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 4,
			'parent_id'		=> 1,
			'left_id'		=> 4,
			'right_id'		=> 5,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_TOOLS',
			'category_name_clean'	=> 'tools',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 5,
			'parent_id'		=> 1,
			'left_id'		=> 6,
			'right_id'		=> 7,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_SECURITY',
			'category_name_clean'	=> 'security',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 6,
			'parent_id'		=> 1,
			'left_id'		=> 8,
			'right_id'		=> 9,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_COMMUNICATION',
			'category_name_clean'	=> 'communication',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 7,
			'parent_id'		=> 1,
			'left_id'		=> 10,
			'right_id'		=> 11,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_PROFILE_UCP',
			'category_name_clean'	=> 'profile',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 8,
			'parent_id'		=> 1,
			'left_id'		=> 12,
			'right_id'		=> 13,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ADDONS',
			'category_name_clean'	=> 'addons',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 9,
			'parent_id'		=> 1,
			'left_id'		=> 14,
			'right_id'		=> 15,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ANTI_SPAM',
			'category_name_clean'	=> 'anti-spam',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 10,
			'parent_id'		=> 1,
			'left_id'		=> 16,
			'right_id'		=> 17,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_ENTERTAINMENT',
			'category_name_clean'	=> 'entertainment',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 11,
			'parent_id'		=> 1,
			'left_id'		=> 18,
			'right_id'		=> 19,
			'category_type'	=> 1,
			'category_name'	=> 'CAT_MISC',
			'category_name_clean'	=> 'misc',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 12,
			'parent_id'		=> 2,
			'left_id'		=> 22,
			'right_id'		=> 23,
			'category_type'	=> 2,
			'category_name'	=> 'CAT_BOARD_STYLES',
			'category_name_clean'	=> 'board_styles',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 13,
			'parent_id'		=> 2,
			'left_id'		=> 24,
			'right_id'		=> 25,
			'category_type'	=> 2,
			'category_name'	=> 'CAT_SMILIES',
			'category_name_clean'	=> 'smilies',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 14,
			'parent_id'		=> 2,
			'left_id'		=> 26,
			'right_id'		=> 27,
			'category_type'	=> 2,
			'category_name'	=> 'CAT_AVATARS',
			'category_name_clean'	=> 'avatars',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 15,
			'parent_id'		=> 2,
			'left_id'		=> 28,
			'right_id'		=> 29,
			'category_type'	=> 2,
			'category_name'	=> 'CAT_RANKS',
			'category_name_clean'	=> 'ranks',
			'category_desc'			=> '',
		),
		array(
			'category_id'	=> 16,
			'parent_id'		=> 2,
			'left_id'		=> 30,
			'right_id'		=> 31,
			'category_type'	=> 2,
			'category_name'	=> 'CAT_MISC',
			'category_name_clean'	=> 'misc',
			'category_desc'			=> '',
		),
	);

	$umil->table_row_insert(TITANIA_CATEGORIES_TABLE, $categories);
}
