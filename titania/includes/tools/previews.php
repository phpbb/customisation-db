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

class titania_previews extends titania_attachments
{
	public function __construct($object_type, $object_id = false)
	{
		parent::__construct(TITANIA_DOWNLOAD_CONTRIB, $object_type, $object_id);
	}

	/**
	* Build view URL for revisions
	*
	* @param string $action
	* @param int $revision_id
	*
	* @return string
	*/
	public function get_url($action = '', $preview_id = false)
	{
		$url = titania::$contrib->get_url('revisions');
		$preview_id = (($preview_id) ? $preview_id : $this->attachment_id);

		if ($action == 'create')
		{
			return titania_url::append_url($url, array('action' => $action));
		}

		return titania_url::append_url($url, array('action' => $action, 'p' => $preview_id));
	}
}