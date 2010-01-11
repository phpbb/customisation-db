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
* Class to titania revision.
* @package Titania
*/
class titania_revision extends titania_database_object
{

	/**
	 * Attachment Object
	 *
	 * @var object
	 */
	public $attachment = '';

	/**
	 * SQL Table
	 *
	 * @var string
	 */
	protected $sql_table = TITANIA_REVISIONS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field = 'revision_id';

	/**
	* Contribution object
	*
	* @var object
	*/
	public $contrib = false;

	public function __construct($contrib, $revision_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'revision_id'			=> array('default' => 0),
			'contrib_id' 			=> array('default' => 0),
			'revision_validated'	=> array('default' => false),
			'attachment_id' 		=> array('default' => 0),
			'revision_name' 		=> array('default' => '', 'max' => 255),
			'revision_time'			=> array('default' => (int) titania::$time),
			'validation_date'		=> array('default' => 0),
			'revision_version'		=> array('default' => ''),
			'phpbb_version'			=> array('default' => ''),
			'install_time'			=> array('default' => 0),
			'install_level'			=> array('default' => 0),
			'revision_submitted'	=> array('default' => false),
		));

		$this->contrib = $contrib;
		$this->contrib_id = $this->contrib->contrib_id;
		$this->revision_id = $revision_id;
	}

	public function display($tpl_block = 'revisions')
	{
		phpbb::$template->assign_block_vars($tpl_block, array(
			'REVISION_ID'		=> $this->revision_id,
			'CREATED'			=> phpbb::$user->format_date($this->revision_time),
			'NAME'				=> censor_text($this->revision_name),
			'VERSION'			=> $this->revision_version,
			'VALIDATED_DATE'	=> ($this->validation_date) ? phpbb::$user->format_date($this->validation_date) : phpbb::$user->lang['NOT_VALIDATED'],

			'U_DOWNLOAD'		=> $this->get_url(),

			'S_VALIDATED'		=> (!$this->revision_validated && titania::$config->use_queue) ? false : true,
		));
	}

	/**
	 * Handle some stuff we need when submitting an attachment
	 */
	public function submit($contrib_name = '')
	{
		if ($this->revision_id && empty($this->sql_data))
		{
			throw new exception('Submitting an edit to a contribution item requires you load it through the $revision->load() method');
		}

		// Some stuff for new submissions
		if ($this->revision_id && !$this->sql_data['revision_submitted'] && $this->revision_submitted)
		{
			// Update the contrib_last_update if required here
			if (!titania::$config->require_validation)
			{
				$sql = 'UPDATE ' . TITANIA_CONTRIBS_TABLE . '
					SET contrib_last_update = ' . titania::$time . '
					WHERE contrib_id = ' . $this->contrib_id;
				phpbb::$db->sql_query($sql);
			}

			// Put in the queue if required
			if (titania::$config->use_queue)
			{
				titania::add_lang('manage');
				$post = new titania_post(TITANIA_QUEUE);
				$post->topic->contrib = $this->contrib;
				$post->__set_array(array(
					'post_subject'		=> $contrib_name . ' - ' . $this->revision_version,
					'post_text'			=> sprintf(phpbb::$user->lang['VALIDATION_POST'], $this->get_url()),
					'post_access'		=> TITANIA_ACCESS_AUTHORS,
				));
				$post->topic->__set_array(array(
					'contrib_id'		=> $this->contrib->contrib_id,
				));
				$post->submit();
			}
		}

		parent::submit();
	}

	/**
	 * Download URL
	 */
	public function get_url()
	{
		return titania_url::build_url('download', array('id' => $this->attachment_id));
	}
}
