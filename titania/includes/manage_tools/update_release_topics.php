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
	* Number of posts to be updated per run
	*/
	var $step_size = 100;
	
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

		titania::_include('functions_posting', 'phpbb_posting');
		titania::add_lang('contributions');

		// Greb our batch
		$sql_ary = array(
			'SELECT'	=> 'c.contrib_id, c.contrib_user_id, r.revision_id, r.attachment_id, r.revision_version, p.post_id',
			
			'FROM'		=> array(
				TITANIA_CONTRIBS_TABLE	=> 'c',
				TITANIA_REVISIONS_TABLE => 'r',
				POSTS_TABLE				=> 'p',
				TOPICS_TABLE			=> 't',
				USERS_TABLE				=> 'u',
			),
			
			'WHERE'		=> "t.topic_id = p.topic_id AND u.user_id = c.contrib_user_id 
				AND c.contrib_release_topic_id = t.topic_id AND c.contrib_id = r.contrib_id
				AND t.topic_first_post_id  = p.post_id",
		);
		
		$sql	= phpbb::$db->sql_build_query('SELECT', $sql_ary);
		$result	= phpbb::$db->sql_query_limit($sql, $this->step_size, $start);
		$batch	= phpbb::$db->sql_fetchrowset($result);
		phpbb::$db->sql_freeresult($result);

		if (sizeof($batch))
		{
			// Walk through the batch
			foreach ($batch as $this->post)
			{
				// Get user data
				$user_ids = array($this->post['contrib_user_id']);
				users_overlord::load_users($user_ids);
				
				// Load the contribution
				$contrib = new titania_contribution();
				$contrib->load((int) $this->post['contrib_id']);
				$contrib->get_download($this->post['revision_id']);
				
				$body = sprintf(phpbb::$user->lang[titania_types::$types[$contrib->contrib_type]->create_public],
					$contrib->contrib_name,
					$contrib->author->get_url(),
					users_overlord::get_user($contrib->author->user_id, '_username'),
					titania_decode_message($contrib->contrib_desc, $contrib->contrib_desc_uid),
					$this->post['revision_version'],
					titania_url::build_url('download', array('id' => $this->post['attachment_id'])),
					$contrib->download['real_filename'],
					$contrib->download['filesize'],
					$contrib->get_url(),
					$contrib->get_url('support')
				);
				
				$options = array(
					'poster_id'		=> titania_types::$types[$contrib->contrib_type]->forum_robot,
					'forum_id' 		=> titania_types::$types[$contrib->contrib_type]->forum_database,
					'topic_id'		=> $contrib->contrib_release_topic_id,
					'post_id'		=> $this->post['post_id'],
					'topic_title'	=> $contrib->contrib_name,
					'post_text'		=> $body,
				);
				
				phpbb_posting('edit', $options);
			}
		}
		
		if ($start < $this->step_size)
		{
			trigger_back('UPDATE_RELEASE_TOPICS_COMPLETE');
		}
		else
		{
			meta_refresh(1, titania_url::build_url('manage/administration', array('t' => 'update_release_topics', 'start' => ++$start, 'submit' => 1, 'hash' => generate_link_hash('manage'))));
			trigger_error(phpbb::$user->lang('UPDATE_RELEASE_TOPICS_PROGRESS', $start));
		}
	}
}

?>