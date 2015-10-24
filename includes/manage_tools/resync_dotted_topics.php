<?php
/**
*
* This file is part of the phpBB Customisation Database package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

class resync_dotted_topics
{
	/**
	* Tool overview page
	*/
	function display_options()
	{
		return 'RESYNC_DOTTED_TOPICS';
	}

	/**
	* Run the tool
	*/
	function run_tool()
	{
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


		trigger_error('RESYNC_DOTTED_TOPICS_COMPLETE');
	}
}
