<?php
/**
 *
 * @package titania
 * @version $Id$
 * @copyright (c) 2008 phpBB Customisation Database Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

class mods_support_info
{
	function module()
	{
		return array(
			'modes'		=> array(
				'support'		=> array('title' => 'MODS_SUPPORT', 'auth' => ''),
				'view'			=> array('title' => 'MODS_VIEW_SUPPORT', 'auth' => ''),
				'post'			=> array('title' => 'MODS_POST_SUPPORT', 'auth' => ''),
				'edit'			=> array('title' => 'MODS_EDIT_SUPPORT', 'auth' => ''),
			),
		);
	}
}

?>