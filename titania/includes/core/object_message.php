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
 * Class providing basic interaction with the message tool
 * This extension expects you use at least message, message_uid, message_bitfield, and message_options fields.  If you do not use at least all of those do not use this extension
 *
 * @package Titania
 */
abstract class titania_message_object extends titania_database_object
{
	/**
	 * Description parsed for storage
	 *
	 * @var bool
	 */
	protected $description_parsed_for_storage = false;

	/**
	 * Submit data in the post_data format (from includes/tools/message.php)
	 *
	 * @param object $message The message object
	 */
	public function post_data($message)
	{
		$post_data = $message->request_data();

		foreach ($this->object_config as $field => $options)
		{
			if (isset($options['message_field']) && isset($post_data[$options['message_field']]))
			{
				$this->$field = $post_data[$options['message_field']];
			}
		}

		$this->generate_text_for_storage($post_data['bbcode_enabled'], $post_data['magic_url_enabled'], $post_data['smilies_enabled']);
	}

	/**
	 * Generate text for storing in the database
	 *
	 * @param bool $allow_bbcode
	 * @param bool $allow_urls
	 * @param bool $allow_smilies
	 */
	public function generate_text_for_storage($allow_bbcode, $allow_urls, $allow_smilies)
	{
		$message = $message_uid = $message_bitfield = $message_options = false;
		$this->get_message_fields($message, $message_uid, $message_bitfield, $message_options);

		generate_text_for_storage($message, $message_uid, $message_bitfield, $message_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->description_parsed_for_storage = true;
	}

	/**
	 * Parse text for display
	 *
	 * @return string Parsed message for output
	 */
	public function generate_text_for_display()
	{
		$message = $message_uid = $message_bitfield = $message_options = false;
		$this->get_message_fields($message, $message_uid, $message_bitfield, $message_options);

		return generate_text_for_display($message, $message_uid, $message_bitfield, $message_options);
	}

	/**
	 * Parse text for edit
	 *
	 * @return array of the items to be used in the message parser class
	 */
	public function generate_text_for_edit()
	{
		// Add the object type and object id
		$for_edit = array(
			'object_type'	=> $this->object_type,
			'object_id'		=> $this->{$this->sql_id_field},
		);

		$message = $message_uid = $message_bitfield = $message_options = false;
		$this->get_message_fields($message, $message_uid, $message_bitfield, $message_options);

		// Outputted via call to generate_text_for_edit()
		//'allow_bbcode'
		//'allow_smilies'
		//'allow_urls'
		//'text'
		$for_edit = array_merge($for_edit, generate_text_for_edit($message, $message_uid, $message_bitfield, $message_options));

		// Add any of the marked fields to the array
		foreach ($this->object_config as $field => $options)
		{
			if (isset($options['message_field']))
			{
				$for_edit[$options['message_field']] = $this->$field;
			}
		}

		return $for_edit;
	}

	private function get_message_fields(&$message, &$message_uid, &$message_bitfield, &$message_options)
	{
		foreach ($this->object_config as $field => $options)
		{
			if (isset($options['message_field']))
			{
				switch ($options['message_field'])
				{
					case 'message' :
						$message = $this->$field;
					break;

					case 'message_uid' :
						$message_uid = $this->$field;
					break;

					case 'message_bitfield' :
						$message_bitfield = $this->$field;
					break;

					case 'message_options' :
						$message_options = $this->$field;
					break;
				}
			}
		}
	}
}