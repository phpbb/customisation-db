<?php
/**
*
* @package Titania
* @version $Id$
* @copyright (c) 2008 phpBB Customisation Database Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_TITANIA'))
{
	exit;
}

if (!class_exists('titania_database_object'))
{
	require TITANIA_ROOT . 'includes/core/object_database.' . PHP_EXT;
}

/**
* Class to abstract titania posts
* @package Titania
*/
class titania_post extends titania_database_object
{
	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table			= TITANIA_TOPICS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field			= 'topic_id';

	/**
	* Topic Type
	*
	* @var string ('tracker', 'queue', 'normal')
	*/
	protected $type = '';

	/**
	 * Constructor class for titania topics
	 *
	 * @param string $type The type of topic ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion
	 * @param int $topic_id The topic_id, 0 for making a new topic
	 */
	public function __construct($type, $topic_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'topic'					=> array('default' => 0),
			'topic_type'			=> array('default' => 0), // Post Type, TITANIA_POST_ constants
			'topic_access'			=> array('default' => 0), // Access level, TITANIA_ACCESS_ constants

			'topic_status'			=> array('default' => 0), // Topic Status, use tags from the DB
			'topic_sticky'			=> array('default' => false),
			'topic_locked'			=> array('default' => false),
			'topic_approved'		=> array('default' => true),
			'topic_reported'		=> array('default' => false), // True if any posts in the topic are reported

			'topic_user_id'			=> array('default' => 0),

			'topic_time'			=> array('default' => 0),
			'topic_deleted'			=> array('default' => 0), // Topic soft deleted; 0 for not deleted, timestamp if (when) last deleted

			'topic_posts'			=> array('default' => ''), // Post count; separated by : between access levels ('10:9:8' = 10 team; 9 Mod Author; 8 Public)

			'topic_subject'			=> array('default' => ''),
		));

		switch ($type)
		{
			case 'tracker' :
			case 'queue' :
				$this->type = $type;
			break;

			default :
				$this->type = 'normal';
			break;
		}

		$this->topic_id = $topic_id;
	}

	/**
	* Get the postcount for displaying
	*
	* @return int The post count for the current user's access level
	*/
	public function get_postcount()
	{
		$postcount = explode(':', $this->topic_posts);

		if (!isset($postcount[titania::$access_level]))
		{
			return 0;
		}

		return $postcount[$access_level];
	}
}
