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
	protected $sql_table		= TITANIA_REVISIONS_TABLE;

	/**
	 * SQL identifier field
	 *
	 * @var string
	 */
	protected $sql_id_field		= 'revision_id';

	/**
	 * Constructor class for titania faq
	 *
	 * @param int $faq_id
	 */
	public function __construct($revision_id = false)
	{
		// Configure object properties
		$this->object_config = array_merge($this->object_config, array(
			'revision_id'			=> array('default' => 0),
			'contrib_id' 			=> array('default' => 0),
			'revision_validated'	=> array('default' => 0),
			'attachment_id' 		=> array('default' => 0),
			'revision_name' 		=> array('default' => '', 'max' => 255),
			'revision_time'			=> array('default' => (int) titania::$time),
			'validation_date'		=> array('default' => 0),
			'revision_version'		=> array('default' => ''),
		));

		if ($revision_id !== false)
		{
			$this->revision_id = $revision_id;
		}
	}

	/**
	 *
	 * @return unknown_type
	 */
	public function request_data()
	{
		$this->__set_array(array(
			'revision_name'			=> request_var('revision_name', '', true),
			'contrib_id'			=> (int) titania::$contrib->contrib_id,
			'revision_validated'	=> request_var('contrib_validated', 0),
			'attachment_id'			=> request_var('attachment_id', 0),
		));
	}

	/**
	* Validate that all the data is correct
	*
	* @return array empty array on success, array with (string) errors ready for output on failure
	*/
	public function validate()
	{

	}

	/**
	 *
	 */
	public function display($revision_id = false)
	{
		// @todo Hanlde unvalidate and validated revisions basesd on if this is a team member, author or user.
		$sql = 'SELECT *
			FROM ' . $this->sql_table . '
			WHERE contrib_id = ' . (int) titania::$contrib->contrib_id .
			(($revision_id !== false) ? ' AND revision_id = ' . (int) $revision_id : '');
		$result = phpbb::$db->sql_query($sql);

		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			phpbb::$template->assign_block_vars('revisions', array(
				'REVISION_ID'		=> $row['revision_id'],
				'CREATED'			=> phpbb::$user->format_date($row['revision_time']),
				// This may need to be changed when the queue is done.
				'VALIDATED_DATE'	=> ($row['validation_date']) ? phpbb::$user->format_date($row['validation_date']) : phpbb::$user->lang['NOT_VALIDATED'],

				'U_DELETE_REVISION'	=> $this->get_url('delete', $row['revision_id']),
			));
		}
		phpbb::$db->sql_freeresult($result);

		phpbb::$template->assign_var('IMG_ICON_DELETE', titania::$style_path . 'theme/images/delete.png');

	}

	/**
	 * Place holder. This function should make sure this new revision shows in the queue as well.
	 *
	 */
	public function submit()
	{
		// Submit the revision.
		parent::submit();
	}

	/**
	* Build view URL for revisions
	*
	* @param string $action
	* @param int $revision_id
	*
	* @return string
	*/
	public function get_url($action = '', $revision_id = false)
	{
		$url = titania::$contrib->get_url('revisions');
		$revision_id = (($revision_id) ? $revision_id : $this->revision_id);

		if ($action == 'create')
		{
			return titania_url::append_url($url, array('action' => $action));
		}

		return titania_url::append_url($url, array('action' => $action, 'r' => $revision_id));
	}
}