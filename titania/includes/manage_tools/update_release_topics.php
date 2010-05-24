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
	* Contains the post that is currently updated
	*/
	var $post = array();

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
		$start	= request_var('start', 0);
		$total = 0;
		$limit = 100;

		titania::_include('functions_posting', 'phpbb_posting');
		titania::add_lang('contributions');

		$sql = 'SELECT COUNT(contrib_id) AS cnt FROM ' . TITANIA_CONTRIBS_TABLE . '
			WHERE contrib_status = ' . TITANIA_CONTRIB_APPROVED;
		phpbb::$db->sql_query($sql);
		$total = phpbb::$db->sql_fetchfield('cnt');
		phpbb::$db->sql_freeresult();
				
		// Greb our batch
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_user_id, c.contrib_type, c.contrib_name, c.contrib_name_clean, c.contrib_desc, c.contrib_desc_uid, c.contrib_release_topic_id,
				r.revision_id, r.attachment_id, r.revision_version, t.topic_first_post_id, a.real_filename, a.filesize',
			
			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE	=> 'c',
				TITANIA_REVISIONS_TABLE => 'r',
				TOPICS_TABLE			=> 't',
				USERS_TABLE				=> 'u',
				TITANIA_ATTACHMENTS_TABLE => 'a',
			),
			
			'WHERE'		=> "u.user_id = c.contrib_user_id 
				AND c.contrib_release_topic_id = t.topic_id AND c.contrib_id = r.contrib_id
				AND r.attachment_id = a.attachment_id",
		);
		
		$sql	= phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result	= phpbb::$db->sql_query_limit($sql, $limit, $start);
		
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			// Get user data
			users_overlord::load_users(array($row['contrib_user_id']));
			
			$contrib = new titania_contribution();
			$contrib->author = new titania_author();
			
			$contrib->__set_array($row);
			$contrib->author->user_id = $contrib->contrib_user_id;
			
			$contrib_desc = $contrib->contrib_desc;
			titania_decode_message($contrib_desc, $contrib->contrib_desc_uid);
			
			$body = sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->create_public],
				$contrib->contrib_name,
				$contrib->author->get_url(),
				users_overlord::get_user($contrib->author->user_id, '_username'),
				$contrib_desc,
				$contrib->revision_version,
				titania_url::build_url('download', array('id' => $row['attachment_id'])),
				$contrib->real_filename,
				$contrib->filesize,
				$contrib->get_url(),
				$contrib->get_url('support')
			);
			
			$options = array(
				'poster_id'		=> titania_types::$types[$contrib->contrib_type]->forum_robot,
				'forum_id' 		=> titania_types::$types[$contrib->contrib_type]->forum_database,
				'topic_id'		=> $contrib->contrib_release_topic_id,
				'post_id'		=> $contrib->topic_first_post_id,
				'topic_title'	=> $contrib->contrib_name,
				'post_text'		=> $body,
			);
			
			phpbb_posting('edit', $options);
		}
		
		phpbb::$db->sql_freeresult($result);
		
		if (($start + $limit) >= $total)
		{
			trigger_back('UPDATE_RELEASE_TOPICS_COMPLETE');
		}
		else
		{
			meta_refresh(1, titania_url::build_url('manage/administration', array('t' => 'update_release_topics', 'start' => ($start + $limit), 'submit' => 1, 'hash' => generate_link_hash('manage'))));
			trigger_error(phpbb::$user->lang('UPDATE_RELEASE_TOPICS_PROGRESS', $start));
		}
	}
}

?>