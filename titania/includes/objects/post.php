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
	protected $sql_table			= TITANIA_POSTS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field			= 'post_id';

	/**
	* Post Type
	*
	* @var string ('tracker', 'queue', 'normal')
	*/
	protected $type = '';

	/**
	 * Constructor class for titania posts
	 *
	 * @param string $type The type of post ('tracker', 'queue', 'normal').  Normal/default meaning support/discussion
	 * @param int $post_id The post_id, 0 for making a new post
	 */
	public function __construct($type, $post_id = 0)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'post_id'				=> array('default' => 0),
			'topic_id'				=> array('default' => 0),
			'post_type'				=> array('default' => 0), // Post Type, TITANIA_POST_ constants
			'post_access'			=> array('default' => 0), // Access level, TITANIA_ACCESS_ constants

			'post_locked'			=> array('default' => false),
			'post_approved'			=> array('default' => true),
			'post_reported'			=> array('default' => false),

			'post_user_id'			=> array('default' => 0),
			'post_ip'				=> array('default' => 0),

			'post_time'				=> array('default' => 0),
			'post_edited'			=> array('default' => 0), // Post edited; 0 for not edited, timestamp if (when) last edited
			'post_deleted'			=> array('default' => 0), // Post soft deleted; 0 for not deleted, timestamp if (when) last deleted

			'post_subject'			=> array('default' => ''),
			'post_text'				=> array('default' => ''),
			'post_text_bitfield'	=> array('default' => ''),
			'post_text_uid'			=> array('default' => ''),
			'post_text_options'		=> array('default' => 7),
			'post_reason'			=> array('default' => ''), // Reason for deleting/editing
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

		$this->post_id = $post_id;
	}

	/**
	* @todo
	*
	* self reminder - check umil::remove_table, it could give an error during uninstallations
	*
	* Access Lists cache system (for authors)
	* 0-1000 in one file, 1001-2000 in another, etc
	* contrib_id => array(author_ids),
	* contrib_id => false, // For non-existing items (so we can skip the SQL query to recache the item if it is requested again)
	*/
}