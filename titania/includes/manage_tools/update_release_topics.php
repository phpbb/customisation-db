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
if (!defined('IN_PHPBB'))
{
	exit;
}

class update_release_topics
{
	/**
	* Tool overview page
	*/
	function display_options()
	{
		return 'UPDATE_RELEASE_TOPICS';
	}

	/**
	* Run the tool
	*/
	function run_tool()
	{
		// Define some vars that we'll need
		$start = request_var('start', 0);
		$limit = 100;

		// Create topic if it does not exist?
		$create_topic = true;

		titania::_include('functions_posting', 'phpbb_posting');
		titania::add_lang('contributions');

		$types = array();
		foreach (titania_types::$types as $id => $class)
		{
			if ($class->forum_robot && $class->forum_database)
			{
				$types[] = $id;
			}
		}

		if (!sizeof($types))
		{
			trigger_back('UPDATE_RELEASE_TOPICS_COMPLETE');
		}

		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) . '
				AND ' . phpbb::$db->sql_in_set('contrib_type', $types);
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		// Grab our batch
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_user_id, c.contrib_type, c.contrib_name, c.contrib_name_clean, c.contrib_desc, c.contrib_desc_uid, c.contrib_release_topic_id,
				t.topic_first_post_id,
				u.user_id, u.username, u.username_clean, u.user_colour',

			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE	=> 'c',
				USERS_TABLE				=> 'u',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TOPICS_TABLE	=> 't'),
					'ON'	=> 't.topic_id = c.contrib_release_topic_id',
				),
			),

			'GROUP_BY'	=> 'c.contrib_id',

			'WHERE'		=> phpbb::$db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) . '
				AND u.user_id = c.contrib_user_id
				AND ' . phpbb::$db->sql_in_set('contrib_type', $types),

			'ORDER_BY'	=> 'c.contrib_id DESC',
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Grab the revisions
			$revisions = array();
			$sql = 'SELECT r.revision_id, r.attachment_id, r.revision_version, a.real_filename, a.filesize
				FROM ' . TITANIA_REVISIONS_TABLE . ' r, ' . TITANIA_ATTACHMENTS_TABLE . ' a
				WHERE r.contrib_id = ' . $row['contrib_id'] . '
					AND r.revision_status = ' . TITANIA_REVISION_APPROVED . '
					AND a.attachment_id = r.attachment_id';
			$rev_result = phpbb::$db->sql_query($sql);
			while ($rev_row = phpbb::$db->sql_fetchrow($rev_result))
			{
				$revisions[$rev_row['revision_version']] = $rev_row;
			}
			phpbb::$db->sql_freeresult($rev_result);

			// Sort the revisions by their version, put the newest one in $revision
			uksort($revisions, 'reverse_version_compare');

			if (!sizeof($revisions))
			{
				continue;
			}

			$revision = array_shift($revisions);

			users_overlord::$users[$row['user_id']] = $row;

			$contrib = new titania_contribution();
			$contrib->__set_array($row);
			$contrib->download = $row;

			// Update the release topic
			$contrib->update_release_topic();
		}

		phpbb::$db->sql_freeresult($result);

		if (($start + $limit) >= $total)
		{
			trigger_back('UPDATE_RELEASE_TOPICS_COMPLETE');
		}
		else
		{
			meta_refresh(0, titania_url::build_url('manage/administration', array('t' => 'update_release_topics', 'start' => ($start + $limit), 'submit' => 1, 'hash' => generate_link_hash('manage'))));
			trigger_error(phpbb::$user->lang('UPDATE_RELEASE_TOPICS_PROGRESS', ($start + $limit), $total));
		}
	}
}