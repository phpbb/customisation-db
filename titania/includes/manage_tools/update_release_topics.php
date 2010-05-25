<?php
/**
*
* @version $Id: update_release_topics.php 321 2010-03-06 06:27:41Z erikfrerejean $
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
		$create_topic = false;

		titania::_include('functions_posting', 'phpbb_posting');
		titania::add_lang('contributions');

		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE ' . phpbb::$db->sql_in_set('contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED));
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();

		// Grab our batch
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_user_id, c.contrib_type, c.contrib_name, c.contrib_name_clean, c.contrib_desc, c.contrib_desc_uid, c.contrib_release_topic_id,
				MAX(r.revision_id), r.attachment_id, r.revision_version,
				t.topic_first_post_id,
				u.user_id, u.username, u.username_clean, u.user_colour,
				a.real_filename, a.filesize',

			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE	=> 'c',
				TITANIA_REVISIONS_TABLE => 'r',
				USERS_TABLE				=> 'u',
				TITANIA_ATTACHMENTS_TABLE => 'a',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TOPICS_TABLE	=> 't'),
					'ON'	=> 't.topic_id = c.contrib_release_topic_id',
				),
			),

			'GROUP_BY'	=> 'c.contrib_id',

			'WHERE'		=> phpbb::$db->sql_in_set('c.contrib_status', array(TITANIA_CONTRIB_APPROVED, TITANIA_CONTRIB_DOWNLOAD_DISABLED)) . '
				AND r.contrib_id = c.contrib_id
				AND r.revision_status = ' . TITANIA_REVISION_APPROVED . '
				AND a.attachment_id = r.attachment_id
				AND u.user_id = c.contrib_user_id',

			'ORDER_BY'	=> 'c.contrib_id DESC',
		);

		$sql = phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result = phpbb::$db->sql_query_limit($sql, $limit, $start);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			users_overlord::$users[$row['user_id']] = $row;

			$contrib = new titania_contribution();
			$contrib->__set_array($row);

			$contrib_desc = $contrib->contrib_desc;
			titania_decode_message($contrib_desc, $contrib->contrib_desc_uid);

			$body = sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->create_public],
				$contrib->contrib_name,
				titania_url::build_url(users_overlord::get_user($row['user_id'], '_titania_profile')),
				users_overlord::get_user($row['user_id'], '_username'),
				$contrib_desc,
				$row['revision_version'],
				titania_url::build_url('download', array('id' => $row['attachment_id'])),
				$row['real_filename'],
				$row['filesize'],
				$contrib->get_url(),
				$contrib->get_url('support')
			);

			$options = array(
				'poster_id'		=> titania_types::$types[$contrib->contrib_type]->forum_robot,
				'forum_id' 		=> titania_types::$types[$contrib->contrib_type]->forum_database,
				'topic_title'	=> $contrib->contrib_name,
				'post_text'		=> $body,
			);

			if ($row['topic_first_post_id'])
			{
				$options = array_merge($options, array(
					'topic_id'		=> $contrib->contrib_release_topic_id,
					'post_id'		=> $row['topic_first_post_id'],
				));

				phpbb_posting('edit', $options);
			}
			else if ($create_topic)
			{
				$topic_id = phpbb_posting('post', $options);

				$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
					SET contrib_release_topic_id = ' . (int) $topic_id . '
					WHERE contrib_id = ' . $contrib->contrib_id;
				phpbb::$db->sql_query($sql);
			}
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