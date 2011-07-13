<?php
/**
*
* @package Titania
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
	* This allows us to have multiple message items for a single object
	*
	* @var string
	*/
	public $message_fields_prefix = 'message';

	/**
	 * Submit data in the post_data format (from includes/tools/message.php)
	 *
	 * @param object $message The message object
	 */
	public function post_data($message)
	{
		$post_data = $message->request_data();

		// Handle different field usage
		if ($this->message_fields_prefix != 'message')
		{
			$post_data[$this->message_fields_prefix] = $post_data['message'];
			unset($post_data['message']);
		}

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
	public function generate_text_for_storage($allow_bbcode = false, $allow_urls = false, $allow_smilies = false)
	{
		$message = $message_uid = $message_bitfield = $message_options = false;
		$this->get_message_fields($message, $message_uid, $message_bitfield, $message_options);

		generate_text_for_storage($message, $message_uid, $message_bitfield, $message_options, $allow_bbcode, $allow_urls, $allow_smilies);

		$this->set_message_fields($message, $message_uid, $message_bitfield, $message_options);
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

		return titania_generate_text_for_display($message, $message_uid, $message_bitfield, $message_options);
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
			// Object types can be setup to grab the value of another field (such as $this->post_type) by setting $this->object_type to the field name (post_type)
			'object_type'	=> (is_string($this->object_type) && isset($this->{$this->object_type})) ? $this->{$this->object_type} : $this->object_type,
			'object_id'		=> $this->{$this->sql_id_field},
		);

		$message = $message_uid = $message_bitfield = $message_options = false;
		$this->get_message_fields($message, $message_uid, $message_bitfield, $message_options);

		titania_decode_message($message, $message_uid);

		$for_edit = array_merge($for_edit, array(
			'allow_bbcode'	=> ($message_options & OPTION_FLAG_BBCODE) ? 1 : 0,
			'allow_smilies'	=> ($message_options & OPTION_FLAG_SMILIES) ? 1 : 0,
			'allow_urls'	=> ($message_options & OPTION_FLAG_LINKS) ? 1 : 0,
			'text'			=> $message, // text is expected by some (it's the default for generate_text_for_edit)
			'message'		=> $message,
		));

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
					case $this->message_fields_prefix :
						$message = $this->$field;
					break;

					case $this->message_fields_prefix . '_uid' :
						$message_uid = $this->$field;
					break;

					case $this->message_fields_prefix . '_bitfield' :
						$message_bitfield = $this->$field;
					break;

					case $this->message_fields_prefix . '_options' :
						$message_options = $this->$field;
					break;
				}
			}
		}
	}

	private function set_message_fields($message, $message_uid, $message_bitfield, $message_options)
	{
		foreach ($this->object_config as $field => $options)
		{
			if (isset($options['message_field']))
			{
				switch ($options['message_field'])
				{
					case $this->message_fields_prefix :
						$this->$field = $message;
					break;

					case $this->message_fields_prefix . '_uid' :
						$this->$field = $message_uid;
					break;

					case $this->message_fields_prefix . '_bitfield' :
						$this->$field = $message_bitfield;
					break;

					case $this->message_fields_prefix . '_options' :
						$this->$field = $message_options;
					break;
				}
			}
		}
	}
}